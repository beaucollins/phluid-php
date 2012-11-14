<?php

namespace Phluid\Middleware;

class StaticFiles {
  
  private $path;
  private $prefix;
  private $mimes = array(
    'css'      => 'text/css',
    'txt'      => 'text/plain',
    'jpg'      => 'image/jpeg',
    'jpeg'     => 'image/jpeg',
    'png'      => 'image/png',
    'html'     => 'text/html',
    'htm'      => 'text/htm',
    'js'       => 'text/javascript',
    'gif'      => 'image/gif',
    'json'     => 'application/json',
    'manifest' => 'text/cache-manifest'
  );
  
  function __construct( $path, $prefix = '/', $mimes = array() ){
    $this->path = $path;
    array_merge( $this->mimes, $mimes );
    $this->prefix = substr( $prefix, strlen( $prefix ) -1 ) == '/' ? $prefix : $prefix . '/';
  }
  
  function __invoke( $request, $response, $next ){
    if ( $this->isStaticRequest( $request ) ) {
      if( $path = $this->pathForRequest( $request ) ){
        $file_info = pathinfo( $path );
        $response->setHeader( 'Content-Type',  $this->mimeForFileSuffix( $file_info['extension'] ) );
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
    if ( is_readable( $path ) && !is_dir( $path ) ) {
      return $path;
    }
    return false;
  }
  
  private function mimeForFileSuffix( $suffix ){
    
    $type = $this->mimes[strtolower( $suffix )];
    if ( !$type ) {
      $type = $this->deafultMimeType();
    }
    return $type;
  }
  
}