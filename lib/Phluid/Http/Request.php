<?php

namespace Phluid\Http;
use React\Socket\ConnectionInterface;
use React\Stream\ReadableStreamInterface;
use Evenement\EventEmitter;

class Request extends EventEmitter {
  
  private $conn;
  private $headers;
  private $memo;
  
  private $readable = true;
  
  function __construct( ConnectionInterface $conn ){
    $this->conn = $conn;
    $this->memo = array();
    
    $parser = new HeaderParser( $conn );
    
    $parser->on( 'headers', function( $headers, $trailing ){
      $contentLength = 0;
      $this->headers = $headers;
      $this->emit( 'headers', array( $headers , $trailing ) );
      
      if ( $this->expectsBody() ) {
        if ( $trailing && strlen( $trailing ) > 0 ) {
          $contentLength += strlen( $trailing );
          $this->emit( 'data', array( $trailing ) );
        }
        $totalLength = $this->getContentLength();
        $this->conn->on( 'data', function( $data ) use ( &$contentLength, $totalLength ){
          $contentLength += strlen( $data );
          $this->emit( 'data', array( $data ) );
          if ( $contentLength == $totalLength ) {
            $this->emit( 'end' );
          }
          
        } );
      } else {
        $this->emit( 'end' );
      }
      
    } );    
  }
  
  private function expectsBody(){
    $method = $this->getMethod();
    return $method != 'GET' && $method != 'HEAD';
  }
    
  function getHeaders(){
    return $this->headers;
  }
  
  function getPath(){
    return $this->headers->path;
  }
  
  function getMethod(){
    return $this->headers->method;
  }
  
  function getHost(){
    return $this->headers['host'];
  }
  
  function getContentLength(){
    $contentLength = $this->headers['content-length'];
    if ( $contentLength != null ) {
      return (int) $contentLength;
    }
  }
  
  function __get( $key ){
    if ( $this->__isset( $key ) ) {
      return $this->memo[$key];
    }
  }
  
  function __set( $key, $value ){
    $this->memo[$key] = $value;
  }
  
  function __isset( $key ){
    array_key_exists( $key, $this->memo );
  }
  
  function __unset( $key ){
    unset( $this->memo[$key] );
  }
  
}