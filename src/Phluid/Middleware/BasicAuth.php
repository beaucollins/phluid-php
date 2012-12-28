<?php
namespace Phluid\Middleware;

class BasicAuth {
  
  private $unauthorizedAction;
  private $authenticator;
  
  function __construct( $authenticator, $unauthorizedAction = null ){
    $this->authenticator = $authenticator;
    if ( $unauthorizedAction ) {
      $this->unauthorizedAction = $unauthorizedAction;
    } else {
      $this->unauthorizedAction = function( $request, $response, $next ){
        $next();
      };
    }
  }
  
  function __invoke( $request, $response, $next ){
    $unauthorized = function() use ($request, $response, $next ){
      $action = $this->unauthorizedAction;
      $action( $request, $response, $next );
    };
    $authorized = function( $user ) use ( $request, $next ){
      $request->user = $user;
      $next();
    };
    if ( $credentials = $this->credentialsForRequest( $request ) ) {
      // authorize the credentials      
      $this->authenticate( $credentials, $authorized, $unauthorized );
    } else {
      // WWW-Authenticate: Basic realm="insert realm"
      $unauthorized( $request, $response, $next );
    }
    
  }
  
  private function authenticate( $credentials, $success, $failure ){
    $authenticator = $this->authenticator;
    $authenticator( $credentials, $success, $failure );
  }
  
  public static function sendUnauthorized( $response ){
    $response->sendHeaders( 401, array(
      'Content-Length' => '0',
      'WWW-Authenticate' => 'Basic realm="phluid"'
    ) );
    $response->end();
  }
  
  private function credentialsForRequest( $request ){
    $header = $request->getHeader( 'Authorization' );
    if ( $header ) {
      return $this->parseCredentials( $header );
    }
  }
  
  public static function parseCredentials( $encoded ){
    $unencoded = base64_decode( substr( $encoded, strlen( 'Basic ' ) ) );
    $position = strpos( $unencoded, ':' );
    return array(
      'username' => substr( $unencoded, 0, $position ),
      'password' => substr( $unencoded, $position + 1 )
    );
  }
 
}