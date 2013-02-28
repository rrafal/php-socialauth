<?php

namespace Radulski\SocialAuth;

require_once __DIR__ . '/Provider.php';
require_once __DIR__ . '/Provider/Facebook.php';
require_once __DIR__ . '/Provider/Google.php';
require_once __DIR__ . '/Provider/OpenID.php';
require_once __DIR__ . '/Provider/Twitter.php';


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
		$classes = array(
			'facebook' => 'Radulski\SocialAuth\Provider\Facebook',
			'google' => 'Radulski\SocialAuth\Provider\Google',
			'openid' => 'Radulski\SocialAuth\Provider\OpenID',
			'twitter' => 'Radulski\SocialAuth\Provider\Twitter',
		);
		
		foreach($config as $name => $provider_config){
			if( isset($provider_config['class']) ){
				$class = $provider_config['class'];
			} elseif( isset($classes[ $name ]) ){
				$class = $classes[$name];
			} else {
				throw new \Exception("Provider not known: $name");
			}
			
			$this->registerProvider($name, $class, $provider_config);
		}
	}	
}

