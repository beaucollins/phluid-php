<?php
namespace Phluid\Test;
use Phluid\Response;
use React\Http\Response as HttpResponse;

class ResponseTest extends TestCase {
    
  function testResponseStatus(){
    $response = $this->makeResponse();
    $this->assertSame( 200, $response->getStatus() );
    $response->setStatus( 404 );
    $this->assertSame( 404, $response->getStatus() );
  }
  
  function testResponseDate(){
    $response = $this->makeResponse();
    $this->assertNotNull( $response->getHeader( 'Date' ) );
    $now = new \DateTime( 'now' );
    $this->assertSame( $response->getHeader( 'Date' ), $now->format( \DateTime::RFC1123 ) );
  }
  
  function testSendFile(){
    $response = $this->makeResponse();
    $response->sendFile( $this->fileFixture( 'style.css' ) );
    
    $this->assertNotNull( $response->getHeader( 'last-modified' ) );
    $this->assertSame( $response->getHeader( 'content-length'), '11' );
  }
  
  function testSendMissingFileFails(){
    $response = $this->makeResponse();
    $response->sendFile( $this->fileFixture( 'lolcats.txt' ) );
    
    $this->assertSame( $response->getStatus(), 404 );
  }
  
  function makeResponse(){
    $http = new HttpResponse( $this->connection );
    $request = $this->makeRequest();
    return new Response( $http, $request );
  }
  
}