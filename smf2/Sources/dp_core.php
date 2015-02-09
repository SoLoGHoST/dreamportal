<?php
/**************************************************************************************
* dp_core.php			                                                              *
***************************************************************************************
* Dream Portal                                                                        *
* Forum Portal Modification Project founded by ccbtimewiz (ccbtimewiz@ccbtimewiz.com) *
* =================================================================================== *
* Software by:                  Dream Portal Team (http://dream-portal.net)			  *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2009-2012 by:       Dream Portal Team									  *
* License:						http://dream-portal.net/index.php?page=license		  *
* Support, News, Updates at:    http://dream-portal.net                               *
**************************************************************************************/

if (!defined('SMF') || !defined('DP'))
	die('Hacking attempt...');

// Load up the Language strings needed.
loadLanguage('ManageDP+DreamPortal');

function add_dp_core_feature(&$core_features)
{
	global $txt, $settings;

	validateSession();
	
	$dream_portal = array(
		'url' => 'action=admin;area=dplayouts',
		'settings' => array(
			'dp_portal_mode' => 1,
		),
		'title' => $txt['dream_portal'],
		'desc' => $txt['core_settings_item_dp_desc'],
		'image' => $settings['images_url'] . '/admin/feature_dp.png',
		'setting_callback' => create_function('$value', '
			global $modSettings;
			$func = (!$value ? \'remove\' : \'add\') . \'_integration_function\';

			$hooks = array(
				\'integrate_pre_include\' => \'$sourcedir/Subs-DreamPortal.php\',
				\'integrate_actions\' => \'add_dream_actions\',
				\'integrate_load_permissions\' => \'add_dp_permissions\',
				\'integrate_menu_buttons\' => \'add_dp_menu_buttons\',
				\'integrate_admin_areas\' => \'add_dp_admin_areas\',
				\'integrate_whos_online\' => \'dream_whos_online\',
			);

			if (empty($modSettings[\'dp_disable_copyright\']))
				$hooks += array(\'integrate_buffer\' => \'dreamBuffer\');

			if (empty($modSettings[\'dp_disable_homepage\']))
				$hooks += array(\'integrate_redirect\' => \'dreamRedirect\');

			foreach($hooks as $type => $value)
				$func($type, $value);
		'),
	);

	// Let's put Dream Portal up top where it belongs ;)
	$core_features = array_merge(array('dp' => $dream_portal), $core_features);
}

?>