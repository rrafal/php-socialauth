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
	
	protected $user_url;
	
	
	function config($config){
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
	
	
	function beginLogin(array $attributes = array()){
		$consumer = $this->getOpenidConsumer();
		$auth_request = $consumer->begin($this->user_url);
		
		// add
		$ax_request = new \Auth_OpenID_AX_FetchRequest();  
		if( in_array('email', $attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email', 1, true, 'email') );
		}
		if( in_array('fullname', $attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson', 1, true, 'fullname') );
		}
		if( in_array('nickname', $attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, true, 'nickname') );
		}
		if( in_array('firstname', $attributes) ){
			$ax_request->add( \Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first', 1, true, 'firstname') );
		}
		if( in_array('lastname', $attributes) ){
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
	        return array(
	        	'status' => 'cancel',
	        );
	    } else if ($response->status == Auth_OpenID_FAILURE) {
	        // Authentication failed; display the error message.
	        // This means the authentication was cancelled.
	        return array(
	        	'status' => 'failure',
	        	'message' => $response->message 
	        );			
	    } else if ($response->status == Auth_OpenID_SUCCESS) {
	        // This means the authentication succeeded; 
	        
	        $sreg_resp = \Auth_OpenID_SRegResponse::fromSuccessResponse($response);
        	$sreg = $sreg_resp->contents();
			
			$ax_resp = \Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response, false);
			$this->user_id = $response->getDisplayIdentifier();
			$this->display_identifier = $response->getDisplayIdentifier();
			
			$info = array(
				'status' => 'success',
				'id' => $this->user_id,
			);
			
			// get info about the user
			$info = array_merge($sreg, $info);
			if($ax_resp){
				$info['email'] = $ax_resp->getSingle('http://axschema.org/contact/email');
				$info['fullname'] = $ax_resp->getSingle('http://axschema.org/namePerson');
				$info['nickname'] = $ax_resp->getSingle('http://axschema.org/namePerson/friendly');
				$info['firstname'] = $ax_resp->getSingle('http://axschema.org/namePerson/first');
				$info['lastname'] = $ax_resp->getSingle('http://axschema.org/namePerson/last');
			}
			
			return $info;		
		}
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

