<?php

namespace Phluid\Http;
use Evenement\EventEmitter;
use React\Http\ServerInterface;
use React\Socket\ServerInterface as SocketServerInterface;
use React\Socket\ConnectionInterface;

class Server extends EventEmitter implements ServerInterface {
  
  function __construct( SocketServerInterface $io ){
    
    $server = $this;
    $io->on( 'connection', array( $this, 'handleConnection' ) );
    
  }
  
  function handleConnection( ConnectionInterface $conn ){
    
    $server = $this;
        
    $request = new Request( $conn );
    $request->on( 'headers' , function( Headers $headers, $trailing ) use ( $server, $conn, $request ) {
      
      $response = new Response( $conn, $request );
            
      // notify of the new request with request and response pair
      $server->emit( 'request', array( $request, $response ) );
      
      // emit data if there was trailing data on the headers
      $request->emit( 'data', array( $trailing ) );
      
    } );
    
  }
    
}