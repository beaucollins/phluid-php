<?php
namespace Phluid;
use React\Http\Request as HttpRequest;

class RequestHeaders extends Headers {
  
  public static function fromHttpRequest( HttpRequest $request ){
     return new RequestHeaders(
       $request->getMethod(),
       $request->getPath(),
       $request->getQuery(),
       $request->getHttpVersion(),
       $request->getHeaders()
     );
  }
  
  public $method;
  public $path;
  public $version;
  public $query;
  
  function __construct( $method, $path, $query, $version = '1.1', $headers = array() ){
    
    $this->method = $method;
    $this->path = $path;
    $this->version = $version;
    $this->query = $query;
    
    parent::__construct( $headers );
        
  }
  
  public function __toString(){
    return $this->method . ' ' . $this->getUri();
  }
  
  public function getUri(){
    return $this->path . $this->getQuerystring();
  }
  
  public function getQuerystring( $prefix = '?' ){
    $query = http_build_query( $this->query );
    if ( $query != "" && $prefix ) {
      $query = $prefix . $query;
    }
    return $query;
  }
  
  
}
