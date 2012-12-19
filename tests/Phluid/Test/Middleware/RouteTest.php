<?php

namespace Phluid\Middleware\Test;
use Phluid\Route;
use Phluid\Test\TestCase;

class RouteTest extends TestCase {
  
  public function getIndex( $request, $response ){
    $response->renderText( 'hello world' );
  }
  
  public function testInvokingRoute(){
    $this->app->get( '/show/:person', function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $this->doRequest( 'GET', '/show/beau' );
    $this->assertSame( 'beau', $this->getBody() );
    
  }
  
  public function testRouteWithArrayCallback(){
    $this->app->get( '/hello', array( $this, 'getIndex' ) );
    $response = $this->doRequest( 'GET', '/hello' );
    $this->assertSame( 'hello world', $this->getBody() );
  }
  
  public function testInvokigRouteWithFilters(){
    $reverse = function( $request, $response, $next ){
      $params = $request->params;
      $params['person'] = strrev( $params['person'] );
      $request->params = $params;
      $next();
    };
    $this->app->get( '/show/:person', $reverse, function( $request, $response ){
      $response->renderText( $request->param('person') );
    });
    $response = $this->doRequest( 'GET', '/show/beau' );
    $this->assertSame( strrev('beau'), $this->getBody() );
  }
  
  public function testInvokingRouteWithRedirectFilter(){
    $redirect = function( $request, $response, $next ){
      $response->redirectTo('/somewhere');
    };
    $this->app->get( '/redirect', $redirect, function( $request, $response ){
    });
    $response = $this->doRequest( 'GET', '/redirect' );
    $this->assertSame( '/somewhere', $response->getHeader('location') );
    
  }

}