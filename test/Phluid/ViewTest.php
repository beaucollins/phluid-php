<?php

require_once 'lib/Phluid/View.php';


class Phluid_ViewTest extends PHPUnit_Framework_TestCase {
  
  public function setUp(){
    Phluid_View::$directory = realpath('.') . '/test/Views';
  }
  
  public function testPath(){
    
    $view = new Phluid_View( 'home' );
    
    $this->assertSame( realpath('.') . '/test/Views/home.php', $view->fullPath() );
    $this->assertFileExists( $view->fullPath() );
  }
  
  public function testCompilation(){
    
    $hello_world = new Phluid_View( 'hello' );
    $this->assertSame( 'Hello World', $hello_world->render() );
    
    $greeting = new Phluid_View( 'home' );
    
    $this->assertSame( 'Hi Beau, how are you?', $greeting->render( array( 'name' => 'Beau' ) ) );
    
  }
  
  public function testLayout(){
      
    $view = new Phluid_View( 'hello', 'layout' );
    
    $this->assertNotNull( $view->getLayout() );
    $this->assertSame( '<html>Hello World</html>', $view->render() );
    
    
  }
  
}