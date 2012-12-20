# Phluid\App with Predis Session Store

Example of using the Predis\Async\Client in a Phluid\Middleware\Sessions\SessionStoreInterface to persist session data.

## Setup

- A running [redis](http://redis.io) server
- PHP Extension: [phpiredis](https://github.com/seppo0010/phpiredis)
- Add [predis/predis-async](https://github.com/nrk/predis-async) to your `composer.json` dependencies and `composer update`


Now you just need to provide the `Phluid\Middleware\Sessions\PredisStore` to your `Session` middleware with a configured `Predis\Async\Client`.

    $client = new Predis\Async\Client("tcp://127.0.0.1:6397", $loop);
    $store = new Phluid\Middleware\Sessions\PredisStore( $client );
    
    $app->inject( new Phluid\Middleware\Sessions( array(
      'store'  => $store,
      'secret' => 'foryoureyesonly'
    ) ));
        
Be sure to use the same event loop for the `Predis\Async\Client`.
