<?php
namespace Phluid\Test\Middleware;
use Phluid\Middleware\Prefix;
use Phluid\Request;

class PrefixTest extends \Phluid\Test\TestCase {
  
  function testNamespace(){
    
    $prefix = new Prefix( "/app" );
    
    $this->app->inject( $prefix );
    
    $this->app->get( '/', function( $req, $res ){
      $this->assertSame( array( '/app' ), $req->prefix );
    } );
    
    $response = $this->doRequest( 'GET', '/app/' );
    
    $this->assertSame( array(), $this->request->prefix );
    
  }
  
}