<?php

require_once 'Classes/Global.php';

class GlobalTest extends PHPUnit_Framework_TestCase {
  
  public function testGlobal(){
    global $app;
    
    get( '/', function( $request, $response ){
      $response->renderString( 'awesome' );
    });
    
    $response = $app->serve( 'GET', '/' );
    
    $this->assertSame( 'awesome', $response->getRawResponse() );
    
    
  }
  
}