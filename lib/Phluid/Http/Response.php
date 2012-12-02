<?php

namespace Phluid\Http;
use Evenement\EventEmitter;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\ReadableStreamInterface;
use Phluid\Utils;
use Phluid\View;

class Response extends EventEmitter implements WritableStreamInterface {
  
  private $conn;
  private $closed = false;
  private $writable = true;
  private $headWritten;
  private $chunkedEncoding = true;
  private $options;
  private $request;
  public $status = 200;
  
  private $headers = array();
  
  function __construct( ConnectionInterface $conn, Request $request ){
    $this->request = $request;
    $this->conn = $conn;
    
    $this->conn->on('end', function () {
        $this->close();
    });

    $this->conn->on('error', function ($error) {
        $this->emit('error', array($error, $this));
        $this->close();
    });

    $this->conn->on('drain', function () {
        $this->emit('drain');
    });
    
    $this->options = array(
      'default_layout' => null,
      'view_path' => null
    );
    
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
  
  public function sendFile( $path, $options = array(), $status = 200 ){
    // TODO: handle if a file doesn't exist or isn't readable
    $this->setHeader( 'Content-Length', filesize( $path ) );
    $this->sendHeaders( $status );
    if( $handle = fopen( $path, 'r' ) ){
      while( $string = fread( $handle, 1024 * 4 ) ){
        $this->write( $string );
      }
      fclose( $handle );          
      $this->end();
    }
  }
  
  public function sendHeaders( $status_or_headers = 200, $headers = array() ){
    if ( !is_null( $status_or_headers ) and is_int( $status_or_headers ) ) {
      $this->status = $status_or_headers;
    } else if ( !is_null( $status_or_headers ) && is_array( $status_or_headers ) ) {
      $headers = $status_or_headers;
    }
    $this->setHeaders( $headers );
    $this->writeHead( $this->status, $this->headers );
  }
  
  public function writeHead( $status = 200, $headers = array() ){
    if ( $this->headWritten ) {
      throw new \Exception("Response head has already been written");
    }
    
    $this->emit( 'headers' );
    
    $this->conn->write( $this->statusHeader( $status ) . "\r\n" );
    $this->eachHeader( function( $name, $value ){
      $this->conn->write( "$name: $value" . "\r\n" );
    });
    $this->conn->write( "\r\n" );
    
    if ( $this->getHeader( 'Content-Length' ) ) {
      $this->chunkedEncoding = false;
    }
    
    $this->headWritten = true;
    
  }
  
  public function statusHeader( $status = 200 ){
    return "HTTP/1.1 " . $status;
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
  
    
  public function isWritable() {
   return $this->writable; 
  }
  
  public function write( $data ) {
      if ( !$this->headWritten ) {
          throw new \Exception( 'Response head has not yet been written.' );
      }

      if ( $this->chunkedEncoding ) {
          $len = strlen( $data );
          $chunk = dechex( $len ) . "\r\n" . $data . "\r\n";
          $flushed = $this->conn->write( $chunk );
      } else {
          $flushed = $this->conn->write( $data );
      }

      return $flushed;
  }
  
  public function end( $data = null ) {
      if ( null !== $data ) {
          $this->write( $data );
      }

      if ( $this->chunkedEncoding ) {
          $this->conn->write( "0\r\n\r\n" );
      }

      $this->emit( 'end' );
      $this->removeAllListeners();
      $this->conn->end();
  }
  
  public function close() {
      if ( $this->closed ) {
          return;
      }

      $this->closed = true;

      $this->writable = false;
      $this->emit( 'close' );
      $this->removeAllListeners();
      $this->conn->close();
  }
  
      
}