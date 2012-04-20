<?php

require_once 'Classes/Request.php';

class RequestTest extends PHPUnit_Framework_TestCase {
  
  public function testPrefix(){
  
    $request = new Request('GET', '/something/other/');
    $new_request = $request->withPrefix( '/something' );
    
    $this->assertSame( '/other/', $new_request->path );
    
  }
  
  public function testAccessors(){
    
    $request = new Request( 'GET', '/' );
    
    $request->something = "Hi";
    
    $this->assertSame( "Hi", $request->something );
    
  }
  
  public function testParsePath(){
    
    $request = new Request( 'GET', '/user/beau' );
    
    $this->assertTrue( $request->parsePath( '/user/:name' ) );
    $this->assertArrayHasKey( 'name', $request->params );
    
  }
  
  public function testRegexCompiling(){
    $request = new Request( 'GET', '/user/beau' );
    
    $this->assertSame( 1, preg_match("#/user(/(?<name>[^/]+))?#", '/user' ) );
    $this->assertSame( "#/user(/(?<name>[^/]+))?#", $request->compileRegex( '/user/:name?' ) );
  }
  
  public function testSplatRoutes(){
    $request = new Request( 'GET', '/user/beau' );
    
    
    $this->assertTrue( $request->parsePath( '/user/(.*)' ) );
    $this->assertContains( 'beau', $request->params );
    
    $this->assertTrue( $request->parsePath( '/user/*' ) );
    
    $request2 = new Request( 'GET', '/user/' );
    $this->assertTrue( $request2->parsePath( '/user/:name?' ) );
    $this->assertTrue( $request2->parsePath( '/user' ) );
    
  }
    
}