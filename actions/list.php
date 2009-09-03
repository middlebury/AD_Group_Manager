<ul class='menu'>
	<li>My Groups</li>
	<li><a href="<?php echo getUrl('list_web'); ?>">All Web Groups</a></li>
	<li><a href="<?php echo getUrl('list_all'); ?>">All Groups</a></li>
</ul>

<?php
$groups = array();
foreach ($ldapConfig['WritableGroupContainers'] as $baseDN) {
	$query = '(objectClass=group)';
	$groups = array_merge($groups, $ldap->search($query, $baseDN, array('cn', 'managedby', 'member')));
}

// Filter on ones managed by the current user
foreach ($groups as $key => $group) {
	if ($group['managedby'][0] != $_SESSION['user'])
		unset($groups[$key]);
}
$groups = array_values($groups);

foreach ($groups as $group) {
	$levels = ldap_explode_dn($group['dn'], 1);
	unset($levels['count']);
	array_pop($levels);
	array_pop($levels);
	$levels = array_reverse($levels);
	
	print "\n<div class='group'>";
// 	print "\n\t<h2>".$group['cn'][0]."</h2>";
// 	print "\n\t<h2>".implode('/', $levels)."</h2>";
	
// 	print "\n\t<fieldset class='location'>\n\t\t<legend>Location</legend>";
// 	foreach ($levels as $level)
// 		print "\n\t<ul>\n\t<li>".$level." <br/>";
// 	foreach ($levels as $level)
// 		print "\n\t</li>\n\t</ul>";
// 	print "\n\t</fieldset>";
	
	print "\n\t<fieldset class='members'>\n\t\t<legend>".implode(' / ', $levels)."</legend>";
	print "\n\t\t<ul>";
	sort ($group['member']);
	foreach ($group['member'] as $memberDN) {
		$members = $ldap->read('(objectclass=*)', $memberDN, array('givenName', 'sn', 'mail'));
		$member = $members[0];
		
		print "\n\t\t<li>".$member['givenname'][0]." ".$member['sn'][0]." (".$member['mail'][0].") ";
		print "\n\t\t\t<input type='hidden' class='group_id' value='".base64_encode($group['dn'])."'/>";
		print "\n\t\t\t<input type='hidden' class='member_id' value='".base64_encode($memberDN)."'/>";
		print "<button class='remove_button'>Remove</button>";
		print "</li>";
	}
	print "\n\t\t</ul>";
	print "\n\t\t<input type='text' class='new_member' size='50'/>";
	print "\n\t\t\t<input type='hidden' class='group_id' value='".base64_encode($group['dn'])."'/>";
	print "\n\t\t<button class='add_button'>Add</button>";
	
	print "\n\t</fieldset>";
	
	
	print "\n</div>";
}