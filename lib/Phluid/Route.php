<?php

class Phluid_Route {
  
  private $closure;
  private $methods;
  private $path;
  
  public function __construct( $methods, $path, $closure ){
    $this->methods = is_array( $methods ) ? $methods : array( $methods );
    $this->path = $path;
    $this->closure = $closure;
  }
  
  public function matches( $request ){
    // method must match
    if ( in_array( $request->method, $this->methods ) && $request->parsePath( $this->path ) ) {
      return true;
    }
    return false;
  }
    
  public function __invoke( $request, $response ){
    $closure = $this->closure;
    $closure( $request, $response);
    
  }
  
  public function __toString(){
    return implode( ',', $this->methods ) . ' ' . $this->path;
  }
  
}