<?php
namespace Phluid\Middleware;
use Phluid\Exception;

class Cache {
  
  function __invoke( $req, $res, $next ){
        
    $res->once( 'headers', function() use ( $req, $res ){
      // if-modified, if-not-modified, etags, range
      $match = $req->getHeader( 'If-Match' );
      $noneMatch = $req->getHeader( 'If-None-Match' );
      $modifiedSince = $req->getHeader( 'If-Modified-Since' );
      $unmodifiedSince = $req->getHeader( 'If-Unmodified-Since' );
      $lastModified = $res->getHeader( 'Last-Modified' );
      $etag = $res->getHeader( 'ETag' );
      
      if ( !empty( $etag ) && !empty( $noneMatch ) ) {
        $tags = explode( "/*, */", $noneMatch );
        if ( in_array( $etag, $tags ) ) 
          throw new ResponseUnmodifiedException();
      }
      
      if ( !empty( $modifiedSince ) && !empty( $lastModified ) ) {
        $last = $this->parseDate( $lastModified );
        $since = $this->parseDate( $modifiedSince );
        if ( $last <= $since ) {
          throw new ResponseUnmodifiedException();
        } 
      }
      
      if ( !empty( $unmodifiedSince ) && !empty( $lastModified ) ) {
        $last = $this->parseDate( $lastModified );
        $since = $this->parseDate( $modifiedSince );
        if ( $last > $since ) {
          throw new ResponseModifiedException();
        }
      }
      
    } );
    
    try {
      $next();      
    } catch (ResponseModifiedException $e) {
      $this->sendResponse( $res, $e->getCode() );
    } catch (ResponseUnmodifiedException $e) {
      $this->sendResponse( $res, $e->getCode() );
    }
  }
  
  static function parseDate( $date ){
    return \DateTime::createFromFormat( \DateTime::RFC1123, $date );
  }
  
  static function sendResponse( $res, $status ){
    $res->eachHeader( function( $header, $value ) use( $res ){
      if( strpos( strtolower( $header ), 'content' ) == 0 )
        $res->removeHeader( $header );
    });
    $res->sendHeaders( $status );
    $res->end();
  }
    
}

class ResponseModifiedException extends Exception {
  
  function __construct( $message = 'Response modified', $code = 412, Exception $previous = null ){
    parent::__construct( $message, $code, $previous );
  }
  
}

class ResponseUnmodifiedException extends Exception {
  function __construct( $message = 'Response not modified', $code = 304, Exception $previous = null ){
    parent::__construct( $message, $code, $previous );
  } 
}