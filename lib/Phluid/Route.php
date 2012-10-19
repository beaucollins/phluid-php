<?php

require_once 'Middleware.php';
require_once 'RouteMatcher.php';

class Phluid_Route implements Phluid_Middleware, Phluid_RouteMatcher {
  
  private $action;
  private $methods;
  private $path;
  private $filters = array();
  
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
   * @param Phluid_Middleware $action 
   * @author Beau Collins
   */
  public function __construct( $methods, $path, $action_or_filters, $action = null ){
    $this->methods = is_array( $methods ) ? $methods : array( $methods );
    $this->path = $path;
    if ( is_null( $action ) ) {
      $this->action = $action_or_filters;
    } else {
      $this->action = $action;
      $this->filters = $action_or_filters;
    }
    
    if ( $this->filters && !is_array( $this->filters )) {
      $this->filters = array( $this->filters );
    }
    
    $this->regex = self::compileRegex( $path );
  }
  
  /**
   * Tests if a route matches a given Phluid_Request
   *
   * @param Phluid_Request $request 
   * @return array of path pattern matches or false if no match
   * @author Beau Collins
   */
  public function matches( $request ){
    // method must match
    if ( in_array( $request->method, $this->methods ) ) {
      if( preg_match( $this->regex, $request->path, $matches) ){
        return $matches;
      }
    }
    return false;
  }
  
  public function __invoke( $request, $response, $next = null ){
    if ( $matches = $this->matches( $request ) ) {
      $response->params = $matches;
      $filters = $this->filters;
      $action = $this->action;
      array_push( $filters, function () use ( $action, $request, $response, $next ){
        $action( $request, $response, $next );
      } );
      Phluid_Utils::performFilters( $request, $response, $filters );
    } else {
      $next();
    }
  }
  
  public function __toString(){
    return implode( ',', $this->methods ) . ' ' . $this->path;
  }
  
  public static function compileRegex( $pattern ){
    // sorry about the magic here
    $regex_pattern = preg_replace( "/\.:/", "\\.:", $pattern );
    $regex_pattern = preg_replace( "/\*/", ".*", $regex_pattern );
    $regex_pattern = preg_replace( "#(/)?:([\w]+)(\?)?#", '($1(?<$2>[^/]+))$3', $regex_pattern );
    return '#^' . $regex_pattern . '/?$#';
  }
  
}