#Phluid

A microframework for PHP. Quite heavily inspired by [Express][]. [![Build Status](https://secure.travis-ci.org/beaucollins/phluid-php.png?branch=master)](https://travis-ci.org/beaucollins/phluid-php)

Built on top of [ReactPHP](https://github.com/reactphp/react).

[Express]: http://expressjs.com "Express web application framework for node"

## Install

[Composer][] sample `composer.json`

    {
      "name" : "beaucollins/sample-app",
      "repositories" : [
        {
          "type" : "git",
          "url" : "https://github.com/beaucollins/phluid-php"
        }
      ],
      "require" : {
        "phluid/phluid":"react-dev"
      }
    }

[Composer]: http://google.com/?q=composer%20php "Google Search: composer php"

## Example

Download [Phluid][] to a server somewhere. 

    <?php
    
    require 'vendor/autoload.php';
        
    $app = new Phluid\App();
    
    // add some handlers
    
    $app->get( '/', function( $request, $response ){
      $response->renderText( 'Hello World' );
    });
    
    $app->get( '/hello/:name', function( $request, $response ){
      $response->renderText( "Hello {$request->param('name')}");
    });
    
    $app->listen( 4000 );
    
    
Save that to `server.php` and then boot up your server:

    $> php server.php
  
Open [http://localhost:4000/][example] in your browser.

[Phluid]: https://github.com/beaucollins/phluid-php/tarball/master "phluid-php master tarball"
[example]: http://localhost:4000/

## Middleware

Any invocable PHP object can be used as a middleware. It receives three
arguments: `$request`, `$response`, and `$next`. If the middleware decides it
doesn't need to handle the request it can simply call `$next()`.

    // You can use a "closure"
    $app->inject( function( $request, $response, $next ){
      if( 0 === strpos( $request->path, '/admin/' ) ){
        $response->redirectTo( '/login' );
      } else {
        $next();
      }
    });
    
    // You can use a string that contains the name of a function
    function server_header( $request, $response, $next ){
      $response->setHeader( 'X-Served-By', 'Phluid' );
      $next();
    };
    $app->inject( 'server_header' );
    
    // Any callable works, so you use an object if you like
    $warden = new Warden();
    $app->inject( array( $warden, 'protect' ) );
    // calls $warden->protect( $request, $response, $next )
    
There are quite a few middlewares provided already:

- BasicAuth: add's support for HTTP Basic Auth header parsing
- Cascade: Chain middleware together
- ExceptionHandler: Renders a stack trace error page for debugging
- FormBodyParser: Parses form encoded request bodies
- JsonBodyParser: Parses JSON request bodies
- MultipartBodyParser: Parses multipart requests
- Prefix: Mounts middleware under a prefix 
- RequestTimer: Records time for the full request/response cycle
- StaticFiles: Serves static files from a specified folder

## Filters

Instead of providing middleware for every request, middleware can be added to
specific routes:

    // class AwesomeSauce implements __invoke( $request, $response, $next )
    $awesome = new AwesomeSauce( 'config' );
    $app->get( '/admin/', $awesome, function( $request, $response ){
      $response->renderText( 'Hello World' );
    } );
    // $awesome->__invoke( $req, $res, $next ) is called before the action
    
Passing an `array` of middlewares will execute each middleware for that route:

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
    
## Templating

Phluid comes with a pretty basic templating system. Given a route like this in
a file named `index.php`:

    $app->get( '/', function( $req, $res ){
      $current_user = findCurrentUser();
      $res->render( 'home', array( 'user' => $current_user ) );
    });

Phluid will look in a folder named `views` in the same directory as the
`index.php` for a file named `home.php` to use as the template. This file is
interpreted as regular old PHP with the `array` extracted into local variables
for the view:

    <!DOCTYPE html>
    <head>
    
    </head>
    <body>
      <?php echo strrev( 'dlrow olleh' ) ?>, <?php echo $user->username ?>
    </body>
    
### Layouts
    
Templates aren't _only_ regular PHP, there's a little magic added behind
the scenes too. Each view is executed in the context of a `Phluid\ViewContext`
instance which gives you some convenient templating methods to help you
organize your content.

First off, there is the `layout` method. Let's assume you want to have all your
boilerplate HTML in one file and you just want to render the view into that
file. In your view you can call `$this->layout` to tell the templating system
which layout to render the view into. So if you change your `home.php` to:

    <?php $this->layout( 'public' ) ?>
    <?php echo strrev( 'dlrow olleh' ) ?>, <?php echo $user->username ?>

The templating system will then look for a `public.php` file and render it with
the content of the `home.php` view. To make this work you need to use the
`content` template method in your layout:

    <!DOCTYPE html>
    <head>
    
    </head>
    <body>
      <?php echo $this->content() ?>
    </body>
    
Since layouts are just views themselves, they too can have layouts.

You can also define a default layout for all `Phluid\Request::render` calls by
providing the `Phluid\App::default_layout` setting.
