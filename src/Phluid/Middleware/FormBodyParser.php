<?php
namespace Phluid\Middleware;

class FormBodyParser {
  
  function __invoke( $request, $response, $next ){
        
    if ( strpos($request->getHeader('Content-Type'),'application/x-www-form-urlencoded')===0 ) {
      $body = "";
      $request->on( 'data', function( $data ) use ( &$body ){
        $body .= $data;
      } );
      
      $request->on( 'end', function() use ( &$body, $request, $next ){
        parse_str( $body, $query );
        $request->body = $query;
        $next();
      } );
      
    } else {
      $next();      
    }
    
  }
  
}
