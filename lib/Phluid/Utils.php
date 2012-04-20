<?php

class Phluid_Utils {
  
  static function array_val( $array, $key, $default = null ){
    if ( array_key_exists( $key, $array ) ) {
      return $array[$key];
    }
    return $default;
  }
  
}