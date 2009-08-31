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

/**
 * Print an HTML block for a group, respecting permissions.
 *
 * @param LdapConnector $ldap
 * @param array $group The group result from an LDAP search.
 * @return void
 * @since 8/31/09
 */
function printGroupHtml (LdapConnector $ldap, array $group) {
	$showControls = ($group['managedby'][0] == $_SESSION['user']);

	$levels = dnToLevels($group['dn']);

	print "\n<div class='group'>";

	print "\n\t<fieldset class='members'>\n\t\t<legend>".implode(' / ', $levels)."</legend>";
	print "\n\t\t<ul>";

	if (isset($group['member']) && is_array($group['member'])) {
		sort ($group['member']);
		foreach ($group['member'] as $memberDN) {
			$members = $ldap->read('(objectclass=*)', $memberDN, array('givenName', 'sn', 'mail'));
			$member = $members[0];

			print "\n\t\t<li>".$member['givenname'][0]." ".$member['sn'][0]." (".$member['mail'][0].") ";
			if ($showControls) {
				print "\n\t\t\t<input type='hidden' class='group_id' value='".base64_encode($group['dn'])."'/>";
				print "\n\t\t\t<input type='hidden' class='member_id' value='".base64_encode($memberDN)."'/>";
				print "<button class='remove_button'>Remove</button>";
			}
			print "</li>";
		}
	}
	print "\n\t\t</ul>";

	if ($showControls) {
		print "\n\t\t<div class='add_member_controls'>";
		print "\n\t\t<input type='text' class='new_member' size='50'/>";
		print "\n\t\t\t<input type='hidden' class='group_id' value='".base64_encode($group['dn'])."'/>";
		print "\n\t\t<button class='add_button'>Add</button>";
		print "\n\t\t</div>";

		print "\n\t\t<div class='delete_controls'>";
		print "\n\t\t\t<input type='hidden' class='group_id' value='".base64_encode($group['dn'])."'/>";
		print "\n\t\t<button class='delete_button'>Delete Group</button>";
		print "\n\t\t</div>";
	}

	print "\n\t</fieldset>";


	print "\n</div>";
}