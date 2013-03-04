<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';


/**
 * This is OAuth2 implementation for Google login
 * @see https://developers.google.com/accounts/docs/OAuth2Login
 */
class Google extends Base {

	private $authenticate_url = 'https://accounts.google.com/o/oauth2/auth';
	private $access_token_url = 'https://accounts.google.com/o/oauth2/token';
	private $profile_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
	
	private $scope;

	private $client_id;
	private $client_secret;
	
	private $access_token;
	
	private $session;

	
	function config($config){
		if( $this->scope === null){
			$this->scope = array();
			$this->scope[] = 'https://www.googleapis.com/auth/userinfo.profile';
			$this->scope[] = 'https://www.googleapis.com/auth/userinfo.email';
		}
		
		if( isset($config['authenticate_url']) ){
			$this->authenticate_url = $config['authenticate_url'];
		}
		if( isset($config['scope']) ){
			$this->scope = $config['scope'];
		}
		
		if( isset($config['client_id']) ){
			$this->client_id = $config['client_id'];
		}
		if( isset($config['client_secret']) ){
			$this->client_secret = $config['client_secret'];
		}		
		
		$this->session = new \Radulski\SocialAuth\Session('Radulski\SocialAuth\Provider\Facebook:'.$this->client_id);
	}
	
	function loadUser($user_id){
		if($this->session->getValue('user_id') == $user_id){
			$this->user_id = $user_id;
			$this->token = $this->session->getValue('token');
			$this->display_identifier = $this->session->getValue('display_identifier');
		} else {
			$this->clearUser();
		}
	} 
	
	function clearUser(){
		$this->user_id = null;
		$this->display_identifier = null;
		$this->token = null;
	}
	
	
	
	function beginLogin(){
		$this->clearUser();
		$this->session->clear();
		
		$url = $this->authenticate_url;
		$url_query = array();
		$url_query['response_type'] = 'code';
		$url_query['client_id'] = $this->client_id;
		$url_query['redirect_uri'] = $this->return_url;
		$url_query['scope'] = implode(' ', $this->scope);
		$url_query['access_type'] = 'online';
		$url_query['approval_prompt'] = 'auto';

		
		if( strpos($url, '?') ){
			$url .= '&';
		} else {
			$url .= '?';
		}
		$url .= http_build_query($url_query);
		
		return array(
    		'type' => 'redirect',
    		'url' => $url,
		);
	}
	function completeLogin($query){
		// get access token
		$query_options = array();
		parse_str($query, $query_options);

		if( ! empty($query_options['error']) ){
			// check for errors
			$error = $query_options['error'];
			if($error == 'access_denied' ){
				return false;
			} else {
				throw new \Exception($error);
			}
		}
		// get authorization code
		if( empty($query_options['code']) ){
			return false;
		}
		
		$auth_code = $query_options['code'];
		
		$params = array(
			'code' => $auth_code,
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri' => $this->return_url,
			'grant_type' => 'authorization_code',
		);

		$response = $this->makeHttpRequest($this->access_token_url, 'POST', $params);
		$token = json_decode($response, true);

		if($token['error']){
			throw new \Exception("Failed to get authorization code: ".$token['error']);
		} else{
			$this->token = $token;
		}
		
		
		// get account info
		$profile = $this->getProfile();
		if($profile){
			$this->user_id = $profile['id'];
			$this->display_identifier = $profile['link'];
			
			// save
			$this->session->setValue('user_id', $this->user_id);
			$this->session->setValue('display_identifier', $this->display_identifier);
			$this->session->setValue('token', $this->token);
			return true;
		} else {
			throw new \Exception("Failed to retrieve profile information.");
		}
	}
	
	public function getProfile(){
		if( ! $this->token){
			return null;
		}
		
		$params  = array('access_token' => $this->token['access_token']);
		$response = $this->makeHttpRequest($this->profile_url, 'GET', $params);
		return json_decode($response, true);
	}
	
}


