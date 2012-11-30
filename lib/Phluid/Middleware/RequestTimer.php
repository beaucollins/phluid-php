<?php

namespace Phluid\Middleware;

class RequestTimer {
  
  public $header;
  
  public function __construct( $header_name = "X-RESPONSE-TIME" ){
    $this->header = $header_name;
  }
  
  public function __invoke( $request, $response, $next ){
    $start = microtime( TRUE );
    $response->once( 'headers', function() use ( $start, $response ){
      $duration = ceil( ( microtime( TRUE ) - $start) * 1000 );
      $response->setHeader( $this->header, "$duration ms" );
    } );
    $response->once( 'close', function() use ($start, $request ){
      $duration = ceil( ( microtime( TRUE ) - $start) * 1000 );
      echo "Completed in $duration ms " . PHP_EOL;
    });
    $next();
  }
  
}