<?php
namespace Phluid;
use React\Http\Response as HttpResponse;
use Evenement\EventEmitter;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;

class Response extends EventEmitter implements WritableStreamInterface {
  
  private $options = array();
  private $response;
  private $request;
  private $headers;
  
  function __construct( HttpResponse $http_response, Request $request ){
    $this->request = $request;
    $this->response = $http_response;
    $this->headers = new ResponseHeaders( 200, 'Success' );
    Utils::forwardEvents( $this, $http_response, array( 'error', 'end', 'drain' ) );
    
  }
  
  public function __toString(){
    return (string) $this->headers;
  }
  
  public function getOptions(){
    return $this->options;
  }
  
  public function setOptions( array $options ){
    $this->options = array_merge( $this->options, $options );
  }
  
  public function getHeaders(){
    return $this->headers;
  }
  
  public function setHeaders( $headers ){
    foreach ( $headers as $name => $value ) {
      $this->setHeader( $name, $value );
    }
  }
  
  public function getHeader( $key ){
    return $this->headers[$key];
  }
  
  public function setHeader( $key, $value ){
    $this->headers[$key] = $value;
  }
  
  public function getStatus(){
    return $this->headers->status;
  }
  
  public function writeHead( $status = 200, array $headers = array() ){
    $this->emit( 'headers' );
    $this->response->writeHead( $status, $headers );
  }
  
  public function sendHeaders( $status_or_headers = 200, $headers = array() ){
    if ( !is_null( $status_or_headers ) and is_int( $status_or_headers ) ) {
      $this->headers->status = $status_or_headers;
    } else if ( !is_null( $status_or_headers ) && is_array( $status_or_headers ) ) {
      $headers = $status_or_headers;
    }
    $this->setHeaders( $headers );
    $this->writeHead( $this->headers->status, $this->headers->toArray() );
  }
  
  public function render( $template, $locals = array(), $options = array() ){
    $layout = Utils::array_val( $options, 'layout', $this->options['default_layout'] );
    $status = Utils::array_val( $options, 'status', 200 );
    $content_type = Utils::array_val($options, 'content-type', 'text/html' );
    $locals['request'] = $this->request;
    $view = new View( $template, $layout, $this->options['view_path'] );
    $this->renderText( $view->render( $locals ), $content_type, $status );
  }
  
  public function renderText( $string, $content_type="text/plain", $status = 200 ){
    $this->setHeader( 'Content-Type', $content_type );
    $this->setHeader( 'Content-Length', strlen( (string) $string ) );
    // write the headers and the body
    $this->sendHeaders( $status );
    $this->end( (string) $string );
  }
  
  public function redirectTo( $path, $status = 302 ){
    $this->setHeader( 'location', $path );
    $this->sendHeaders( $status );
    $this->end();
  }
  
  
  /**
   * Iterate throuch each header name/value with a callback
   *
   * @param string $callback that accepts to arguments
   * @return void
   * @author Beau Collins
   */
  public function eachHeader( $callback ){
    foreach( $this->headers as $name => $value ){
      $callback( $name, $value );
    }
  }
  
  public function sendFile( $path, $options_or_status = array(), $status = 200 ){
    // TODO: handle if a file doesn't exist or isn't readable
    if ( is_int( $options_or_status )) {
      $status = $options_or_status;
      $options = array();
    } else {
      $options = $options_or_status;
    }
    if ( array_key_exists( 'attachment', $options ) ) {
      $disposition = $options['attachment'];
      if( $disposition === true ){
        $disposition = "attachment;";
      } else {
        $disposition = "attachment; filename=\"$disposition\"";
      }
      $this->setHeader( 'Content-Disposition', $disposition );
    }
    $this->setHeader( 'Content-Length', filesize( $path ) );
    $this->sendHeaders( $status );
    if( $handle = fopen( $path, 'r' ) ){
      $readFile = function() use ( $handle ){
        while( $string = fread( $handle, 2048 ) ){
          if ( feof( $handle ) ) {
            fclose( $handle );
            $this->end( $string );
            return;
          } else {
            if( !$this->write( $string ) ) return;
          }
        }
      };
      $this->on( 'drain', $readFile );
      $readFile();
    }
  }
  
  public function isWritable(){
    return $this->response->isWritable();
  }
  public function write( $data ){
    return $this->response->write( $data );
  }
  public function end( $data = null ){
    return $this->response->end( $data );
  }
  
  public function close(){
    return $this->response->close();
  }
  
  
}
