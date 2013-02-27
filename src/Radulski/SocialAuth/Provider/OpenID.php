<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';
require_once 'Auth/OpenID.php';
require_once 'Auth/OpenID/AX.php';
require_once 'Auth/OpenID/Consumer.php';
require_once 'Auth/OpenID/PAPE.php';
require_once 'Auth/OpenID/SReg.php';

/**
 * Install: php-openid
 */
class OpenID extends Base {

	protected $storage_type;
	protected $storage_config;
	
	protected $login_attributes;
	protected $user_url;
	protected $profile;
	
	
	function config($config){
		if( $this->login_attributes === null ){
			$this->login_attributes = array();
			$this->login_attributes[] = 'email';
			$this->login_attributes[] = 'fullname';
			$this->login_attributes[] = 'nickname';
			$this->login_attributes[] = 'firstname';
			$this->login_attributes[] = 'lastname';
		}
		
		if( isset($config['user_url']) ){
			$this->user_url = $config['user_url'];
		}
		if( isset($config['storage_type']) ){
			if( $config['storage_type'] == 'file' ){
				$this->setFileStorage($config['storage_path']);
			}
		}
		
	}
	
	public function setUserUrl($url){
		$this->user_url = $url;
	}
	
	public function setDatabaseStorage($type, $config){
		$this->storage_type = 'database';
		$this->storage_config = $config;
	}
	public function setFileStorage($path){
		$this->storage_type = 'file';
		$this->storage_config = array('path' => $path);	
	}
	
	
	function beginLogin(){
		$consumer = $this->getOpenidConsumer();
		$auth_request = $consumer->begin($this->user_url);
		
		// add
		$ax_request = new \Auth_OpenID_AX_FetchRequest();  
		if( in_array('email', $this->login_attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email', 1, true, 'email') );
		}
		if( in_array('fullname', $this->login_attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson', 1, true, 'fullname') );
		}
		if( in_array('nickname', $this->login_attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, true, 'nickname') );
		}
		if( in_array('firstname', $this->login_attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first', 1, true, 'firstname') );
		}
		if( in_array('lastname', $this->login_attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last', 1, true, 'lastname') );   
		}
		
		
		$auth_request->addExtension($ax_request);
		
		 
		if( $auth_request->shouldSendRedirect() ){
			$redirect_url = $auth_request->redirectURL($this->base_url, $this->return_url);
					
			if (\Auth_OpenID::isFailure($redirect_url)) {
				throw new \Exception("Canned create redirect URL");
			}
			
			// done
        	return array(
        		'type' => 'redirect',
        		'url' => $redirect_url,
			);
		} else {
			$form_html = $auth_request->htmlMarkup($this->base_url, $this->return_url);
			//$form_html = " <!-- $form_html -->";

	        // Display an error if the form markup couldn't be generated;
	        // otherwise, render the HTML.
	        if (\Auth_OpenID::isFailure($form_html)) {
	            throw new \Exception("Failed to generate OpenID login form.");
	        }
			
			return array(
        		'type' => 'html',
        		'html' => $form_html,
			);
		}
	}
	function completeLogin($query){
		$consumer = $this->getOpenidConsumer();
		
		// parse request
		$query_map = \Auth_OpenID::params_from_string( $query );
		
		// complete authentication
		$response = $consumer->complete($this->return_url, $query_map);
		
		if ($response->status == Auth_OpenID_CANCEL) {
	        // This means the authentication was cancelled.
	        return false;
	    } else if ($response->status == Auth_OpenID_FAILURE) {
	        // Authentication failed; display the error message.
	        // This means the authentication was cancelled.
	        throw new \Exception($response->message);
	    } else if ($response->status == Auth_OpenID_SUCCESS) {
	        // This means the authentication succeeded; 
	        
	        $sreg_resp = \Auth_OpenID_SRegResponse::fromSuccessResponse($response);
        	$sreg = $sreg_resp->contents();
			
			$ax_resp = \Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response, false);
			$this->user_id = $response->getDisplayIdentifier();
			$this->display_identifier = $response->getDisplayIdentifier();
			
			
			
			// get info about the user
			$profile = array();
			$profile = array_merge($sreg, $profile);
			if($ax_resp){
				$profile['email'] = $ax_resp->getSingle('http://axschema.org/contact/email');
				$profile['fullname'] = $ax_resp->getSingle('http://axschema.org/namePerson');
				$profile['nickname'] = $ax_resp->getSingle('http://axschema.org/namePerson/friendly');
				$profile['firstname'] = $ax_resp->getSingle('http://axschema.org/namePerson/first');
				$profile['lastname'] = $ax_resp->getSingle('http://axschema.org/namePerson/last');
			}
			
			
			$this->profile = $profile;
			return true;
		}
	}
	
	public function getProfile(){
		return $this->profile;
	}
	
	private function getOpenidConsumer(){
		if($this->storage_type == 'file'){
			require_once 'Auth/OpenID/FileStore.php';
			
			
			$store = new \Auth_OpenID_FileStore($this->storage_config['path']);
		    $consumer = new \Auth_OpenID_Consumer($store);
			//new \GApps_OpenID_Discovery($consumer);
			return $consumer;
		} else {
			throw new \Exception("Not implemented");
			
		}
	}
}

