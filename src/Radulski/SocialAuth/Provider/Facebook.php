<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';



/**
 */
class Facebook extends Base {
	private $app_id;
	private $secret;
	
	private $profile;
	
	function config($config){
		if( isset($config['app_id']) ){
			$this->app_id = $config['app_id'];
		}
		
		if( isset($config['secret']) ){
			$this->secret = $config['secret'];
		} 
	}
	
	
	
	function beginLogin(){
		$facebook = $this->getApi();
		
		$scope = array();
		if( in_array('email', $attributes) ){
			$scope = 'email';
		}
		$login_url = $facebook->getLoginUrl(array('scope' => $scope, 'redirect_uri' => $this->return_url));
		
		
		return array(
    		'type' => 'redirect',
    		'url' => $login_url,
		);
	}
	function completeLogin($query){
		$facebook = $this->getApi();
		$this->user_id = $facebook->getUser();
		$this->display_identifier = null;
		
		if($this->user_id){
			$this->profile = $facebook->api('/me');			
			$this->display_identifier = $this->profile['link'];
			return true;
		} else {
			return false;
		}
	}
	function getProfile(){
		if( ! $this->profile){
			$facebook = $this->getApi();
			$this->profile = $facebook->api('/me');	
		}
		return $this->profile;
	}
	
	private function getApi(){
		$config = array(
		  'appId'  => $this->app_id,
		  'secret' => $this->secret,
		);

		$facebook = new \Facebook($config);
		return $facebook;
	}
}

