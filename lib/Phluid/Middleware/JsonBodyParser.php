<?php

namespace Phluid\Middleware;

class JsonBodyParser {
  
  private $array;
  
  function __construct( $array = true ){
    $this->array = $array;
  }
  
  //Just JSON
  function __invoke( $request, $response, $next ){
    if ( strpos( $request->getBody(), "{") === 0 || $request->getHeader('Content-Type') == 'application/json' ) {
      try {
        $request->setBody( json_decode( $request->getBody(), $this->array ) );
      } catch (Exception $e) {
        
      }
    }
    $next();
  }
  
}