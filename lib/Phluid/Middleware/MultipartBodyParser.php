<?php
namespace Phluid\Middleware;
use Phluid\Http\Request;
use Evenement\EventEmitter;
use React\Stream\WritableStreamInterface;

define( 'MULTIPART_CONTENT_TYPE', 'multipart/form-data' );

class MultipartBodyParser {
  
  private $upload_dir;
  
  function __construct( $upload_dir = './tmp', $clean_after_request = true ){
    $this->upload_dir = $upload_dir;
    if(  !is_dir( $upload_dir ) ) mkdir( $upload_dir, 0777, true );
    
  }
  
  function __invoke( $request, $response, $next ){
    
    if ( $request->getMethod() == 'GET' || $request->getMethod() == 'HEAD') {
      return $next();
    }
    
    $content_type = $request->getContentType();
    if ( $boundary = self::parseBoundary( $content_type ) ) {
      // split the body on $boundary
      $parser = new MultipartStreamParser( $this->upload_dir, $boundary );
      $request->pipe( $parser );
      $parser->on( 'end', function( $content, $uploads ) use ( $request, $response, $next ){
        $response->once( 'close', function() use ( $uploads ){
          foreach ( $uploads as $upload ) {
            unlink( $upload->content );
          }
        } );
        $request->body = $content;
        $next();
      });
      
    } else {
      return $next();      
    }
    
    
  }
  
  public static function parseBoundary( $header_value ){
    if ( strpos( $header_value, MULTIPART_CONTENT_TYPE ) === false ) {
      return false;
    }
    list( $type, $boundary ) = explode( '; boundary=', $header_value );
    if ( $type == MULTIPART_CONTENT_TYPE && $boundary ) {
      return trim( $boundary );
    }
  }
  
}

define( "MULTIPART_BOUNDARY_PREFIX", "--");
define( "MULTIPART_BOUNDARY_SUFFIX", "\r\n");
define( "MULTIPART_HEADER_END_STRING", "\r\n\r\n");

class MultipartStreamParser extends EventEmitter implements WritableStreamInterface {
  
  private $buffer;
  private $directory;
  private $boundary;
  private $content = array();
  private $closed = false;
  private $writable = true;
  private $currentPart;
  private $uploads = array();
  
  public function __construct( $directory, $boundary ){
    
    $this->directory = $directory;
    $this->boundary = MULTIPART_BOUNDARY_PREFIX . $boundary;
    $this->buffer = new StringBuffer();
            
  }
  
  private function parse(){
    if ( $this->currentPart ) {
      // if we find the boundary, end it
      if ( !$this->buffer->containsString( $this->boundary ) ) {
        $this->currentPart->write( $this->buffer->readAll() );
      } else {
        // echo "Found the boundary " . $position .  PHP_EOL;
        $this->currentPart->end( $this->buffer->readTo( $this->boundary ) );
        $this->currentPart = null;
        $this->parse();
      }
    } else {
      $this->parseHeader();
    }
  }
  
  private function parseHeader(){
    if( !$this->buffer->containsString( $this->boundary ) ) return;
    $this->buffer->chop( $this->boundary );
    if ( !$this->buffer->containsString( MULTIPART_HEADER_END_STRING ) ) return;
    $header = $this->buffer->chop( MULTIPART_HEADER_END_STRING );
    // let's start parsing the part
    $this->currentPart = new MultipartPartParser( $header, $this->directory );
    // write the rest of the buffer to the part
    $this->currentPart->once( 'end', function( $part ){
      $this->addPartAtQueryPath( $part->name, $part, $this->content );
      if ( $part->isFile() ) {
        array_push( $this->uploads, $part );
      }
    });
    $this->parse();
    
  }
  
  public static function addPartAtQueryPath( $path, $object, &$container ){
    if ( $pos = strpos( $path, "[", 1 ) ) {
      $key = trim( substr( $path, 0, $pos ), "[]" );
      $path = substr( $path, $pos );
      self::addPartAtQueryPath( $path, $object,  $container[$key] );
    } else {
      $key = trim( $path, "[]" );
      if( $key == "" ){
        array_push( $container, $object );
      } else {
        $container[$key] = $object;
      }
    }
  }
  
  public function isWritable(){
    return $this->writable;
  }
  
