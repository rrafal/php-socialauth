<?php
if( ! class_exists('Facebook') ){
	require_once __DIR__ . '/facebook-php-sdk/src/facebook.php';
}

if( ! class_exists('OAuthRequester') ){
	require_once __DIR__ . '/oauth-php/library/OAuthRequester.php';
	require_once __DIR__ . '/oauth-php/library/OAuthStore.php';
}


