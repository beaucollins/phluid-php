<?php

require_once 'Classes/Route.php';

class RouteTest extends PHPUnit_Framework_TestCase {

  public function testPathVariables(){
    $check = array();
    $route = new Route( 'GET', '/show/:person', function( $request, $response ) {
      
    });
    
    $request = new Request( 'GET', '/show/beau' );
    
    $this->assertTrue( $route->matches( $request ), "Route should match path" );
  }

}