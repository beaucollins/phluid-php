<?php


class AppTest extends PHPUnit_Framework_TestCase {
  
  public $app;
  
  function setup(){
    require realpath('.') . '/App.php';
    $this->app = $app;
  }
  
  function testIndexRequest(){
    //fake request
    $app = $this->app;
    $req = new Phluid_Request( 'GET', '/' );
    $res = $app->serve( $req );
    
    $this->assertSame( 'Hello World', $res->getBody() );
  }
  
  function testTemplateRequest(){
    $app = $this->app;
    $req = new Phluid_Request( 'GET', '/profile' );
    $res = $app->serve( $req );
    
    $responseText = <<<RESPONSE
Hello <a href="http://viewsource.beaucollins.com">Beau Collins</a>    
RESPONSE;
    
    $this->assertSame( trim($responseText), $res->getBody() );
    
  }
  
  function testMiddleware(){
    
    $req = new Phluid_Request( 'GET', '/' );
    $res = $this->app->serve( $req );
    
    $this->assertSame( 'Awesomesauce', $res->getHeader( 'X-SERVER' ) );
    
  }
  
  function testMiddlewareClosure(){
    $req = new Phluid_Request( 'GET', '/reverse' );
    $res = $this->app->serve( $req );
    
    $this->assertSame( strrev('Hello World'), $res->getBody() );
    
    $responseText = <<<RESPONSE
Hello <a href="http://viewsource.beaucollins.com">Beau Collins</a>    
RESPONSE;
    
    $req = new Phluid_Request( 'GET', '/profile/reverse' );
    $res = $this->app->serve( $req );
    $this->assertSame( strrev(trim($responseText)), $res->getBody() );
    
  }
  
  
}