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
	
	protected function makeHttpRequest($url, $method = 'GET', $params = array()){
		if( strtolower($method) == 'get' && $params){
			$url = $this->buildUrl($url, null, $params);
		}
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_VERBOSE, 0); 
		curl_setopt($curl, CURLOPT_HEADER, 0);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		if( strtolower($method) == 'post' ){
			$post_data = http_build_query($params, '', '&');
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); 
		}
		
		$return = curl_exec($curl); 
		curl_close($curl); 
		return $return; 
	}
	protected function buildUrl($base, $path = null, $query = array(), $fragment = null){
		$url = $base;
		if($path){
			$url = rtrim($url, '/') . '/' . ltrim($path, '/');
		}
		if($query){
			$url .= strpos($url, '?') ? '&' : '?';
			
			if( is_array($query) ){
				$url .= http_build_query($query, '', '&');
			} else {
				$url .= $query;
			}
		}
		if($fragment){
			$url .= "#" . $fragment;
		}
		return $url;
	}
	
	abstract public function getProfile();
	
	/**
	 * Begins login process.
	 * Returns array with key 'type' and additional information
	 */
	abstract public function beginLogin();
	/**
	 * Completes login process
	 * Returns array with key 'status' and other information.
	 * Status may be: failure, cancel, success
	 */
	abstract public function completeLogin($query);
}

