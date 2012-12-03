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
    } catch( Exception $e ){
      $caught = true;
      $this->assertSame( 'No route matching GET /doesnt-exist', $e->getMessage() );
    }
    $this->assertTrue( $caught, "No exception was raised." );
    
  }
  
  public function testExceptionHandler(){
    
    $this->app->inject( new HandleException() );
    $response = $this->doRequest( 'GET', 'doesnt-exists' );
        
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
    
    $response = $this->doRequest( 'POST', '/robot', array(), false );
    $response->on( 'end', function() use( $response ){
      $this->assertSame( '18', $response->getBody() );
    });
    
    $this->send( '?something=awesome' );
  }
  
  private function doRequest( $method = 'GET', $path = '/', $headers = array(), $auto_close = true ){
    
    $request_headers = new \Phluid\Http\Headers( $method, $path, 'HTTP', '1.1', $headers );
    $this->request = $request = new RequestStub( $request_headers );
    $request->method = $method;
    $request->path = $path;
    $response = new ResponseStub( $request );
    $this->http->emit( 'request', array( $request, $response ) );
    if ( $auto_close ) $request->send();
    return $response;
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

class RequestStub extends \Phluid\Http\Request {
    
  function __construct( $headers ){
    parent::__construct( new ConnectionStub() );
    $this->headers = $headers;
    $this->emit( 'headers', array( $headers ) );
  }
  
  public function send( $body = null ){
    
    if ( $body != null ) {
      while( strlen( $body ) > 0 ){
        $part = substr( $body, 0, 1024 );
        $body = substr( $body, 1024 );
        $this->emit( 'data', array( $part ) );
      }
    }
    $this->close();
    
  }
  
  public function isReadable(){
    return $this->readable;
  }
  
  public function pause(){
    $this->emit( 'pause' );
  }
  
  public function resume(){
    $this->emit( 'resume' );
  }
  
  public function close(){
    $this->readable = false;
    $this->emit( 'end' );
    $this->removeAllListeners();
  }
  
  public function pipe( \React\Stream\WritableStreamInterface $dest, array $options = array() ){
    \React\Util::pipe( $this, $dest, $options );
    return $dest;
  }
  
}

class ResponseStub extends \Phluid\Http\Response {
  
  protected $body = "";
  
  function __construct( $request ){
    $conn = new ConnectionStub();
    parent::__construct( $conn, $request );
  }
  
  public function writeHead( $status = 200, $headers = array() ){
    parent::writeHead( $status, $headers );
    $this->captureBody = true;
  }
  
  function write( $data ){
    $this->body .= $data;
    parent::write( $data );
  }
  
  function getBody(){
    return $this->body;
  }
    
}