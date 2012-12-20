<?php

require_once( realpath( '../../' ) . '/vendor/autoload.php' );

$loop = React\EventLoop\Factory::create();

$client = new Predis\Async\Client('tcp://127.0.0.1:6379', $loop);
$socket = new React\Socket\Server( $loop );
$http = new React\Http\Server( $socket, $loop );

$session_store = new Phluid\Middleware\Sessions\PredisStore( $client );

$app = new Phluid\App();
$app->inject( new Phluid\Middleware\Cookies() );
$app->inject( new Phluid\Middleware\Sessions( array(
  'store' => $session_store,
  'secret' => 'aslkji339jkcmas0o329insdlsdoisdf0s09jasfd'
) ));
  
$app->get( '/', function( $req, $res ){
  if ( $count = $req->session['counter'] ) {
    $count++;
  } else {
    $count = 1;
  }
  $req->session['counter'] = $count;
  
  $res->renderText( "Hello world ;): $count" );
});

$app->createServer( $http );

$socket->listen( 4000 );

$loop->run();
