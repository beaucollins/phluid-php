<?php
namespace Phluid\Http;

require_once 'tests/helper.php';

use Phluid\Tests\SocketStub;
use Phluid\Tests\ConnectionStub;

class ServerTest extends \PHPUnit_Framework_TestCase {
  
  function testRequest(){
    
    $socket = new SocketStub();
    $conn = new ConnectionStub();
    
    $server = new Server( $socket );
    
    $server->on( 'request', function( $request, $response ){
      
      $this->assertSame( '/', $request->getPath() );
      $this->assertSame( 'GET', $request->getMethod() );
      $this->assertSame( 'en.blog.wordpress.com', $request->getHost() );
      
      $headers = $request->getHeaders();
      $this->assertSame( 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', $headers['Accept'] );
      $this->assertSame( 'keep-alive', $headers['Connection'] );
            
    } );
    
    $socket->emit( 'connection', array( $conn ) );
    
    // let's stream in a file
    $request_file = realpath('.') . '/tests/files/get-request';
    $conn->streamFile( $request_file );
    
  }
  
  function testPostRequest(){
    
    $socket = new SocketStub();
    $conn = new ConnectionStub();
    
    $server = new Server( $socket );
    
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
    
    $socket->emit( 'connection', array( $conn ) );
    
    // let's stream in a file
    $conn->streamFile( realpath('.') . '/tests/files/post-request' );
    
  }
  
}

