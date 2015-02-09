<?php
/*
	This script removes Dream Portal's settings.
	NOTE: This script is meant to run using the <samp><code></code></samp> elements of the package-info.xml file. This is because certain items in the database and within SMF will need to be removed regardless of whether the user wants to keep data or not. In this instance, the registered hooks need to lose their calls to Dream Portal's functions, else the forum'll crash.
	@package installer
	@since 1.1

	Before attempting to execute, this file attempts to load SSI.php to enable access to the database functions.
*/

// If SSI.php is in the same place as this file, and SMF isn't defined...
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');

// Only Admin can uninstall...
if((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

remove_integration_function('integrate_admin_include', '$sourcedir/dp_core.php');
remove_integration_function('integrate_core_features', 'add_dp_core_feature');

// These integration functions are only present if Dream Portal is enabled when you uninstall it.
remove_integration_function('integrate_pre_include', '$sourcedir/Subs-DreamPortal.php');
remove_integration_function('integrate_actions', 'add_dream_actions');
remove_integration_function('integrate_load_permissions', 'add_dp_permissions');
remove_integration_function('integrate_menu_buttons', 'add_dp_menu_buttons');
remove_integration_function('integrate_admin_areas', 'add_dp_admin_areas');
remove_integration_function('integrate_whos_online', 'dream_whos_online');

// Only present if the Homepage is enabled.
remove_integration_function('integrate_redirect', 'dreamRedirect');

// Only present if the Dream Portal copyright is enabled.
remove_integration_function('integrate_buffer', 'dreamBuffer');
	
?>