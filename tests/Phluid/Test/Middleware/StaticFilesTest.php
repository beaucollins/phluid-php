<?php
namespace Phluid\Test\Middleware;
use Phluid\Middleware\StaticFiles;
use Phluid\Test\TestCase;

class StaticFileTest extends TestCase {
  
  function setUp(){
    
    $this->file_path = realpath('.') . '/tests/files';
    parent::setUp();
  }
  
  function testServeFile(){
    
    $static_files = new StaticFiles( $this->file_path );
    $this->app->inject( $static_files );
    
    $response = $this->doRequest( 'GET', '/hello_world.txt' );
        
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( 'Hello world', $this->getBody() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'text/plain' );
    
    
  }
  
  function testServeImage(){
    
    $static_files = new StaticFiles( $this->file_path );
    $this->app->inject( $static_files );
    
    $response = $this->doRequest( 'GET', '/200.jpg' );
    
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'image/jpeg' );
    
  }
  
  function testServeCss(){
    
    $static_files = new StaticFiles( $this->file_path );
    $this->app->inject( $static_files );
    
    $response = $this->doRequest( 'GET', '/style.css' );
    
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'text/css' );
    
  }
  
  
}