<?php
namespace Phluid\Http;
use Evenement\EventEmitter;

define( 'HEADER_EOL', "\r\n" );

class HeaderParser extends EventEmitter {
  
  private $buffer = '';
  private $conn;
  private $headers;
  
  function __construct( \React\Socket\ConnectionInterface $conn ){
    $this->conn = $conn;
    $conn->on( 'data', $this );
  }
  
  function __invoke( $data ){
    $this->buffer .= $data;

    while ( $position = strpos( $this->buffer, HEADER_EOL ) ) {
      $raw_header = substr( $this->buffer, 0, $position );
      if( $position > 1 ){
        $this->parseRawHeader( $raw_header );
        $this->buffer = substr( $this->buffer, $position + 1 );
      } else {
        $this->conn->removeListener( 'data', $this );
        $this->emit( 'headers', array( $this->headers, substr( $this->buffer, $position + strlen( HEADER_EOL ) ) ) );
        unset( $this->buffer );
        break;        
      }
    }
        
  }
  
  private function parseRawHeader( $header ){
    // if it's the first header then it has the verb path and protocol version
    if ( $colon = strpos( $header, ":" ) ) {
      list( $key, $value ) = explode( ":", trim( $header ), 2 );
      $this->headers[$key] = trim( $value );
    } else {
      list( $method, $path, $protocol ) = explode( ' ', $header, 3 );
      list( $protocol, $version ) = explode( '/', $header, 2 );
      $this->headers = new Headers( $method, $path, $protocol, $version );
    }
  }
  
}