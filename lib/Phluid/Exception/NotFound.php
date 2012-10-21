<?php

namespace Phluid;

class Exception_NotFound extends Exception {
  
  function __construct( $message, $code = 404, $previous = null ){
    parent::__construct( $message, $code, $previous );
  }
  
}