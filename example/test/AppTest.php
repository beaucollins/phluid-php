<?php

use Phluid\Request;

class AppTest extends PHPUnit_Framework_TestCase {
  
  public $app;
  
  function setup(){
    require realpath('.') . '/App.php';
    $this->app = $app;
  }
  
  
  function buildRequest( $method, $path = "/" ){
    return new Request( $method, $path );
  }
  
  function testIndexRequest(){
    //fake request
    $app = $this->app;
    $req = $this->buildRequest( 'GET', '/' );
    $res = $app->serve( $req );
    
    $this->assertSame( 'Hello World', $res->getBody() );
  }
  
  function testTemplateRequest(){
    $app = $this->app;
    $req = $this->buildRequest( 'GET', '/profile' );
    $res = $app->serve( $req );
    
    $responseText = <<<RESPONSE
Hello <a href="http://viewsource.beaucollins.com">Beau Collins</a>    
RESPONSE;
    
    $this->assertSame( trim($responseText), $res->getBody() );
    
  }
  
  function testMiddleware(){
    
    $req = $this->buildRequest( 'GET', '/' );
    $res = $this->app->serve( $req );
    
    $this->assertSame( 'Awesomesauce', $res->getHeader( 'X-SERVER' ) );
    
  }
  
  function testMiddlewareClosure(){
    $req = $this->buildRequest( 'GET', '/reverse' );
    $res = $this->app->serve( $req );
    
    $this->assertSame( strrev('Hello World'), $res->getBody() );
    
    $responseText = <<<RESPONSE
Hello <a href="http://viewsource.beaucollins.com">Beau Collins</a>    
RESPONSE;
    
    $req = $this->buildRequest( 'GET', '/profile/reverse' );
    $res = $this->app->serve( $req );
    $this->assertSame( strrev(trim($responseText)), $res->getBody() );
    
  }
  
  
}