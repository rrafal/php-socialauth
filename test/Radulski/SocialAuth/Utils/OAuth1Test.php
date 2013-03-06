<?php


namespace Radulski\SocialAuth\Utils;
/**
 * @see https://dev.twitter.com/docs/auth/creating-signature
 */
class OAuth1Text extends  \PHPUnit_Framework_TestCase {
	
	function testGetOAuthParams(){
		$oauth = $this->getSignatureOAuth();
		$params['status'] = 'Hello Ladies + Gentlemen, a signed OAuth request!';
		$params['include_entities'] = 'true';
		
		$actual = $oauth->getAuthParams('POST', 'https://api.twitter.com/1/statuses/update.json', $params );


		$this->assertEquals('xvz1evFS4wEEPTGEFPHBog', $actual['oauth_consumer_key']);
		$this->assertEquals('kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg', $actual['oauth_nonce']);
		$this->assertEquals('tnnArxj06cWHq44gCs1OSKk/jLY=', $actual['oauth_signature']);
	}
	function testCalculateDataSignature(){
		$oauth = $this->getSignatureOAuth();
		$params['status'] = 'Hello Ladies + Gentlemen, a signed OAuth request!';
		$params['include_entities'] = 'true';
		$params['oauth_consumer_key'] = 'xvz1evFS4wEEPTGEFPHBog';
		$params['oauth_nonce'] = 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg';
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = '1318622958';
		$params['oauth_token'] = '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb';
		$params['oauth_version'] = '1.0';
		
		$actual = $oauth->calculateDataSignature('POST', 'https://api.twitter.com/1/statuses/update.json',  $params );
		$expected = 'tnnArxj06cWHq44gCs1OSKk/jLY=';
		$this->assertEquals($expected, $actual);
	}
	
	function testGetHeader(){
		$oauth = $this->getSignInOAuth();
		$oauth->setNonce('ea9ec8429b68d6b77cd5600adbbb0456');
		$params['oauth_callback'] = 'http://localhost/sign-in-with-twitter/';
		
		$actual = $oauth->getHeader('POST', 'https://api.twitter.com/oauth/request_token',  $params );
		$expected = 'Authorization: OAuth oauth_callback="http%3A%2F%2Flocalhost%2Fsign-in-with-twitter%2F",
              oauth_consumer_key="cChZNFj6T5R0TigYB9yd1w",
              oauth_nonce="ea9ec8429b68d6b77cd5600adbbb0456",
              oauth_signature="F1Li3tvehgcraF8DMJ7OyxO4w9Y%3D",
              oauth_signature_method="HMAC-SHA1",
              oauth_timestamp="1318467427",
              oauth_version="1.0"';
        
        // compare without spaces
        $expected = str_replace(" ", '', $expected);
        $expected = str_replace("\n", '', $expected);
        $actual = str_replace(" ", '', $actual);
        $actual = str_replace("\n", '', $actual);
        
		$this->assertEquals($expected, $actual);
	}
	private function getSignInOAuth(){
		$oauth = new OAuth1();
		$oauth->setConsumer('cChZNFj6T5R0TigYB9yd1w', 'L8qq9PZyRg6ieKGEKhZolGC0vJWLw8iEJ88DRdyOg');

		$oauth->setNonce('kYjzVBB');
		$oauth->setTimestamp('1318467427');
		return $oauth;
		return $oauth;
	}
	
	private function getSignatureOAuth(){
		$oauth = new OAuth1();
		$oauth->setConsumer('xvz1evFS4wEEPTGEFPHBog', 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw');
		$oauth->setAccessToken('370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb', 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE');
		$oauth->setNonce('kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg');
		$oauth->setTimestamp('1318622958');
		return $oauth;
	}
}


