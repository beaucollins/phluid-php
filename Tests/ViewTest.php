<?php

require_once 'Classes/View.php';


class ViewTest extends PHPUnit_Framework_TestCase {
  
  public function setUp(){
    View::$directory = realpath('.') . '/Tests/Views';
  }
  
  public function testPath(){
    
    $view = new View( 'home' );
    
    $this->assertSame( realpath('.') . '/Tests/Views/home.php', $view->fullPath() );
    $this->assertFileExists( $view->fullPath() );
  }
  
  public function testCompilation(){
    
    $hello_world = new View( 'hello' );
    $this->assertSame( 'Hello World', $hello_world->render() );
    
    $greeting = new View( 'home' );
    
    $this->assertSame( 'Hi Beau, how are you?', $greeting->render( array( 'name' => 'Beau' ) ) );
    
  }
  
  public function testLayout(){
      
    $view = new View( 'hello', 'layout' );
    
    $this->assertNotNull( $view->getLayout() );
    $this->assertSame( '<html>Hello World</html>', $view->render() );
    
    
  }
  
}