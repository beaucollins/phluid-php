<?php

namespace Phluid;

class ViewTest extends \PHPUnit\Framework\TestCase {

  /**
   * @before
   */
  public function setUpViewPach(){
    $this->view_path = realpath('.') . '/tests/Views';
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
    
    $view = new View( 'hello-html', null, $this->view_path );
    
    $this->assertNull( $view->getLayout() );
    $this->assertSame( "<!DOCTYPE html>\nHello World<footer></footer>", $view->render() );
    
  }
  
  public function testFragment(){
    
    $view = new View( 'fragment-test', null, $this->view_path );
    $name = "sam";
    $this->assertSame( "Hello world, {$name}", $view->render( array( 'name' => $name ) ) );
    
  }
  
  public function testMissingViewException(){
    $view = new View( 'gone' );
    try {
      $view->render();
    } catch ( Exception\MissingView $e) {
      
      $this->assertSame( "Missing template /gone.php", $e->getMessage() );
      
    }
  }
  
}
