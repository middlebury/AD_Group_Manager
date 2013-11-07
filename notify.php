<?php
/**
 * This script displays a web-form for managing AD groups.
 *
 *
 * @package group_manager
 * 
 * @copyright Copyright &copy; 2013, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

define("MYDIR", realpath(dirname(__FILE__)));

global $notify_queue_dsn, $notify_queue_user, $notify_queue_pass, $notify_queue_options;
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

try {

	require_once(MYDIR.'/actions/notify.php');
	
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