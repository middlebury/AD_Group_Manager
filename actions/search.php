<?php
/**
 * Search for users or groups.
 *
 * @since 8/28/09
 * @package 
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

while(ob_get_level())
	ob_end_clean();

header('Content-Type: text/plain');

$q = strtolower($_GET["q"]);
if (!$q) 
	exit;
if (!preg_match('/^[\w@.\'_-\s]+$/i', $q))
	exit;

if (isset($_GET['limit'])) {
	$limit = (int)$_GET['limit'];
	$limit = max(1, $limit);
	$limit = min(100, $limit);
} else {
	$limit = 20;
}

$results = $ldap->search('(&(ANR='.$q.')(objectClass=User)(!(objectClass=Computer)))', $ldapConfig['BaseDN'], array('givenName', 'sn', 'cn', 'mail', 'objectClass'), $limit);
foreach ($results as $entry) {
	if (in_array('group', $entry['objectclass'])) {
		$levels = ldap_explode_dn($entry['dn'], 1);
		unset($levels['count']);
		array_pop($levels);
		array_pop($levels);
		$levels = array_reverse($levels);
		print implode('/', $levels);
	} else {
		if (isset($entry['givenname'][0]) && isset($entry['sn'][0]))
			print $entry['givenname'][0]." ".$entry['sn'][0];
		else if (isset($entry['cn'][0]))
			print $entry['cn'][0];
		else
			continue;
		
		if (isset($entry['mail'][0]))
			print " (".$entry['mail'][0].")";
	}
	
	print "|".base64_encode($entry['dn'])."\n";
}


exit;