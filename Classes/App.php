<?php

require 'Utils.php';
require 'Router.php';
require 'Request.php';
require 'Response.php';

class App {
  
  private $router;
  private $middleware = array();
  public $prefix = "";
  
  public function __construct( $options = array() ){
    
    $this->prefix = array_key_exists('prefix', $options) ? $options['prefix'] : "";

    $this->router = new Router();
    
  }
  
  public function run(){
    
    ob_start();
    
    $request = Request::fromServer()->withPrefix( $this->prefix );    
    $response = $this->serve( $request );
    
    $this->sendResponseHeaders( $response );
    ob_end_clean();
    echo $response->getBody();
    
  }
  
  private function sendResponseHeaders( $response ){
    header( $response->statusHeader() );
    $response->eachHeader( function( $name, $value ){
      header( $name . ': ' . $value, true );
    } );
  }
  
  public function inject( $middleware ){
    array_push( $this->middleware, $middleware );
    return $this;
  }
  
  private function matching( $request ){
    $routes = $this->router->matching( $request );
    return array_merge( $this->middleware, $routes );
  }
    
  public function route( $method, $path, $closure ){
    
    $this->router->route( $method, $path, $closure );
    return $this;
    
  }
  
  public function get( $path, $closure ){
    return $this->route( 'GET', $path, $closure );
  }
  
  public function post( $path, $closure ){
    return $this->route( 'POST', $path, $closure );
  }
  
  public function serve( $request, $response = null, $routes = null ){
    
    if ( !$response ) $response = new Response();
    
    if( !$routes ) $routes = $this->matching( $request );
    $route = array_shift( $routes );
    $app = $this;
    $next = function() use( $app, $request, $response, $routes ){
      $app->serve( $request, $response, $routes );
    };
    
    $route( $request, $response, $next );
    
    return $response;
    
  }
  
}