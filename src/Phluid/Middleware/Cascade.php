<?php
namespace Phluid\Middleware;
/*
 * Given an array of middlewares it will perform each middleware by
 * cascading the request and response through each middleware
 */
class Cascade {
  
  private $middlewares;
  
  function __construct( $middlewares = array() ){
    $this->middlewares = $middlewares;
  }
  
  function __invoke( $request, $response, $next ){
    // copies the middleware array
    $middlewares = $this->middlewares;    
    $this->runMiddlewares( $middlewares, $request, $response, $next );
  }
  
  private function runMiddlewares( $middlewares, $request, $response, $final ){
    // pull off a middleware
    if( $middleware = array_shift( $middlewares ) ){
      $middleware( $request, $response, function() use( $middlewares, $request, $response, $final ){
        $this->runMiddlewares( $middlewares, $request, $response, $final );
      } );      
    } else {
      $final();
    }
  }
    
}
