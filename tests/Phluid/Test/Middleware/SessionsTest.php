<?php
namespace Phluid\Test\Middleware;
use Phluid\Test\TestCase;
use Phluid\Middleware\Sessions;
use Phluid\Middleware\Cookies;

class SessionsTest extends TestCase {
  
  function testSession(){
    
    $response = $this->doRequest( 'GET', '/' );
    $this->assertNotNull( $this->request->sessionId );
    $this->assertNotNull( $this->request->session );
    $this->assertNotNull( $response->getHeader( 'set-cookie' ) );
  }
  
  function testSessionAccess(){
    
    $this->app->get( '/session', function( $request, $response ){
      $request->session->name = 'mark';
      if( !$request->session['visits'] ){
        $request->session['visits'] = 0;
      }
      $request->session['visits'] += 1;
      
      $response->renderString( ':)' );
    } );
    
    $response = $this->doRequest( 'GET', '/session' );
    $this->assertSame( 'mark', $this->request->session->name );
    $this->assertSame( $this->request->session->name, $this->request->session['name'] );
    $this->assertSame( 1, $this->request->session->visits );
    $this->assertSame( ':)', $response->getBody() );
    
    $sid = $response->cookies[$this->sessions->key]->value;
    $response = $this->doRequest( 'GET', '/session', array( 'Cookie' => "{$this->sessions->key}={$sid}" ) );
    $this->assertSame( 2, $this->request->session->visits );
    
    $this->assertSame( ':)', $response->getBody() );
    
  }
  
  function setUp(){
    
    parent::setUp();
    
    $this->app->inject( new Cookies() );
    $session_store = new SessionStoreStub( array( 'testsession' => array( 'hello' => 'world' ) ) );
    $this->sessions = new Sessions( array( 'secret' => 'lol', 'store' => $session_store  ) );
    $this->app->inject( $this->sessions );
  }
  
}

use Phluid\Middleware\Sessions\SessionStoreInterface;
class SessionStoreStub implements SessionStoreInterface {
  
  public $sessions;
  
  function __construct( $sessions = array() ){
    $this->sessions = $sessions;
  }
  
  public function find( $sid, $fn ) {
    $session = null;
    if( array_key_exists( $sid, $this->sessions ) ) $session = $this->sessions[$sid];
    $fn( $session );
  }
  
  public function save( $sid, $session, $fn ){
    $this->sessions[ $sid ] = $session;
    $fn();
  }
  
  public function destroy( $sid, $fn ){
    if ( array_key_exists( $sid, $this->sessions ) ) unset( $this->sessions[$sid] );
    $fn();
  }
   
}