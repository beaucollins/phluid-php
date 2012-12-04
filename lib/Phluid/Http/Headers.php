<?php
namespace Phluid\Http;

class Headers implements \ArrayAccess {
  
  private $headers;
  private $header_names;
  public $method;
  public $path;
  public $protocol;
  public $version;
  
  function __construct( $method, $path, $protocol = 'HTTP', $version = '1.1', $headers = array() ){
    $this->headers = array();
    $this->header_names = array();
    $this->method = $method;
    $this->path = $path;
    $this->protocol = $protocol;
    $this->version = $version;
    
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


}