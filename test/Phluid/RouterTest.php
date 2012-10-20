<?php

class Phluid_RouterTest extends PHPUnit_Framework_TestCase {
  
  function testFindsRoute(){
    $router = new Phluid_Router();
    $matcher = new Phluid_RequestMatcher( 'GET', '/:path?' );
    $route = $router->route( $matcher, function(){} );
    
    $this->assertSame( $route, $router->find( new Phluid_Request( 'GET', '/something' ) ) );
    $this->assertSame( $route, $router->find( new Phluid_Request( 'GET', '/' ) ) );
  }
  
}