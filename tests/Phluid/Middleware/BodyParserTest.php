<?php

namespace Phluid\Middleware;

use Phluid\Test\Request;
use Phluid\App;

require_once 'tests/helper.php';

class BodyParserTest extends \PHPUnit_Framework_TestCase {
  
  public function testJsonParsing(){
    
    $thing = new \stdClass();
    $thing->awesome = "YES";
    
    $parser = new JsonBodyParser( false );
    $this->app->inject( $parser );
    
    $body = json_encode( $thing );
    $response = $this->doRequest( 'POST', '/', array(
      'Content-Type' => 'application/json',
      'Content-Length' => strlen( $body )
    ), false );
    $this->send( $body );
        
    
    $this->assertSame( $thing->awesome, $this->request->body->awesome );
    
  }
  
  public function testFormParsing(){
    
    $parser = new FormBodyParser();
    $values = array( 'field' => 'value' );
    $body = http_build_query( $values );
    $this->app->inject( $parser );
    
    $this->doRequest( 'POST', '/', array(
      'Content-Type' => 'application/x-www-form-urlencoded',
      'Content-Length' => strlen( $body )
    ), false );
    
    $this->send( $body );
    $this->assertSame( $values, $this->request->body );
    
  }
  
  public function testMultipartParsing(){
    
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $this->app->inject( $parser );
    $this->app->inject( function( $request, $response, $next ){

      $this->assertArrayHasKey( 'name', $request->body );
      $this->assertArrayHasKey( 'file', $request->body );
      
      $this->assertFileExists( (string) $request->body['file'] );
    } );
    
    $response = $this->doRequest( 'POST', '/', array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryoOeNyQKwEVuvehNw'
    ), false);
    $this->sendFile( realpath('.') . '/tests/files/multipart-body' );
  }
  
  public function testMultipartAssocParsing(){
    
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $this->app->inject( $parser );
    $this->app->inject( function( $request, $response, $next ){
      
      $body = $request->body;
      $this->assertArrayHasKey( 'first', $body['name'] );
      $this->assertArrayHasKey( 'last', $body['name'] );

      $this->assertSame( "Sammy", (string) $body['name']['first'] );
      $this->assertSame( 'Collins', (string) $body['name']['last'] );
    
      $this->assertArrayHasKey( 0, $body['file']['for'] );
      $this->assertArrayHasKey( 1, $body['file']['for'] );
      
      $this->assertFileExists( (string) $body['file']['for'][0] );
      $this->assertFileExists( (string) $body['file']['for'][1] );
      $next();
    });
    
    $response = $this->doRequest( 'POST', '/', array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryAYD2hRdSJxpcdK2a'
    ), false );
    
    $this->sendFile( realpath('.') . '/tests/files/multipart-assoc' );
    
  }
  
  public function testMultipartSkipsParsing(){
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $this->app->inject( $parser );
    
    $response = $this->doRequest( 'POST', '/', array( 'Content-Type' => 'text/plain'), false );
    $this->send( "Hello" );
    $this->assertNull( $this->request->body );
  }
  
  private function doRequest( $method = 'GET', $path = '/', $headers = array(), $auto_close = true ){
    
    $request_headers = new \Phluid\Http\Headers( $method, $path, 'HTTP', '1.1', $headers );
    $this->request = $request = new \Phluid\Test\Request( $request_headers );
    $request->method = $method;
    $request->path = $path;
    $response = new \Phluid\Test\Response( $request );
    $this->http->emit( 'request', array( $request, $response ) );
    if ( $auto_close ) $request->send();
    return $response;
  }
  
  private function send( $data ){
    $this->request->send( $data );
  }
  
  private function sendFile( $file ){
    $this->request->sendFile( $file );
  }
  
  function setUp(){
    
    $this->app = new App();
    $this->app->post( '/', function( $request, $response, $next ){
      $response->renderString('Hello World');
    } );
    $this->http = new \Phluid\Test\Server();
    $this->app->createServer( $this->http );
    
  }
  
}