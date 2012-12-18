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
  
  public function doRequest( $method = 'GET', $path = '/', $query = array(), $headers = array(), $auto_close = true ){
    
    $this->request = $request = new Request( $method, $path, $query, '1.1', $headers );
    $request->method = $method;
    $request->path = $path;
    $response = new Response( $request );
    $this->http->emit( 'request', array( $request, $response ) );
    if ( $auto_close ) $request->send();
    return $response;
  }
  
}