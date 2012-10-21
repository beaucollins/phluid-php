<?php

namespace Phluid;

class Router {
  
  private $routes = array();
  
  public function __invoke( $req, $res, $next ){
    $route = $this->find( $req );
    if( $route ){
      $route( $req, $res, $next );
    } else {
      throw new Exception_NotFound( "No route matching {$req}" );
    }
    
  }
  
  public function find( $request ){
    foreach( $this->routes as $route ){
      if ( $matches = $route->matches($request) ) {
        $request->params = $matches;
        return $route;
      }
    }
  }
  
  public function route( $matcher, $filters, $action = null ){
    
    $route = new Route( $matcher, $filters, $action );
    array_push( $this->routes, $route );
      
    return $route;
      
  }
  
  public function prepend( $matcher, $filters, $closure ){
    
    $route = new Route( $matcher, $filters, $closure );
    array_unshift( $this->routes, $route );
    
    return $route;
    
  }
  
  public function allRoutes(){
    return $this->routes;
  }
      
}
