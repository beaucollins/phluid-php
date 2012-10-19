<?php

require_once 'Utils.php';
require_once 'Router.php';
require_once 'Request.php';
require_once 'Response.php';
require_once 'Settings.php';
require_once 'Exceptions.php';
require_once 'Middleware.php';

class Phluid_App {
  
  private $router;
  private $middleware = array();
  private $settings;
  private $router_mounted = false;
  
  /**
   * Passes an array of settings to initialize Phluid_Settings with.
   *
   * @param array $options the settings for the app
   * @return Phluid_App
   * @author Beau Collins
   **/
  public function __construct( $options = array() ){
    
    $defaults = array( 'view_path' => realpath('.') . '/views' );
    $this->settings = new Phluid_Settings( array_merge( $defaults, $options ) );
    $this->router = new Phluid_Router();
    
  }
  
  /**
   * Retrieve a setting
   *
   * @param string $key 
   * @return mixed
   * @author Beau Collins
   */
  public function __get( $key ){
    return $this->settings->__get( $key );
  }
  
  /**
   * Set a setting
   *
   * @param string $key the setting name
   * @param mixed $value value to set
   * @author Beau Collins
   */
  public function __set( $key, $value ){
    return $this->settings->__set( $key, $value );
  }
  
  /**
   * Starts up the app and renders a response to stdout
   *
   * @return void
   * @author Beau Collins
   */
  public function run(){
    
    ob_start();
    
    $request = Phluid_Request::fromServer()->withPrefix( $this->prefix );    
    $response = $this->serve( $request );
    
    $this->sendResponseHeaders( $response );
    ob_end_clean();
    echo $response->getBody();
    
  }
  
  /**
   * Given a Phluid_Request it runs the configured middlewares and routes and
   * returns the response.
   *
   * @param Phluid_Request $request 
   * @return Phluid_Response
   * @author Beau Collins
   */
  public function serve( $request ){
    // mount the router if it hasn't been mounted explicitly
    if ( $this->router_mounted === false ) $this->inject( $this->router );
    
    // get a copy of the middleware stack
    $middlewares = $this->middleware;
    $response = new Phluid_Response( $this, $request );
    self::runMiddlewares( $this, $request, $response, $middlewares );
    
    return $response;
    
  }
  
  /**
   * Runs the provided middlewares with the request and response. This should
   * produce no side effects to the app so it can be called any number of times.
   *
   * @param Phluid_Request $request 
   * @param Phluid_Response $response 
   * @param array $middlewares 
   * @author Beau Collins
   */
  public static function runMiddlewares( $app, $request, $response, $middlewares ){
    if ( $middleware = array_shift( $middlewares ) ) {
      $response = $middleware( $request, $response, function () use ($app, $request, $response, $middlewares){
        $app::runMiddlewares( $app, $request, $response, $middlewares );
      });
    }
    
  }
  
  /**
   * Convenience method invokes the serve method
   *
   * @param string $request 
   * @return void
   * @author Beau Collins
   */
  public function __invoke( $request ){
    return $this->serve( $request );
  }
  
  /**
   * calls header for each header in Phluid_Response.
   * TODO: Better suited for some kind of adapter
   *
   * @param Phluid_Response $response 
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
   * Adds the given middleware to the app's middleware stack. Returns $this for
   * chainable calls.
   *
   * @param Phluid_Middleware $middleware 
   * @return Phluid_App
   * @author Beau Collins
   */
  public function inject( $middleware ){
    if ( $middleware === $this->router ) $this->router_mounted = true;
    array_push( $this->middleware, $middleware );
    return $this;
  }
  
  /**
   * Configures a route give the HTTP request method, calls Phluid_Router::route
   * returns $this for chainable calls
   *
   * Example:
   *
   *  $app->route( 'GET', '/profile/:username', function( $req, $res, $next ){
   *    $res->renderText( "Hello {$req->param('username')}");
   *  });
   *
   * @param string $method GET, POST or other HTTP method
   * @param string $path the matching path, refer to Phluid_Router::route for options
   * @param string $closure an invocable object/function that conforms to Phluid_Middleware
   * @return Phluid_App
   * @author Beau Collins
   */
  public function route( $method, $path, $closure ){
    
    $this->router->route( $method, $path, $closure );
    return $this;
    
  }
  
  /**
   * Adds a route matching a "GET" request to the given $path. Returns $this so
   * it is chainable.
   *
   * @param string $path 
   * @param Phluid_Middleware $closure compatible function/invocable
   * @return Phluid_App
   * @author Beau Collins
   */
  public function get( $path, $closure ){
    return $this->route( 'GET', $path, $closure );
  }
  
  /**
   * Adds a route matching a "POST" request to the given $path. Returns $this so
   * it is chainable.
   *
   * @param string $path 
   * @param Phluid_Middleware $closure 
   * @return Phluid_App
   * @author Beau Collins
   */
  public function post( $path, $closure ){
    return $this->route( 'POST', $path, $closure );
  }
  
}