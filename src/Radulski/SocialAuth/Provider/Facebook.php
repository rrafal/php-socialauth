<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';

if( ! class_exists('Facebook') ){
	require_once __DIR__ . '/FacebookSDK/facebook.php';
}


/**
 */
class Facebook extends Base {
	private $app_id;
	private $secret;
	
	function config($config){
		if( isset($config['app_id']) ){
			$this->app_id = $config['app_id'];
		}
		
		if( isset($config['secret']) ){
			$this->secret = $config['secret'];
		} 
	}
	
	
	
	function beginLogin(array $attributes = array()){
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
			$info = $facebook->api('/me');
			$info['status'] = 'success';
			$info['id'] = $this->user_id;
			
			$this->display_identifier = $info['link'];
		} else {
			$info = array();
			$info['status'] = 'cancel';
		}
		return $info;
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

