<?php

namespace Phluid;

class RequestMatcher {
  
  private $pattern;
  private $methods;
  
  public function __construct( $method, $pattern ){
    
    $this->methods = is_array( $method ) ? $method : array( $method );
    $this->pattern = self::compileRegex( $pattern );
    
  }
  
  public function matches( Request $request ){
    if ( in_array( $request->getMethod(), $this->methods ) ) {
      if( preg_match( $this->pattern, $request->getPath(), $matches) ){
        return $matches;
      }
    }
    return false;
  }
  
  public function __invoke( Request $request ){
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