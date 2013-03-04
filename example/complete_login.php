<?php

include_once __DIR__ . '/util.php';
$provider_name = $_GET['provider'];
$identifier = $_GET['identifier'];

$return_url =  SocialAuth_Util::getRootUrl() . 'complete_login.php?provider='.urlencode($provider_name);

$provider = SocialAuth_Util::getProvider($provider_name);
$provider->setReturnUrl( $return_url );
try{
	$ok = $provider->completeLogin($_SERVER['QUERY_STRING']);
	if( ! $ok){
		die("Login Failed");
	}
}catch(\Exception $ex){
	echo $ex->getMessage();
	die();
}
@session_start();

$_SESSION['provider'] = $provider_name;
$_SESSION['user_id'] = $provider->getUserId();

SocialAuth_Util::redirect('profile.php');
