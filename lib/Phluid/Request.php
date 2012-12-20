<?php
namespace Phluid;
use React\Http\Request as HttpRequest;
use Evenement\EventEmitter;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\Util as StreamUtil;


class Request extends EventEmitter implements ReadableStreamInterface {
  
  private $request;
  private $headers;
  public $method;
  public $path;
  private $ended = false;
  
  function __construct( HttpRequest $request ){
    $this->request = $request;
    $this->headers = RequestHeaders::fromHttpRequest( $request );
    $this->query = $request->getQuery();
    
    // forward the events data, end, close
    Utils::forwardEvents( $this, $request, array( 'pipe', 'data', 'close', 'error' ) );
    
    $request->on( 'end', function(){
      if ( !$this->ended ) {
        $this->ended = true;
        $this->emit( 'end' );
      }
    });
    
    // we need to determine when a request has ended since React\Http\Server
    // doesn't do it for us
    if ( $this->expectsBody() ) {
      $total_length = $this->getContentLength();
      if ( $total_length != null) {
        $seen_length = 0;
        $request->on( 'data', function( $data ) use ( $total_length, &$seen_length ){
          $seen_length += strlen( $data );
          if ( $seen_length >= $total_length ) {
            // TODO: should we wait for the next tick in the event loop?
            $this->ended = true;
            $this->emit( 'end' );
          }
        } );
      }
      
    }
    
  }
  
  public function __toString(){
    
    return $this->headers->__toString();
  }
  
  public function expectsBody(){
    return !in_array( $this->getMethod(), array( 'HEAD', 'GET' ) );
  }
  
  public function getMethod(){
    return $this->headers->method;
  }
  
  public function getPath(){
    return $this->headers->path;
  }
  
  public function setPath( $path ){
    $this->headers->path = $path;
  }
      
  public function param( $param ){
    if ( $this->params && array_key_exists( $param, $this->params ) ) {
      return $this->params[ $param ];
    } else if( $this->query && array_key_exists( $param, $this->query ) ){
      return $this->query[ $param ];
    }
  }
  
  public function getContentLength(){
    $contentLength = $this->headers['content-length'];
    if ( $contentLength != null ) {
      return (int) $contentLength;
    }
  }
  
  
  public function getContentType(){
    return $this->headers['content-type'];
  }
  
  public function getHeader( $header ){
    return $this->headers[$header];
  }
  
  public function getHost(){
    return $this->headers['host'];
  }
  
  public function isReadable(){
    return $this->request->isReadable();
  }
  
  public function pause() {
    return $this->request->pause();
  }
  
  public function resume(){
    return $this->request->resume();
  }
  
  public function pipe(WritableStreamInterface $dest, array $options = array()){
    return StreamUtil::pipe( $this, $dest, $options );
  }
  
  public function close(){
    return $this->request->close();
  }
  
  
}