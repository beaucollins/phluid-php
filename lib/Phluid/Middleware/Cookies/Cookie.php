<?php
namespace Phluid\Middleware\Cookies;
use Phluid\Utils;
class Cookie {
  
  public $value;
  public $http_only = false;
  public $max_age   = false;
  public $secure    = false;
  public $domain    = null;
  public $path      = null;
  
  function __construct( $value, $options = array()  ){
    $this->value     = (string) $value;
    $this->http_only = Utils::array_val( $options, 'http_only', $this->http_only );
    $this->secure    = Utils::array_val( $options, 'secure', $this->secure );
    $this->domain    = Utils::array_val( $options, 'domain', $this->domain );
    $this->path      = Utils::array_val( $options, 'path', $this->path );
    $this->max_age   = Utils::array_val( $options, 'max_age', $this->max_age );
  }
  
  function __toString(){
    $val = urlencode( $this->value );
    if ( $val != "" ) {
      $val = '"' . $val . '"';
    }
    if ( $this->domain )    $val .= "; Domain=$this->domain";
    if ( $this->path )      $val .= "; Path=$this->path";
    if ( $this->max_age )   $val .= "; Max-Age=$this->max_age";
    if ( $this->http_only ) $val .= "; HttpOnly";
    if ( $this->secure )    $val .= "; Secure";
    return $val;
  }
  
}

