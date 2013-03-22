<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';


use Radulski\SocialAuth\Exception;
use Radulski\SocialAuth\NotSupportedException;




/**
 */
class Facebook extends Base {
	private $auth_url = 'https://www.facebook.com/dialog/oauth';
	private $access_token_url = 'https://graph.facebook.com/oauth/access_token';

	private $app_id;
	private $secret;
	
	private $access_token;
	private $profile;
	private $session;
	
	
	function config($config){
		if( isset($config['app_id']) ){
			$this->app_id = $config['app_id'];
		}
		
		if( isset($config['secret']) ){
			$this->secret = $config['secret'];
		}
		
		$this->session = new \Radulski\SocialAuth\Session('Radulski\SocialAuth\Provider\Facebook:'.$this->app_id);
	}
	
	function loadUser($user_id){
		if($this->session->getValue('user_id') == $user_id){
			$this->user_id = $user_id;
			$this->access_token = $this->session->getValue('access_token');
			$this->display_identifier = $this->session->getValue('display_identifier');
		} else {
			$this->user_id = null;
			$this->access_token = null;
			$this->display_identifier = null;
		}
	}
	
	function beginLogin(){
		$this->session->clear();
		$scope = array('email');

		$query = array();
		$query['client_id'] = $this->app_id;
		$query['redirect_uri'] = $this->return_url;
		$query['state'] = $this->getCSRFToken();
		$query['scope'] = implode(',', $scope);
		
		$login_url = $this->buildUrl($this->auth_url, null, $query);
		
		
		return array(
    		'type' => 'redirect',
    		'url' => $login_url,
		);
	}
	
	function completeLogin($query){
		$query_params = array();
		parse_str($query, $query_params);
		
		if( empty($query_params['code']) || empty($query_params['state']) ){
			return false;
		}
		
		if( ! $this->checkCSRFToken($query_params['state']) ){
			throw new Exception("Cannot validate the login response.");
		}
		$code = $query_params['code'];
		$params = array(
			'client_id' => $this->app_id,
			'client_secret' => $this->secret,
			'redirect_uri' => $this->return_url,
			'code' => $query_params['code'],
		);
		$response = $this->makeHttpRequest($this->access_token_url, 'POST', $params);
		$token = array();
    	parse_str($response, $token);

		                      
		if( empty($token['access_token']) ){
			return false;
		} else {
			// get and save profile info
			$this->access_token = $token['access_token'];
			
			$this->profile = $this->getProfileImpl();
			if( ! $this->profile){
				throw new Exception("Failed to retrieve user profile.");
			}
			$this->user_id = $this->profile['id'];
			$this->display_identifier = $this->profile['link'];
			
			$this->session->setValue('access_token', $this->access_token);
			$this->session->setValue('display_identifier', $this->display_identifier);
			$this->session->setValue('user_id', $this->user_id);
			return true;
		}
	}
	function getProfile(){
		if( ! $this->profile){
			$this->profile = $this->getProfileImpl();
		}
		return $this->profile;
	}
	private function getProfileImpl(){
		if( $this->access_token ){
			return $this->graphRequest('me');
		} else {
			return null;
		}
	}
        
        function beginAuthorization($scope){
            throw new NotSupportedException("Authorization request is not supported.");
        }
        function completeAuthorization($query){
            throw new NotSupportedException("Authorization request is not supported.");
        }
	
	function graphRequest($name, $params = array()){
		$url = 'https://graph.facebook.com/'.$name;
		$params['access_token'] = $this->access_token;
   		$params['format'] = 'json-strings';
   		$response = $this->makeHttpRequest($url, 'GET', $params);
   		return json_decode($response, true);
	}
	
	function getCSRFToken(){
		$token = $this->session->getValue('csrf');
		if( $token == null){
			$token = md5(mt_rand());
			$this->session->setValue('csrf', $token);
		}
		return $token;
	}
	function checkCSRFToken($value){
		if( ! $value){
			return false;
		}
		$correct = $this->getCSRFToken();
		return $correct === $value;
	}
	
	
	
}

