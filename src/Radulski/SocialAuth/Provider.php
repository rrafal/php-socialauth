<?php

namespace Radulski\SocialAuth;

interface Provider {

	function config($config);

	/**
	 * Sets our server's URL
	 */
	function setBaseUrl($url);
	
	/**
	 * URL for redirect after login.
	 */
	function setReturnUrl($url);
	
	/**
	 * Sets what user we should connect to.
	 */
	function loadUser($user_id);
	
	/**
	 * Returns ID for current user.
	 * Call this after login to save user's ID.
	 * It must be unique within provider.
	 */
	function getUserId();
	
	/**
	 * This is human-friendly identfier for the user.
	 * It should be globally unique.
	 */
	function getDisplayIdentifier();
	
	/**
	 * Return information about the user, such as name and address.
	 * It's best to call this right after completeLogin()
	 * @return array|null
	 */
	function getProfile();

	
	/**
	 * Begins login process.
	 * 
	 *
	 * Return array like:
	 * - array('type' => 'redirect', 'url' => '...')
	 * - array('type' => 'html', 'html' => '...')
	 *
	 * 
	 */
	function beginLogin();
	
	/**
	 * Completes login process
	 * Returns array with key 'status' and other information.
	 * Status may be: failure, cancel, success
	 */
	function completeLogin($query);
}

