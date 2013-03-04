<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';


	
/**
 * Authenticates user against Twitter account.
 *
 * Requires "consumer_key" and "consumer_secret"
 * @see https://dev.twitter.com/apps/new
 */
class Twitter extends Base {
	private $server_url = 'https://api.twitter.com';
	private $access_token_url = 'https://api.twitter.com/oauth/access_token';
	private $authenticate_url = 'https://api.twitter.com/oauth/authenticate';
	private $request_token_url = 'https://api.twitter.com/oauth/request_token';
	private $request_base_url = 'https://api.twitter.com/1.1/';
	
	private $consumer_key;
	private $consumer_secret;

	
	function config($config){
		if( isset($config['store']) ){
			$this->store_name = $config['store'];
		}
		if( isset($config['access_token_url']) ){
			$this->access_token_url = $config['access_token_url'];
		}
		if( isset($config['authenticate_url']) ){
			$this->authenticate_url = $config['authenticate_url'];
		}
		if( isset($config['request_token_url']) ){
			$this->request_token_url = $config['request_token_url'];
		}
		if( isset($config['consumer_key']) ){
			$this->consumer_key = $config['consumer_key'];
		}
		if( isset($config['consumer_secret']) ){
			$this->consumer_secret = $config['consumer_secret'];
		}

	}
	
	
	
	function beginLogin(){
		$this->clearSession();
		
		$oauth = $this->getApi();
		$request_token_info = $oauth->getRequestToken($this->request_token_url, $this->return_url);
		
		$this->setSessionValue('oauth_token', $request_token_info['oauth_token']);
		$this->setSessionValue('oauth_token_secret', $request_token_info['oauth_token_secret']);
		

		$url = $this->authenticate_url . '?oauth_token='.urlencode($request_token_info['oauth_token']);
		
		return array(
    		'type' => 'redirect',
    		'url' => $url,
		);
	}
	function completeLogin($query){
		$query_options = array();
		parse_str($query, $query_options);
		
		if( ! empty($query_options['denied']) ){
			return false;
		}
		if( empty($query_options['oauth_token']) ){
			return false;
		}
		
		// get access token
		$oauth = $this->getApi();
		$oauth_token_secret = $this->getSessionValue( 'oauth_token_secret' );
		
		$oauth->setToken($query_options['oauth_token'], $oauth_token_secret);
		$access_token_info = $oauth->getAccessToken($this->access_token_url);
		
		if(empty($access_token_info['user_id']) ){
			return false;
		}
	
		// save it
		$this->setSessionValue('oauth_access_token', $access_token_info['oauth_token']);
		$this->setSessionValue('oauth_access_token_secret', $access_token_info['oauth_token_secret']);
		

		$this->user_id = $access_token_info['user_id'];
		$this->display_identifier = 'http://twitter.com/'.$access_token_info['screen_name'];
		
		return true;
	}
	
	function getProfile(){
		$url = $this->getRequestUrl('account/verify_credentials');
		
		$oauth = $this->getApi();
		$oauth->fetch( $url );
		
		$response = $oauth->getLastResponse();
		$profile = json_decode($response, true);
		return $profile;
	}
	
	function getApi(){
		$oauth = new \OAuth($this->consumer_key,$this->consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		
		$access_token = $this->getSessionValue('oauth_access_token');
		$access_token_secret = $this->getSessionValue('oauth_access_token_secret');
		if($access_token && $access_token_secret){
			$oauth->setToken($access_token, $access_token_secret);
		}
		return $oauth;
	}
	
	function getRequestUrl($name){
		return $this->request_base_url . '/' . $name . '.json';
	}
	
	/**
	 * Persist info in session
	 */
	private function clearSession(){
		@session_start();
		$base_key = 'Radulski\SocialAuth\Provider\Twitter:'.$this->consumer_key;
		$_SESSION[ $base_key ] = array();
	}
	
	private function setSessionValue($key, $value){
		@session_start();
		$base_key = 'Radulski\SocialAuth\Provider\Twitter:'.$this->consumer_key;
		$_SESSION[ $base_key ][ $key ] = $value;
	}
	private function getSessionValue($key){
		@session_start();
		$base_key = 'Radulski\SocialAuth\Provider\Twitter:'.$this->consumer_key;
		if( isset($_SESSION[ $base_key ]) && isset($_SESSION[ $base_key ][ $key ]) ){
			return $_SESSION[ $base_key ][ $key ];
		} else {
			return null;
		}
	}
}


