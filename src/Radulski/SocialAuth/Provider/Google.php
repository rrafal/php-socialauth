<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';

use Radulski\SocialAuth\Exception;
use Radulski\SocialAuth\NotSupportedException;



/**
 * This is OAuth2 implementation for Google login
 * @see https://developers.google.com/accounts/docs/OAuth2Login
 */
class Google extends Base {

	private $authenticate_url = 'https://accounts.google.com/o/oauth2/auth';
	private $access_token_url = 'https://accounts.google.com/o/oauth2/token';
	private $profile_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
	private $list_contacts_url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=10000';
	
	private $scope;

	private $client_id;
	private $client_secret;
	
	
	private $session;

	public function __construct(){
		$this->config(array());
	}
	
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
		
		$this->session = new \Radulski\SocialAuth\Session('Radulski\SocialAuth\Provider\Google:'.$this->client_id);
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
	
	
	
	function beginLogin($scope = null){
		$this->clearUser();
		$this->session->clear();
		
		$url = $this->authenticate_url;
		$url_query = array();
		$url_query['response_type'] = 'code';
		$url_query['client_id'] = $this->client_id;
		$url_query['redirect_uri'] = $this->return_url;
		$url_query['scope'] = $this->getFullScopeString($scope);
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
		$token = $this->getAccessTokenAfterAuth($query);
		if( ! $token){
			return false;
		}
			
		$this->token = $token;
		
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
			throw new Exception("Failed to retrieve profile information.");
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
	
	
	public function listContacts(){
		$params  = array('access_token' => $this->token['access_token']);
		$response = $this->makeHttpRequest($this->list_contacts_url, 'GET', $params);
		$response = str_replace('gd:', 'gd_', $response);
		$xml = simplexml_load_string($response); 
		
		$contacts = array();
		foreach($xml->entry as $entry){
			$contacts[] = array(
			    'id' => $entry->id,
			    'name' => (string)$entry->title,
			    'email' => (string)$entry->gd_email['address'],
			    'phone' => (string)$entry->gd_phoneNumber,
			);
			
		}
		return $contacts;
	}
	public function getContact($id){
		$url = str_replace('/base/', '/full/', $id);
		$url = str_replace('http://', 'https://', $url);
		
		
		$params  = array('access_token' => $this->token['access_token']);
		$response = $this->makeHttpRequest($url, 'GET', $params);
		
		$response = str_replace('gd:', 'gd_', $response);
		$xml = simplexml_load_string($response); 
		
		$contact = array();
		$contact['id'] = $xml->id;
		$contact['name'] = (string)$xml->title;
		$contact['email'] = (string)$xml->gd_email['address'];
		$contact['phone'] = (string)$xml->gd_phoneNumber;		
	}
	
	private function getFullScopeString($extra_scope){
		$all = $this->scope;
		if(is_array($extra_scope)){
			$all = array_merge($all, $extra_scope);
		} else {
			$all[] = $extra_scope;
		}
		
		$all = array_map(array($this, 'normalizeScope'), $all);
		$all = array_unique($all);
		return implode(' ', $all);
	}
	public function normalizeScope($name){
		if($name == 'contacts'){
			return 'https://www.google.com/m8/feeds';
		} elseif($name == 'email-address'){
			return 'https://www.googleapis.com/auth/userinfo.email';
		} elseif($name == 'profile'){
			return 'https://www.googleapis.com/auth/userinfo.profile';
		} else {
			return $name;
		}
	}
	
	private function getAccessTokenAfterAuth($query){
		// process query
		$query_options = array();
		parse_str($query, $query_options);

		if( ! empty($query_options['error']) ){
			// check for errors
			$error = $query_options['error'];
			if($error == 'access_denied' ){
				return false;
			} else {
				throw new Exception($error);
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

		// convert to access token
		$response = $this->makeHttpRequest($this->access_token_url, 'POST', $params);
		$token = json_decode($response, true);

		if( ! empty($token['error']) ){
			throw new Exception("Failed to get authorization code: ".$token['error']);
		}
		return $token;
	}
	
}


