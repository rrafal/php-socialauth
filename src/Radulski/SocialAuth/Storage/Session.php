<?php

namespace Radulski\SocialAuth\Storage;

class Session {
	private $base_key;
	
	public function __construct($base_key){
		@session_start();
		$this->base_key = $base_key;
	}

	public function clear(){
		$_SESSION[ $this->base_key ] = array();
	}
	
	public function setValue($key, $value){
		$_SESSION[ $this->base_key ][ $key ] = $value;
	}
	public function getValue($key){
		if( isset($_SESSION[ $this->base_key ]) && isset($_SESSION[ $this->base_key ][ $key ]) ){
			return $_SESSION[ $this->base_key ][ $key ];
		} else {
			return null;
		}
	}
}
