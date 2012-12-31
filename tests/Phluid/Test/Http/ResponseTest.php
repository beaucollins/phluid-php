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
  
  function testNotModified(){
    $response = $this->makeResponse();
    $response->setHeader( 'Content-Type', 'text/html' );
    $this->assertNotNull( $response->getHeader( 'Content-Type' ) );
    $response->sendNotModified();
    $this->assertSame( 304, $response->getStatus() );
    $this->assertNull( $response->getHeader( 'Content-Type' ) );
  }
  
  function testSendFile(){
    $response = $this->makeResponse();
    $response->sendFile( $this->fileFixture( 'style.css' ) );
    
    $lastModified = 'Sun, 02 Dec 2012 06:22:15 +0000';
    $this->assertSame( $response->getHeader( 'last-modified' ), $lastModified );
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