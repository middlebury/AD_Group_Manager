<?php
/**
 * @since 3/25/09
 * @package directory
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * An LDAP connector
 * 
 * @since 3/25/09
 * @package directory
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class LdapConnector {
	/**
	 * @var array $_config;  
	 * @access private
	 * @since 3/25/09
	 */
	private $_config;
	
	/**
	 * @var resourse $_connection;  
	 * @access private
	 * @since 3/25/09
	 */
	private $_connection;
		
	/**
	 * Constructor
	 * 
	 * @param array $config
	 * @return void
	 * @access public
	 * @since 3/25/09
	 */
	public function __construct (array $config) {
		/*********************************************************
		 * Check our configuration
		 *********************************************************/
		if (!isset($config['LDAPHost']) || !strlen($config['LDAPHost']))
			throw new ConfigurationErrorException("Missing LDAPHost configuration");
		if (!isset($config['LDAPPort']) || !strlen($config['LDAPPort']))
			throw new ConfigurationErrorException("Missing LDAPPort configuration");
		
		if (!isset($config['BindDN']) || !strlen($config['BindDN']))
			throw new ConfigurationErrorException("Missing BindDN configuration");
		if (!isset($config['BindDNPassword']) || !strlen($config['BindDNPassword']))
			throw new ConfigurationErrorException("Missing BindDNPassword configuration");
		
		if (!isset($config['UserBaseDN']) || !strlen($config['UserBaseDN']))
			throw new ConfigurationErrorException("Missing UserBaseDN configuration");
		if (!isset($config['GroupBaseDN']) || !strlen($config['GroupBaseDN']))
			throw new ConfigurationErrorException("Missing GroupBaseDN configuration");
		
		if (!isset($config['WritableGroupContainers']) || !is_array($config['WritableGroupContainers']))
			throw new ConfigurationErrorException("Missing WritableGroupContainers configuration");
		
		$this->_config = $config;
		$this->_connection = false;
		$this->_bind = false;
	}
	
	/**
	 * Clean up an open connections or cache.
	 * 
	 * @return void
	 * @access public
	 * @since 3/25/09
	 */
	public function __destruct () {
		if (isset($this->_connection) && $this->_connection)
			$this->disconnect();
	}
	
	/**
	 * Connects to the LDAP server.
	 * @access public
	 * @return void 
	 **/
	public function connect() {		
		$this->_connection = 
			ldap_connect($this->_config['LDAPHost'], intval($this->_config['LDAPPort']));
		if (!$this->_connection)
			throw new LDAPException ("LdapConnector::connect() - could not connect to LDAP host <b>".$this->_config['LDAPHost']."</b>!");
		
		ldap_set_option($this->_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->_connection, LDAP_OPT_REFERRALS, 0);
		
		$this->bindAsAdmin();
	}
	
	/**
	 * Bind as the admin user
	 * 
	 * @return void
	 * @access public
	 * @since 8/27/09
	 */
	public function bindAsAdmin () {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		$this->_bind = @ldap_bind($this->_connection, $this->_config['BindDN'], $this->_config['BindDNPassword']);
		if (!$this->_bind)
			throw new LDAPException ("LdapConnector::bindAsAdmin() - could not bind to LDAP host <b>".$this->_config['LDAPHost']." using the BindDN and BindDNPassword given.</b>");
	}
	
	/**
	 * Bind as another user. 
	 * 
	 * An LDAPException is thrown on failure.
	 * 
	 * @param string $username
	 * @param string $password
	 * @return string The user DN who successfully logged in.
	 * @access public
	 * @since 8/27/09
	 */
	public function bindAsUser ($username, $password) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		$dn = $this->getUserDN($username);
		$this->_bind = @ldap_bind($this->_connection, $dn, $password);
		if (!$this->_bind)
			throw new LDAPException ("LdapConnector::bindAsUser() - could not bind to LDAP host <b>".$this->_config['LDAPHost']." using the username and password given.</b>");
		
		return $dn;
	}
	
	/**
	 * Answer a user DN given a username or email address.
	 *
	 * An LDAPException is thrown on failure.
	 * 
	 * @param string $username
	 * @return string The User DN.
	 * @access public
	 * @since 8/27/09
	 */
	public function getUserDN ($username) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		// Match a search string that might match a username, email address, first and/or last name.
		if (!preg_match('/^[a-z0-9_,.\'&\s@-]+$/i', $username))
			throw new InvalidArgumentException("query '$query' is not valid format.");
		
		$result = ldap_search($this->_connection, $this->_config['UserBaseDN'], 
						"(|(samaccountname=".$username.")(mail=".$username."))", array('dn'));
						
		if (ldap_errno($this->_connection))
			throw new LDAPException("Read failed for username '$username' with message: ".ldap_error($this->_connection));
		
		$entries = ldap_get_entries($this->_connection, $result);
		ldap_free_result($result);
		
		if (!intval($entries['count']))
			throw new UnknownIdException("Could not find a user matching '$username'.");
		if (intval($entries['count']) > 1)
			throw new OperationFailedException("Found more than one user matching '$username'.");
		
		return $entries[0]['dn'];
	}
	
	/**
	 * Disconnects from the LDAP server.
	 * @access public
	 * @return void 
	 **/
	public function disconnect() {
		ldap_close($this->_connection);
		$this->_connection = NULL;
	}
	
	
	/**
	 * Escape a DN and throw an InvalidArgumentException if it is not of a valid format.
	 * 
	 * @param string $dn
	 * @return string
	 * @access public
	 * @since 4/2/09
	 */
	public function escapeDn ($dn) {
		$dn = strval($dn);
		if (!preg_match('/^[a-z0-9_=\\\,.\'&\s()-]+$/i', $dn))
			throw new InvalidArgumentException("dn '".$dn."' is not valid format.");
		
		// @todo - Escape needed control characters.
		
		return $dn;
	}
	
	/**
	 * Escape a DN value and throw an InvalidArgumentException if it is not of a valid format.
	 *
	 * @param string $dn
	 * @return string
	 * @access public
	 * @since 4/2/09
	 */
	public function escapeDnValue ($dnValue) {
		$dnValue = strval($dnValue);
		if (!preg_match('/^[a-z0-9_=\\\,.\'&\s()-]+$/i', $dnValue))
			throw new InvalidArgumentException("dnValue '".$dnValue."' is not valid format.");

		$dnValue = str_replace(',', '\,', $dnValue);

		return $dnValue;
	}

	/**
	 * Run an LDAP search
	 * 
	 * @param string $query
	 * @param string $baseDN
	 * @param optional array $attributes
	 * @param option int $sizeLimit
	 * @return array
	 * @access public
	 * @since 8/27/09
	 */
	public function search ($query, $baseDN, array $attributes = array(), $sizeLimit = 0) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		$result = @ldap_search($this->_connection, $baseDN, $query, $attributes, 0, $sizeLimit);
		
		// Throw exceptions, but exclude size-exceeded errors for non-zero size limits
		if (ldap_errno($this->_connection) && (ldap_errno($this->_connection) != 4 || $sizeLimit == 0))
			throw new LDAPException("Read failed for query '$query' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));
		
		$entries = ldap_get_entries($this->_connection, $result);
		ldap_free_result($result);
		return $this->reduceLdapResults($entries);
	}
	
	/**
	 * Run an LDAP read
	 * 
	 * @param string $query
	 * @param string $baseDN
	 * @param optional array $attributes
	 * @param option int $sizeLimit
	 * @return array
	 * @access public
	 * @since 8/27/09
	 */
	public function read ($query, $baseDN, array $attributes = array(), $sizeLimit = 0) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		$result = ldap_read($this->_connection, $baseDN, $query, $attributes, 0, $sizeLimit);
						
		// Throw exceptions, but exclude size-exceeded errors for non-zero size limits
		if (ldap_errno($this->_connection) && (ldap_errno($this->_connection) != 4 || $sizeLimit == 0))
			throw new LDAPException("Read failed for query '$query' at DN '$baseDN' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));
		
		$entries = ldap_get_entries($this->_connection, $result);
		ldap_free_result($result);
		return $this->reduceLdapResults($entries);
	}
	
	/**
	 * Reduce a set of results into nicely nested PHP arrays without count elements.
	 * 
	 * @param array $resultSet
	 * @return array
	 * @access protected
	 * @since 8/27/09
	 */
	protected function reduceLdapResults (array $resultSet) {
		unset($resultSet['count']);
		foreach ($resultSet as &$result) {
			for ($i = 0; $i < $result['count']; $i++)
				unset($result[$i]);
			unset($result['count']);
			foreach ($result as &$attributeValue) {
				if (is_array($attributeValue))
					unset($attributeValue['count']);
			}
		}
		return $resultSet;
	}
	
	/**
	 * Add a new entry to the LDAP directory
	 * 
	 * @param string $dn The DN of the new entry to add
	 * @param array $entry An array of attributes as defined in ldap_add()
	 * @return boolean True on success. Exceptions will be thrown on error
	 * @access public
	 * @since 8/28/09
	 */
	public function add ($dn, array $entry) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		$result = ldap_add($this->_connection, $dn, $entry);
		
		if (ldap_errno($this->_connection) || !$result)
			throw new LDAPException("Add failed for dn '$dn' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));
		
		return true;
	}
	
	/**
	 * Delete an entry frp, the LDAP directory
	 *
	 * @param string $dn The DN of the entry to delete
	 * @return boolean True on success. Exceptions will be thrown on error
	 * @access public
	 * @since 8/28/09
	 */
	public function delete ($dn) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");

		if (!$this->_bind)
			$this->bindAsAdmin();

		$success = ldap_delete($this->_connection, $dn);

		if (ldap_errno($this->_connection) || !$success)
			throw new LDAPException("Delete failed for dn '$dn' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));

		return true;
	}

	/**
	 * Add an attribute value to an LDAP entry
	 * 
	 * @param string $dn
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return boolean True on success. Exceptions will be thrown on error
	 * @access public
	 * @since 8/28/09
	 */
	public function addAttribute ($dn, $attributeName, $attributeValue) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		$result = ldap_mod_add($this->_connection, $dn, array($attributeName => $attributeValue));
		
		if (ldap_errno($this->_connection) || !$result)
			throw new LDAPException("Add failed for dn '$dn' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));
		
		return true;
	}
	
	/**
	 * Delete an attribute value to an LDAP entry
	 * 
	 * @param string $dn
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return boolean True on success. Exceptions will be thrown on error
	 * @access public
	 * @since 8/28/09
	 */
	public function delAttribute ($dn, $attributeName, $attributeValue) {
		if (!$this->_connection)
			throw new LDAPException ("Not connected to LDAP host <b>".$this->_config['LDAPHost']."</b>.");
		
		if (!$this->_bind)
			$this->bindAsAdmin();
		
		$result = ldap_mod_del($this->_connection, $dn, array($attributeName => $attributeValue));
		
		if (ldap_errno($this->_connection) || !$result)
			throw new LDAPException("Add failed for dn '$dn' with message: ".ldap_error($this->_connection).' Code: '.ldap_errno($this->_connection));
		
		return true;
	}
}

/**
 * An LDAP Exception
 * 
 * @since 11/6/07
 * @package harmoni.osid_v2.agentmanagement.authn_methods
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LDAPConnector.class.php,v 1.17 2008/04/04 17:55:22 achapin Exp $
 */
class LDAPException
	extends HarmoniException
{
	
}

?>