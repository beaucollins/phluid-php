<?php

require 'Classes/Router.php';

class RouterTest extends PHPUnit_Framework_TestCase {
  
  public function setUp(){
    
    $this->router = new Router();
    
    
  }
  
  public function testRouteMatching(){
    
    $this->router
      ->get( '/', function( $params ){
          $this->renderString('hello');
        } );
    
    $this->router->serve( 'GET', '/' );
    
    $this->assertSame( 'hello', $this->router->raw_response );
    
  }
  
}

