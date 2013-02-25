<?php

include_once __DIR__ . '/util.php';

try{

	if( empty($_GET['login']) ){
		SocialAuth_Util::redirect('index.php');
	} 
	
	$provider = @$_GET['provider'];
	$identifier = @$_GET['identifier'];
	
	if($_GET['login'] == 'google'){
		$provider = 'openid';
		$identifier = 'https://www.google.com/accounts/o8/id';
	} elseif($_GET['login'] == 'yahoo'){
		$provider = 'openid';
		$identifier = 'https://me.yahoo.com/';
	} else {
		SocialAuth_Util::redirect('index.php');
	}
	
	
	$provider = SocialAuth_Util::getProvider($provider);
	$provider->setIdentifier($identifier);
	$provider->setReturnUrl( SocialAuth_Util::getRootUrl() . 'complete_login.php');

	$info = $provider->beginLogin(array('nickname', 'email'));
	
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


