<?php

interface Phluid_Middleware {
  
  public function __invoke( $request, $response, $next );
  
}