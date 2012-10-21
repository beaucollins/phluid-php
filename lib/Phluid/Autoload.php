<?php

namespace Phluid;

class Autoload {
  
  function __invoke( $className ){
    $className = ltrim($className, '\\');
    if ( 0 !== strpos( $className, 'Phluid' ) ) return;
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . $fileName;
  }
  
}

spl_autoload_register( new Autoload() );