<?php

include_once __DIR__ . '/util.php';

try{


	$provider_name = @$_GET['provider'];
	$identifier = @$_GET['identifier'];
	
	if($_GET['login'] == 'google'){
		$provider_name = 'openid';
		$identifier = 'https://www.google.com/accounts/o8/id';
	} elseif($_GET['login'] == 'yahoo'){
		$provider_name = 'openid';
		$identifier = 'https://me.yahoo.com/';
	} elseif($_GET['login'] == 'facebook'){
		$provider_name = 'facebook';
	}
	
	$return_url =  SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.urlencode($provider_name);
	
	$provider = SocialAuth_Util::getProvider($provider_name);
	$provider->setIdentifier($identifier);
	$provider->setReturnUrl( $return_url);

	$info = $provider->beginLogin(array('nickname', 'fullname', 'firstname', 'lastname', 'email'));
	
	if($info['type'] == 'redirect'){
		SocialAuth_Util::redirect($info['url']);
	} elseif($info['type'] == 'html'){
		echo $info['html'];	
	} else {
		echo "I don't know how to begin login";
	}
} catch(Exception $ex){
	echo  $ex->getMessage();
	die();
}


