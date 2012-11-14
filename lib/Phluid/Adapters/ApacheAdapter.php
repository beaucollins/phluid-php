<?php
namespace Phluid\Adapters;
use Phluid\Request;
/**
 * Everything that needs to happen to construct a Phluid\Request
 *
 * @package default
 * @author Beau Collins
 */
class ApacheAdapter {
  
  private $app;
  
  function __construct( $app ){
    $this->app = $app;
  }
  
  function __invoke(){
    
    $app = $this->app;
    
    ob_start();
    
    $request = $this->fromServer();
    $response = $app->serve( $request );
    
    $this->sendResponseHeaders( $response );
    ob_end_clean();
    echo $response->getBody();
    
  }
  
  /**
   * calls header for each header in Response.
   *
   * @param Response $response 
   * @return void
   * @author Beau Collins
   */
  private function sendResponseHeaders( $response ){
    header( $response->statusHeader() );
    $response->eachHeader( function( $name, $value ){
      header( $name . ': ' . $value, true );
    } );
  }
  
  
  /**
   * Static method that constructs a Request from global $_SERVER 
   * variables for use with Apache/Nginx.
   *
   * @return Request
   * @author Beau Collins
   */
  public static function fromServer(){
    
    $uri = $_SERVER['REQUEST_URI'];
    $query_position = strpos( $uri, '?' );
    if ($query_position) {
      $path = substr( $uri, 0, $query_position );
    } else {
      $path = $uri;
    }
    
    $request = new Request( $_SERVER['REQUEST_METHOD'], $path, $_GET, $_SERVER, @file_get_contents('php://input') );
    
    return $request;
  }
  
  
  
}