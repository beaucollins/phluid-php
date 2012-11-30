<?php

namespace Phluid;

use Phluid\Http\Server;
use React\Http\ServerInterface as HttpServerInterface;
use React\Socket\Server as SocketServer;
use Phluid\Middleware\Cascade;
use React\EventLoop\Factory as LoopFactory;

class App {
  
  private $router;
  private $middleware = array();
  private $settings;
  private $router_mounted = false;
  
  public $http;
  public $socket;
  public $loop;
  
  /**
   * Passes an array of settings to initialize Settings with.
   *
   * @param array $options the settings for the app
   * @return App
   * @author Beau Collins
   **/
  public function __construct( $options = array() ){
    
    $defaults = array( 'view_path' => realpath('.') . '/views' );
    $this->settings = new Settings( array_merge( $defaults, $options ) );
    $this->router = new Router();
    
  }
  
  public function createServer( HttpServerInterface $http = null ){
    if ( $http === null ) {
      $this->loop = $loop = LoopFactory::create();
      $this->socket = $socket = new SocketServer( $loop );
      $this->http = $http = new Server( $socket, $loop );
    }
    $http->on( 'request', function( $request, $response ){
      $app = $this;
      $response->setOptions( array(
        'view_path' => $this->view_path,
        'default_layout' => $this->default_layout
      ) );
      $app( $request, $response );
    });
    return $this;
  }
  
  public function listen( $port, $host = '127.0.0.1' ){
    $this->socket->listen( $port, $host );
    $this->loop->run();
    return $this;
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
   * An app is just a specialized middleware
   *
   * @param string $request 
   * @return void
   * @author Beau Collins
   */
  public function __invoke( $request, $response, $next = null ){
    
    if ( $this->router_mounted === false ) $this->inject( $this->router );
    
    $middlewares = $this->middleware;
    $cascade = new Cascade( $middlewares );
    $cascade( $request, $response, $next );
    
  }
  
  public function buildResponse( $request = null ){
    return new Response( $request, array(
      'view_path' => $this->view_path,
      'default_layout' => $this->default_layout
    ) );
  }
    
  /**
   * Adds the given middleware to the app's middleware stack. Returns $this for
   * chainable calls.
   *
   * @param Middleware $middleware 
   * @return App
   * @author Beau Collins
   */
  public function inject( $middleware ){
    if ( $middleware === $this->router ) $this->router_mounted = true;
    array_push( $this->middleware, $middleware );
    return $this;
  }
  
  /**
   * Configures a route give the HTTP request method, calls Router::route
   * returns $this for chainable calls
   *
   * Example:
   *
   *  $app->on( 'GET', '/profile/:username', function( $req, $res, $next ){
   *    $res->renderText( "Hello {$req->param('username')}");
   *  });
   *
   * @param string $method GET, POST or other HTTP method
   * @param string $path the matching path, refer to Router::route for options
   * @param invocable $closure an invocable object/function that conforms to Middleware
   * @return App
   * @author Beau Collins
   */
  public function on( $method, $path, $filters, $action = null ){
    return $this->route( new RequestMatcher( $method, $path ), $filters, $action );
  }
  
  /**
   * Chainable call to the router's route method
   *
   * @param invocable $matcher 
   * @param invocable or array $filters 
   * @param invocable $action 
   * @return App
   * @author Beau Collins
   */
  public function route( $matcher, $filters, $action = null ){
    $this->router->route( $matcher, $filters, $action );
    return $this;
  }
  
  /**
   * Adds a route matching a "GET" request to the given $path. Returns $this so
   * it is chainable.
   *
   * @param string $path 
   * @param invocable or array $filters compatible function/invocable
   * @param invocable $closure compatible function/invocable
   * @return App
   * @author Beau Collins
   */
  public function get( $path, $filters, $action = null ){
    return $this->on( 'GET', $path, $filters, $action );
  }
  
  /**
   * Adds a route matching a "POST" request to the given $path. Returns $this so
   * it is chainable.
   *
   * @param string $path 
   * @param invocable or array $filters compatible function/invocable
   * @param invocable $closure compatible function/invocable
   * @return App
   * @author Beau Collins
   */
  public function post( $path, $filters, $action = null ){
    return $this->on( 'POST', $path, $filters, $action );
  }
    
}
