<?php

namespace Phluid;

class Request {
 
  var $method;
  var $path;
  var $headers;
  var $params = array();
  var $memo = array();
  var $body;
 
  /**
   * Static method that constructs a Request from global $_SERVER 
   * variables for use with Apache/Nginx.
   *
   * @return Request
   * @author Beau Collins
   */
  public static function fromServer(){
    
    $uri = $_SERVER['REQUEST_URI'];
    $query_position = strpos( $uri, '?' );
    if ($query_position) {
      $path = substr( $uri, 0, $query_position );
    } else {
      $path = $uri;
    }
    
    $request = new Request( $_SERVER['REQUEST_METHOD'], $path, $_SERVER );
    $request->setBody( @file_get_contents('php://input') );
    
    return $request;
  }
  
  /**
   * Constructs a Request
   *
   * @param string $method HTTP Method
   * @param string $path Path for the HTTP request
   * @param string $headers Array of HTTP request headers
   * @param string $body Body for the HTTP request
   * @author Beau Collins
   */
  public function __construct( $method, $path, $headers = array(), $body=null ){
    $this->method = $method;
    $this->path = $path;
    $this->headers = $headers;
    $this->body = $body;
  }
  
  public function getBody(){
    return $this->body;
  }
  
  public function setBody( $body ){
    $this->body = $body;
  }
  
  public function getHeader( $key ){
    return Utils::array_val( $this->headers, strtoupper($key) );
  }
  
  public function __get( $key ){
    if ( array_key_exists( $key, $this->memo ) ) {
      return $this->memo[$key];
    }
  }
  
  public function __set( $key, $value ){
    $this->memo[$key] = $value;
  }
  
  public function __toString(){
    return $this->method . ' ' . $this->path;
  }
  
  /**
   * Searches for a parameter, first from the path, then from get, then from post
   *
   *
   */
  public function param( $key ){
    if ( array_key_exists( $key, $this->params ) ) {
      return $this->params[$key];
    } else if( array_key_exists( $key, $_GET ) ) {
      return $_GET[$key];
    } else if( array_key_exists( $key, $_POST ) ) {
      return $_POST[$key];
    }
  }
  
}