  public function write( $data ){
    $this->buffer->write( $data );
    $this->parse();      
  }
  
  public function end( $data = null ){
    if ( is_string( $data ) ) {
      $this->write( $data );
    }
    $this->close();
  }
  
  public function close(){
    $this->closed = true;
    $this->writable = false;
    $this->buffer = "";
    $this->emit( 'end', array( $this->content, $this->uploads ) );
  }
  
}

class MultipartPartParser extends EventEmitter implements WritableStreamInterface {
  
  private $writable = true;
  private $closed = false;
  
  private $disposition;
  private $headers = array();
  private $part;
  private $resource;
  
  function __construct( $header, $directory ){
    $this->parseHeader( new StringBuffer( trim( $header ) ) );
    
    $this->part = new FormPart( $this->headers, $this->disposition );
    
    if ( $this->part->isFile() ) {
      $file = tempnam( $directory, $this->part->filename . '.' );
      $this->part->content = $file;
      $this->resource = fopen( $file, 'w' );
    } else {
      $this->content = "";
    }
    
  }
  
  private function parseHeader( $header ){
    while ( $chars = $header->chop( "\n" ) ) {
      if ( $chars == "" ) break;
      list( $name, $value ) = explode( ':', $chars, 2 );
      $this->headers[$name] = trim( $value );
    }
    
    $this->parseDisposition();
  }
  
  private function parseDisposition(){
    if( $disposition = $this->headers['Content-Disposition'] ){
      $disposition = new StringBuffer( $disposition );
      $this->disposition = array( 'type' => $disposition->chop( "; " ) );
      while( $data = $disposition->chop( "; " ) ){
        list( $key, $val ) = explode( "=", $data, 2 );
        $this->disposition[$key] = trim( $val, '"' );
      }
    }
    
  }
  
  public function isWritable(){
    return $this->writable;
  }
  
  public function write( $data ){
    if ( $this->part->isFile() ) {
      fputs( $this->resource, $data );
    } else {
      $this->part->content .= $data;
    }
  }
  
  public function end( $data = null ){
    if ( is_string( $data ) ) {
      $this->write( $data );
    }
    if ( $this->part->isFile() ) {
      fclose( $this->resource );
    }
    $this->close();
  }
  
  public function close(){
    $this->closed = true;
    $this->writable = false;
    $this->emit( 'end', array( $this->part ) );
  }
  
}

class FormPart {
  
  private $headers;
  private $disposition;
  public $content;
  
  public function __construct( $headers, $disposition ){
    $this->headers = $headers;
    $this->disposition = $disposition;
  }
  
  public function isFile(){
    return array_key_exists( 'filename', $this->disposition ) && $this->filename != "";
  }
  
  public function getType(){
    return $this->disposition['type'];
  }
  
  public function getName(){
    return $this->disposition['name'];
  }
  
  public function getFilename(){
    if ( array_key_exists( 'filename', $this->disposition ) ) {
      return $this->disposition['filename'];
    }
  }
  
  public function __get( $key ){
    if ( array_key_exists( $key, $this->disposition ) ) {
      return $this->disposition[$key];
    }
  }
  
  public function __toString(){
    return $this->content;
  }
  
}

class StringBuffer {
  
  private $buffer;
  
  public function __construct( $initial = "" ){
    $this->buffer = $initial;
  }
  
  public function write( $data ){
    $this->buffer .= $data;
  }
  
  public function read( $length = 0 ){
    $data = substr( $this->buffer, 0, $length );
    $this->buffer = substr( $this->buffer, $length );
    return $data;
  }
  
  public function readAll(){
    return $this->read( strlen( $this->buffer ) );
  }
  
  public function readTo( $string ){
    $position = strpos( $this->buffer, $string );
    if ( $position !== false ) {
      return $this->read( $position );
    }
    return "";
  }
  
  public function chop( $string ){
    $pos = strpos( $this->buffer, $string );
    if ( $pos === false ) {
      return $this->readAll();
    } else {
      $data = $this->read( $pos );
      // discard the string contents
      $this->read( strlen( $string ) ); 
      return $data;
    }
  }
  
  public function containsString( $string ){
    return strpos( $this->buffer, $string ) !== false;
  }
  
}