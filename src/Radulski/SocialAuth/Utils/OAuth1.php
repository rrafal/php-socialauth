<?php

namespace Radulski\SocialAuth\Utils;



	
/**
 * Authenticates user against Twitter account.
 *
 * Requires "consumer_key" and "consumer_secret"
 * @see https://dev.twitter.com/apps/new
 * @see https://dev.twitter.com/docs/auth/implementing-sign-twitter
 */
class OAuth1  {
	
	private $consumer_key;
	private $consumer_secret;
	
	private $access_token;
	private $access_token_secret;
	
	// used for testing
	private $nonce;
	private $timestamp;
	

	function setConsumer($key, $secret){
		$this->consumer_key = $key;
		$this->consumer_secret = $secret;
	}

	function setAccessToken($token, $secret){
		$this->access_token = $token;
		$this->access_token_secret = $secret;
	}
	function setNonce($nonce){
		$this->nonce = $nonce;
	}
	function setTimestamp($ts){
		$this->timestamp = $ts;
	}
	
	function fetchRequestToken($request_token_url, $return_url){
		// clear
		$this->access_token = null;
		$this->access_token_secret = null;
		
		// make request
		$params = array('oauth_callback' => $return_url);
		$method = 'POST';
	
		$headers = array();
		$headers[] = $this->getHeader($request_token_url, $method, $params);


		$response = $this->makeHttpRequest($request_token_url, $method, array(), $headers);

		// parse response
		$info = array();
		parse_str($response, $info);        
		return $info;
	}
	
	function fetchAccessToken($access_token_url, $token_verifier){
		// make request
		$params = array(
			'oauth_verifier' => $token_verifier,
			);
		$method = 'POST';
	
		$headers = array();
		$headers[] = $this->getHeader($access_token_url, $method, $params);

		$response = $this->makeHttpRequest($request_token_url, $method, $params, $headers);

		// parse response
		$info = array();
		parse_str($response, $info);        
		return $info;
	}
	/**
	 * Creates oauth header.
	 */
	function getHeader( $method, $url, $params){
		$auth_params = $this->getAuthParams($method, $url, $params);
		
		$lines = array();
		foreach($auth_params as $k => $v){
			$lines[] = sprintf('%s="%s"', $this->urlencode($k), $this->urlencode($v) );
		}

		return "Authorization: OAuth ".implode(", ", $lines);
	}
	
	/**
	 * Returns all oauth authorization parameters that should be included in request.
	 */
	function getAuthParams( $method, $url, $params){
		$auth_params = array();
		$auth_params['oauth_consumer_key'] = $this->consumer_key;
		$auth_params['oauth_nonce'] = $this->getNonce();
		$auth_params['oauth_signature_method'] = 'HMAC-SHA1';
		$auth_params['oauth_timestamp'] = $this->getTimestamp();
		$auth_params['oauth_version'] = '1.0';
		
		if( isset($params['oauth_callback']) ){
			$auth_params['oauth_callback'] = $params['oauth_callback'];
		}
		
		if($this->access_token){
			$auth_params['oauth_token'] = $this->access_token;
		}
		
		$all_params = array_merge($params, $auth_params);
		$auth_params['oauth_signature'] = $this->calculateDataSignature($method, $url, $all_params);
		
		ksort($auth_params);
		return $auth_params;
	}
	/**
	 * Calculates signature of privded parameters.
	 * The parameters should include:
	 * - oauth_consumer_key
	 * - oauth_nonce
	 * - oauth_signature_method
	 * - oauth_timestamp
	 * - oauth_version
	 */
	public function calculateDataSignature( $method, $url, $params){
		// encode data
		$lines = array();
		$lines[] = strtoupper($method);
		$lines[] = $this->urlencode( $url );
		$lines[] = $this->urlencode( $this->serializeParams($params) );
		
		
		$plain = implode('&', $lines);


		// get signature key
		$key = $this->urlencode($this->consumer_secret) . '&';
		
		if($this->access_token_secret){
			$key .= $this->urlencode($this->access_token_secret);
		}

		// generate signature
		$signature = base64_encode(hash_hmac('sha1', $plain, $key, TRUE ));
		return $signature;
	}
	private function urlencode($value){
		return str_replace('%7E', '~', rawurlencode($value));
	}
	
	private function serializeParams($params)  {
		$normalized_params = array();
		$return_array = array();

		foreach ( $params as $k => $v) {
			$normalized_params[ $this->urlencode($k)] = $this->urlencode($v);	
        }

		ksort($normalized_params);

		foreach($normalized_params as $key=>$val) 
		{
			array_push($return_array, $key .'='. $val);
		}

		return join("&", $return_array);
    }
    
    private function getNonce(){
    	if($this->nonce){
    		return $this->nonce;
    	} else {
    		return md5(rand());
    	}
    }
    private function getTimestamp(){
    	if($this->timestamp){
    		return $this->timestamp;
    	} else {
    		return time();
    	}
    }
    
    protected function makeHttpRequest($url, $method = 'GET', $params = array(), $headers = array()){
		if( strtolower($method) == 'get' && $params){
			$url = $this->buildUrl($url, null, $params);
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_VERBOSE, 0); 
		curl_setopt($curl, CURLOPT_HEADER, 0);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		if( strtolower($method) == 'post' ){
			$post_data = http_build_query($params, '', '&');
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); 
		}
		if( $headers ){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
		}
		
		$return = curl_exec($curl); 
		curl_close($curl); 
		return $return; 
	}
}


