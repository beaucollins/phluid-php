<?php

class Phluid_RouterTest extends PHPUnit_Framework_TestCase {
  
  function testFindsRoute(){
    $router = new Phluid_Router();
    
    $route = $router->route( 'GET', '/:path?', function(){} );
    
    $this->assertSame( $route, $router->find( new Phluid_Request( 'GET', '/something' ) ) );
  }
  
}