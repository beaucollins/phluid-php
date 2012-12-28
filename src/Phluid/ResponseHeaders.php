<?php
namespace Phluid;

class ResponseHeaders extends Headers {
  
  public $status;
  public $success;
  
  function __construct( $status, $message ){
    $this->status = $status;
    $this->message = $message;
    parent::__construct( array() );
  }
  
  function __toString(){
    return (string) $this->status;
  }
  
}