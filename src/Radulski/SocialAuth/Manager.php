<?php

namespace RadulskiLib\SocialAuth;

class Manager {
	/**
	 * @return Base
	 */
	public static function getDriver($identifier){
		switch($identifier){
			case 'facebook':
				throw new \Exception("Not implemented");
			case 'twitter':
				throw new \Exception("Not implemented");
			case 'google':
				return new OpenID('https://www.google.com/accounts/o8/id');
			case 'yahoo':
				return new OpenID('https://www.google.com/accounts/o8/id');
			default:
				return new OpenID($identifier);
				
		}
	}
}
	