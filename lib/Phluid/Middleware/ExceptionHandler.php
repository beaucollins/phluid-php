<?php

namespace Phluid\Middleware;
/**
 * Catches Exceptions and renders a page with debug output. Useful for development.
 *
 * @package default
 * @author Beau Collins
 */
class ExceptionHandler {
  
  var $template;
  
  function __construct( $template = null ){
    if ( $template ) {
      $this->template = $template;
    } else {
      $this->template =  __DIR__ . DIRECTORY_SEPARATOR . basename( __FILE__, '.php' ) . DIRECTORY_SEPARATOR . 'exception';
    }
  }
  
  function __invoke( $request, $response, $next ){
    
    try {
      $next();
    } catch (\Exception $exception) {
      $response->render( $this->template, array( 'exception' => $exception ), array( 'layout' => false ) );
    }
    
  }
  
}

namespace Phluid\Middleware\ExceptionHandler;

function hello_world(){
  return 'Hello World';
}
