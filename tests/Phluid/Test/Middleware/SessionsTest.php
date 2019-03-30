<?php
namespace Phluid\Test\Middleware;
use Phluid\Test\TestCase;
use Phluid\Middleware\Sessions;
use Phluid\Middleware\Sessions\MemoryStore as SessionsMemoryStore;
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
      
      $response->renderText( ':)' );
    } );
    
    $response = $this->doRequest( 'GET', '/session' );
    $this->assertSame( 'mark', $this->request->session->name );
    $this->assertSame( $this->request->session->name, $this->request->session['name'] );
    $this->assertSame( 1, $this->request->session->visits );
    $this->assertSame( ':)', $this->getBody() );
    
    $sid = $response->cookies[$this->sessions->key]->value;
    $response = $this->doRequest( 'GET', '/session', array(), array( 'Cookie' => "{$this->sessions->key}={$sid}" ) );
    $this->assertSame( 2, $this->request->session->visits );
    
    $this->assertSame( ':)', $this->getBody() );
    
  }
  
  /**
   * @before
   */
  function injectCookies(){
    $this->app->inject( new Cookies() );
    $session_store = new SessionsMemoryStore( array( 'testsession' => array( 'hello' => 'world' ) ) );
    $this->sessions = new Sessions( array( 'secret' => 'lol', 'store' => $session_store  ) );
    $this->app->inject( $this->sessions );
  }
  
}
