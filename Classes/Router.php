<?php

class Router {
  
  private $routes = array();
  
  public function serve( $method, $path ){

    $route = $this->find( $method, $path );
    $closure = $route['closure'];

    $action = $closure->bindTo( $this );
    $action( array() );
  }
  
  public function find( $method, $path ){
    foreach( $this->routes as $route ){
      if ( $route['method'] == $method && $route['path'] == $path ) {
        return $route;
      }
    }
  }
  
  public function on( $method, $path, $closure ){
    
    array_push( $this->routes, array(
      'method'   => $method,
      'path'     => $path,
      'closure'  => $closure
    ) );
      
    return $this;
      
  }
  
  public function get( $path, $closure ){
    
    return $this->on( 'GET', $path, $closure );
    
  }
  
  public function post( $path, $closure ){
    
    return $this->on( 'POST', $path, $closure );
    
  }
  
  private function render(){
    
  }
  
  public function renderString( $string ){
    $this->raw_response = $string;
  }
  
}
