Phluid
======

A microframework for PHP.

Example
-------

    <?php
    
    require 'lib/Phluid.php';
    
    $app = new Phluid_App();
    
    $app->get( '/', function( $request, $response ){
      $response->renderText( 'Hello World' );
    })
    
Save that to `index.php` and put it on a webserver somewhere.