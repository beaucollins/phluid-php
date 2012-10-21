Phluid
======

A microframework for PHP.

Example
-------

Download `phluid-php` to a server somewhere. 

    <?php
    
    require 'path/to/lib/Phluid.php';
    
    $app = new App();
    
    $app->get( '/', function( $request, $response ){
      $response->renderText( 'Hello World' );
    });
    
    $app->get( '/hello/:name', function( $request, $response ){
      $response->renderText( "Hello {$request->param('name')}");
    });
    
    $app->run();
    
    
Save that to `index.php` and put it on a webserver somewhere and have it serve all file
requests with an `.htaccess` like this one.

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)?$ index.php [L]

