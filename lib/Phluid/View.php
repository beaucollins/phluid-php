<?php
/**
 * Represents HTML or any content for that matter to be rendered as the body for a response
 * It needs to know the template name as well as the directory the templates live in. When
 * rendering it starts a new output buffer, includes the template, stores the buffer and 
 * cleans it. This way we can allow layouts to nest view inside them.
 * 
 * A layout is simply a view that has an `echo $content` somewhere in it.
 */
namespace Phluid;

class View {
    
  private $template;
  private $path;
  private $layout;
  
  function __construct( $template, $layout = null, $path = null ){
    $this->template = $template;
    $this->layout = $layout;
    $this->path = $path;
  }
  
  /**
   * include a PHP file with the given locals as variables available to it
   */
  public function render( $locals = array(), $content = null ){
    
    $path = $this->fullPath();
    $compile = function( $path, $locals ) {
      extract($locals);
      return @include $path;
    };
    $context = new ViewContext( $this->path, $content );
    $render = $compile->bindTo( $context );
    ob_start();
    $included = $render( $path, $locals );
    $content = ob_get_clean();
    
    if( $included === false )
      throw new Exception\MissingView( "Missing template " . $this->fullPath() );
    
    if ( $layout = $this->getLayout( $context->getLayout() ) ) {
      $content = $layout->render( array_merge( $locals ), $content );
    }
    
    return $content;
    
  }
  
  public function getLayout( $layout = null ){
    $use_layout = $layout ?: $this->layout;
    if ( $use_layout ) {
      return new View( $use_layout, null, $this->path );
    }
  }
    
  public function fullPath(){
    return $this->path . '/' . $this->template . '.php';
  }
  
}

class ViewContext {
  
  private $layout = false;
  private $layout_content;
  private $view_path = null;
  
  function __construct( $view_path, $content = null ){
    $this->view_path = $view_path;
    $this->layout_content = $content;
  }
  
  static function esc_html( $content ){
    return htmlentities( $content );
  }
  
  public function layout( $layout ){
    $this->layout = $layout;
  }
  
  public function getLayout(){
    return $this->layout;
  }
  
  public function content(){
    return $this->layout_content;
  }
  
  public function fragment( $name, $locals ){
    
    $paths = explode( DIRECTORY_SEPARATOR, $name );
    array_push( $paths, '_' . array_pop( $paths ) );
    array_unshift( $paths, $this->view_path );
    
    $file = implode( DIRECTORY_SEPARATOR, $paths ) . '.php';
    
    $compile = function( $file, $locals ) {
      extract( $locals );
      return @include $file;
    };
    
    $context = new ViewContext( $this->view_path );
    $render = $compile->bindTo( $context );
    
    ob_start();
    $included = $render( $file, $locals );
    $content = ob_get_clean();
    
    if ( false === $included ) 
      throw new Exception\MissingView( "Missing template " . $file );
    
    return $content;
    
  }
  
}

namespace Phluid\Exception;
use Phluid\Exception;

class MissingView extends Exception {
  function __construct( $message ){
    parent::__construct( $message, 404 );
  }
}
