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
  
  function makeResponse(){
    $http = new HttpResponse( $this->connection );
    $request = $this->makeRequest();
    return new Response( $http, $request );
  }
  
}