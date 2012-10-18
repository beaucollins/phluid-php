<?php

require 'Route.php';

class Phluid_Router implements Phluid_Middleware {
  
  private $routes = array();
  
  public function __invoke( $req, $res, $next ){
    $route = $this->find( $req );
    if( $route ){
      $route( $req, $res, $next );
    } else {
      throw new Phluid_Exception_NotFound( "No route matching {$req}" );
    }
    
  }
  
  /**
   * Finds the first matching route for the given Phluid_Request
   *
   * @param Phluid_Request $request 
   * @return Phluid_Route
   * @author Beau Collins
   */
  public function find( $request ){
    foreach( $this->routes as $route ){
      if ( $route->matches($request) ) {
        return $route;
      }
    }
  }
  
  /**
   * Creates a Phluid_Route with the given HTTP method and path.
   *
   * @param string            $method  HTTP Method (GET, POST, etc.)
   * @param string            $path    path to match
   * @param Phluid_Middleware $closure invocable that conforms to Phluid_Middleware
   * @return void
   * @author Beau Collins
   */
  public function route( $method, $path, $closure ){
    
    $route = new Phluid_Route( $method, $path, $closure );
    array_push( $this->routes, $route );
      
    return $route;
      
  }
  
  /**
   * Adds a route to the beginning of the route stack
   *
   * @param string            $method  HTTP Method (GET, POST, etc.)
   * @param string            $path    path to match
   * @param Phluid_Middleware $closure invocable that conforms to Phluid_Middleware
   * @return void
   * @author Beau Collins
   */
  public function prepend( $method, $path, $closure ){
    
    $route = new Phluid_Route( $method, $path, $closure );
    array_unshift( $this->routes, $route );
    
    return $route;
    
  }
  
  public function allRoutes(){
    return $this->routes;
  }
      
}
