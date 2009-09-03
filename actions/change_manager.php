<?php
/**
 * Change the manager of a group.
 *
 * @since 8/28/09
 * @package
 *
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

if (!isset($_POST['group_id']) || !$_POST['group_id'])
	throw new InvalidArgumentException("No group_id passed");

$groupId = base64_decode($_POST['group_id'], true);
if (!$groupId)
	throw new InvalidArgumentException("Invalid group_id passed");

$userId = base64_decode($_POST['new_manager'], true);
if (!$userId)
	throw new InvalidArgumentException("Invalid user_id passed");

// Verify that the current user really can manage the group.
$groups = $ldap->read('(objectclass=group)', $groupId, array('managedby', 'member'));
if (count($groups) != 1)
	throw new Exception("Could not find the group specified");
$group = $groups[0];
if ($group['managedby'][0] != $_SESSION['user'])
	throw new PermissionDeniedException("You are not authorized to manage this group.");

// Delete the group
$ldap->delAttribute($groupId, 'managedby', $_SESSION['user']);
$ldap->addAttribute($groupId, 'managedby', $userId);

// Print out its HTML for insertion into the document
while(ob_get_level())
	ob_end_clean();
header('Content-Type: text/html');
$group['managedby'][0] = $userId;
printGroupHtml($ldap, $group);
exit;