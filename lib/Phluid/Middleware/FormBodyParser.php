<?php
namespace Phluid\Middleware;

class FormBodyParser {
  
  function __invoke( $request, $response, $next ){
        
    if ( $request->getHeader('Content-Type') == 'application/x-www-form-urlencoded' ) {
      $body = "";
      $request->on( 'data', function( $data ) use ( &$body ){
        $body .= $data;
      } );
      
      $request->on( 'end', function() use ( &$body, $request, $next ){
        parse_str( $body, $query );
        $request->body = $query;
        $request->hello = "World";
        $next();
      } );
      
    } else {
      $next();      
    }
    
  }
  
}