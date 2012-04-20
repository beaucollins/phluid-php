<?php

require_once 'Classes/App.php';

class AppTest extends PHPUnit_Framework_TestCase {
  
  
  public function setUp(){
    
    $this->app = new App();
    $this->app->get( '/', function( $request, $response ){
      $response->renderString('Hello World');
    } );
    
  }
  
  public function testAppRoute(){
    
    
    $request = new Request( 'GET', '/', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testFullRequest(){
    View::$directory = realpath('.') . '/Tests/Views';
    $response = $this->app
      ->get( '/users/:username' , function( $request, $response ){
        $response->render( 'profile', array( "username" => $request->param( 'username' ) ) );
      } )
      ->serve( new Request( 'GET', '/users/beau' ) );
    
      $this->assertSame( 'Hello beau', $response->getBody() );
      
  }
  
  public function testRouteActionAsObject(){
    
    $this->app->get( '/awesome', new HelloWorldAction );
    
    $request = new Request( 'GET', '/awesome', array() );
    $response = $this->app->serve( $request );
    
    $this->assertSame( 'Hello World!', $response->getBody() );
    
  }
  
  public function testMiddleware(){
    
    $this->app->inject( new Lol() );
    
    $request = new Request( 'GET', '/' );
    
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
