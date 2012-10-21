Phluid
======

A microframework for PHP.

Example
-------

Download [Phluid][] to a server somewhere. 

    <?php
    
    require 'path/to/lib/Phluid/Autoload.php';
        
    $app = new Phluid\App();
    
    // add some handlers
    
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

[Phluid]: https://github.com/beaucollins/phluid-php/tarball/master "phluid-php master tarball"

Middleware
--------------

Any invocable PHP object can be used as a middleware. It receives three
arguments: `$request`, `$response`, and `$next`. If the middleware decides it
doesn't need to handle the request it can simply call `$next()`.

    // You can use a "closure"
    $app->inject( function( $request, $response, $next ){
      if( 0 === strpos( $request->path, '/admin/' ) ){
        $response->redirect( '/login' );
      } else {
        $next();
      }
    });
    
    // You can use a string that contains the name of a function
    function server_header( $request, $response, $next ){
      $request->setHeader( 'Served-By', 'Phluid' );
      $next();
    };
    $app->inject( 'server_header' );
    
    // Any callable works, so you use an object if you like
    $warden = new Warden();
    $app->inject( array( $warden, 'protect' ) );
    // calls $warden->protect( $request, $response, $next )

Filters
-------

Instead of providing middleware for every request, middleware can be added to
specific routes:

    // class AwesomeSauce implements __invoke( $request, $response, $next )
    $awesome = new AwesomeSauce( 'config' );
    $app->get( '/admin/', $awesome, function( $request, $response ){
      $response->renderText( 'Hello World' );
    } );
    // $awesome->__invoke( $req, $res, $next ) is called before the action
    
You can pass an `array` of middlewares to be used as filters as well:

    $filters = array(
      // calls RequestLogger::__invoke instance method
      new RequestLogger( "/var/log/phluid" ),
      // calls  RequestLogger::logRequest instance method
      array( new RequestLogger(), 'logRequest' ),
      // calls RequestLogger::someMethod
      array( 'RequestLogger', 'someMethod' ),
      // call logRequest() global function
      'logRequest'
    );
    
    $app->get( '/logout', $filters, function( $request, $response, $next ){
      $request->session->user = null;
      $response->renderText( "Goodbye." );
    } );
    
