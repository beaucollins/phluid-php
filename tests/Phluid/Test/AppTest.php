<?php
namespace Phluid\Test;

class AppTest extends TestCase {
  
  public function testSettings(){
    
    $this->assertSame( $this->app->view_path, realpath('.') . '/views' );
    
    $this->app->prefix = 'test';
    $this->assertSame( 'test', $this->app->prefix );
    
  }
  
  public function testAppRoute(){
        
    $response = $this->doRequest();
    
    $this->assertSame( 'Hello World', $response->getBody() );
  }
  
  public function testFullRequest(){
    
    $this->app->view_path = realpath('.') . '/tests/Views';
    $this->app->get( '/users/:username' , function( $request, $response ){
      $response->render( 'profile', array( "username" => $request->param( 'username' ) ) );
    } );
    
    $response = $this->doRequest( 'GET', '/users/beau' );
    
    $this->assertSame( 'Hello beau', $response->getBody() );
      
  }
  
  public function testRouteActionAsObject(){
    
    $this->app->get( '/awesome', new HelloWorldAction );
    $response = $this->doRequest( 'GET', '/awesome' );
    
    $this->assertSame( 'Hello World!', $response->getBody() );
    
  }
  
  public function testMiddleware(){
    
    $this->app->inject( new Lol() );
    $response = $this->doRequest();
    $this->assertSame( 'LOL', $response->getBody() );
    
    $response = $this->doRequest();
    $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testNoMatchingResponse(){
    
    $caught = false;
    try {
      $response = $this->doRequest( 'GET', '/doesnt-exist' );
    } catch( \Exception $e ){
      $caught = true;
      $this->assertSame( 'No route matching GET /doesnt-exist', $e->getMessage() );
    }
    $this->assertTrue( $caught, "No exception was raised." );
    
  }
  
  public function testExceptionHandler(){
    
    $this->app->inject( new HandleException() );
    $response = $this->doRequest( 'GET', '/doesnt-exists' );
        
    $this->assertSame( 'Uh, Oh', $response->getBody() );
    
  }
  
  public function testPostRequest(){
    
    $this->app->post( '/robot', function( $request, $response ){
      $body = "";
      $request->on( 'data', function( $data ) use ( &$body ){
        $body .= $data;
      });
      $request->on( 'end', function() use ( &$body, $response ) {
        $response->renderString( strlen( $body ) );
      });
    } );
    
    $response = $this->doRequest( 'POST', '/robot', array(), array(), false );
    $response->on( 'end', function() use( $response ){
      $this->assertSame( '18', $response->getBody() );
    });
    
    $this->send( '?something=awesome' );
  }
  

  
  private function send( $data ){
    $this->request->send( $data );
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
    } catch (\Exception $e) {
      $response->renderString( 'Uh, Oh', 'text/plain', $e->getCode() );
    }
  }
  
  public function __toString(){
    return __CLASS__;
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

