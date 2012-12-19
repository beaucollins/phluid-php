<?php

namespace Phluid\Http;
use React\Http\Response as ReactResponse;
use React\Socket\ConnectionInterface;
use Phluid\Utils;
use Phluid\View;

class Response extends ReactResponse {
  
  private $request;
  public $status = 200;
  private $options = array();
  private $conn;
  
  private $headers = array();
  
  function __construct( ConnectionInterface $conn, Request $request ){
    $this->conn = $conn;
    parent::__construct( $conn );
    $this->request = $request;
  }
  
  public function __toString(){
    return (string) $this->status;
  }
  
  public function getOptions(){
    return $this->options;
  }
  
  public function setOptions( array $options ){
    $this->options = array_merge( $this->options, $options );
  }
  
  /**
   * Reteurn an associative array of all HTTP headers
   *
   * @return (array) header name and value pairs
   * @author Beau Collins
   */
  public function getHeaders(){
    $headers = $this->headers;
    return $headers;
  }
  
  public function setHeaders( $headers ){
    foreach ( $headers as $name => $value ) {
      $this->setHeader( $name, $value );
    }
  }
  
  public function getStatus(){
    return $this->status;
  }
  
  /**
   * Set an HTTP response header
   *
   * @param string $key    header name
   * @param string $value  header value
   * @return void
   * @author Beau Collins
   */
  public function setHeader( $key, $value ){
    $this->headers[trim( strtoupper( $key ) )] = $value;
  }
  
  public function getHeader( $key ){
    $key = strtoupper( $key );
    if ( array_key_exists( $key, $this->headers ) ) {
      return $this->headers[$key];
    }
  }
  
  public function redirectTo( $path, $status = 302 ){
    $this->setHeader( 'location', $path );
    $this->sendHeaders( $status );
    $this->end();
  }
  
  public function render( $template, $locals = array(), $options = array() ){
    $layout = Utils::array_val( $options, 'layout', $this->options['default_layout'] );
    $status = Utils::array_val( $options, 'status', 200 );
    $content_type = Utils::array_val($options, 'content-type', 'text/html' );
    $locals['request'] = $this->request;
    $view = new View( $template, $layout, $this->options['view_path'] );
    $this->renderString( $view->render( $locals ), $content_type, $status );
  }
  
  public function renderString( $string, $content_type="text/plain", $status = 200 ){
    $this->status_code = $status;
    $this->setHeader( 'Content-Type', $content_type );
    $this->setHeader( 'Content-Length', strlen( (string) $string ) );
    // write the headers and the body
    $this->sendHeaders( $status );
    $this->end( (string) $string );
  }
  
  /**
   * Alias of renderString
   *
   * @param string $string       text to respond with
   * @param string $content_type content type for HTTP header
   * @param int    $status       HTTP status code to use
   * @return void
   * @author Beau Collins
   */
  public function renderText( $string, $content_type="text/plain", $status = 200 ){
    $this->renderString( $string, $content_type, $status );
  }
  
  /**
   * Renders the given object as a string encoded with json_encode and given
   * application/json as the content-type
   *
   * @param string $object 
   * @param int    $status HTTP status to send
   * @return void
   * @author Beau Collins
   */
  public function renderJSON( $object, $status = 200 ){
    $this->renderString( json_encode($object), "application/json" );
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
        while( $string = fread( $handle, $this->conn->bufferSize ) ){
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
  
  public function sendHeaders( $status_or_headers = 200, $headers = array() ){
    if ( !is_null( $status_or_headers ) and is_int( $status_or_headers ) ) {
      $this->status = $status_or_headers;
    } else if ( !is_null( $status_or_headers ) && is_array( $status_or_headers ) ) {
      $headers = $status_or_headers;
    }
    $this->setHeaders( $headers );
    $this->writeHead( $this->status, $this->headers->toArray() );
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
  
  public function writeHead($status = 200, array $headers = array()){
    $this->emit('headers');
    parent::writeHead( $status, $headers);
  }
        
}