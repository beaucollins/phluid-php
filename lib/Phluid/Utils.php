<?php

namespace Phluid;

class Utils {
  
  static function array_val( $array, $key, $default = null ){
    if ( array_key_exists( $key, $array ) ) {
      return $array[$key];
    }
    return $default;
  }
  
  static function performFilters( $request, $response, $filters ){
    if ( $filter = array_shift( $filters ) ) {
      $filter( $request, $response, function() use ( $request, $response, $filters ) {
        Utils::performFilters( $request, $response, $filters );
      });
    }
    
  }
  
}