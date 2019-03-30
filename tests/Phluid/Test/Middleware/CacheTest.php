<?php
namespace Phluid\Test\Middleware;
use Phluid\Test\TestCase;
use Phluid\Middleware\Cache;
use Phluid\Middleware\StaticFiles;

class CacheTest extends TestCase {
  /**
   * @before
   */
  function injectCache(){
    $this->app->inject( new Cache() );
    $this->app->inject( new StaticFiles( realpath('.') . '/tests/files' ) );
  }
  
  function testIfModifiedSince(){
            
    $response = $this->doRequest( 'GET', '/style.css' );
    $lastModified = $response->getHeader( 'last-modified' );
    $this->assertNotNull( $lastModified );
    
    $response = $this->doRequest( 'GET', '/style.css', array(), array(
      'If-Modified-Since' => $lastModified
    ), function( $req, $res ){
      $this->assertSame( $res->getStatus(), 304 );
    } );
  
  }
  
  function testIfUnmodifiedSince(){
    $date = new \DateTime( 'first day of January 2008' );
    $response = $this->doRequest( 'GET', '/style.css', array(), array(
      'If-Unmodified-Since' => $date->format( \DateTime::RFC1123 )
    ) );
    // pick an old date
    $this->assertSame( $response->getStatus(), 412 );
    
  }
  
  function testIfMatch(){
    
    $count = 0;
    $this->app->get( '/tagged', function( $req, $res ) use ( &$count ){
      $res->setHeader( 'ETag', 'abc123' );
      $res->renderText( 'LOL' );
      $count ++;
    });
    
    $response = $this->doRequest( 'GET', '/tagged' );
    $this->assertNotNull( $response->getHeader( 'ETag' ) );
    $this->assertSame( $count, 1 );
    
    $response = $this->doRequest( 'GET', '/tagged', array(), array(
      'If-None-Match' => $response->getHeader( 'ETag' )
    ));
    $this->assertSame( $response->getStatus(), 304 );
    $this->assertSame( $count, 1 );
  }
  
  
}