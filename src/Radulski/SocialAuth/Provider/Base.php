<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__ . '/../Provider.php';

abstract class Base implements \Radulski\SocialAuth\Provider {
	protected $user_id;
	protected $display_identifier;
	
	protected $base_url;
	protected $return_url;
	
	
	public function __construct(){
		
	}
	
	public abstract function config($config);
	
	public function setBaseUrl($url){
		$this->base_url = $url;
	}
	public function setReturnUrl($url){
		$this->return_url = $url;
	}
	
	public function loadUser($user_id){
		$this->user_id = $user_id;
	}
	
	public function getUserId(){
		return $this->user_id;
	}
	
	public function getDisplayIdentifier(){
		return $this->display_identifier;
	}
	
	/**
	 * Begins login process.
	 * Returns array with key 'type' and additional information
	 */
	abstract public function beginLogin(array $attributes = array());
	/**
	 * Completes login process
	 * Returns array with key 'status' and other information.
	 * Status may be: failure, cancel, success
	 */
	abstract public function completeLogin($query);
}

