<?php

namespace Phluid\Test;

class Request extends \Phluid\Http\Request {
  
  public function __construct($method, $path, $query = array(), $httpVersion = '1.1', $headers = array()) {
    parent::__construct( $method, $path, $query, $httpVersion, $headers );
    $this->emit( 'headers', array( $headers ) );
  }
  
  public function send( $body = null ){
    
    if ( $body != null ) {
      while( strlen( $body ) > 0 ){
        $part = substr( $body, 0, 1024 );
        $body = substr( $body, 1024 );
        $this->emit( 'data', array( $part ) );
      }
    }
    $this->close();
    
  }
  
  public function sendFile( $file ){
    $handle = fopen( $file, 'r' );
    while( $string = fread( $handle, 1024 ) ){
      $this->emit( 'data', array( $string ) );
    }
    fclose( $handle );
    $this->close();
  }
  
}
