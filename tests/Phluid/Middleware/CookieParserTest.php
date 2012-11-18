<?php
namespace Phluid\Middleware;
use Phluid\Request;
use Phluid\Response;

class CookieParserTest extends \PHPUnit_Framework_TestCase {
  
  function testCookies() {
    
    
    $request = new Request( 'GET', '/', array(), array(
      'Cookie' => 'something=awesome'
    ));
      
    $this->cookies( $request );
    
    $this->assertNotNull( $request->cookies  );
    $this->assertArrayHasKey( 'something', $request->cookies );
    
  }
  
  function testUglyCookie(){
    
    $cookie = '__utma=55650728.1809615938.1317503009.1353223329.1353260336.1061; __utmb=55650728.1.10.1353260336; __utmc=55650728; __utmz=55650728.1352924698.1053.58.utmcsr=google.com|utmccn=(referral)|utmcmd=referral|utmcct=/reader/view/; reddit_first=%7B%22organic_pos%22%3A%206%2C%20%22firsttime%22%3A%20%22first%22%7D';
    
    $request = new Request( 'GET', '/', array(), array(
      'cookie' => $cookie
    ));
    
    $this->cookies( $request );
    
    $this->assertNotNull( $request->cookies );
    $this->assertArrayHasKey( '__utma', $request->cookies );
    $this->assertSame( '{"organic_pos": 6, "firsttime": "first"}', $request->cookies['reddit_first'] );
    
  }
  
  private function cookies( $request ){
    $parser = new CookieParser();
    $parser( $request, null, function(){} );
  }
  
}

