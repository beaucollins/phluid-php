<?php

namespace Phluid;

require_once 'test/helper.php';

class RequestMatcherTest extends \PHPUnit_Framework_TestCase {
  
  public function testRegexCompiling(){
    $this->assertSame( "#^/user(/(?<name>[^/]+))?/?$#", RequestMatcher::compileRegex( '/user/:name?' ) );
  }
  
  public function testPathVariables(){
    $matcher = new RequestMatcher( 'GET', '/show/:person' );
    $request = new Request( 'GET', '/show/beau' );
    $this->assertArrayHasKey( 'person', $matcher( $request ), "Route should match path" );
  }
  
  public function testSlashDelimiter(){
    $matcher = new RequestMatcher( 'GET', '/show/:person' );
    $request = new Request( 'GET', '/show/beau/something' );
    $this->assertFalse( $matcher( $request ), "Route shouldn't match" );
  }
  
  public function testSplatRoutes(){
    $matcher = new RequestMatcher( 'GET', '/user/*' );
    $request = new Request( 'GET', '/user/beau' );
    $this->assertArrayHasKey( 0, $matcher( $request ) );
  }
  
  public function testParamRoute(){
    $request = new Request( 'GET', '/user/' );
    $matcher = new RequestMatcher( 'GET', '/user/:name?' );
    $this->assertArrayHasKey( 0, $matcher( $request ) );
    $matches = $matcher->matches( new Request( 'GET', '/user/beau/' ) );
    $this->assertArrayHasKey( 'name', $matches  );
    $this->assertSame( 'beau', $matches['name'] );
  }
  
}