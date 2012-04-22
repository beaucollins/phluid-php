<?php

class Phluid_Exception extends Exception {
  
  function __construct( $message, $code = 0, Exception $previous = null ){
    parent::__construct( $message, $code, $previous );
  }
  
}

class Phluid_Exception_NotFound extends Phluid_Exception {
  
  function __construct( $message, $code = 404, $previous = null ){
    parent::__construct( $message, $code, $previous );
  }
  
}