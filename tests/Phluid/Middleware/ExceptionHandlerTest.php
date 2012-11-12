<?php

namespace Phluid\Middleware;
use Phluid\Request;
use Phluid\App;

require_once 'tests/helper.php';

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase {
  
  function testRenderTemplate(){
    
    $handler = new ExceptionHandler();
    
    $app = new App( array( 'view_path' => __DIR__ ) );
    
    $app->get( '/', $handler, function( $request, $response ){
      $response->render( 'lol' );
    });
    
    $response = $app->serve( new Request( 'GET', '/' ) );          
    
    $this->assertTag( array( 'tag' => 'title', 'content' => 'Application Error:' ), $response->getBody() );
    
  }
  
}