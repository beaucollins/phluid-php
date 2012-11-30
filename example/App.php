<?php

require_once( realpath( '../' ) . '/vendor/autoload.php' );
  
$app = new Phluid\App();

/**
 * Adds an X-SERVER header to each request
 *
 * @author Beau Collins
 */
$app->inject( function( $req, $res, $next ){
  $res->setHeader('X-SERVER', 'Awesomesauce');
  $next();
} );

/**
 * takes any request that ends in /reverse and string reverses the body
 *
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
 *
 * @author Beau Collins
 */
$app->get( '/', function( $req, $res, $next ){
  $res->renderText( "Hello World" );
});

/**
 * Responds to GET /profile and renders the profile.php template
 *
 * @author Beau Collins
 */
$app->get( '/profile', function( $req, $res, $next ){
  $user = new stdClass();
  $user->name = "Beau Collins";
  $user->username = "beaucollins";
  $user->url = "http://viewsource.beaucollins.com";
  $res->render( 'profile', array( 'user' => $user ) );
});

return $app;