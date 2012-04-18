<?php

class Request {
 
  var $method;
  var $path;
  var $headers;
 
  public static function fromServer(){
    $request = new Request( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER );
    return $request;
  }
  
  public function __construct( $method, $path, $headers = array() ){
    $this->method = $method;
    $this->path = $path;
    $this->headers = $headers;
  }
  
}