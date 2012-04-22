<?php

require_once 'lib/Phluid/App.php';

class Phluid_AppTest extends PHPUnit_Framework_TestCase {
  
  
  public function setUp(){
    
    $this->app = new Phluid_App();
    $this->app->get( '/', function( $request, $response ){
      $response->renderString('Hello World');
    } );
    
  }
  
  public function testSettings(){
    
    $this->assertSame( $this->app->view_path, realpath('.') . '/views' );
    
    $this->app->prefix = 'test';
    $this->assertSame( 'test', $this->app->prefix );
    
  }
  
  public function testAppRoute(){
    
    
    $request = new Phluid_Request( 'GET', '/', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testFullRequest(){
    
    $this->app->view_path = realpath('.') . '/test/Views';
    $response = $this->app
      ->get( '/users/:username' , function( $request, $response ){
        $response->render( 'profile', array( "username" => $request->param( 'username' ) ) );
      } )
      ->serve( new Phluid_Request( 'GET', '/users/beau' ) );
    
      $this->assertSame( 'Hello beau', $response->getBody() );
      
  }
  
  public function testRouteActionAsObject(){
    
    $this->app->get( '/awesome', new HelloWorldAction );
    
    $request = new Phluid_Request( 'GET', '/awesome', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World!', $response->getBody() );
    
  }
  
  public function testMiddleware(){
    
    $this->app->inject( new Lol() );
    
    $request = new Phluid_Request( 'GET', '/' );
    
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'LOL', $response->getBody() );
    
    $response = $this->app->serve( $request );
    $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testNoMatchingResponse(){
    
    $request = new Phluid_Request( 'GET', '/doesnt-exist' );
    try {
      $response = $this->app->serve( $request );
    } catch( Exception $e ){
      $this->assertSame( 'No more routes', $e->getMessage() );
    }
    
  }
  
  public function testExceptionHandler(){
    $app = new Phluid_App();
    $handler = new HandleException();
    $app->inject( $handler );
    
    $request = new Phluid_Request( 'GET', '/doesnt-exist' );
    $matches = $app->matching( $request );
    $this->assertSame( 1, count( $matches ) );
    $this->assertSame( $handler, $matches[0] );
    
    $response = $app( $request );
    
    $this->assertSame( 'Uh, Oh', $response->getBody() );
    
  }
  
  public function testPostRequest(){
    
    $this->app->post( '/robot', function( $request, $response ){
      
    } );
    
  }
  
}

class HelloWorldAction {
  
  private $response = "Hello World!";
  
  public function __invoke( $request, $response ){
    $response->renderString( $this->response );
  }
  
}

class HandleException {
  
  public function __invoke( $request, $response, $next ){
    try {
      $next();
    } catch (Phluid_Exception $e) {
      $response->renderString( 'Uh, Oh', 'text/plain', $e->getCode() );
    }
  }
  
  public function __toString(){
    return __CLASS__ . ": what's wrong?";
  }
  
}

class Lol {
  
  var $lol = true;
  
  function __invoke( $req, $res, $next ){
    if ($this->lol) {
      $this->lol = false;
      $res->renderString( "LOL" );
    } else {
      $next();
    }
  }
  
  function __toString(){
    return __CLASS__ . ' : ' . ($this->lol ? 'will LOL' : 'wont LOL' );
  }
  
}
