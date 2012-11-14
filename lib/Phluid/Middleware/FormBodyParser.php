<?php
namespace Phluid\Middleware;

class FormBodyParser {
  
  function __invoke( $request, $response, $next ){
        
    if ( $request->getHeader('Content-Type') == 'application/x-www-form-urlencoded' ) {
      \parse_str( $request->getBody(), $body );
      $request->setBody( $body );
    }
    
    $next();
    
  }
  
}