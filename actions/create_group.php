<?php
/**
 * Create a new group.
 *
 * @since 8/28/09
 * @package
 *
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

if (!isset($_POST['container_dn']) || !$_POST['container_dn'])
	throw new InvalidArgumentException("No container_dn passed");

$containerDn = base64_decode_fix($_POST['container_dn'], true);
if (!$containerDn)
	throw new InvalidArgumentException("Invalid container_dn passed");

if (!isset($_POST['new_group_name']) || !$_POST['new_group_name'])
	throw new InvalidArgumentException("No new_group_name passed");

$newGroupName = $_POST['new_group_name'];
if (!preg_match('/^[a-z0-9][a-z0-9\s.,_\'&-]+$/i', $newGroupName))
	throw new InvalidArgumentException("Invalid new_group_name passed");

$groupId = "CN=".$ldap->escapeDnValue($newGroupName).",".$containerDn;

// Verify that the current user really can manage the group.
try {
	$groups = $ldap->read('(objectclass=group)', $groupId, array('managedby', 'member'));
	if (count($groups))
		throw new Exception("A group with the name $newGroupName already exists.");
} catch (LdapException $e) {
}

$entry['cn'][0] = $newGroupName;
$entry['objectclass'][0] = 'top';
$entry['objectclass'][1] = 'group';
$entry['grouptype']="-2147483640";
$entry['managedby'][0] = $_SESSION['user_dn'];
// $entry["sAMAccountName"] = $newGroupName;

// create the new group
$ldap->add($groupId, $entry);

// Print out its HTML for insertion into the document
while(ob_get_level())
	ob_end_clean();
header('Content-Type: text/html');
$entry['dn'] = $groupId;
printGroupHtml($ldap, $entry);
exit;
