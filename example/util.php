<?php

require_once __DIR__ . '/../include.php';
require_once __DIR__ . '/../src/Radulski/SocialAuth/Manager.php';

class SocialAuth_Util {

	static function redirect($url){
		header( "Location: $url" );
		die();
	}
	
	static function getRootUrl(){
		$path = $_SERVER['REQUEST_URI'];
		$path = preg_replace('/[?].*/', '', $path);
		$path = dirname($path);

		return 'http://' . $_SERVER['HTTP_HOST'] . $path . '/';
	}
	static function getProvider($name){
		include __DIR__ . '/config.php';
		
		$manager = new Radulski\SocialAuth\Manager();
		$manager->setBaseUrl( self::getRootUrl() );
		$manager->configProviders($config);
		
		$provider = $manager->getProvider($name);
		return $provider;
	}
}
