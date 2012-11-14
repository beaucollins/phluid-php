<?php

namespace Phluid;

class Request {
 
  var $method;
  var $path;
  var $headers;
  var $params = array();
  var $memo = array();
  var $body;
  var $query = array();
   
  /**
   * Constructs a Request
   *
   * @param string $method HTTP Method
   * @param string $path Path for the HTTP request
   * @param string $headers Array of HTTP request headers
   * @param string $body Body for the HTTP request
   * @author Beau Collins
   */
  public function __construct( $method, $path, $query = array(), $headers = array(), $body=null ){
    $this->method  = $method;
    $this->path    = $path;
    $this->headers = array_change_key_case( $headers, CASE_UPPER );
    $this->body    = $body;
    $this->query   = $query;
  }
  
  public function getBody(){
    return $this->body;
  }
  
  public function setBody( $body ){
    $this->body = $body;
  }
  
  public function getHost(){
    return $this->getHeader('host') ?: $this->getHeader('http_host');
  }
  
  public function getHeader( $key ){
    return Utils::array_val( $this->headers, strtoupper($key) );
  }
  
  public function setHeader( $key, $val ){
    $this->headers[strtoupper( $key )] = $val;
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
    return $this->method . ' ' . $this->path . $this->queryString();
  }
  
  public function queryString( $prefix = '?' ){
    $query = http_build_query( $this->query );
    if ( $query != "" && $prefix ) {
      $query = $prefix . $query;
    }
    return $query;
  }
  
  /**
   * Searches for a parameter, first from the path, then from get, then from post
   *
   *
   */
  public function param( $key ){
    if ( array_key_exists( $key, $this->params ) ) {
      return $this->params[$key];
    } else if( is_array( $this->query ) && array_key_exists( $key, $this->query ) ) {
      return $this->query[$key];
    } else if( is_array( $this->body ) && array_key_exists( $key, $this->body ) ) {
      return $this->body[$key];
    }
  }
  
}