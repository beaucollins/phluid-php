<?php
namespace Phluid\Test\Middleware;
use Phluid\Test\TestCase;
use Phluid\Middleware\Cookies;
use Phluid\Middleware\Cookies\Cookie;

class CookiesTest extends TestCase {
  
  function testCookies() {
    
    $this->doRequest( 'GET', '/', array(), array( 'Cookie' => 'something=awesome') );
    
    $this->assertObjectHasAttribute( 'cookies', $this->request  );
    $this->assertArrayHasKey( 'something', $this->request->cookies );
    
  }
  
  function testUglyCookie(){
    
    $cookie = '__utma=55650728.1809615938.1317503009.1353223329.1353260336.1061; __utmb=55650728.1.10.1353260336; __utmc=55650728; __utmz=55650728.1352924698.1053.58.utmcsr=google.com|utmccn=(referral)|utmcmd=referral|utmcct=/reader/view/; reddit_first=%7B%22organic_pos%22%3A%206%2C%20%22firsttime%22%3A%20%22first%22%7D';
    
    $this->doRequest( 'GET', '/', array(), array(
      'cookie' => $cookie
    ));
    
    $request = $this->request;
    $this->assertNotNull( $request->cookies );
    $this->assertArrayHasKey( '__utma', $request->cookies );
    $this->assertSame( '{"organic_pos": 6, "firsttime": "first"}', $request->cookies['reddit_first'] );
    
  }
  
  function testSettingCookie() {
    $this->app->get( '/remember', function( $request, $response, $next ){
      $response->cookies['something'] = 'test';
      $response->renderText( 'done' );
    } );
    $response = $this->doRequest( 'GET', '/remember' );
    $this->assertSame( 'something=test;', $response->getHeader( 'Set-Cookie' ) );
  }
  
  function testSettingMultipleCookies(){
    $this->app->get( '/remember', function( $request, $response, $next ){
      $response->cookies['hello'] = 'world';
      $response->cookies['something'] = new Cookie( 'test', array( 'max_age' => 60 ) );
      $response->renderText( 'done' );
    } );
    
    $response = $this->doRequest( 'GET', '/remember' );
    
    $cookie_header = $response->getHeader( 'Set-Cookie' );
    $this->assertSame( 'hello=world;', $cookie_header[0] );
    $this->assertSame( 'something=test; Max-Age=60;', $cookie_header[1] );
  }
  
  private function cookies( $request ){
    $parser = new CookieParser();
    $parser( $request, null, function(){} );
  }

  /**
   * @before
   */
  function injectCookies(){
    $this->app->inject( new Cookies() );
  }
  
}

