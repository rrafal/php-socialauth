<?php

namespace Radulski\SocialAuth;

require_once __DIR__ . '/Provider.php';

class Manager {
	private $base_url;
	private $provider_map;
	
	public function __construct(){
		$this->provider_map = array();
	}
	
	public function setBaseUrl($base_url){
		$this->base_url = $base_url;
	}

	/**
	 * @return Base
	 */
	public function getProvider($name){
		if( empty( $this->provider_map[$name] ) ){
			throw new \Exception("Provider not registered: $name");
		}
		
		// get class
		$info = $this->provider_map[$name];
		if( ! empty($info['file']) ){
			include_once $info['file'];
		}
		
		$ref = new \ReflectionClass($info['class']);
		
		$provider = $ref->newInstanceArgs();
		
		// configure it
		if($this->base_url){
			$provider->setBaseUrl($this->base_url);
		}
		if( ! empty($info['config']) ){
			$provider->config($info['config']);
		}
	
		// done
		return $provider;
	}
	
	public function registerProvider($name, $info){
		if(	empty($info['class']) ){
			throw new \Exception("Unknown class for auth provider: $name");
		}
		$this->provider_map[$name] = $info;
	}
	public function configProviders($config){
		$defaults = array(
			'aol' =>  array(
				'class' => 'Radulski\SocialAuth\Provider\OpenID', 
				'file' => __DIR__ .'/Provider/OpenID.php',
				'user_url' => 'https://openid.aol.com/',
				'storage_type' => 'session',
				),
			'facebook' => array(
				'class' => 'Radulski\SocialAuth\Provider\Facebook', 
				'file' => __DIR__ .'/Provider/Facebook.php',
				),
			'google' =>  array(
				'class' => 'Radulski\SocialAuth\Provider\Google', 
				'file' => __DIR__ .'/Provider/Google.php',
				),
			'openid' =>  array(
				'class' => 'Radulski\SocialAuth\Provider\OpenID', 
				'file' => __DIR__ .'/Provider/OpenID.php',
				'storage_type' => 'session',
				),
			'twitter' =>  array(
				'class' => 'Radulski\SocialAuth\Provider\Twitter', 
				'file' => __DIR__ .'/Provider/Twitter.php'),
			'yahoo' =>  array(
				'class' => 'Radulski\SocialAuth\Provider\OpenID', 
				'file' => __DIR__ .'/Provider/OpenID.php',
				'user_url' => 'https://me.yahoo.com/',
				'storage_type' => 'session',
				),
		);
		
		foreach($config as $name => $provider_config){
			$info = array();
			if( isset($provider_config['class']) ){
				$info['class'] = $provider_config['class'];
				$info['file'] = isset($provider_config['file']) ? $provider_config['file'] : null;
				$this->registerProvider($name, $info);
			} elseif( isset($defaults[ $name ]) ){
				$info['class'] = $defaults[$name]['class'];
				$info['file'] = $defaults[$name]['file'];
				
				$info['config'] = array_merge($defaults[ $name ], $provider_config);
				unset($info['config']['class']);
				unset($info['config']['file']);
				
				$this->registerProvider($name, $info);
			} else {
				throw new \Exception("Provider not known: $name");
			}
		}
	}	
}

