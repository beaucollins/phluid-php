<?php

require_once 'lib/Phluid/Route.php';

class Phluid_RouteTest extends PHPUnit_Framework_TestCase {

  public function testPathVariables(){
    
    $route = new Phluid_Route( 'GET', '/show/:person', function( $request, $response ) {});
    $request = new Phluid_Request( 'GET', '/show/beau' );
    $this->assertTrue( $route->matches( $request ), "Route should match path" );
    
  }
  
  public function testSlashDelimiter(){
    
    $route = new Phluid_Route( 'GET', '/show/:person', function( $request, $response ) {});
    $request = new Phluid_Request( 'GET', '/show/beau/something' );
    $this->assertFalse( $route->matches( $request ), "Route shouldn't match" );
    
  }

}