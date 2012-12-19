<?php
namespace Phluid;

class Headers implements \ArrayAccess {
  
  protected $headers;
  protected $header_names;
  
  function __construct( $headers = array() ){
    $this->headers = array();
    $this->header_names = array();
    
    $that = $this;
    foreach ( $headers as $key => $value) {
      $that[$key] = $value;
    }
  }
  
  public function offsetExists ( $offset ){
    $normal = $this->normalizeHeaderName( $offset );
    return array_key_exists( $normal, $this->headers );
  }
  
  public function offsetGet ( $offset ){
    $normal = $this->normalizeHeaderName( $offset );
    if ( array_key_exists( $normal, $this->headers ) ) {
      return $this->headers[$normal];
    }
  }
  
  public function offsetSet ( $offset , $value ){
    $normal = $this->normalizeHeaderName( $offset );
    $this->headers[$normal] = $value;
    $this->header_names[$normal] = $offset;
  }
  
  public function offsetUnset ( $offset ){
    $normal = $this->normalizeHeaderName( $offset );
    unset( $this->headers[$normal] );
    unset( $this->header_names[$normal] );
  }
  
  public static function normalizeHeaderName( $name ){
    return strtolower( (string) $name );
  }
  
  public function toArray(){
    $export = array();
    foreach( $this->headers as $key => $value ){
      $export[$this->header_names[$key]] = $value;
    }
    return $export;
  }


}