<?php

require 'Utils.php';
require 'Router.php';
require 'Request.php';
require 'Response.php';
require 'Settings.php';
require 'Exceptions.php';

class Phluid_App {
  
  private $router;
  private $middleware = array();
  private $settings;
  
  public function __construct( $options = array() ){
    
    $defaults = array( 'view_path' => realpath('.') . '/views' );
    $this->settings = new Phluid_Settings( array_merge( $defaults, $options ) );
    $this->router = new Phluid_Router();
    
  }
  
  public function __get( $key ){
    return $this->settings->__get( $key );
  }
  
  public function __set( $key, $value ){
    return $this->settings->__set( $key, $value );
  }
    
  public function run(){
    
    ob_start();
    
    $request = Phluid_Request::fromServer()->withPrefix( $this->prefix );    
    $response = $this->serve( $request );
    
    $this->sendResponseHeaders( $response );
    ob_end_clean();
    echo $response->getBody();
    
  }
  
  public function serve( $request, $response = null, $handlers = null ){
    
    if ( !$response ) $response = new Phluid_Response( $this, $request );
    if( !$handlers ) $handlers = $this->matching( $request );
    $handler = array_shift( $handlers );
    if ( $handler ) {
      $app = $this;
      $next = function() use( $app, $request, $response, $handlers ){
        if ( count( $handlers ) == 0) {
          throw new Phluid_Exception_NotFound( "No more routes" );
        }
        $app->serve( $request, $response, $handlers );
      };
      $handler( $request, $response, $next );
    } else {
      throw new Phluid_Exception_NotFound( "No more routes" );
    }
    
    return $response;
    
  }
  
  public function __invoke( $request ){
    return $this->serve( $request );
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
  
  public function matching( $request ){
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
  
}