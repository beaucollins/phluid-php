<?php

class Router {
  
  private $routes = array();
    
  public function find( $request ){
    foreach( $this->routes as $route ){
      if ( $route['method'] == $request->method && $route['path'] == $request->path ) {
        return $route;
      }
    }
  }
  
  public function route( $method, $path, $closure ){
    
    array_push( $this->routes, array(
      'method'   => $method,
      'path'     => $path,
      'closure'  => $closure
    ) );
      
    return $this;
      
  }
    
}
