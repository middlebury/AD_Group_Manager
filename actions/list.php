<ul class='menu'>
	<li>My Web Groups</li>
	<li><a href="<?php echo getUrl('list_web'); ?>">All Web Groups</a></li>
	<li><a href="<?php echo getUrl('list_all'); ?>">All Groups</a></li>
</ul>

<div id='groups'>

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

// Sort the groups
$sortKeys = array();
foreach ($groups as $group)
	$sortKeys[] = implode(' / ', dnToLevels($group['dn']));
array_multisort($sortKeys, $groups);

// PRint the groups
foreach ($groups as $group) {
	printGroupHtml($ldap, $group);
}

?>

</div>

<form action="<?php echo getUrl('create_group'); ?>" method="post" id="create_group_form">
	<p>Create a new group in
		<select name="container_dn" id="new_group_container_dn">
<?php
foreach ($ldapConfig['WritableGroupContainers'] as $dn) {
	print "\n\t\t\t<option value=\"".base64_encode($dn)."\">".implode(" / ", dnToLevels($dn))."</option>";
}
?>

		</select>
		 named
		<input type="text" name="new_group_name" id='new_group_name'/>
		<input type="submit" value="Create"/>
	</p>
</form>