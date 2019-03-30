<?php
namespace Phluid\Test\Middleware;
use Phluid\Middleware\StaticFiles;
use Phluid\Test\TestCase;

class StaticFileTest extends TestCase {
  
  /**
   * @before
   */
  function injectStaticFiles(){
    $this->file_path = realpath('.') . '/tests/files';
    $static_files = new StaticFiles( $this->file_path );
    $this->app->inject( $static_files );
    
  }
  
  function testServeFile(){
    
    $response = $this->doRequest( 'GET', '/hello_world.txt' );
        
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( 'Hello world', explode( "\n", $this->getBody() )[0] );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'text/plain' );
    
    
  }
  
  function testServeImage(){
    
    
    $response = $this->doRequest( 'GET', '/200.jpg' );
    
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'image/jpeg' );
    
  }
  
  function testServeCss(){
    
    $response = $this->doRequest( 'GET', '/style.css' );
    
    $this->assertSame( 200, $response->getStatus() );
    $this->assertSame( $response->getHeader( 'Content-Type'), 'text/css' );
    
  }
  
  function testServeRange(){
    
    $contents = file_get_contents( $this->file_path . '/hello_world.txt' );
    $response = $this->doRequest( 'GET', '/hello_world.txt', array(), array(
      'Range' => 'bytes=15-'
    ));
      
    $this->assertSame( $response->getStatus(), 206 );
    $this->assertSame( $response->getHeader( 'Content-Type' ), 'text/plain' );
    $this->assertNotNull( $response->getHeader( 'Content-Range' ) );
    $this->assertSame( substr( $contents, 15 ), $response->data );
    $this->assertSame( $response->getHeader( 'Content-Length' ), strlen( $contents ) - 15 );
    
    $response = $this->doRequest( 'GET', '/hello_world.txt', array(), array(
      'Range' => 'bytes=0-1'
    ));
      
    $this->assertSame( substr( $contents, 0, 2 ), $response->data );
    $this->assertSame( $response->getHeader( 'Content-Length' ), 2 );
    
    $response = $this->doRequest( 'GET', '/hello_world.txt', array(), array(
      'Range' => 'bytes=-15'
    ));
      
    $this->assertSame( $response->getHeader( 'Content-Length' ), 15 );
    $this->assertSame( substr( $contents, -15 ), $response->data );
    
  }
  
  function testServeMultipleRanges(){
    $response = $this->doRequest( 'GET', '/hello_world.txt', array(), array(
      'Range' => 'bytes=10-23,-10,25-'
    ));
    
    $this->assertSame( $response->getStatus(), 206 );
    $this->assertStringStartsWith( "multipart/byteranges; boundary=", $response->getHeader( 'Content-Type') );
    
        
    $size = filesize( $this->file_path . '/hello_world.txt' );
    
    $this->assertStringContainsString( "Content-Range: bytes 10-23/$size", $response->data );
    $start = $size - 10;
    $end = $size - 1;
    $this->assertStringContainsString( "Content-Range: bytes $start-$end/$size", $response->data );
    
    $boundary = substr( $response->getHeader( 'Content-Type' ), strlen( "multipart/byteranges; boundary=" ) );
    $parts = array_slice( explode( "--$boundary", trim( $response->data ) ), 1, -1 );
    $this->assertSame( 3, count( $parts ) );
    $this->assertSame( $response->getHeader( 'Content-Length' ), strlen( $response->data ) );
    
  }
    
}