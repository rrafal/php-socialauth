<?php

include_once __DIR__ . '/util.php';

try{
	$provider_name = @$_GET['provider'];
	$user_url = @$_GET['user_url'];
	$return_url =  SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.urlencode($provider_name);
	
	$provider = SocialAuth_Util::getProvider($provider_name);
	$provider->setReturnUrl( $return_url);
	if($provider_name == 'openid'){
		$provider->setUserUrl($user_url);
	}

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


