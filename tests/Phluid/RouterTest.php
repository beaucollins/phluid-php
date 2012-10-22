<?php

namespace Phluid;

require_once 'tests/helper.php';

class RouterTest extends \PHPUnit_Framework_TestCase {
  
  function testFindsRoute(){
    $router = new Router();
    $matcher = new RequestMatcher( 'GET', '/:path?' );
    $route = $router->route( $matcher, function(){} );
    
    $this->assertSame( $route, $router->find( new Request( 'GET', '/something' ) ) );
    $this->assertSame( $route, $router->find( new Request( 'GET', '/' ) ) );
  }
  
}