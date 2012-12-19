<?php

namespace Phluid\Middleware;

use Phluid\Test\TestCase;
use Phluid\Http\Request;

class CascadeTest extends TestCase {
  
  function testCascade(){
    
    $incrementer = function( $request, $response, $next ){
      if ( property_exists( $request, 'inc_value' ) ) {
        $request->inc_value ++;
      } else {
        $request->inc_value = 1;
      }
      $next();
    };
    
    $cascade = new Cascade( array(
      $incrementer,
      $incrementer,
      $incrementer
    ) );
      
    $this->app->get( '/increment', $cascade, function( $request, $response, $next ){
      $request->done = true;
    });
    
    $response = $this->doRequest( 'GET', '/increment' );
    
    $this->assertSame( 3, $this->request->inc_value );
    $this->assertTrue( $this->request->done );
    
    // Should be able to run a second time with no side effects
    
    $response = $this->doRequest( 'GET', '/increment' );
    
    $this->assertSame( 3, $this->request->inc_value );
    $this->assertTrue( $this->request->done );
    
  }
  
}