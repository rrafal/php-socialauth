<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';
require_once __DIR__.'/../Utils/OAuth1.php';

	
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
	
	private $access_token;
	private $access_token_secret;
	
	private $session;

	
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
		
		$this->session = new \Radulski\SocialAuth\Session('Radulski\SocialAuth\Provider\Twitter:'.$this->consumer_key);
	}
	
	function loadUser($user_id){
		if($this->session->getValue('user_id') == $user_id){
			$this->user_id = $user_id;
			$this->display_identifier = $this->session->getValue('display_identifier');
			$this->access_token = $this->session->getValue('oauth_access_token');
			$this->access_token_secret = $this->session->getValue('oauth_access_token_secret');
		} else {
			$this->user_id = null;
			$this->display_identifier = null;
			$this->access_token = null;
			$this->access_token_secret = null;
		}
	}
	
	
	function beginLogin(){
		$this->session->clear();
		
		$oauth = $this->getOAuth();
		$request_token_info = $oauth->fetchRequestToken($this->request_token_url, $this->return_url);
		print_r($request_token_info);die();
		$this->session->setValue('oauth_token', $request_token_info['oauth_token']);
		$this->session->setValue('oauth_token_secret', $request_token_info['oauth_token_secret']);
		

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
		
		
		$token = $this->session->getValue('oauth_token');
		$token_verifier = $this->session->getValue('oauth_verifier');
		$token_secret = $this->session->getValue('oauth_token_secret');
		
		if($token != $query_options['oauth_token']){
			return false;// cannot validate without correct secret
		}
				
		$oauth = $this->getOAuth();
		$oauth->setToken($token, $token_secret);
		$access_token_info = $oauth->fetchAccessToken($this->access_token_url, $token_verifier);
		
		// get access token
		$oauth = $this->getApi();
		$oauth_token_secret = $this->session->getValue( 'oauth_token_secret' );
		
		$oauth->setToken($query_options['oauth_token'], $oauth_token_secret);
		$access_token_info = $oauth->getAccessToken($this->access_token_url);
		
		if(empty($access_token_info['user_id']) ){
			return false;
		}
	
		$this->user_id = $access_token_info['user_id'];
		$this->display_identifier = 'http://twitter.com/'.$access_token_info['screen_name'];
		$this->access_token = $access_token_info['oauth_token'];
		$this->access_token_secret = $access_token_info['oauth_token_secret'];
		
		// save it
		$this->session->setValue('user_id', $this->user_id);
		$this->session->setValue('display_identifier', $this->display_identifier);
		$this->session->setValue('oauth_access_token', $this->access_token);
		$this->session->setValue('oauth_access_token_secret', $this->access_token_secret);
		
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
		
		if($this->access_token && $this->access_token_secret){
			$oauth->setToken($this->access_token, $this->access_token_secret);
		}
		return $oauth;
	}
	
	function getOAuth(){
		$oauth = new \Radulski\SocialAuth\Utils\OAuth1();
		$oauth->setConsumer($this->consumer_key, $this->consumer_secret);

		if($this->access_token && $this->access_token_secret){
			$oauth->setAccessToken($this->access_token, $this->access_token_secret);
		}
		return $oauth;
	}
	
	function getRequestUrl($name){
		return $this->request_base_url . '/' . $name . '.json';
	}
	
}


