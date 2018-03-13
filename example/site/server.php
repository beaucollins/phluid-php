<?php

require_once( realpath( '.' ) . '/vendor/autoload.php' );

$app = require( 'App.php' );

$app->listen( 4000 );
