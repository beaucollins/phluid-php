<?php
namespace Phluid\Middleware;
use Phluid\Middleware\Sessions\SessionStoreInterface;
use Phluid\Middleware\Sessions\Session;
use Phluid\Utils;
use Phluid\Middleware\Sessions\MemoryStore;
use Phluid\Middleware\Cookies\Cookie;

class Sessions {
  
  private $options = array();
  
  function __construct( $options = array() ){
    $this->options = array_merge( array(
      'key'    => 'phluid.session',
      'cookie' => array(),
      'store'  => null,
      'secret' => null
    ), $options );
    $this->store = $this->options['store'] ?: new MemoryStore;
    if ( !$this->secret ) {
      throw new \Exception( "Sessions middleware secret option is not set" );
    }
  }
  
  function __invoke( $request, $response, $next ){
    if ( $request->session ) return $next();
    $request->sessionStore = $this->store;
    $request->sessionId = $sid = $this->getValidSessionId( $request );
    $response->on( 'close', function() use ( $request, $next ) {
      $this->store->save( $request->sessionId, $request->session->getData(), function(){} );
    });
    if ( !$sid ) {
      $this->generate( $request );
      $response->cookies[$this->key] = new Cookie( $this->signSessionId( $request->sessionId, $this->secret ), $this->cookie );
      return $next();
    }
    $this->store->find( $sid, function( $data ) use ($request, $next) {
      if ( !$data ) {
        $this->generate( $request );
      } else {
        $request->session = new Session( $request, $data );        
      }
      $next();
    });
  }
  
  function __get( $key ){
    return array_key_exists( $key, $this->options ) ? $this->options[$key] : null;
  }
  
  public static function generate( $request ){
    $request->sessionId = $sid = Utils::uid( 24 );
    $request->session = new Session( $request );
  }
  
  public static function sessionIdSignature( $sid, $secret ){
    return hash_hmac( 'sha256', $sid, $secret );
  }
  
  public static function signSessionId( $sid, $secret ){
    return $sid .' s:' . self::sessionIdSignature( $sid, $secret );
  }
  
  private function getSessionId( $request ){
    return $request->cookies[ $this->options['key'] ];
  }
  
  private function getValidSessionId( $request ){
    $signedId = $request->cookies[$this->key];
    if ( $signedId && strpos( $signedId, ' s:' ) ) {
      list( $sid, $signature ) = explode( ' s:', $signedId, 2 );
      if( $signature == $this->sessionIdSignature( $sid, $this->secret ) ){
        return $sid;
      }
    }
  }
  
}

