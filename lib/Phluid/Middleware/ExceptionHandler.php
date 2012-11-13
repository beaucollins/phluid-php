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
      $response->render( $this->template,
        array(
          'exception' => $exception,
          'app_path' => $this->common_path( $request->getHeader( 'script_filename' ) )
        ),
        array( 'layout' => false )
      );
    }
    
  }
  
  function common_path( $path ){
    $file= __FILE__;
    if ( $path == $file ) {
      return $path;
    }
    $common = '';
    for ( $i=0; $i < strlen( $file ); $i++ ) { 
      $str = substr( $file, $i, 1 );
      if ( $str == substr( $path, $i, 1 ) ) {
        $common .= $str;
      } else {
        break;
      }
    }
    return $common;
    
  }
  
}

namespace Phluid\Middleware\ExceptionHandler;

function hello_world(){
  return 'Hello World';
}

function common_path( $path, $root ){
  if ( strpos( $path, $root ) == 0 ) {
    return substr( $path, strlen( $root ) );
  } else {
    return $path;
  }
}
