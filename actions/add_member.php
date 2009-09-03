<?php
/**
 * Add a new user to a group.
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
	
if (!isset($_POST['user_id']) || !$_POST['user_id'])
	throw new InvalidArgumentException("No user_id passed");

$userId = base64_decode($_POST['user_id'], true);
if (!$userId)
	throw new InvalidArgumentException("Invalid user_id passed");


// Verify that the current user really can manage the group.
$groups = $ldap->read('(objectclass=group)', $groupId, array('managedby', 'member'));
if (count($groups) != 1)
	throw new Exception("Could not find the group specified");
$group = $groups[0];
if ($group['managedby'][0] != $_SESSION['user_dn'])
	throw new PermissionDeniedException("You are not authorized to manage this group.");

// Verify that the user is not already in the group
if (in_array($userId, $group['member']))
	throw new Exception("The user is already a member of this group.");

// Add the user.
$ldap->addAttribute($groupId, 'member', $userId);

while(ob_get_level())
	ob_end_clean();
header('Content-Type: text/plain');

notify($groupId);

print "Success";
exit;