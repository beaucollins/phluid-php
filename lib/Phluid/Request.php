<?php

class Phluid_Request {
 
  var $method;
  var $path;
  var $headers;
  var $params;
  var $memo = array();
  var $body;
 
  /**
   * Static method that constructs a Phluid_Request from global $_SERVER 
   * variables for use with Apache/Nginx.
   *
   * @return Phluid_Request
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
    
    $request = new Phluid_Request( $_SERVER['REQUEST_METHOD'], $path, $_SERVER );
    $request->setBody( @file_get_contents('php://input') );
    
    return $request;
  }
  
  /**
   * Constructs a Phluid_Request
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
    return Phluid_Utils::array_val( $this->headers, strtoupper($key) );
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
    } else {
      return $_GET[$key];
    }
  }
  
  /**
   * Compares the request's path with the a route's pattern
   * Stores the parameters as an associative array and returns true
   * if there is a match.
   *
   * @param  string  $pattern
   * @return boolean
   */
  public function parsePath( $pattern ){
    // first find if the pattern has any variables in it
    if ( strpos( $pattern, ":" ) || strpos( $pattern, '*' ) ) {
      // turn colon parameters ":name" into named sub-patterns
      $regex_pattern = $this->compileRegex( $pattern );
      $count = preg_match( $regex_pattern, $this->path, $matches );
      $this->params = $matches;
      return $count > 0;
    } else {
      return $pattern == $this->path || $pattern . '/' == $this->path;
    }
    
  }
  
  /**
   * Given a $pattern "/users/:name" it will turn it into a regex that can be used
   * to compare against request paths.
   *
   * @param  string $pattern
   * @return string
   */
  public function compileRegex( $pattern ){
    // sorry about the magic here
    $regex_pattern = preg_replace( "/\.:/", "\\.:", $pattern );
    $regex_pattern = preg_replace( "#(/)?:([\w]+)(\?)?#", '($1(?<$2>[^/]+))$3', $regex_pattern );
    return '#' . $regex_pattern . '#';
  }
  
  public function withPrefix( $prefix ){
    if ( stripos( $this->path, $prefix ) === 0 ) {
      $new_path = substr( $this->path, strlen( $prefix ) );
      return new Phluid_Request( $this->method, $new_path, $this->headers, $this->body );
    } else {
      return $this;
    }
  }
  
}