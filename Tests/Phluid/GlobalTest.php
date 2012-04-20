<?php

require_once 'lib/Phluid/Global.php';

class Phluid_GlobalTest extends PHPUnit_Framework_TestCase {
  
  public function testGlobal(){
    global $app;
    
    get( '/', function( $request, $response ){
      $response->renderString( 'awesome' );
    });
    
    $request = new Phluid_Request( 'GET', '/' );
    $response = $app->serve( $request );
    
    $this->assertSame( 'awesome', $response->getBody() );
    
    
  }
  
}