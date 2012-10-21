<?php

namespace Phluid;

class RequestMatcher {
  
  private $pattern;
  private $methods;
  
  public function __construct( $method, $pattern ){
    
    $this->methods = is_array( $method ) ? $method : array( $method );
    $this->pattern = self::compileRegex( $pattern );
    
  }
  
  public function matches( $request ){
    if ( in_array( $request->method, $this->methods ) ) {
      if( preg_match( $this->pattern, $request->path, $matches) ){
        return $matches;
      }
    }
    return false;
  }
  
  public function __invoke( $request ){
    return $this->matches( $request );
  }
  
  public static function compileRegex( $pattern ){
    // sorry about the magic here
    $regex_pattern = preg_replace( "/\.:/", "\\.:", $pattern );
    $regex_pattern = preg_replace( "/\*/", ".*", $regex_pattern );
    $regex_pattern = preg_replace( "#(/)?:([\w]+)(\?)?#", '($1(?<$2>[^/]+))$3', $regex_pattern );
    return '#^' . $regex_pattern . '/?$#';
  }
  
}