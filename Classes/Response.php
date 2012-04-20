<?php

require_once 'View.php';

class Response {

  private $raw_body;
  private $status_code = 200;
  private $headers = array();
  
  function __construct(){
    
  }
  
  public function render( $template, $locals = array(), $options = array() ){
    $layout = Utils::array_val( $options, 'template' );
    $status = Utils::array_val( $options, 'status', 200 );
    $content_type = Utils::array_val($options, 'content-type', 'text/html' );
    $view = new View( $template, $layout );
    $this->renderString( $view->render( $locals ), $content_type, $status );
  }
  
  public function setHeader( $key, $value ){
    $this->headers[trim(strtoupper($key))] = $value;
  }
  
  public function getHeader( $key ){
    $key = strtoupper( $key );
    if ( array_key_exists( $this->headers, $key ) ) {
      return $this->headers[$key];
    }
  }
  
  public function eachHeader( $callback ){
    foreach( $this->headers as $name => $value ){
      $callback( $name, $value );
    }
  }
  
  public function statusHeader(){
    return "HTTP/1.0 " . $this->statusMessage();
  }
  
  public function statusMessage(){
    return (string) $this->status_code;
  }
  
  public function redirect( $url ){
    $this->setHeader( "Location", $url );
    $this->raw_body = null;
  }
  
  public function renderString( $string, $content_type="text/plain", $status = 200 ){
    $this->status_code = $status;
    $this->raw_body = $string;
    $this->setHeader( 'Content-Type', $content_type );
  }
  
  public function renderJSON( $object, $status = 200 ){
    $this->renderString( json_encode($object), "application/json", $status );
  }
  
  public function getBody(){
    return $this->raw_body;
  }
}