<?php

namespace Phluid\Middleware;
use Phluid\Utils;
use Evenement\EventEmitter;

class StaticFiles {
  
  private $path;
  private $prefix;
  private $mimes = array(
    'css'      => 'text/css',
    'txt'      => 'text/plain',
    'jpg'      => 'image/jpeg',
    'jpeg'     => 'image/jpeg',
    'png'      => 'image/png',
    'html'     => 'text/html',
    'htm'      => 'text/htm',
    'js'       => 'text/javascript',
    'gif'      => 'image/gif',
    'json'     => 'application/json',
    'manifest' => 'text/cache-manifest'
  );
  
  public $defaultMimeType = "application/octet-stream";
  
  function __construct( $path, $prefix = '/', $mimes = array() ){
    $this->path = substr( $path, -1 ) == '/' ? substr( $path, 0, -1 ) : $path;
    $this->mimes = array_merge( $this->mimes, $mimes );
    $this->prefix = substr( $prefix, -1 ) == '/' ? $prefix : $prefix . '/';
  }
  
  function __invoke( $request, $response, $next ){
    if ( $this->isStaticRequest( $request ) ) {
      if( $path = $this->pathForRequest( $request ) ){
        $file_info = pathinfo( $path );
        $content_type = $this->mimeForFileSuffix( $file_info['extension'] );
        $response->setHeader( 'Content-Type', $content_type );
        $range = $request->getHeader( 'Range' );
        $ifRange = $request->getHeader( 'If-Range' );
        if ( !empty( $range ) && 0 === strpos( $range, 'bytes=' ) ) {
          // only send the portion of the file requested
          $ranges = $this->parseRanges( $range );
          $this->sendRanges( $response, $content_type, $path, $ranges );
        } else {
          $response->sendFile( $path );
        }
        return;
      } else {
        $next();      
      }
    } else {
      $next();      
    }
  }
  
  public static function parseRanges( $range ){
    $range_strings = explode( ",", substr( $range, strlen( 'bytes=' ) ) );
    $ranges = array();
    foreach ( $range_strings as $range ) {
      array_push( $ranges, Range::parseRange( $range ) );
    }
    return $ranges;
  }
  
  public static function sendRanges( $response, $content_type, $path, $ranges ){
    
    $streamer = new PartialFileStreamer( $response, $content_type, $path, $ranges );
    $streamer->send();
  }
  
  private function isStaticRequest( $request ){
    return strpos( $request->getPath(), $this->prefix ) === 0 && $request->getMethod() == 'GET';
  }
  
  private function pathForRequest( $request ){
    $path = $this->path . $request->getPath();
    if ( is_readable( $path ) && !is_dir( $path ) ) {
      return $path;
    }
    return false;
  }
  
  private function mimeForFileSuffix( $suffix ){
    $suffix = strtolower( $suffix );
    if ( array_key_exists( $suffix, $this->mimes ) ) {
      return $this->mimes[strtolower( $suffix )];
    } else {
      return $this->defaultMimeType;
    }
  }
    
}

class PartialFileStreamer {
  
  function __construct( $response, $content_type, $path, $ranges ){
    $this->response = $response;
    $this->path = $path;
    $this->ranges = $ranges;
    $this->content_type = $content_type;
  }
  
  public function send(){
    $handle = fopen( $this->path, 'r' );
    $info = fstat( $handle );
    $size = $info['size'];
    $modifiedDate = \DateTime::createFromFormat( 'U', $info['mtime'] );
    
    if ( ! $this->validRanges( $size ) ) {
      $this->response->setStatus( 416 );
      $this->response->setHeader( 'Content-Range', '*/' . $size );
      $this->response->end();
      return; 
    }
    
    $this->response->setStatus( 206 );
    $this->response->setHeader( 'Last-Modified', $modifiedDate->format( \DateTime::RFC1123 ) );
    
    $this->response->on( 'end', function() use ( $handle ){
      fclose( $handle );
    });
    
    // if a single range is requested then response is not multipart
    if ( count( $this->ranges ) == 1 ) {
      $range = $this->ranges[0];
      $boundary = $range->boundaryForSize( $size );
      $this->response->setHeader( 'Content-Range', "$boundary[0]-$boundary[1]/$size" );
      $this->response->setHeader( 'Content-Length', ($boundary[1]+1) - $boundary[0]);
            
      $this->response->sendHeaders( 206 );
      $part = new RangePart( $this->response, $this->content_type, null, $handle, $range );
      $part->once( 'end', function(){
        $this->response->end();
      });
      $part->send( true );
      
      
    } else {
      // multipart
      $boundary = 'PHLUID-BRS-' . Utils::uid( 24 );
      $this->response->setHeader( 'Content-Type', "multipart/byteranges; boundary=$boundary" );
      $parts = RangePart::partsFromRanges( $this->response, $this->content_type, $boundary, $handle, $this->ranges );
      $contentLength = $this->contentLengthForParts( $parts );
      $contentLength += strlen( "--$boundary--" );
      $this->response->sendHeaders( 206, array( 'Content-Length' => $contentLength ) );
      $this->writeParts( $parts, $boundary );
    }
        
  }
  
