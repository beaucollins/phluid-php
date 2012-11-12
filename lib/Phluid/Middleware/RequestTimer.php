<?php

namespace Phluid\Middleware;

class RequestTimer {
  
  public $header;
  
  public function __construct( $header_name = "X-RESPONSE-TIME" ){
    $this->header = $header_name;
  }
  
  public function __invoke( $request, $response, $next ){
    $start = microtime( TRUE );
    $next();
    $duration = ceil( ( microtime( TRUE ) - $start) * 1000 );
    $response->setHeader( $this->header, "$duration ms" );
  }
  
}