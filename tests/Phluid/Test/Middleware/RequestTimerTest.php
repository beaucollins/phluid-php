<?php

namespace Phluid\Test\Middleware;

use Phluid\App;
use Phluid\Request;

class RequestTimerTest extends \Phluid\Test\TestCase {
  
  function testTimer(){
    
    $this->app->get( '/timed', new \Phluid\Middleware\RequestTimer(), function( $request, $response, $next ){
      time_nanosleep( 0, 0.03 * 1000000000 );
      $response->renderText( "DONE" );
    } );
    
    $response = $this->doRequest( 'GET', '/timed' );
    $this->assertGreaterThan( 10, $this->request->duration );
    
  }
  
}