Copyright &copy; 2009-2020, Middlebury College
License: http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)

Author: 	Adam Franco
Date:		2009-09-04

-----------------------

For documentation on this service, see:
	https://mediawiki.middlebury.edu/wiki/LIS/AD_Group_Manager

For code releases, see:
	http://github.com/adamfranco/AD_Group_Manager/

== Installation ==
1. Copy config.inc.php-sample to config.inc.php
2. Change config options as appropriate.
3. Make the public/ directory accessible on a websever.



== Change-Log ==
0.2.1
 - Added support for ldap:// and ldaps:// configuration urls.
0.2.0
 - Updated phpCAS from 1.2.2 to 1.3.4
 - New super-admin role
 - Web-service notifications now happen asynchronously via cron.
 - Improved display of sub-groups.
 - Alphabetical sorting of group members.
 - Fixed PHP notices.
0.1.0
	- First release.
