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
  
  public $defaultMimeType = "application/octet-stream";
  
  function __construct( $path, $prefix = '/', $mimes = array() ){
    $this->path = substr( $path, -1 ) == '/' ? substr( $path, 0, -1 ) : $path;
    $this->mimes = array_merge( $this->mimes, $mimes );
    $this->prefix = substr( $prefix, -1 ) == '/' ? $prefix : $prefix . '/';
  }
  
  function __invoke( $request, $response, $next ){
    if ( $this->isStaticRequest( $request ) ) {
      if( $path = $this->pathForRequest( $request ) ){
        $file_info = pathinfo( $path );
        $response->setHeader( 'Content-Type', $this->mimeForFileSuffix( $file_info['extension'] ) );
        $response->sendFile( $path );
        return;
      }
    }
    $next();      
  }
  
  private function isStaticRequest( $request ){
    return strpos( $request->path, $this->prefix ) === 0 && $request->getMethod() == 'GET';
  }
  
  private function pathForRequest( $request ){
    $path = $this->path . $request->path;
    if ( is_readable( $path ) && !is_dir( $path ) ) {
      return $path;
    }
    return false;
  }
  
  private function mimeForFileSuffix( $suffix ){
    $suffix = strtolower( $suffix );
    if ( array_key_exists( $suffix, $this->mimes ) ) {
      return $this->mimes[strtolower( $suffix )];
    } else {
      return $this->defaultMimeType;
    }
  }
    
}