<?php
namespace Phluid\Middleware;

class CookieParser {
    
  function __invoke( $request, $response, $next ){
    $cookie = $request->getHeader( 'Cookie' );
    if ( $cookie && !$request->cookies ) {
      $request->cookies = $this->parseCookie( $cookie );
      
    }
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

