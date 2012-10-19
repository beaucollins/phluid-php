<?php

require_once 'lib/Phluid/Route.php';

class Phluid_RouteTest extends PHPUnit_Framework_TestCase {

  public function testPathVariables(){
    
    $route = new Phluid_Route( 'GET', '/show/:person', function( $request, $response ) {});
    $request = new Phluid_Request( 'GET', '/show/beau' );
    $this->assertArrayHasKey( 'person', $route->matches( $request ), "Route should match path" );
    
  }
  
  public function testSlashDelimiter(){
    
    $route = new Phluid_Route( 'GET', '/show/:person', function( $request, $response ) {});
    $request = new Phluid_Request( 'GET', '/show/beau/something' );
    $this->assertFalse( $route->matches( $request ), "Route shouldn't match" );
    
  }
  
  public function testSplatRoutes(){
    $request = new Phluid_Request( 'GET', '/user/beau' );
    $route = new Phluid_Route( 'GET', '/user/*', function(){} );
    
    $this->assertArrayHasKey( 0, $route->matches( $request ) );
        
  }
  
  public function testParamRoute(){
    $request = new Phluid_Request( 'GET', '/user/' );
    $route = new Phluid_Route( 'GET', '/user/:name?', function(){} );
    
    $this->assertArrayHasKey( 0, $route->matches( $request ) );
    $matches = $route->matches( new Phluid_Request( 'GET', '/user/beau/' ) );
    $this->assertArrayHasKey( 'name', $matches  );
    $this->assertSame( 'beau', $matches['name'] );
    
  }
  
  public function testRegexCompiling(){
    $this->assertSame( "#^/user(/(?<name>[^/]+))?/?$#", Phluid_Route::compileRegex( '/user/:name?' ) );
  }
  
  

}