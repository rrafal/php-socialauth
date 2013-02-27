<?php

namespace Radulski\SocialAuth\Provider;

require_once __DIR__.'/Base.php';

if( ! class_exists('OAuthRequester') ){
	require_once __DIR__ . '/oauth-php/library/OAuthRequester.php';
	require_once __DIR__ . '/oauth-php/library/OAuthStore.php';
}


/**
 * Authenticates user against Twitter account.
 *
 * Requires "consumer_key" and "consumer_secret"
 * @see https://dev.twitter.com/apps/new
 */
class Twitter extends Base {
	private $server_url = 'https://api.twitter.com';
	private $access_token_url = 'https://api.twitter.com/oauth/access_token';
	private $authenticate_url = 'https://api.twitter.com/oauth/authenticate';
	private $request_token_url = 'https://api.twitter.com/oauth/request_token';
	private $store_name = 'Session';
	private $consumer_key;
	private $consumer_secret;
	
	function config($config){
		if( isset($config['store']) ){
			$this->store_name = $config['store'];
		}
		if( isset($config['access_token_url']) ){
			$this->access_token_url = $config['access_token_url'];
		}
		if( isset($config['authenticate_url']) ){
			$this->authenticate_url = $config['authenticate_url'];
		}
		if( isset($config['request_token_url']) ){
			$this->request_token_url = $config['request_token_url'];
		}
		if( isset($config['consumer_key']) ){
			$this->consumer_key = $config['consumer_key'];
		}
		if( isset($config['consumer_secret']) ){
			$this->consumer_secret = $config['consumer_secret'];
		}

		$options = array();
		$options['server_uri'] = $this->server_url;
		$options['access_token_uri'] = $this->access_token_url;
		$options['authorize_uri'] = $this->authenticate_url;
		$options['request_token_uri'] = $this->request_token_url;
		$options['consumer_key'] = $this->consumer_key;
		$options['consumer_secret'] = $this->consumer_secret;

		\OAuthStore::instance($this->store_name, $options);
	}
	
	
	
	function beginLogin(array $attributes = array()){
		$params = array('oauth_callback' => $this->return_url);
		$info = \OAuthRequester::requestRequestToken ( $this->consumer_key, 0, $params );

		$url = $info['authorize_uri'] . '?oauth_token='.urlencode($info['token']);
		
		return array(
    		'type' => 'redirect',
    		'url' => $url,
		);
		
		/*$params = array('oauth_callback' => $this->return_url);
		$request = new TwitterRequester($this->request_token_url, 'POST', $params);
		$result = $request->doRequest();
		
		$info = array();
		parse_str($result['body'], $info);
		
		$url = $this->authenticate_url . '?oauth_token='.urlencode($info['oauth_token']);

		
		return array(hicago,
    		'type' => 'redirect',
    		'url' => $url,
		);*/
	}
	function completeLogin($query){
		// get access token
		$query_options = array();
		parse_str($query, $query_options);
			
		$params = array(
			'oauth_verifier' => $query_options['oauth_verifier'], 
			'oauth_token' => $query_options['oauth_token'],
			);
		$request = new TwitterRequester($this->access_token_url, 'POST', $params);
		$result = $request->doRequest();
		$access_token = array();
		parse_str($result['body'], $access_token);
		
		// save access token
		$store = \OAuthStore::instance();
		$store->addServerToken($this->consumer_key, 'access', $access_token['oauth_token'], $access_token['oauth_token_secret'], null);
		
		
		// get account info
		$params = array('user_id' => $access_token['user_id'], 'screen_name' => $access_token['screen_name']);
		$request = new TwitterRequester('http://api.twitter.com/1.1/users/show.json', 'GET', $params);
		$result = $request->doRequest();


		$info = json_decode($result['body']);
		return $info;
	}
}

class TwitterRequester extends \OAuthRequester {
	/** oAuth authorization params must be ordered alphabetically **/
	function getAuthorizationHeader ()
	{
		if (!$this->signed)
		{
			$this->sign($this->usr_id);
		}
		
		$oauth_params = array();
		foreach($this->param as $name => $value){
			if (strncmp($name, 'oauth_', 6) == 0 || strncmp($name, 'xoauth_', 7) == 0)
          	{
				$oauth_params[ $name ] = $value;
			}
		}
		ksort($oauth_params);
		
		$h   = array();
		//$h[] = 'realm=""';
		foreach ($oauth_params as $name => $value)
		{
			$h[] = $name.'="'.$value.'"';
		}
		$hs = 'Authorization: OAuth '.implode(', ', $h);

		return $hs;
	}
}

