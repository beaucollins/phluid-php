<?php

require_once 'lib/Phluid/Request.php';
require_once 'lib/Phluid/Middleware/JsonBodyParser.php';

class Phluid_Middleware_BodyParserTest extends PHPUnit_Framework_TestCase {
  
  public function testJsonParsing(){
    
    $thing = new stdClass();
    $thing->awesome = "YES";
    
    $request = new Phluid_Request( 'POST', '/', array( 'Content-Type' => 'application/json' ), json_encode( $thing ) );
    
    $this->assertSame( json_encode( $thing ), $request->getBody() );
    
    $parser = new Phluid_Middleware_JsonBodyParser( false );
    
    $next = function() use( $request, $thing ) {
      $this->assertSame( $thing->awesome, $request->getBody()->awesome );
    };
    $parser( $request, null, $next );
    
  }
  
}