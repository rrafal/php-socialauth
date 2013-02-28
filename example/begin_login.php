<?php

include_once __DIR__ . '/util.php';

try{


	$provider_name = null;
	$user_url = null;
	
	if($_GET['login'] == 'google'){
		$provider_name = 'google';
	} elseif($_GET['login'] == 'yahoo'){
		$provider_name = 'openid';
		$user_url = 'https://me.yahoo.com/';
	} elseif($_GET['login'] == 'facebook'){
		$provider_name = 'facebook';
	} elseif($_GET['login'] == 'twitter'){
		$provider_name = 'twitter';
	}
	
	$return_url =  SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.urlencode($provider_name);
	
	$provider = SocialAuth_Util::getProvider($provider_name);
	if($provider_name == 'openid'){
		$provider->setUserUrl($user_url);
	}
	$provider->setReturnUrl( $return_url);

	$info = $provider->beginLogin();
	
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


