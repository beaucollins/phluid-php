<?php

namespace Phluid;

class Route {
  
  private $action;
  private $matcher;
  private $filters = array();
  
  public function __construct( $matcher, $action_or_filters, $action = null ){
    
    $this->matcher = $matcher;
    if ( is_null( $action ) ) {
      $this->action = $action_or_filters;
    } else {
      $this->action = $action;
      $this->filters = $action_or_filters;
    }
    
    if ( $this->filters && !is_array( $this->filters )) {
      $this->filters = array( $this->filters );
    }
    
  }
  
  public function matches( $request ){
    $matcher = $this->matcher;
    return $matcher( $request );
  }
  
  public function __invoke( $request, $response, $next = null ){
    if ( $matches = $this->matches( $request ) ) {
      $response->params = $matches;
      $filters = $this->filters;
      $action = $this->action;
      array_push( $filters, function () use ( $action, $request, $response, $next ){
        $action( $request, $response, $next );
      } );
      Utils::performFilters( $request, $response, $filters );
    } else {
      $next();
    }
  }
  
  public function __toString(){
    return implode( ',', $this->methods ) . ' ' . $this->path;
  }
    
}