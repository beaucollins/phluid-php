<?php

namespace Phluid\Http;
use React\Socket\ConnectionInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;
use Evenement\EventEmitter;

class Request extends EventEmitter implements ReadableStreamInterface {
  
  private $conn;
  private $headers;
  private $memo;
  
  public $path;
  public $method;
  public $query = array();
  
  private $readable = true;
  
  function __construct( ConnectionInterface $conn ){
    $this->conn = $conn;
    $this->memo = array();
    
    $parser = new HeaderParser( $conn );
    
    $parser->on( 'headers', function( $headers, $trailing ){
      $contentLength = 0;
      $this->headers = $headers;
      if ( strpos( $headers->path, '?' ) ) {
        list( $path, $querystring ) = explode( '?', $headers->path, 2 );
        $this->path = $path;
        parse_str( $querystring, $query );
        $this->query = $query;
      } else {
        $this->path = $headers->path;
      }
      $this->method = $headers->method;
      
      
      $this->emit( 'headers', array( $headers , $trailing ) );
      
      if ( $this->expectsBody() ) {
        if ( $trailing && strlen( $trailing ) > 0 ) {
          $contentLength += strlen( $trailing );
          $this->emit( 'data', array( $trailing ) );
        }
        $totalLength = $this->getContentLength();
        $this->conn->on( 'data', function( $data ) use ( &$contentLength, $totalLength ){
          // TODO: Chunk encoding
          // TODO: Length exceeds Content-Length header 401
          $contentLength += strlen( $data );
          $this->emit( 'data', array( $data ) );
          if ( $contentLength == $totalLength ) {
            $this->close();
          }
          
        } );
      } else {
        $this->close();
      }
      
    } );    
  }
  
  public function __toString(){
    return $this->getMethod() . ' ' . $this->getPath() . $this->getQuerystring();
  }
  
  private function expectsBody(){
    $method = $this->getMethod();
    return $method != 'GET' && $method != 'HEAD';
  }
    
  public function getHeaders(){
    return $this->headers;
  }
  
  public function getHeader( $header ){
    return $this->headers[$header];
  }
  
  public function getPath(){
    return $this->path;
  }
  
  public function getMethod(){
    return $this->method;
  }
  
  public function getHost(){
    return $this->headers['host'];
  }
  
  public function getQuerystring( $prefix = '?' ){
    $query = http_build_query( $this->query );
    if ( $query != "" && $prefix ) {
      $query = $prefix . $query;
    }
    return $query;
  }
  
  public function getContentLength(){
    $contentLength = $this->headers['content-length'];
    if ( $contentLength != null ) {
      return (int) $contentLength;
    }
  }
  
  public function __get( $key ){
    if ( $this->__isset( $key ) ) {
      return $this->memo[$key];
    }
  }
  
  public function __set( $key, $value ){
    $this->memo[$key] = $value;
  }
  
  public function __isset( $key ){
    array_key_exists( $key, $this->memo );
  }
  
  public function __unset( $key ){
    unset( $this->memo[$key] );
  }
  
  public function isReadable(){
    return $this->readable;
  }
  
  public function pause(){
    $this->conn->pause();
    $this->emit( 'pause' );
  }
  
  public function resume(){
    $this->conn->resume();
    $this->emit( 'resume' );
  }
  
  public function close(){
    $this->readable = false;
    $this->emit( 'end' );
    $this->removeAllListeners();
  }
  
  public function pipe( WritableStreamInterface $dest, array $options = array() ){
    Util::pipe( $this, $dest, $options );
    return $dest;
  }
  
}