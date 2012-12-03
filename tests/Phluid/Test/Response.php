<?php

namespace Phluid\Test;

class Response extends \Phluid\Http\Response {
  
  protected $body = "";
  
  function __construct( $request ){
    $conn = new Connection();
    parent::__construct( $conn, $request );
  }
  
  public function writeHead( $status = 200, $headers = array() ){
    parent::writeHead( $status, $headers );
    $this->captureBody = true;
  }
  
  function write( $data ){
    $this->body .= $data;
    parent::write( $data );
  }
  
  function getBody(){
    return $this->body;
  }
    
}
