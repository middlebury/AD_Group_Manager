<?php

/**
 * Answer a URL to another action with optional params
 * 
 * @param string $action
 * @param optional array $params
 * @return void
 * @access public
 * @since 8/27/09
 */
function getUrl ($action, array $params = array()) {
	$params['action'] = $action;
	return MYURL.'?'.http_build_query($params);
}


/**
 * Forward the user to another action with optional params
 * 
 * @param string $action
 * @param optional array $params
 * @return void
 * @access public
 * @since 8/27/09
 */
function forward ($action, array $params = array()) {
	header("Location: ".getUrl($action, $params));
	exit;
}

/**
 * Answer an array of the parts of a DN with the domain stripped off.
 *
 * @param string $dn
 * @return array
 * @access public
 * @since 8/28/09
 */
function dnToLevels ($dn) {
	$levels = ldap_explode_dn($dn, 1);
	unset($levels['count']);
	array_pop($levels);
	array_pop($levels);
	$levels = array_reverse($levels);
	return $levels;
}