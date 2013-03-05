<?php

namespace Radulski\SocialAuth\Utils;

require_once 'Auth/OpenID/Interface.php';

class OpenIDSessionStore extends \Auth_OpenID_OpenIDStore {
	private $session_base_key;
	
	public function __construct(){
		@session_start();
		$this->session_base_key = 'Radulski\SocialAuth\Utils\OpenIDSessionStore';
	}
	
	function storeAssociation($server_url, $association){
        $association_key = $this->associationKey($server_url, $association->handle);
        $server_key = $this->associationServerKey($server_url);
        
        $server_associations = $this->getValue($server_key);
        
        if ( ! $server_associations) {
            $server_associations = array();
        }
        $server_associations[ $association->issued ] = $association_key;
        
        $this->setValue( $server_key, $server_associations );
        $this->setValue( $association_key, $association );
	}
	
	function getAssociation($server_url, $handle = null) {
        if ($handle !== null) {
        	// get association directly
            return $this->getValue( $this->associationKey($server_url, $handle) );
        }
        
        // look up the list
        $server_key = $this->associationServerKey($server_url);
        
        $server_associations = $this->getValue($server_key);
        if (!$server_associations) {
            return null;
        }
        
        // get key of most recently issued association
        $keys = array_keys($server_associations);
        sort($keys);
        $last_key = $server_associations[array_pop($keys)];
        
        return $this->getValue($last_key);
    }

	function removeAssociation($server_url, $handle) {
        $server_key = $this->associationServerKey($server_url);
        $association_key = $this->associationKey($server_url, $handle);
        
        // remove from list of associations
        $server_associations = $this->getValue($server_key);
        
        if ( $server_associations) {
            $key = array_search( $association_key, $server_associations);
            if( $key ){
            	 unset($server_associations[$key]);
            	 $this->setValue($server_key, $server_associations);
            }
        }
        
        $this->deleteValue($association_key);

        // delete association 
        return $this->connection->delete($association_key);
    }
    
    function useNonce($server_url, $timestamp, $salt) {
        global $Auth_OpenID_SKEW;

        if ( abs($timestamp - time()) > $Auth_OpenID_SKEW ) {
            return false;
        }
        
        $nonce_key = $this->nonceKey($server_url, $salt);
        
        if( $this->getValue($nonce_key) ){
        	return false;
        } else {
        	$this->setValue($nonce_key, 1);
        	return true;
        }
    }
	
	
	
	private function setValue($key, $value){
		$_SESSION[ $this->session_base_key ][ $key ] = serialize($value);
	}

	private function getValue($key){
		if( isset( $_SESSION[ $this->session_base_key ] ) && isset($_SESSION[ $this->session_base_key ][ $key ]) ){
			$serial = $_SESSION[ $this->session_base_key ][ $key ];
			return @unserialize($serial);
		} else {
			return null;
		}
		
	}
	
	private function deleteValue($key){
		if( isset( $_SESSION[ $this->session_base_key ] ) && isset($_SESSION[ $this->session_base_key ][ $key ]) ){
			unset($_SESSION[ $this->session_base_key ][ $key ]);
		}
	}
	
		
	private function associationKey($server_url, $handle = null) {
        return 'openid_association_' . md5($server_url) . '_' . md5($handle);
    }
    
    private function associationServerKey($server_url) {
        return 'openid_association_server_' . md5($server_url);
    }

	private function nonceKey($server_url, $salt) {
        return 'openid_nonce_' . md5($server_url) . '_' . md5($salt);
    }
	
}


