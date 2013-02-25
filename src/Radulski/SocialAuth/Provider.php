<?php

namespace Radulski\SocialAuth;

interface Provider {

	function config($config);

	/**
	 * Server's URL
	 */
	function setBaseUrl($url);
	
	/**
	 * URL for redirect after login.
	 */
	function setReturnUrl($url);
	
	/**
	 * Identifier used during login.
	 */
	function setIdentifier($id);

	
	/**
	 * Begins login process.
	 * 
	 *
	 * Return array like:
	 * - array('type' => 'redirect', 'url' => '...')
	 * - array('type' => 'html', 'html' => '...')
	 *
	 * @param $attributes Attributes to request during login.
	 * 
	 */
	function beginLogin(array $attributes);
	
	/**
	 * Completes login process
	 * Returns array with key 'status' and other information.
	 * Status may be: failure, cancel, success
	 */
	function completeLogin($query);
}

