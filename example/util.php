<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../src/Radulski/SocialAuth/Manager.php';

class SocialAuth_Util {

	static function redirect($url){
		header( "Location: $url" );
		die();
	}
	
	static function getRootUrl(){
		$path = dirname($_SERVER['REQUEST_URI']);
		return 'http://' . $_SERVER['HTTP_HOST'] . $path . '/';
	}
	static function getProvider($name){
		$config = array(
			'facebook' => array('app_id' => FACEBOOK_APP_ID, 'secret' => FACEBOOK_SECRET),
			'openid' => array('storage_type' => 'file', 'storage_path' => '/tmp/social_auth/'),
		);
		$manager = new Radulski\SocialAuth\Manager();
		$manager->setBaseUrl( self::getRootUrl() );
		$manager->configProviders($config);
		
		$provider = $manager->getProvider($name);
		return $provider;
	}
}
