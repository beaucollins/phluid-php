<?php

require_once( realpath( '../' ) . '/vendor/autoload.php' );

$app = require( 'App.php' );
$app->inject( function( $request, $response, $next ){
  echo $request . PHP_EOL;
  $next();
} );

$app->inject( new \Phluid\Middleware\RequestTimer() );

$app->inject( new \Phluid\Middleware\ExceptionHandler );

$app->get( '/form', function( $request, $response ){
  $response->render( 'form' );
} );

$app->post( '/form', new \Phluid\Middleware\FormBodyParser() , function( $request, $response ){
  $response->render( 'form' );
} );

$app->get( '/upload', function( $request, $response ){
  $response->render( 'upload' );  
} );

$app->post( '/upload', new \Phluid\Middleware\MultipartBodyParser(), function( $request, $response ){
  $response->render( 'upload' );
} );

$app->listen( 4000 );
