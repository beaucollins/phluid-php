<?php

class Phluid_RequestMatcherTest extends PHPUnit_Framework_TestCase {
  
  public function testRegexCompiling(){
    $this->assertSame( "#^/user(/(?<name>[^/]+))?/?$#", Phluid_RequestMatcher::compileRegex( '/user/:name?' ) );
  }
  
  public function testPathVariables(){
    $matcher = new Phluid_RequestMatcher( 'GET', '/show/:person' );
    $request = new Phluid_Request( 'GET', '/show/beau' );
    $this->assertArrayHasKey( 'person', $matcher( $request ), "Route should match path" );
  }
  
  public function testSlashDelimiter(){
    $matcher = new Phluid_RequestMatcher( 'GET', '/show/:person' );
    $request = new Phluid_Request( 'GET', '/show/beau/something' );
    $this->assertFalse( $matcher( $request ), "Route shouldn't match" );
  }
  
  public function testSplatRoutes(){
    $matcher = new Phluid_RequestMatcher( 'GET', '/user/*' );
    $request = new Phluid_Request( 'GET', '/user/beau' );
    $this->assertArrayHasKey( 0, $matcher( $request ) );
  }
  
  public function testParamRoute(){
    $request = new Phluid_Request( 'GET', '/user/' );
    $matcher = new Phluid_RequestMatcher( 'GET', '/user/:name?' );
    $this->assertArrayHasKey( 0, $matcher( $request ) );
    $matches = $matcher->matches( new Phluid_Request( 'GET', '/user/beau/' ) );
    $this->assertArrayHasKey( 'name', $matches  );
    $this->assertSame( 'beau', $matches['name'] );
  }
  
}