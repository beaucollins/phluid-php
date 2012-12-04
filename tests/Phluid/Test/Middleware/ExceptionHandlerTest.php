<?php

namespace Phluid\Middleware;
use Phluid\Request;
use Phluid\App;

class ExceptionHandlerTest extends \Phluid\Test\TestCase {
  
  function testRenderTemplate(){
    
    $handler = new ExceptionHandler();
    
    $this->app->get( '/gone', $handler, function( $request, $response ){
      // this template does not exist
      $response->render( 'lol' );
    });
    
    $response = $this->doRequest( 'GET', '/gone' );
    $this->assertTag( array( 'tag' => 'title', 'content' => 'Application Error:' ), $response->getBody() );
    
  }
  
}