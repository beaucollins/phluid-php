<?php

require_once 'Classes/App.php';

class AppTest extends PHPUnit_Framework_TestCase {
  
  public function setUp(){
    
    $this->app = new App();
    
  }
  
  public function testAppRoute(){
    
    $this->app->get( '/', function( $request, $response ){
      $response->renderString('hello');
    } );
    
    $request = new Request( 'GET', '/', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'hello', $response->getRawResponse() );
    
  }
  
  public function testRouteActionAsObject(){
    
    $this->app->get( '/awesome', new HelloWorldAction );
    
    $request = new Request( 'GET', '/awesome', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World!', $response->getRawResponse() );
    
  }
  
}

class HelloWorldAction {
  
  private $response = "Hello World!";
  
  public function __invoke( $request, $response ){
    $response->renderString( $this->response );
  }
  
}
