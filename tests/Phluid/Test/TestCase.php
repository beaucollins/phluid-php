<?php
namespace Phluid\Test;
use Phluid\App;
use React\Http\Request as HttpRequest;
use React\Http\Response as HttpResponse;
use Phluid\Request;
use Phluid\Response;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase {
  /**
   * @before
   */
  function setUpConnection(){
    
    $this->connection = new Connection();
    $this->app = new App();
    $this->app->get( '/', function( $request, $response, $next ){
      $response->renderText('Hello World');
    } );
    $this->http = new Server();
    $this->app->createServer( $this->http );
  }
  
  public function doRequest( $method = 'GET', $path = '/', $query = array(), $headers = array(), $action = false ){
    
    $request = $this->makeRequest( $method, $path, $query, $headers );
    $request->method = $method;
    $request->path = $path;
    $http_response = new HttpResponse( $this->connection );
    $this->response = $response = new MockResponse( $http_response, $request );
    $this->app->__invoke( $request, $response );
    if ( !$action ){
     $request->close(); 
    } else {
      $action( $request, $response );
    }
    return $response;
  }
  
  public function makeRequest( $method = 'GET', $path = '/', $query = array(), $headers = array() ){
    $request = new HttpRequest( $method, $path, $query, '1.1', $headers );
    $this->request = new Request( $request );
    return $this->request;
  }
  
  public function send( $body = null ){
    if ( $body != null ) {
      while( strlen( $body ) > 0 ){
        $part = substr( $body, 0, 1024 );
        $body = substr( $body, 1024 );
        $this->request->emit( 'data', array( $part ) );
      }
    }
    $this->request->close();
    
  }
  
  public function sendFile( $file ){
    $handle = fopen( $file, 'r' );
    while( $string = fread( $handle, 1024 ) ){
      $this->request->emit( 'data', array( $string ) );
    }
    fclose( $handle );
    $this->request->close();
  }
  
  public function getBody(){
    return $this->response->data;
  }
  
  public function fileFixture( $file ){
    return realpath('.') . '/tests/files/' . $file;
  }
  
}

class MockResponse extends Response {
  
  public $data = "";
  private $capture = false;
  
  public function writeHead( $status = 200, array $headers = array() ){
    parent::writeHead( $status, $headers );
    $this->capture = true;
  }
  
  public function write( $data ){
    parent::write( $data );
    if ( $this->capture ) $this->data .= $data;
  }
  
  public function end( $data = null ){
    parent::end( $data );
    if ( $this->capture ) $this->data .= $data;
  }
  
}