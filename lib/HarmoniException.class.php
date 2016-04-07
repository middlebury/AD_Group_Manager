<?php
/**
 * @since 9/5/07
 * @package harmoni.error_handler
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniException.class.php,v 1.7 2007/10/10 22:57:43 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/OsidExceptions.php");

/**
 * The HarmoniException adds pretty HTML formatting to the built-in exception class.
 *
 * @since 9/5/07
 * @package harmoni.error_handler
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniException.class.php,v 1.7 2007/10/10 22:57:43 adamfranco Exp $
 */
class HarmoniException
	extends Exception
{
	/**
	 * @var string $type;
	 * @access private
	 * @since 9/5/07
	 */
	private $type = '';

	/**
	 * @var boolean $isFatal;
	 * @access private
	 * @since 9/5/07
	 */
	private $isFatal = true;

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param optional integer $code
	 * @param optional string $type
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function __construct ($message, $code = 0, $type = '', $isFatal = true) {
		parent::__construct($message, $code);
		$this->type = $type;
		$this->isFatal = $isFatal;
	}

	/**
	 * Answer the type of error (generally the package it occurred in).
	 *
	 * @return string
	 * @access public
	 * @since 9/5/07
	 */
	public function getType () {
		return $this->type;
	}

	/**
	 * Answer true if this exception causes a fatal error if not caught.
	 *
	 * @return boolean
	 * @access public
	 * @since 9/5/07
	 */
	public function isFatal () {
		return $this->isFatal;
	}
}


/**
 * Thrown on an unknown Action
 *
 * @since 10/4/07
 * @package harmoni.error_handler
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: OsidExceptions.php,v 1.1 2007/10/05 14:01:50 adamfranco Exp $
 */
class UnknownActionException
	extends HarmoniException
{

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param optional integer $code
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function __construct ($message = '', $code = 0) {
		parent::__construct('UNKNOWN ACTION: '.$message, $code);
	}

}

?>
