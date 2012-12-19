<?php
namespace Phluid\Test;
use Evenement\EventEmitter;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class Connection extends EventEmitter implements ConnectionInterface {
  
  public $data = '';
  private $readable = true;
  private $writable = true;
  public $bufferSize = 1024;
    
  public function streamFile( $file ){
    $resource = fopen( $file, 'r' );      
    stream_set_read_buffer( $resource, 1024 );
    while( $data = fgets( $resource ) ){
      $this->emit( 'data', array( $data ) );
    }
    fclose( $resource );
  }

  public function isReadable() {
      return $this->readable;
  }

  public function isWritable() {
      return $this->writable;
  }

  public function pause() {
    $this->emit( 'pause' );
  }

  public function resume() {
    $this->emit( 'resume' );
  }

  public function pipe(WritableStreamInterface $dest, array $options = array()){
      Util::pipe($this, $dest, $options);

      return $dest;
  }

  public function write($data){
      $this->data .= $data;

      return true;
  }

  public function end($data = null) {
    if( $data ) $this->write( $data );
    $this->writable = false;
    $this->emit( 'end' );
  }

  public function close(){
    $this->writable = false;
    $this->emit( 'close' );
  }

  public function getData(){
      return $this->data;
  }

  public function getRemoteAddress(){
      return '127.0.0.1';
  }
}


