<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Group Manager</title>

		<!-- Start Midd 2D -->
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="follow,index" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php echo CDN_BASE; ?>/StyleSheets/2d.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php echo CDN_BASE; ?>/StyleSheets/2dFlex.css" title="flex" />
		<link rel="alternate stylesheet" media="screen" type="text/css" href="<?php echo CDN_BASE; ?>/StyleSheets/2dFixed.css" title="fixed" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php echo CDN_BASE; ?>/StyleSheets/Menu.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php echo CDN_BASE; ?>/StyleSheets/MenuStyle.css" />
		<script type="text/javascript" src="<?php echo CDN_BASE; ?>/JavaScript/StyleSwitcher.js"></script>
		<!-- End Midd 2D -->

		<link rel="stylesheet" type="text/css" href="group_manager.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js" type="text/javascript"></script>
		<script type='text/javascript' src='jquery-autocomplete/jquery.autocomplete.js'></script>
		<link rel="stylesheet" type="text/css" href="jquery-autocomplete/jquery.autocomplete.css" />
		<script type="text/javascript" src="group_manager.js"></script>
	</head>
	<body>
		<div class="main">
			<div class="header">
				<div class="headerWelcome">
					Welcome <?php print call_user_func($getUserDisplayName); ?> &#160; | &#160;
					<a href="<?php echo getUrl('logout'); ?>">Logout</a> &#160; | &#160;
					<a href="#" onclick="setActiveStyleSheet('fixed'); return false;">Fixed</a> or
					<a href="#" onclick="setActiveStyleSheet('flex'); return false;">Flex</a>
				</div>
				<div class="clear">&#160;</div>
				<a href="http://www.middlebury.edu">
					<img class="headerLogo" src="<?php echo CDN_BASE; ?>/Images/mclogo.gif" alt="Click here to return to Middlebury College home page" />
				</a>
				<div class="headerSite">
					<h1>Group Manager</h1>
				</div>
				<div class="clear">
					&#160;
				</div>
			</div>
			<div class="headerNavigation">
				<div class="CssMenu">
					<div class="AspNet-Menu-Horizontal">
						<ul class="AspNet-Menu">
							<li class="AspNet-Menu-Leaf <?php if ($action == 'list') { echo 'AspNet-Menu-Selected'; } ?>">
								<a href="<?php echo getUrl('list'); ?>" class="AspNet-Menu-Link">My Web Groups</a>
							</li>
							<li class="AspNet-Menu-Leaf <?php if ($action == 'list_web') { echo 'AspNet-Menu-Selected'; } ?>">
								<a href="<?php echo getUrl('list_web'); ?>" class="AspNet-Menu-Link">All Web Groups</a>
							</li>
							<li class="AspNet-Menu-Leaf <?php if ($action == 'list_all') { echo 'AspNet-Menu-Selected'; } ?>">
								<a href="<?php echo getUrl('list_all'); ?>" class="AspNet-Menu-Link">All Groups</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="https://mediawiki.middlebury.edu/wiki/LIS/AD_Group_Manager" target="_blank" class="AspNet-Menu-Link">Help</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
			<div class="content">
