<?php
namespace Phluid;

require_once 'vendor/autoload.php';

namespace Phluid\Tests;
use Evenement\EventEmitter;
use React\Socket\ServerInterface;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;

class SocketStub extends EventEmitter implements ServerInterface {
  
  public function listen( $port, $host = '127.0.0.1' ) {
    
  }
  
  public function getPort() {
    return 80;
  }
  
  public function shutdown() {
    
  }
  
}

class ConnectionStub extends EventEmitter implements ConnectionInterface {
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
