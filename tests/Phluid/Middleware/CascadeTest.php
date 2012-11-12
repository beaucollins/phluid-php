<?php

namespace Phluid\Middleware;

use Phluid\Request;

class CascadeTest extends \PHPUnit_Framework_TestCase {
  
  function testCascade(){
    
    $incrementer = function( $request, $response, $next ){
      if ( $request->inc_value ) {
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
    
    $request = new Request( 'GET', '/' );
    $cascade( $request, null, function() use ( $request ){
      $request->done = true;
    } );
    
    $this->assertSame( 3, $request->inc_value );
    $this->assertTrue( $request->done );
    
    // Should be able to run a second time with no side effects
    
    $request = new Request( 'GET', '/' );
    $cascade( $request, null, function() use ( $request ){
      $request->done = true;
    } );
    $this->assertSame( 3, $request->inc_value );
    $this->assertTrue( $request->done );
    
    
  }
  
}