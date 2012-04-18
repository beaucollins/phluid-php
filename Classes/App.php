<?php

require 'Router.php';
require 'Request.php';
require 'Response.php';

class App {
  
  private $router;
  public $prefix = "";
  
  public function __construct( $options = array() ){
    
    $this->prefix = array_key_exists('prefix', $options) ? $options['prefix'] : "";

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

    $request = $request->withPrefix( $this->prefix );
    $route = $this->router->find( $request );
    
    $response = new Response();
    
    $route( $request, $response );
    
    return $response;
    
  }
  
}