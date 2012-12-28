<?php

namespace Phluid;

class Settings {
  
  private $settings = array();
  
  function __construct( $defaults = array() ){
    
    $this->settings = array_merge( $this->settings, $defaults );
  }
    
  public function __set( $key, $value ){
    $this->settings[$key] = $value;
  }
  
  public function __get( $key ){
    return Utils::array_val( $this->settings, $key );
  }
  
}