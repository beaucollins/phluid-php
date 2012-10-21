<?php

namespace Phluid\Middleware;

use Phluid\Request;

require_once 'test/helper.php';

class BodyParserTest extends \PHPUnit_Framework_TestCase {
  
  public function testJsonParsing(){
    
    $thing = new \stdClass();
    $thing->awesome = "YES";
    
    $request = new Request( 'POST', '/', array( 'Content-Type' => 'application/json' ), json_encode( $thing ) );
    
    $this->assertSame( json_encode( $thing ), $request->getBody() );
    
    $parser = new JsonBodyParser( false );
    
    $next = function() use( $request, $thing ) {
      $this->assertSame( $thing->awesome, $request->getBody()->awesome );
    };
    $parser( $request, null, $next );
    
  }
  
}