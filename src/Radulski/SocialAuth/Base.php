<?php

namespace RadulskiLib\SocialAuth;

abstract class Base {
	protected $identifier;
	protected $storage_type;
	protected $storage_config;
	protected $base_url;
	protected $return_url;
	protected $request_attributes;
	
	public function __construct($identifier){
		$this->identifier = $identifier;
		$this->request_attributes = array();
	}
	
	public function setBaseUrl($url){
		$this->base_url = $url;
	}
	public function setReturnUrl($url){
		$this->return_url = $url;
	}
	public function setDatabaseStorage($type, $config){
		$this->storage_type = 'database';
		$this->storage_config = $config;
	}
	public function setFileStorage($path){
		$this->storage_type = 'file';
		$this->storage_config = array('path' => $path);	
	}
	
	public function addRequestAttribute($key){
		$this->require_attributes[] = $key;
	}
	
	/**
	 * Begins login process.
	 * Returns array with key 'type' and additional information
	 */
	abstract function beginLogin();
	/**
	 * Completes login process
	 * Returns array with key 'status' and other information.
	 * Status may be: failure, cancel, success
	 */
	abstract function completeLogin($query);
}

