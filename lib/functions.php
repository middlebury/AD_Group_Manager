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

