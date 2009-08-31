<ul class='menu'>
	<li><a href="<?php echo getUrl('list'); ?>">My Groups</a></li>
	<li><a href="<?php echo getUrl('list_web'); ?>">All Web Groups</a></li>
	<li>All Groups</li>
</ul>

<ul id='group_tree'>

<?php
if (isset($_GET['current']) && $_GET['current'])
	$current = base64_decode($_GET['current'], true);

if (!isset($current) || ! $current)
	$current = $ldapConfig['BaseDN'];

$levels = ldap_explode_dn($current, 0);
unset($levels['count']);
$open = array();
while (count($levels) > 1) {
	$open[] = implode(',', $levels);
	array_shift($levels);
}

printHierarchy($ldap, $ldapConfig['BaseDN'], $open);

?>

</ul>


<div id='groups'>

<?php
// $groups = array();
// foreach ($ldapConfig['WritableGroupContainers'] as $baseDN) {
// 	$query = '(objectClass=group)';
// 	$groups = array_merge($groups, $ldap->search($query, $baseDN, array('cn', 'managedby', 'member')));
// }
// $groups = array_values($groups);
//
// // Sort the groups
// $sortKeys = array();
// foreach ($groups as $group)
// 	$sortKeys[] = implode(' / ', dnToLevels($group['dn']));
// array_multisort($sortKeys, $groups);
//
// // Print the groups
// foreach ($groups as $group) {
// 	printGroupHtml($ldap, $group);
// }

?>

</div>
