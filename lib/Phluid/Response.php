<?php

namespace Phluid;

class Response {

  private $raw_body;
  private $status_code = 200;
  private $headers = array();
  private $options;
  public $request;
  
  function __construct( $request = null, $options = array() ){
    $this->request = $request;
    $this->options = array_merge( array(
      'default_layout' => null,
      'view_path' => null
    ), $options);
  }
  
  /**
   * Searches for template in the App view_path setting and renders it
   * using a View
   *
   * @param string $template name of template to find
   * @param array  $locals   variables that will be available to the template
   * @param string $options  can set HTTP status code with 'status' and provide a layout to use with 'layout'
   * @return void
   * @author Beau Collins
   */
  public function render( $template, $locals = array(), $options = array() ){
    $layout = Utils::array_val( $options, 'layout', $this->options['default_layout'] );
    $status = Utils::array_val( $options, 'status', 200 );
    $content_type = Utils::array_val($options, 'content-type', 'text/html' );
    $locals['request'] = $this->request;
    $view = new View( $template, $layout, $this->options['view_path'] );
    $this->renderString( $view->render( $locals ), $content_type, $status );
  }
  
  /**
   * Returns the response status code, default is 200
   *
   * @return void
   * @author Beau Collins
   */
  public function getStatus(){
    return $this->status_code;
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
  
  public function statusHeader(){
    return "HTTP/1.1 " . $this->statusMessage();
  }
  
  public function statusMessage(){
    return (string) $this->status_code;
  }
  
  /**
   * Respond with a redirect to a new URL
   *
   * @param string $url 
   * @param int $status  status code to send with the redirect
   * @return void
   * @author Beau Collins
   */
  public function redirect( $url, $status = 301 ){
    $this->status_code = $status;
    $this->setHeader( "Location", $url );
    $this->raw_body = "Redirecting: $url";
  }
  
  /**
   * Send plain text back as a response
   *
   * @param string $string       text to respond with
   * @param string $content_type content type for HTTP header
   * @param int    $status       HTTP status code to use
   * @return void
   * @author Beau Collins
   */
  public function renderString( $string, $content_type="text/plain", $status = 200 ){
    $this->status_code = $status;
    $this->raw_body = (string) $string;
    $this->setHeader( 'Content-Type', $content_type );
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
    $this->renderString( json_encode($object), "application/json", $status );
  }
  
  public function getBody(){
    return $this->raw_body;
  }
  
  public function setBody( $body ){
    $this->raw_body = $body;
  }
  
  public function __toString(){
    return $this->statusHeader();
  }
  
}