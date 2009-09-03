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

$ldap = new LdapConnector($ldapConfig);
$ldap->connect();

try {
	// Parse/validate our arguments and run the specified action.
	if (isset($_REQUEST['action'])) {
		if (preg_match('/^[a-z0-9_-]+$/i', $_REQUEST['action']))
			$action = $_REQUEST['action'];
		else
			throw new UnknownActionException('Invalid action format.');
	} else {
		$action = 'login';
	}
	if (!isset($_SESSION['user']) || !strlen($_SESSION['user']))
		$action = 'login';
	
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