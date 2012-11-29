<?php
namespace Phluid\Http;

use Evenement\EventEmitter;
use React\Socket\ServerInterface;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;

require_once 'tests/helper.php';

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

class SocketStub extends EventEmitter implements ServerInterface {
  
  public function listen( $port, $host = '127.0.0.1' ) {
    
  }
  
  public function getPort() {
    return 80;
  }
  
  public function shutdown() {
    
  }
  
}

class ConnectionStub extends EventEmitter implements ConnectionInterface
{
    private $data = '';
    
    public function streamFile( $file ){
      $resource = fopen( $file, 'r' );      
      stream_set_read_buffer( $resource, 1024 );
      while( $data = fgets( $resource ) ){
        $this->emit( 'data', array( $data ) );
      }
      fclose( $resource );
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function pause()
    {
    }

    public function resume()
    {
    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function write($data)
    {
        $this->data .= $data;

        return true;
    }

    public function end($data = null)
    {
    }

    public function close()
    {
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRemoteAddress()
    {
        return '127.0.0.1';
    }
}
