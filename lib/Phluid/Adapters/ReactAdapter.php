<?php

namespace Phluid\Adapters;
use Phluid\Request;
/**
 * Makes Phluid apps work with React\Http\Server request events
 *
 * @package default
 * @author Beau Collins
 */
class ReactAdapter {
  
  private $app;
  
  function __construct( $app ){
    $this->app = $app;
  }
  
  function __invoke( $request, $response ){
    
    $responder = new Responder( $this->app, $request, $response );
    $request->on( 'data',  $responder );
    
  }
  
}

class Responder {
  
  private $request;
  private $response;
  private $app;
  
  function __construct( $app, $request, $response ){
    $this->app = $app;
    $this->request = $request;
    $this->response = $response;
  }
  
  function __invoke( $body ){
    $app = $this->app;
    $request = $this->requestFromReactRequest( $this->request, $body );
    $response = $app->buildResponse( $request );
    $app( $request, $response );
    // write the headers and deliver the body
    try {
      $this->response->writeHead( $response->getStatus(), $response->getHeaders() );
      $this->response->end( $response->getBody() );
    } catch (\Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
    
  }
    
  protected static function requestFromReactRequest( \React\Http\Request $request, $data ){
    $body = $data;
    return new Request( $request->getMethod(),
                        $request->getPath(),
                        $request->getQuery(),
                        $request->getHeaders(),
                        $body );
  }
    
  
  
}
