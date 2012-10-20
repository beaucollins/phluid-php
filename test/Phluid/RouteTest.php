<?php

require_once 'lib/Phluid/Route.php';


class Phluid_RouteTest extends PHPUnit_Framework_TestCase {
  
  public function getIndex( $request, $response ){
    $response->renderText( 'hello world' );
  }
  
  public function testInvokingRoute(){
    $app = new Phluid_App();
    $app->get( '/show/:person', function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $app( new Phluid_Request( 'GET', '/show/beau' ) );    
    $this->assertSame( 'beau', $response->getBody() );
    
  }
  
  public function testRouteWithArrayCallback(){
    $app = new Phluid_App();
    $app->get( '/', array( $this, 'getIndex' ) );
    $response = $app( new Phluid_Request( 'GET', '/' ) );
    $this->assertSame( 'hello world', $response->getBody() );
  }
  
  public function testInvokigRouteWithFilters(){
    $app = new Phluid_App();
    $reverse = function( $request, $response, $next ){
      $next();
      $response->setBody( strrev( $response->getBody() ) );
    };
    $app->get( '/show/:person', $reverse, function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $app( new Phluid_Request( 'GET', '/show/beau' ) );    
    $this->assertSame( strrev('beau'), $response->getBody() );
  }
  
  public function testInvokingRouteWithRedirectFilter(){
    $app = new Phluid_App();
    $redirect = function( $request, $response, $next ){
      $response->redirect('/somewhere');
    };
    $app->get( '/', $redirect, function( $request, $response ){
    });
    $response = $app( new Phluid_Request( 'GET', '/' ) );
    $this->assertSame( '/somewhere', $response->getHeader('location') );
    
  }

}