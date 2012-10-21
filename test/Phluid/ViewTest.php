<?php

namespace Phluid;

require_once 'test/helper.php';

class ViewTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp(){
    $this->view_path = realpath('.') . '/test/Views';
  }
  
  public function testPath(){
    
    $view = new View( 'home', null, $this->view_path );
    
    $this->assertSame( $this->view_path . '/home.php', $view->fullPath() );
    $this->assertFileExists( $view->fullPath() );
  }
  
  public function testCompilation(){
    
    $hello_world = new View( 'hello', null, $this->view_path );
    $this->assertSame( 'Hello World', $hello_world->render() );
    
    $greeting = new View( 'home', null, $this->view_path );
    
    $this->assertSame( 'Hi Beau, how are you?', $greeting->render( array( 'name' => 'Beau' ) ) );
    
  }
  
  public function testLayout(){
      
    $view = new View( 'hello', 'layout', $this->view_path );
    
    $this->assertNotNull( $view->getLayout() );
    $this->assertSame( '<html>Hello World</html>', $view->render() );
    
    
  }
  
  public function testMissingViewException(){
    $view = new View( 'gone' );
    try {
      $view->render();
    } catch ( Exception_MissingView $e) {
      
      $this->assertSame( "Missing template /gone.php", $e->getMessage() );
      
    }
  }
  
}