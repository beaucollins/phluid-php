<?php
namespace Phluid\Test;
use \Phluid\App;

class TestCase extends \PHPUnit_Framework_TestCase {
  
  function setUp(){
    
    $this->app = new App();
    $this->app->get( '/', function( $request, $response, $next ){
      $response->renderString('Hello World');
    } );
    $this->http = new Server();
    $this->app->createServer( $this->http );
  }
  
  public function doRequest( $method = 'GET', $path = '/', $headers = array(), $auto_close = true ){
    
    $request_headers = new \Phluid\Http\Headers( $method, $path, 'HTTP', '1.1', $headers );
    $this->request = $request = new Request( $request_headers );
    $request->method = $method;
    $request->path = $path;
    $response = new Response( $request );
    $this->http->emit( 'request', array( $request, $response ) );
    if ( $auto_close ) $request->send();
    return $response;
  }
  
}