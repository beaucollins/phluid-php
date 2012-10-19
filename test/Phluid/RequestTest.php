<?php

require_once 'lib/Phluid/Request.php';

class Phluid_RequestTest extends PHPUnit_Framework_TestCase {
  
  public function testPrefix(){
  
    $request = new Phluid_Request('GET', '/something/other/');
    $new_request = $request->withPrefix( '/something' );
    
    $this->assertSame( '/other/', $new_request->path );
    
  }
  
  public function testAccessors(){
    
    $request = new Phluid_Request( 'GET', '/' );
    
    $request->something = "Hi";
    
    $this->assertSame( "Hi", $request->something );
    
  }  
  
  public function testBody(){
    $request = new Phluid_Request( 'POST', '/' );
    $request->setBody( "Hello world" );
    $this->assertSame( "Hello world", $request->getBody() );
  }
    
}