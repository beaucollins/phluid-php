<?php

namespace Phluid;
use Evenement\EventEmitter;

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
  
  static function forwardEvents( EventEmitter $to, EventEmitter $from, array $events ){
    foreach( $events as $event ) {
      $from->on( $event, function() use( $event, $to ){
        $args = func_get_args();
        array_unshift( $args, $event );
        call_user_func_array( array( $to, 'emit' ), $args );
      });
    }
  }
    
}