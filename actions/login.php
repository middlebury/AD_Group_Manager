<?php
/**
 * @since 8/27/09
 * @package group_manager
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

// Already logged in.
if (isset($_SESSION['user_dn']) && strlen($_SESSION['user_dn']))
	forward('list');

// Bind as the user and send them to the list
if (isset($_POST['username']) && strlen($_POST['username']) && isset($_POST['password']) && strlen($_POST['password'])) {
	$_SESSION['user_dn'] = $ldap->bindAsUser($_POST['username'], $_POST['password']);
	forward('list');
}

// Print out the login form.
?>

<form action="<? echo getUrl('login'); ?>" method="post">
	<fieldset>
		<legend>Login</legend>
		<label>Username: <input type="text" name="username"/></label> <br/>
		<label>Password: <input type="password" name="password"/></label> <br/>
		<input type="submit" value="Log In"/>
	</fieldset>
</form>