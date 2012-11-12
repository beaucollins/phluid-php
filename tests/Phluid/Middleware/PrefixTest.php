<?php
namespace Phluid\Middleware;
use Phluid\App;
use Phluid\Request;

class PrefixTest extends \PHPUnit_Framework_TestCase {
  
  function testNamespace(){
    
    $prefix = new Prefix( "/app" );
    
    $app = new App();
    $app->inject( $prefix );
    
    $app->get( '/', function( $req, $res ){
      $this->assertSame( array( '/app' ), $req->prefix );
    } );
    
    $request = new Request( 'GET', '/app/' );
    $app->serve( $request );
    
    $this->assertSame( array(), $request->prefix );
    
  }
  
}