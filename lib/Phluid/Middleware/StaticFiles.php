<?php

namespace Phluid\Middleware;

class StaticFiles {
  
  private $path;
  private $prefix;
  
  function __construct( $path, $prefix = '/' ){
    $this->path = $path;
    $this->prefix = substr( $prefix, strlen( $prefix ) -1 ) == '/' ? $prefix : $prefix . '/';
  }
  
  function __invoke( $request, $response, $next ){
    if ( $this->isStaticRequest( $request ) ) {
      if( $path = $this->pathForRequest( $request ) ){
        $file = new \finfo( FILEINFO_SYMLINK );        
        $response->setHeader( 'Content-Type',  $file->file( $path, FILEINFO_MIME ) );
        $response->setBody( file_get_contents( $path ) );
        return $response;
      }
    }
    return $next();
  }
  
  private function isStaticRequest( $request ){
    return strpos( $request->path, $this->prefix ) === 0;
    
  }
  
  private function pathForRequest( $request ){
    $path = $this->path . $request->path;
    if ( is_readable( $path ) ) {
      return $path;
    }
    return false;
  }
  
}