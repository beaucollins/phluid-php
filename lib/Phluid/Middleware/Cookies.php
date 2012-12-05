<?php
namespace Phluid\Middleware;
use Phluid\Middleware\Cookies\Cookie;

class Cookies {
  
  function __invoke( $request, $response, $next ){
    $cookie = $request->getHeader( 'Cookie' );
    if ( $cookie && !$request->cookies ) {
      $request->cookies = $this->parseCookie( $cookie );
    }
    $response->cookies = new CookieJar();
    $response->on( 'headers', function() use ( $response ){
      if( $value = $response->cookies->headerValues() ){
        $response->setHeader( 'Set-Cookie',  $value );        
      }
    });
    $next();
  }
  
  function parseCookie( $cookie ){
    $pairs = explode( '; ', $cookie );
    $cookies = array();
    foreach ( $pairs as $pair ) {
      list( $name, $val ) = explode( '=', $pair, 2 );
      $cookies[$name] = urldecode( $val );
    }
    return $cookies;
    
  }
  
}

class CookieJar implements \ArrayAccess {
  
  private $cookies = array();
  
  public function headerValues( ){
    $cookies = array();
    if ( count( $this->cookies ) == 0 ) {
      return false;
    }
    foreach ( $this->cookies as $key => $value ) {
      array_push( $cookies, "$key=$value" );
    }
    if( count( $cookies ) == 1 ){
      return $cookies[0];
    } else {
      return $cookies;
    }
  }
  
  public function offsetExists ( $offset ){
    return array_key_exists( $offset, $this->cookies );
  }
  
  public function offsetGet ( $offset ){
    if ( array_key_exists( $offset, $this->cookies ) ) {
      return $this->cookies[$offset];
    }
  }
  
  public function offsetSet ( $offset , $value ){
    if ( $value instanceof Cookie ) {
      $this->cookies[$offset] = $value;
    } else {
      $this->cookies[$offset] = new Cookie( $value );
    }
  }
  
  public function offsetUnset ( $offset ){
    unset( $this->cookies[$offset] );
  }
  
}

