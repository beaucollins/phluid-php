<?php

namespace Phluid\Middleware;

class JsonBodyParser {
  
  private $array = true;
  private $json_decode_options; // JSON_BIGINT_AS_STRING option
  private $depth;
  
  function __construct( $as_assoc_array = true, $depth = 512, $json_decode_options = JSON_BIGINT_AS_STRING ){
    $this->array = $as_assoc_array;
    $this->depth = $depth;
    $this->options = $json_decode_options;
  }
  
  //Just JSON
  function __invoke( $request, $response, $next ){
    if ( $request->getHeader('Content-Type') == 'application/json' ) {
      $body = "";
      $request->on( 'data', function( $data ) use ( &$body ){
        $body .= $data;
      } );
      $request->on( 'end', function() use ( &$body, $request, $next ){
        $request->body = json_decode( $request->getBody(), $this->array );
        $next();
      } );
    } else {
      $next();      
    }
  }
  
}