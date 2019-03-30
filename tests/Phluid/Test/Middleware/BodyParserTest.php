<?php

namespace Phluid\Test\Middleware;

use Phluid\Middleware\JsonBodyParser;
use Phluid\Middleware\MultipartBodyParser;
use Phluid\Middleware\FormBodyParser;

class BodyParserTest extends \Phluid\Test\TestCase {
  
  public function testJsonParsing(){
    
    $thing = new \stdClass();
    $thing->awesome = "YES";
    
    $parser = new JsonBodyParser( false );
    $this->app->inject( $parser );
    
    $body = json_encode( $thing );
    $response = $this->doRequest( 'POST', '/', array(), array(
      'Content-Type' => 'application/json',
      'Content-Length' => strlen( $body )
    ), function( $request ) use ( $body ){
      $this->send( $body );
    } );
        
    $this->assertSame( $thing->awesome, $this->request->body->awesome );
    
  }
  
  public function testJsonParsingWithCharset() {
    
    $thing = new \stdClass();
    $thing->awesome = "YES";
    
    $parser = new JsonBodyParser( false );
    $this->app->inject( $parser );
    
    $body = json_encode( $thing );
    $response = $this->doRequest( 'POST', '/', array(), array(
      'Content-Type' => 'application/json; charset=UTF-8',
      'Content-Length' => strlen( $body )
    ), function( $request ) use ( $body ){
      $this->send( $body );
    } );
        
    $this->assertSame( $thing->awesome, $this->request->body->awesome );
  }
  
  public function testFormParsing(){
    
    $parser = new FormBodyParser();
    $values = array( 'field' => 'value' );
    $body = http_build_query( $values );
    $this->app->inject( $parser );
    
    $this->doRequest( 'POST', '/', array(), array(
      'Content-Type' => 'application/x-www-form-urlencoded',
      'Content-Length' => strlen( $body )
    ), function() use ( $body ){
      $this->send( $body );
    } );
    
    $this->assertSame( $values, $this->request->body );
    
  }
  
  public function testMultipartParsing(){
    
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $this->app->inject( $parser );
    $this->app->inject( function( $request, $response, $next ){

      $this->assertArrayHasKey( 'name', $request->body );
      $this->assertArrayHasKey( 'file', $request->body );
      
      $this->assertFileExists( (string) $request->body['file'] );
      $next();
      
    } );
    
    $response = $this->doRequest( 'POST', '/', array(), array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryoOeNyQKwEVuvehNw'
    ), function(){
      $this->sendFile( realpath('.') . '/tests/files/multipart-body' );
    });
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
    
    $response = $this->doRequest( 'POST', '/', array(), array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryAYD2hRdSJxpcdK2a'
    ), function(){
      $this->sendFile( realpath('.') . '/tests/files/multipart-assoc' );
    } );
    
  }
  
  public function testMultipartSkipsParsing(){
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $this->app->inject( $parser );
    
    $response = $this->doRequest( 'POST', '/', array(), array( 'Content-Type' => 'text/plain'), function(){
      $this->send( "Hello" );
    } );
    $this->assertObjectNotHasAttribute( 'body', $this->request );
  }
  
  /**
   * @before 
   */
  function runRequest(){
    $this->app->post( '/', function( $request, $response ){
      $response->renderText( "done" );
    } );
  }
}