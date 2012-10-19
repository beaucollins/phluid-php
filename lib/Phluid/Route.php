<?php

require_once 'Middleware.php';
require_once 'RouteMatcher.php';

class Phluid_Route implements Phluid_Middleware, Phluid_RouteMatcher {
  
  private $closure;
  private $methods;
  private $path;
  
  /**
   * Build a Phluid_Route that matches the given HTTP method and path. Paths
   * can match patterns. For example:
   *
   *    new Phluid_Route( 'GET', '/some/:var', function( $req, $res, $next ){} );
   * 
   * Matches any request to /some/thing or /some/other-thing and the request
   * variable is stored in the Phluid_Request as a param
   *
   * @param string, array     $methods HTTP methods to match
   * @param string            $path 
   * @param Phluid_Middleware $closure 
   * @author Beau Collins
   */
  public function __construct( $methods, $path, $closure ){
    $this->methods = is_array( $methods ) ? $methods : array( $methods );
    $this->path = $path;
    $this->closure = $closure;
  }
  
  /**
   * Tests if a route matches a given Phluid_Request
   *
   * @param Phluid_Request $request 
   * @return boolean
   * @author Beau Collins
   */
  public function matches( $request ){
    // method must match
    if ( in_array( $request->method, $this->methods ) && $request->parsePath( $this->path ) ) {
      return true;
    }
    return false;
  }
  
  public function __invoke( $request, $response, $next = null ){
    if ( $this->matches( $request )) {
      $closure = $this->closure;
      $closure( $request, $response, $next );
    } else {
      $next();
    }
  }
  
  public function __toString(){
    return implode( ',', $this->methods ) . ' ' . $this->path;
  }
  
}