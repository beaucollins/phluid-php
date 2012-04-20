<?php

require_once 'lib/Phluid/App.php';

class Phluid_AppTest extends PHPUnit_Framework_TestCase {
  
  
  public function setUp(){
    
    $this->app = new Phluid_App();
    $this->app->get( '/', function( $request, $response ){
      $response->renderString('Hello World');
    } );
    
  }
  
  public function testAppRoute(){
    
    
    $request = new Phluid_Request( 'GET', '/', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testFullRequest(){
    Phluid_View::$directory = realpath('.') . '/test/Views';
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
  
}

class HelloWorldAction {
  
  private $response = "Hello World!";
  
  public function __invoke( $request, $response ){
    $response->renderString( $this->response );
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
  
}
