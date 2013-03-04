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
		
		$info = $this->provider_map[$name];
		$ref = new \ReflectionClass($info['class']);
		
		$provider = $ref->newInstanceArgs();
		$provider->setBaseUrl($this->base_url);
		$provider->config($info['config']);
	
		return $provider;
	}
	
	public function registerProvider($name, $class, $config){

		$this->provider_map[$name] = array('class'=>$class, 'config'=>$config);
	}
	public function configProviders($config){
		$defaults = array(
			'facebook' => array('class' => 'Radulski\SocialAuth\Provider\Facebook', 'file' => __DIR__ .'/Provider/Facebook.php'),
			'google' =>  array('class' => 'Radulski\SocialAuth\Provider\Google', 'file' => __DIR__ .'/Provider/Google.php'),
			'openid' =>  array('class' => 'Radulski\SocialAuth\Provider\OpenID', 'file' => __DIR__ .'/Provider/OpenID.php'),
			'twitter' =>  array('class' => 'Radulski\SocialAuth\Provider\Twitter', 'file' => __DIR__ .'/Provider/Twitter.php'),
		);
		
		foreach($config as $name => $provider_config){
			$file = null;
			if( isset($provider_config['class']) ){
				$class = $provider_config['class'];
			} elseif( isset($defaults[ $name ]) ){
				$class = $defaults[$name]['class'];
				$file = $defaults[$name]['file'];
			} else {
				throw new \Exception("Provider not known: $name");
			}
			
			if($file){
				include_once $file;
			}
			$this->registerProvider($name, $class, $provider_config);
		}
	}	
}

