<?php

class Request {
 
  var $method;
  var $path;
  var $headers;
  var $params;
 
  public static function fromServer(){
    
    $uri = $_SERVER['REQUEST_URI'];
    $query_position = strpos( $uri, '?' );
    if ($query_position) {
      $path = substr( $uri, 0, $query_position );
    } else {
      $path = $uri;
    }
    
    $request = new Request( $_SERVER['REQUEST_METHOD'], $path, $_SERVER );
    
    return $request;
  }
  
  public function __construct( $method, $path, $headers = array() ){
    $this->method = $method;
    $this->path = $path;
    $this->headers = $headers;
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
    $regex_pattern = preg_replace( "#(/)?:([\w]+)(\?)?#", '($1(?<$2>[^/]+))$3', $pattern );
    return '#' . $regex_pattern . '#';
  }
  
  public function withPrefix( $prefix ){
    if ( stripos( $this->path, $prefix ) === 0 ) {
      $new_path = substr( $this->path, strlen( $prefix ) );
      return new Request( $this->method, $new_path, $this->headers );
    } else {
      return $this;
    }
  }
  
}