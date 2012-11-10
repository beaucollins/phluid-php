<?php

namespace Phluid\Exception;
use Phluid\Exception;

class NotFound extends Exception {
  
  function __construct( $message, $code = 404, $previous = null ){
    parent::__construct( $message, $code, $previous );
  }
  
}