<?php
namespace Phluid\Test\Http;
use Phluid\Http\Server;

class ServerTest extends \PHPUnit_Framework_TestCase {
  
  function testRequest(){
      
    $server = new \Phluid\Http\Server( $this->socket );
    
    $server->on( 'request', function( $request, $response ){
      
      $this->assertSame( '/', $request->getPath() );
      $this->assertSame( 'GET', $request->getMethod() );
      $this->assertSame( 'en.blog.wordpress.com', $request->getHost() );
      
      $headers = $request->getHeaders();
      $this->assertSame( 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', $headers['Accept'] );
      $this->assertSame( 'keep-alive', $headers['Connection'] );
            
    } );
    
    $this->sendFile( realpath('.') . '/tests/files/get-request' );
    
  }
  
  function testPostRequest(){
    
    
    $server = new Server( $this->socket );
    
    $server->on( 'request', function( $request, $response ){
      
      $this->assertSame( '/', $request->getPath() );
      $this->assertSame( 'POST', $request->getMethod() );
      
      $this->assertSame( '15', $request->getHeaders()['Content-Length']);
      
      $body = "";
      
      $request->on( 'data', function( $data ) use ( &$body ){
        $body .= $data;
      });
      
      $request->on( 'end', function() use ( &$body, $request ){
        $this->assertSame( 'something=HI%21',  $body );
      });
      
    } );
    
    $this->sendFile( realpath('.') . '/tests/files/post-request' );
    
  }
  
  function setup(){
    $this->socket = new \Phluid\Test\Socket();
    $this->conn = new \Phluid\Test\Connection( $this->socket );
  }
  
  private function sendFile( $file ){
    // let's stream in a file
    $this->socket->emit( 'connection', array( $this->conn ) );
    $this->conn->streamFile( $file );
    
  }
  
}

