<?php
namespace Phluid\Middleware\Sessions;

class Session implements \ArrayAccess {
  
  private $request;
  private $data;
  private $id;
  
  function __construct( $request, $data = array() ){
    $this->request = $request;
    $this->id = $request->sessionId;
    $this->data = $data;
  }
  
  public function getData(){
    return $this->data;
  }
  
  public function __get( $key ){
    if( array_key_exists( $key, $this->data ) ) return $this->data[$key];
  }
  
  public function __set( $key, $val ){
    $this->data[$key] = $val;
  }
  
  public function __isset( $key ){
    return array_key_exists( $key, $this->data );
  }
  
  public function __unset( $key ){
    unset( $this->data[$key] );
  }
  
  public function offsetExists ( $offset ){
    return array_key_exists( $offset, $this->data );
  }
  
  public function offsetGet ( $offset ){
    if ( array_key_exists( $offset, $this->data ) ) {
      return $this->data[$offset];
    }
  }
  
  public function offsetSet ( $offset , $value ){
    $this->data[$offset] = $value;
  }
  
  public function offsetUnset ( $offset ){
    unset( $this->data[$offset] );
  }
  
}