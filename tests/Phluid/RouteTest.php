<?php

namespace Phluid;

require_once 'tests/helper.php';

class RouteTest extends \PHPUnit_Framework_TestCase {
  
  public function getIndex( $request, $response ){
    $response->renderText( 'hello world' );
  }
  
  public function testInvokingRoute(){
    $app = new App();
    $app->get( '/show/:person', function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $app( new Request( 'GET', '/show/beau' ) );    
    $this->assertSame( 'beau', $response->getBody() );
    
  }
  
  public function testRouteWithArrayCallback(){
    $app = new App();
    $app->get( '/', array( $this, 'getIndex' ) );
    $response = $app( new Request( 'GET', '/' ) );
    $this->assertSame( 'hello world', $response->getBody() );
  }
  
  public function testInvokigRouteWithFilters(){
    $app = new App();
    $reverse = function( $request, $response, $next ){
      $next();
      $response->setBody( strrev( $response->getBody() ) );
    };
    $app->get( '/show/:person', $reverse, function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $app( new Request( 'GET', '/show/beau' ) );    
    $this->assertSame( strrev('beau'), $response->getBody() );
  }
  
  public function testInvokingRouteWithRedirectFilter(){
    $app = new App();
    $redirect = function( $request, $response, $next ){
      $response->redirect('/somewhere');
    };
    $app->get( '/', $redirect, function( $request, $response ){
    });
    $response = $app( new Request( 'GET', '/' ) );
    $this->assertSame( '/somewhere', $response->getHeader('location') );
    
  }

}