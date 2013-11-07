<?php
/**
 * This script displays a web-form for managing AD groups.
 *
 *
 * @since 8/27/09
 * @package group_manager
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

$name = preg_replace('/[^a-z0-9_-]/i', '', dirname($_SERVER['SCRIPT_NAME']));
session_name($name);
session_start();

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';
if ($_SERVER['SCRIPT_NAME'])
	$scriptPath = $_SERVER['SCRIPT_NAME'];
else
	$scriptPath = $_SERVER['PHP_SELF'];
define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($scriptPath)));
define("MYURL", trim(MYPATH, '/')."/".basename($scriptPath));
define("MYDIR", realpath(dirname(__FILE__)."/.."));

global $notifyConfig;

require_once(MYDIR.'/config.inc.php');
require_once(MYDIR.'/lib/HarmoniException.class.php');
require_once(MYDIR.'/lib/ErrorPrinter.class.php');
require_once(MYDIR.'/lib/LdapConnector.class.php');
require_once(MYDIR.'/lib/functions.php');

if (!defined('SHOW_TIMERS_IN_OUTPUT'))
	define('SHOW_TIMERS_IN_OUTPUT', false);
if (!defined('SHOW_TIMERS'))
	define('SHOW_TIMERS', false);
if (!defined('DISPLAY_ERROR_BACKTRACE'))
	define('DISPLAY_ERROR_BACKTRACE', false);

require_once(MYDIR.'/lib/phpcas/source/CAS.php');

if (!isset($getUserDisplayName)) {
	$getUserDisplayName = create_function('', 'return phpCAS::getUser();');
}

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, CAS_HOST, CAS_PORT, CAS_PATH, false);
// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();
// force CAS authentication
phpCAS::forceAuthentication();

// var_dump($_SESSION);

$ldap = new LdapConnector($ldapConfig);
$ldap->connect();

try {
	// Check authorization
	if (!empty($authorizedUserAttributes)) {
		if (!is_array($authorizedUserAttributes))
			throw new Exception('Configuration Error: $authorizedUserAttributes must be an array');
		$isAuthorized = false;
		$attributes = phpCAS::getAttributes();
		foreach ($authorizedUserAttributes as $attr => $authorized_values) {
			if (!is_array($authorized_values))
				$authorized_values = array($authorized_values);
			foreach ($authorized_values as $authorized_value) {
				if (!empty($attributes[$attr])) {
					if (is_array($attributes[$attr])) {
						if (in_array($authorized_value, $attributes[$attr])) {
							$isAuthorized = true;
							break;
							break;
						}
					} else if ($attributes[$attr] == $authorized_value) {
						$isAuthorized = true;
						break;
						break;
					}
				}
			}
		}
		
		if (!$isAuthorized)
			throw new PermissionDeniedException("You are not authorized to use this application.");
	}

	// Parse/validate our arguments and run the specified action.
	if (isset($_REQUEST['action'])) {
		if (preg_match('/^[a-z0-9_-]+$/i', $_REQUEST['action']))
			$action = $_REQUEST['action'];
		else
			throw new UnknownActionException('Invalid action format.');
	} else {
		$action = 'list';
	}
	
	if ($action == 'logout') {
		phpCAS::logout();
	}
	
	// Try to load the user's DN from the LDAP server. If they are not in
	// LDAP (such as guests) then they cannot use this application.
	if (!isset($_SESSION['user_dn']) || !strlen($_SESSION['user_dn'])) {
		try {
			$_SESSION['user_dn'] = $ldap->getUserDN(phpCAS::getUser());
		} catch (UnknownIdException $e) {
			throw new PermissionDeniedException("We could not find your account on the LDAP server. You are not authorized to create groups.");
		}
	}
	
	ob_start();
	if (file_exists(MYDIR.'/actions/'.$action.'.php'))
		require_once(MYDIR.'/actions/'.$action.'.php');
	else
		throw new UnknownActionException("Unknown action, $action.");
	$content = ob_get_clean();
	
	// Print out the content
	require_once(MYDIR.'/theme/header.php');
	print $content;
	require_once(MYDIR.'/theme/footer.php');
	
// Handle certain types of uncaught exceptions specially. In particular,
// Send back HTTP Headers indicating that an error has ocurred to help prevent
// crawlers from continuing to pound invalid urls.
} catch (UnknownActionException $e) {
	ErrorPrinter::handleException($e, 404);
} catch (NullArgumentException $e) {
	ErrorPrinter::handleException($e, 400);
} catch (InvalidArgumentException $e) {
	ErrorPrinter::handleException($e, 400);
} catch (PermissionDeniedException $e) {
	ErrorPrinter::handleException($e, 403);
} catch (UnknownIdException $e) {
	ErrorPrinter::handleException($e, 404);
}
// Default 
catch (Exception $e) {
	ErrorPrinter::handleException($e, 500);
}