  private function writeParts( $parts, $boundary ){
    // write the boundary
    if( $part = array_shift( $parts ) ){
      $part->once( 'end', function() use ( $parts, $boundary ){
        $this->writeParts( $parts, $boundary );
      } );
      $part->send();
    } else {
      $this->response->end("--$boundary--");
    }
  }
  
  private function validRanges( $size ){
    foreach ( $this->ranges as $range ) {
      if ( ! $range->isValidForSize( $size ) ) return false;
    }
    return true;
  }
  
  public static function contentLengthForParts( $parts ){
    $total = 0;
    foreach ( $parts as $part ) {
      $total += $part->contentLength();
    }
    return $total;
  }
  
}

class RangePart extends EventEmitter {
  
  private $written = 0;
  private $total;
  
  public static function partsFromRanges( $response, $content_type, $boundary, $handle, $ranges ){
    $parts = array();
    foreach( $ranges as $range ){
      array_push( $parts, new self( $response, $content_type, $boundary, $handle, $range ) );
    }
    return $parts;
  }
  
  function __construct( $response, $content_type, $boundary, $handle, $range ){
    $this->response = $response;
    $this->handle = $handle;
    $this->range  = $range;
    $this->boundary = $boundary;
    $info = fstat( $handle );
    $boundary = $range->boundaryForSize( $info['size'] );
    $this->start = $boundary[0];
    $this->end = $boundary[1];
    $this->total = $this->end - $this->start + 1;
    $this->file_size = $info['size'];
    $this->content_type = $content_type;
  }
  
  function send(){
    rewind( $this->handle );
    if( $this->start > 0 ) fread( $this->handle, $this->start );
    $send = function() {
      $length = min( $this->total, 2048 );
      while( $string = fread( $this->handle, $length ) ){
        $this->written += strlen( $string );
        $buffered = $this->response->write( $string );
        if ( feof( $this->handle ) || $this->total == $this->written ) {
          if( $this->boundary != null ) $this->response->write("\r\n");
          $this->emit( 'end' );
          return;
        }
        if ( !$buffered ) break;
      }
    };
    $this->response->on( 'drain', $send );
    if( $this->boundary != null ){
      $this->response->write( $this->header() );
    }
    $send();
  }
  
  private function header(){
    return "--$this->boundary\r\nContent-Type: $this->content_type\r\nContent-Range: bytes $this->start-$this->end/$this->file_size\r\n\r\n";
  }
  
  public function contentLength(){
    $total = $this->total;
    if ( $this->boundary != null ) {
      $total += strlen( $this->header() ) + 2;
    }
    return $total;
  }
  
}

class Range {
  
  public static function parseRange( $string ){
    $start = null;
    $end = null;
    $index = strpos( $string, '-' );
    if ( 0 == $index ) {
      $start = intval( $string );
    } else if ( $index == strlen( $string ) ) {
      $start = intval( substr( $string, 0, -1 ) );
    } else {
      $parts = explode( "-", $string, 2 );
      $start = intval( $parts[0] );
      $end = intval( $parts[1] );
    }
    return new self( $start, $end );
  }
  
  function __construct( $start, $end = null ){
    $this->start = $start;
    $this->end = $end;
  }
  
  function boundaryForSize( $size ){
    if ( $this->start < 0 ) {
      return array( $this->start + $size, $size-1 );
    }
    
    if( $this->end == null ){
      return array( $this->start, $size-1 );
    }
    
    return array( $this->start, $this->end < $size ? $this->end : $size - 1 );
  }
  
  function isValidForSize( $size ){
    if ( $this->start < 0 && abs( $this->start ) < $size ) {
      return true;
    }
    
    if ( $this->end == null && $this->start < $size ) {
      return true;
    }
    
    return $this->start < $this->end && $this->start < $size;    
  }
  
}