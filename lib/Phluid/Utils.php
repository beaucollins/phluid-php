<?php

namespace Phluid;

class Utils {
  
  static function array_val( $array, $key, $default = null ){
    if ( array_key_exists( $key, $array ) ) {
      return $array[$key];
    }
    return $default;
  }
  
  static function uid( $length ){
    return bin2hex( openssl_random_pseudo_bytes( $length ) );
  }
    
}