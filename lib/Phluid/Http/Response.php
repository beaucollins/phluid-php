<?php

namespace Phluid\Http;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class Response {
  
  private $io;
  private $closed = false;
  private $writable = true;
  private $headWritten;
  private $chunkEncoding = true;
  
  function __construct( ConnectionInterface $conn, Request $request ){
    $this->io = $conn;
  }
  
  public function writeHead( $status = 200, $headers = array() ){
    
  }
  
    
}