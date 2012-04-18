<?php

require 'Router.php';
require 'Request.php';
require 'Response.php';

class App {
  
  private $router;
  
  public function __construct(){
    
    $this->router = new Router();
    
  }
    
  public function route( $closure ){
    
    return $this->router->route( 'GET', $path, $closure );
    
  }
  
  public function get( $path, $closure ){
    return $this->router->route( 'GET', $path, $closure );
  }
  
  public function post( $path, $closure ){
    return $this->router->route( 'POST', $path, $closure );
  }
  
    
  public function serve( $request ){

    $route = $this->router->find( $request );
    $closure = $route['closure'];
    
    $response = new Response();
    
    $closure( $request, $response );
    
    return $response;
    
  }
  
}