<?php

class Response {

  private $raw_response;
  
  private function render(){
    
  }
  
  public function renderString( $string ){
    $this->raw_response = $string;
  }
  
  public function getRawResponse(){
    return $this->raw_response;
  }
}