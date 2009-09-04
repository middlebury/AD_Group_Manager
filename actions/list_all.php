<ul id='group_tree'>

<?php
if (isset($_GET['current']) && $_GET['current'])
	$current = base64_decode_fix($_GET['current'], true);

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
