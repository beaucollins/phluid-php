<?php

require_once 'App.php';

$app = new App();

function route( $method, $path, $closure ){
  global $app;
  return $app->route( $method, $path, $closure );
}

function get( $path, $closure ){
  global $app;
  return $app->get( $path, $closure );
}

function post( $path, $closure ){
  global $app;
  return $app->post( $path, $closure );
}