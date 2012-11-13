<?php
namespace Phluid\Middleware;

class StaticFileTest extends \PHPUnit_Framework_TestCase {
  
  function setUp(){
    
    $this->file_path = realpath('.') . '/tests/files';
    
  }
  
  function testServeFile(){
    
    $static_files = new StaticFiles( $this->file_path );
    
    $request = new \Phluid\Request( 'GET', '/hello_world.txt' );
    $response = new \Phluid\Response( $request );
    
    $static_files( $request, $response, function() {
      $this->fail( "Didn't find a file" );
    } );
        
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( 'Hello world', $response->getBody() );
    $this->assertArrayHasKey( 'CONTENT-TYPE', $response->getHeaders() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'text/plain; charset=us-ascii' );
    
    
  }
  
  function testServeImage(){
    
    $static_files = new StaticFiles( $this->file_path );
    
    $request = new \Phluid\Request( 'GET', '/200.jpg' );
    $response = new \Phluid\Response( $request );
    
    $static_files( $request, $response, function() use ( $request ) {
      $this->fail( "Didn't find a file for: $request" );
    } );
        
    $this->assertSame( 200, $response->getStatus() );
    $this->assertArrayHasKey( 'CONTENT-TYPE', $response->getHeaders() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'image/jpeg; charset=binary' );
    
  }
    
  
}