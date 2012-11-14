<?php
namespace Phluid\Middleware;

class MultipartBodyParser {
  
  private $upload_dir;
  
  function __construct( $upload_dir ){
    $this->upload_dir = $upload_dir;
    if(  !is_dir( $upload_dir ) ) mkdir( $upload_dir, 0777, true );
    
  }
  
  function __invoke( $request, $response, $next ){
    
    if ( $request->getMethod() == 'GET' || $request->getMethod() == 'HEAD') {
      return $next();
    }
    
    $content_type = $request->getContentType();
    if ( $boundary = self::parseBoundary( $content_type ) ) {
      // split the body on $boundary\n
      $body = Part::parseContent( $request->getBody(), $boundary, $this->upload_dir );
      $request->setBody( $body );
    }
    
    return $next();
    
  }
  
  public static function parseBoundary( $header_value ){
    if ( strpos( $header_value, 'multipart/form-data' ) === false ) {
      return false;
    }
    list( $type, $boundary ) = explode( '; boundary=', $header_value );
    if ( $type == 'multipart/form-data' && $boundary ) {
      return trim( $boundary );
    }
  }
  
}

function add_object_at_key_path( $path, $object, &$body ){
  if( is_null( $body ) ){
    $body = array();
  }
  if ( $pos = strpos( $path, "[", 1 ) ) {
    $key = trim( substr( $path, 0, $pos ), "[]" );
    $path = substr( $path, $pos );
    add_object_at_key_path( $path, $object,  $body[$key] );
  } else {
    $key = trim( $path, "[]" );
    if( $key == "" ){
      array_push( $body, $object );
    } else {
      $body[$key] = $object;
    }
  }
}

class Part {
  
  public static function parseContent( $content, $boundary, $dir = null ){
    $parts = explode( '--' . $boundary, $content );
    $body = array();
    foreach ( $parts as $raw ) {
      if ( trim( $raw ) != "" && trim( $raw ) != '--' ) {
        $part = new self( $raw, $dir );
        add_object_at_key_path( $part->name , $part, $body );
      }
    }
    return $body;
    
  }
  
  private $raw;
  private $content;
  private $disposition;
  private $upload_dir;
  public $headers = array();
  
  function __construct( $raw, $upload_dir = null ){
    $this->upload_dir = $upload_dir;
    $this->raw = ltrim( $raw );
    $this->parse();
  }
  
  public function __toString(){
    return $this->getContent();
  }
  
  public function getContent(){
    return $this->content;
  }
  
  public function getContentType(){
    if ( array_key_exists( 'Content-Type', $this->headers ) ) {
      return $this->headers['Content-Type'];
    }
  }
  
  public function __get( $key ){
    if ( array_key_exists( $key, $this->disposition ) ) {
      return $this->disposition[$key];
    }
  }
  
  public function getType(){
    return $this->disposition['type'];
  }
  
  public function getName(){
    return $this->disposition['name'];
  }
  
  public function getFilename(){
    if ( array_key_exists( 'filename', $this->disposition ) ) {
      return $this->disposition['filename'];
    }
  }
  
  private function parse(){
    if ( is_string( $this->raw ) && strlen( $this->raw ) > 0 ) {
      while ( $chars = chop( "\n", $this->raw ) ) {
        if ( $chars == "" ) break;
        list( $name, $value ) = explode( ':', $chars, 2 );
        $this->headers[$name] = trim( $value );
      }
      $this->content = $this->raw;
      $this->raw = null;
      $this->parseDisposition();
      $this->saveFile();
    }
  }
  
  private function parseDisposition(){
    if( $disposition = $this->headers['Content-Disposition'] ){
      $this->disposition = array( 'type' => chop( "; ", $disposition ) );
      while( $data = chop( "; ", $disposition ) ){
        list( $key, $val ) = explode( "=", $data, 2 );
        $this->disposition[$key] = trim( $val, '"' );
      }
    }
    
  }
  
  private function saveFile(){
    if ( $this->filename ) {
      $file = tempnam( $this->upload_dir, $this->filename );
      $fh = fopen( $file, 'w' );
      fwrite( $fh, $this->content );
      fclose( $fh );
      $this->content = $file;
    } else {
      $this->content = trim( $this->content );
    }
    
  }
  
}

function chop( $char, &$string ){
  $pos = strpos( $string, $char );
  if ( $pos === false ) {
    $chopped = $string;
    $string = "";
  } else {
    $chopped = substr( $string, 0, $pos );
    $string = substr( $string, $pos + 1 );
  }
  return trim( $chopped );
}