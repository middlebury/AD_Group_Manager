<ul class='menu'>
	<li><a href="<?php echo getUrl('list'); ?>">My Groups</a></li>
	<li>All Web Groups</li>
	<li><a href="<?php echo getUrl('list_all'); ?>">All Groups</a></li>
</ul>

<div id='groups'>

<?php
$groups = array();
foreach ($ldapConfig['WritableGroupContainers'] as $baseDN) {
	$query = '(objectClass=group)';
	$groups = array_merge($groups, $ldap->search($query, $baseDN, array('cn', 'managedby', 'member')));
}
$groups = array_values($groups);

// Sort the groups
$sortKeys = array();
foreach ($groups as $group)
	$sortKeys[] = implode(' / ', dnToLevels($group['dn']));
array_multisort($sortKeys, $groups);

// Print the groups
foreach ($groups as $group) {
	printGroupHtml($ldap, $group);
}

?>

</div>
