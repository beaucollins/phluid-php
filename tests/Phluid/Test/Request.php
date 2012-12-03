<?php

namespace Phluid\Test;

class Request extends \Phluid\Http\Request {
    
  function __construct( $headers ){
    parent::__construct( new Connection() );
    $this->headers = $headers;
    $this->emit( 'headers', array( $headers ) );
  }
  
  public function send( $body = null ){
    
    if ( $body != null ) {
      while( strlen( $body ) > 0 ){
        $part = substr( $body, 0, 1024 );
        $body = substr( $body, 1024 );
        $this->emit( 'data', array( $part ) );
      }
    }
    $this->close();
    
  }
  
  public function isReadable(){
    return $this->readable;
  }
  
  public function pause(){
    $this->emit( 'pause' );
  }
  
  public function resume(){
    $this->emit( 'resume' );
  }
  
  public function close(){
    $this->readable = false;
    $this->emit( 'end' );
    $this->removeAllListeners();
  }
  
  public function pipe( \React\Stream\WritableStreamInterface $dest, array $options = array() ){
    \React\Util::pipe( $this, $dest, $options );
    return $dest;
  }
  
}
