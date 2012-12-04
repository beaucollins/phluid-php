<?php

namespace Phluid\Middleware;

class Route {
  
  private $action;
  private $matcher;
  private $filters = array();
  
  public function __construct( $matcher, $action_or_filters, $action = null ){
    
    $this->matcher = $matcher;
    if ( is_null( $action ) ) {
      $this->action = $action_or_filters;
      $filters = array();
    } else {
      $this->action = $action;
      $filters = $action_or_filters;
    }
    
    if ( $filters && !is_array( $filters ) ) {
      $filters = array( $filters );
    }
    
    $this->filters = new Cascade( $filters );
    
  }
  
  public function matches( $request ){
    $matcher = $this->matcher;
    return $matcher( $request );
  }
  
  public function __invoke( $request, $response, $next ){
    if ( $matches = $this->matches( $request ) ) {
      $request->params = $matches;
      $filters = $this->filters;
      $action = $this->action;
      $filters( $request, $response, function() use ( $request, $response, $next, $action ) {
        $action( $request, $response, $next );
      } );
    } else {
      $next();
    }
  }
  
  public function __toString(){
    return implode( ',', $this->methods ) . ' ' . $this->path;
  }
    
}