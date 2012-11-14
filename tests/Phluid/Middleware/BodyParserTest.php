<?php

namespace Phluid\Middleware;

use Phluid\Request;

require_once 'tests/helper.php';

class BodyParserTest extends \PHPUnit_Framework_TestCase {
  
  public function testJsonParsing(){
    
    $thing = new \stdClass();
    $thing->awesome = "YES";
    
    $request = new Request( 'POST', '/', array(), array( 'Content-Type' => 'application/json' ), json_encode( $thing ) );
    
    $this->assertSame( json_encode( $thing ), $request->getBody() );
    
    $parser = new JsonBodyParser( false );
    
    $next = function() use( $request, $thing ) {
      $this->assertSame( $thing->awesome, $request->getBody()->awesome );
    };
    $parser( $request, null, $next );
    
  }
  
  public function testFormParsing(){
    
    $parser = new FormBodyParser();
    $body = array( 'field' => 'value' );
    $request = new Request( 'POST', '/', array(), array( 'Content-Type' => 'application/x-www-form-urlencoded' ), http_build_query( $body ) );
    $parser( $request, null, function() use($request, $body){
        
      $this->assertSame( $body, $request->getBody() );
        
    } );
  }
  
  public function testMultipartParsing(){
    
    $request = new Request( 'POST', '/', array(), array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryoOeNyQKwEVuvehNw'      
    ), file_get_contents( realpath('.') . '/tests/files/multipart-body' ) );
    
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $parser( $request, null, function() use( $request ){
      
      $this->assertArrayHasKey( 'name', $request->getBody() );
      $this->assertArrayHasKey( 'file', $request->getBody() );
      
      $this->assertFileExists( (string) $request->param('file') );
      
    });
    
  }
  
  public function testMultipartAssocParsing(){
    
    $request = new Request( 'POST', '/', array(), array(
      'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryAYD2hRdSJxpcdK2a'
    ), file_get_contents( realpath('.') . '/tests/files/multipart-assoc' ) );
    
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $parser( $request, null, function() use( $request ){
      
      $body = $request->getBody();
      $this->assertArrayHasKey( 'first', $body['name'] );
      $this->assertArrayHasKey( 'last', $body['name'] );
      
      $this->assertSame( 'Sammy', (string) $body['name']['first'] );
      $this->assertSame( 'Collins', (string) $body['name']['last'] );
      
      $this->assertArrayHasKey( 0, $body['file']['for'] );
      $this->assertArrayHasKey( 1, $body['file']['for'] );
      
      $this->assertFileExists( (string) $body['file']['for'][0] );
      $this->assertFileExists( (string) $body['file']['for'][1] );
      
    });
    
  }
  
  public function testMultipartSkipsParsing(){
    $request = new Request( 'POST', '/', array(), array( 'Content-Type' => 'text/plain'), "Hello" );
    $parser = new MultipartBodyParser( realpath( '.' ) . '/tests/uploads' );
    $parser( $request, null, function() use($request){
      $this->assertSame( "Hello", $request->getBody() );
    });
  }
  
}