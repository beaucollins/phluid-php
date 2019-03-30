<?php
namespace Phluid\Test\Middleware\Sessions;
use Phluid\Middleware\Sessions\MemoryStore;

class MemoryStoreTest extends \PHPUnit\Framework\TestCase {
  
  /**
   * @before
   */
  function setUpMemoryStore(){
    $this->store = new MemoryStore();
  }
  
  function testStore(){
    $sid = 'abcdef';
    $data = array( "hello" => "world" );
    $saved = false;
    $this->store->save( $sid, $data, function() use ( &$saved ){
      $saved = true;
    });
    $this->assertTrue( $saved );
    
    $found = false;
    $this->store->find( $sid, function( $session ) use( &$found ){
      $found = $session;
    });
    
    $this->assertSame( $found, $data );
    
    $this->store->destroy( $sid, function(){} );
    
    $this->store->find( $sid, function( $session ) use ( &$found ){
      $found = $session;
    });
    
    $this->assertNull( $found );
  }
  
  
}