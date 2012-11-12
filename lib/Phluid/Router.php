<?php
namespace Phluid;
use Phluid\Middleware\Cascade;
class Router {
  
  private $routes = array();
  
  public function __invoke( $req, $res, $next ){
    
    $routes = $this->routes;
    $cascade = new Cascade( $routes );
    $cascade( $req, $res, function() use ($req){
      throw new Exception\NotFound( "No route matching {$req}" );
    });
    
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
