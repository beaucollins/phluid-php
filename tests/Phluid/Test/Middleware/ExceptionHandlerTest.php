<?php

namespace Phluid\Middleware;
use Phluid\Request;
use Phluid\App;
use PHPUnit\Framework\DOMTestTrait;

class ExceptionHandlerTest extends \Phluid\Test\TestCase {
  use DOMTestTrait;

  function testRenderTemplate(){
    
    $handler = new ExceptionHandler();
    
    $this->app->get( '/gone', $handler, function( $request, $response ){
      // this template does not exist
      $response->on('data', function(){
        echo "Data?" . PHP_EOL;
      });
      $response->render( 'lol' );
    });
    
    $response = $this->doRequest( 'GET', '/gone' );
    $this->assertSelectEquals( 'title', 'Application Error:', true, $this->getBody() );
    
  }
  
}