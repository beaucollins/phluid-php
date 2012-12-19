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
        $event_args = func_get_args();
        call_user_func_array( array( $to, 'emit' ), array( $event, $event_args ) );
      });
    }
  }
    
}