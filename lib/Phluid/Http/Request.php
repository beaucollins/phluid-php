<?php

namespace Phluid\Http;
use React\Http\Request as ReactRequest;
use React\Socket\ConnectionInterface;

class Request extends ReactRequest {
  
  private $memo;
  
  public $path;
  public $method;
  public $query = array();
  
  private $readable = true;
  
  public $headers;
  
  public function __construct($method, $path, $query = array(), $version = '1.1', $headers = array()) {
    parent::__construct( $method, $path, $query, $version, $headers );
    $this->memo = array();
    $this->headers = new Headers( $method, $path, $version, $headers);
  }
  
  public function __toString(){
    return $this->getMethod() . ' ' . $this->getPath() . $this->getQuerystring();
  }
  
  private function expectsBody(){
    $method = $this->getMethod();
    return $method != 'GET' && $method != 'HEAD';
  }
  
  public function param( $param ){
    if ( $this->params && array_key_exists( $param, $this->params ) ) {
      return $this->params[ $param ];
    } else if( $this->query && array_key_exists( $param, $this->query ) ){
      return $this->query[ $param ];
    }
  }
      
  public function getHeader( $header ){
    return $this->headers[$header];
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
  
  public function getContentType(){
    return $this->headers['content-type'];
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
    return array_key_exists( $key, $this->memo );
  }
  
  public function __unset( $key ){
    unset( $this->memo[$key] );
  }
  
}