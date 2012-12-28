<?php
namespace Phluid\Middleware\Sessions;

class MemoryStore implements SessionStoreInterface {
  
  private $sessions = array();
  
  public function find( $sid, $fn ){
    if ( array_key_exists( $sid, $this->sessions )) {
      $fn( $this->sessions[$sid] );
    } else {
      $fn( null );
    }
  }
  
  public function save( $sid, $session, $fn ){
    $this->sessions[$sid] = $session;
    $fn();
  }
  
  public function destroy( $sid, $fn ){
    if( array_key_exists( $sid, $this->sessions ) ) unset( $this->sessions[$sid] );
    $fn();
  }
  
}