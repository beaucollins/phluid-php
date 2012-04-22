<?php

require_once 'lib/Phluid/View.php';


class Phluid_ViewTest extends PHPUnit_Framework_TestCase {
  
  public function setUp(){
    $this->view_path = realpath('.') . '/test/Views';
  }
  
  public function testPath(){
    
    $view = new Phluid_View( 'home', null, $this->view_path );
    
    $this->assertSame( $this->view_path . '/home.php', $view->fullPath() );
    $this->assertFileExists( $view->fullPath() );
  }
  
  public function testCompilation(){
    
    $hello_world = new Phluid_View( 'hello', null, $this->view_path );
    $this->assertSame( 'Hello World', $hello_world->render() );
    
    $greeting = new Phluid_View( 'home', null, $this->view_path );
    
    $this->assertSame( 'Hi Beau, how are you?', $greeting->render( array( 'name' => 'Beau' ) ) );
    
  }
  
  public function testLayout(){
      
    $view = new Phluid_View( 'hello', 'layout', $this->view_path );
    
    $this->assertNotNull( $view->getLayout() );
    $this->assertSame( '<html>Hello World</html>', $view->render() );
    
    
  }
  
  public function testMissingViewException(){
    $view = new Phluid_View( 'gone' );
    try {
      $view->render();
    } catch ( Phluid_Exception_MissingView $e) {
      
      $this->assertSame( "Missing template gone", $e->getMessage() );
      
    }
  }
  
}