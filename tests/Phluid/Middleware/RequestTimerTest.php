<?php

namespace Phluid\Middleware;

use Phluid\App;
use Phluid\Request;

class RequestTimerTest extends \PHPUnit_Framework_TestCase {
  
  function testTimer(){
    
    $app = new App();
    $app->get( '/', new RequestTimer(), function( $request, $response, $next ){
      time_nanosleep( 0, 0.03 * 1000000000 );
    } );
    
    $response = $app(new Request( 'GET', '/' ) );
    $this->assertGreaterThan( 10, intval( $response->getHeader('x-response-time') ) );
    
  }
  
}