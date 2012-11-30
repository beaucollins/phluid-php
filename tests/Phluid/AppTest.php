<?php
namespace Phluid;

require_once 'tests/helper.php';
use Phluid\Http\Request;
use Phluid\Tests\ConnectionStub;

class AppTest extends \PHPUnit_Framework_TestCase {
  
  
  public function setUp(){
    
    $this->app = new App();
    $this->app->get( '/', function( $request, $response, $next ){
      $response->renderString('Hello World');
    } );
    $this->http = new ServerStub();
    $this->app->createServer( $this->http );
  }
  
  public function testSettings(){
    
    $this->assertSame( $this->app->view_path, realpath('.') . '/views' );
    
    $this->app->prefix = 'test';
    $this->assertSame( 'test', $this->app->prefix );
    
  }
  
  public function testAppRoute(){
    
    $connection = new ConnectionStub();
    $request = new Request( $connection );
    
    // $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testFullRequest(){
    
    $connection = new ConnectionStub();
    $this->app->view_path = realpath('.') . '/tests/Views';
    $response = $this->app
      ->get( '/users/:username' , function( $request, $response ){
        $response->render( 'profile', array( "username" => $request->param( 'username' ) ) );
      } );
    
    // $this->assertSame( 'Hello beau', $response->getBody() );
      
  }
  
  public function testRouteActionAsObject(){
    
    $this->app->get( '/awesome', new HelloWorldAction );
    $connection = new ConnectionStub();
    $request = new Http\Request( $connection );
    
    // $this->assertSame( 'Hello World!', $response->getBody() );
    
  }
  
  public function testMiddleware(){
    
    $this->app->inject( new Lol() );
    
    $connection = new ConnectionStub();
    $request = new Request( $connection );
        
    // $this->assertSame( 'LOL', $response->getBody() );
    
    // $response = $this->app->serve( $request );
    // $this->assertSame( 'Hello World', $response->getBody() );
    
  }
  
  public function testNoMatchingResponse(){
    
    $connection = new ConnectionStub();
    $request = new Request( $connection );
    try {
      $response = $this->app->serve( $request );
    } catch( Exception $e ){
      $this->assertSame( 'No route matching GET /doesnt-exist', $e->getMessage() );
    }
    
  }
  
  public function testExceptionHandler(){
    $app = new App();
    $handler = new HandleException();
    $app->inject( $handler );
    $connection = new ConnectionStub();
    $request = new Request( $connection );
    $response = $app->serve( $request );
        
    $this->assertSame( 'Uh, Oh', $response->getBody() );
    
  }
  
  public function testPostRequest(){
    
    $this->app->post( '/robot', function( $request, $response ){
      $response->renderString( strlen( $request->getBody() ) );
    } );
    
    $connection = new ConnectionStub();
    $request = new Request( $connection );
     
    $response = $this->app->serve( $request );
    
    $this->assertSame( '18', $response->getBody() );
    
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
    } catch (Exception $e) {
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

class ServerStub extends \Evenement\EventEmitter implements \React\Http\ServerInterface {
  
}