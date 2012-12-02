<?php

require_once( realpath( '../' ) . '/vendor/autoload.php' );
  
$app = new Phluid\App( array(
  'default_layout' => 'layout'
) );

/**
 * Adds an X-SERVER header to each request
 * @author Beau Collins
 */
$app->inject( function( $req, $res, $next ){
  $res->setHeader('X-Powered-By', 'Awesomesauce');
  $next();
} );

$app->inject( new \Phluid\Middleware\RequestTimer() );
$app->inject( function( $request, $response, $next ){
  echo $request . PHP_EOL;
  $next();
} );
$app->inject( new \Phluid\Middleware\ExceptionHandler );
$app->inject( new \Phluid\Middleware\BasicAuth( function( $credentials, $success, $failure ){
  $username = $credentials['username'];
  $password = $credentials['password'];
  if ( strtolower( $username ) == 'admin' && $password == "secret" ) {
    $success( $username );    
  } else {
    $failure();
  }
} ) );
$app->inject( new \Phluid\Middleware\StaticFiles( __DIR__ . '/public' ) );

/**
 * takes any request that ends in /reverse and string reverses the body
 * @author Beau Collins
 */
$app->inject( function( $req, $res, $next ){
  $reverse = false;
  $new_path = preg_replace( '/\/reverse\/?$/', '/', $req->path );
  if ( $new_path !== $req->path ) {
    $req->path = $new_path;
    $reverse = true;
  }
  $next();
  if ( $reverse ) {
    $res->setBody( strrev( $res->getBody() ) );
  }
} );

/**
 * Responds to GET / renders plain text "Hello World"
 * @author Beau Collins
 */
$app->get( '/', function( $req, $res, $next ){
  $res->render( 'home' );
});

/**
 * Responds to GET /profile and renders the profile.php template
 * @author Beau Collins
 */
$app->get( '/profile', function( $req, $res, $next ){
  $user = new stdClass();
  $user->name = "Beau Collins";
  $user->username = "beaucollins";
  $user->url = "http://viewsource.beaucollins.com";
  $res->render( 'profile', array( 'user' => $user ) );
});

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

$app->get( '/login', function( $request, $response, $next ){
  if ( !$request->user ) {
    \Phluid\Middleware\BasicAuth::sendUnauthorized( $response );
  } else {
    echo "We have a user: " . $request->user . PHP_EOL;
    $response->redirectTo( '/', 302 );
  }
});

$app->get( '/wait', function( $request, $response ){
  // this blocks!
  shell_exec( 'sleep 15' );
  $response->render( 'wait' );
} );

return $app;