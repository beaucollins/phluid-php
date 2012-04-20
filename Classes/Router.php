<?php

require 'Route.php';

class Router {
  
  private $routes = array();
    
  public function find( $request ){
    foreach( $this->routes as $route ){
      if ( $route->matches($request) ) {
        return $route;
      }
    }
  }
  
  public function matching( $request ){
    
    return array_filter( $this->routes, function( $route ) use( $request ) {
      return $route->matches( $request );
    } );
    
  }
  
  public function route( $method, $path, $closure ){
          
    array_push( $this->routes, new Route( $method, $path, $closure ) );
      
    return $this;
      
  }
  
  public function prepend( $method, $path, $closure ){
    
    array_unshift( $this->routes, new Route( $method, $path, $closure ) );
    
    return $this;
    
  }
      
}
