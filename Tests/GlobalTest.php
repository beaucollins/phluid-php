<?php

require_once 'Classes/Global.php';

class GlobalTest extends PHPUnit_Framework_TestCase {
  
  public function testGlobal(){
    global $app;
    
    get( '/', function( $request, $response ){
      $response->renderString( 'awesome' );
    });
    
    $request = new Request( 'GET', '/' );
    $response = $app->serve( $request );
    
    $this->assertSame( 'awesome', $response->getBody() );
    
    
  }
  
}