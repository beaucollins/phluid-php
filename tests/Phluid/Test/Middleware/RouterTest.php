<?php
namespace Phluid\Test\Middleware;
use Phluid\Middleware\Router;
use Phluid\RequestMatcher;
use Phluid\Test\TestCase;

class RouterTest extends TestCase {
  
  function testFindsRoute(){
    $router = new Router();
    $matcher = new RequestMatcher( 'GET', '/:path?' );
    $route = $router->route( $matcher, function( $request, $response){
      $response->renderText( $request->path );
    } );
    $this->app->inject( $router );
    
    $response = $this->doRequest( 'GET', '/something' );
    $this->assertSame( $this->getBody(), '/something' );
    $response = $this->doRequest( 'GET', '/' );
    $this->assertSame( $this->getBody(), '/' );
    
    $this->assertTrue( true );

  }

}