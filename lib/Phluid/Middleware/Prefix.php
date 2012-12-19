<?php
namespace Phluid\Middleware;
/**
 * With a given prefix like "app" it will strip the prefix from each
 * request path and store it in $request->namespace which will be an array
 * of namespaces which can be used in templates to construct correct urls.
 *
 * @package default
 * @author Beau Collins
 */
class Prefix {
  
  private $prefix;
  
  function __construct( $prefix ){
    $this->prefix = strtolower( $prefix );
  }
    
  function __invoke( $request, $response, $next ){
    if ( property_exists( $request, 'prefix' ) ) {
      $ns = $request->prefix;
    } else {
      $ns = array();
    }
    $prefix = $this->prefix;
    $path = strtolower( $request->getPath() );
    
    // if /prefix is the path or /prefix/ is strpos 0
    $match = true;
    if ( $prefix === $path ){
      array_push( $ns, $this->prefix );
      $request->setPath( "/" );
    } else if ( strpos( $path, $prefix . '/' ) === 0 ){
      array_push( $ns, $this->prefix );
      $request->setPath( substr( $path, strlen( $prefix ) ) );
    } else {
      $match = false;
    }
    $request->prefix = $ns;
    $next();
    if ( $match === true ) {
      array_pop( $ns );
      $request->setPath( $prefix . $request->getPath() );
    }
    $request->prefix = $ns;
    
    
  }
  
}