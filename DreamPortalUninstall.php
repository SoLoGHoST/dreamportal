<?php
/*
* This script completely removes Dream Portal!
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

// An array of all table names, minus the prefixes, to uninstall.
$dp_tables = array('groups', 'layouts', 'actions', 'layout_positions', 'module_positions', 'modules', 'module_clones', 'module_parameters', 'templates', 'languages', 'module_files', 'dream_pages', 'dream_menu');

// storing all settings to be removed.
$dp_settings = array(
	'dp_pages_mode',
	'dp_menu_mode',
	'dp_collapse_modules',
	'dp_disable_homepage',
	'dp_disable_copyright',
	'dp_add_modules_limit',
	'dp_add_templates_limit',
	'dp_add_languages_limit',
	'dp_module_title_char_limit',
	'dp_module_display_style',
	'dp_module_enable_animations',
	'dp_module_animation_speed',
	'dp_icon_directory',
	'dp_disable_custommod_icons',
	'dp_enable_custommod_icons',
);

db_extend('packages');

// Remove the tables.
foreach ($dp_tables as $table)
	$smcFunc['db_drop_table']('{db_prefix}dp_' . $table);

// Let's remove dp settings rows
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:dp_settings})',
	array(
		'dp_settings' => $dp_settings,
	)
);

// Need to clean up all Modules and Templates!
require_once($sourcedir . '/Subs-Package.php');

// Remove all theme-related files/folders that any modules have created!
$mod_dirs = array('css/', 'scripts/', '', '/languages');
$td = opendir($boarddir . '/Themes');
while(false !== ($theme_name = readdir($td)))
{
	if ($theme_name == '.' || $theme_name == '..')
		continue;

	if (!is_dir($boarddir . '/Themes/' . $theme_name))
		continue;

	foreach($mod_dirs as $dirs)
	{
		// If any language files exist for Modules/Templates, remove them!
		if ($dirs == '/languages')
		{
			$tt = opendir($boarddir . '/Themes/' . $theme_name . '/languages');
			while (false !== ($lang_file = readdir($tt)))
			{
				if ($lang_file == '.' || $lang_file == '..')
						continue;

				$dp_lang = false;

				// Also need to remove all Language files that have been installed into Dream Portal!
				$lang_name = array(
					substr($lang_file, 0, 13) == 'DreamModules.' ? 1 : 0,
					substr($lang_file, 0, 15) == 'DreamTemplates.' ? 1 : 0,
					substr($lang_file, 0, 9) == 'ManageDP.' ? 1 : 0,
					substr($lang_file, 0, 12) == 'DreamPortal.' ? 1 : 0,
					substr($lang_file, 0, 10) == 'DreamHelp.' ? 1 : 0,
					substr($lang_file, 0, 17) == 'DreamPermissions.' ? 1 : 0,
				);

				foreach ($lang_name as $language)
				{
					if (!empty($language))
					{
						$dp_lang = true;
						break;
					}
				}

				// It's not a Dream Portal language file, so get outta here!
				if (!$dp_lang)
					continue;

				// Delete the language file now.
				@unlink($boarddir . '/Themes/' . $theme_name . '/languages/' . $lang_file);
			}
		}
		else
			if (is_dir($boarddir . '/Themes/' . $theme_name . '/' . $dirs . 'dreamportal'))
				deltree($boarddir . '/Themes/' . $theme_name . '/' . $dirs . 'dreamportal');
	}
}
closedir($td);

// Ok, now let's execute the <database><uninstall> tag for every module that has them defined.
require_once($sourcedir . '/Subs.php');
$md = opendir($boarddir . '/dreamportal/modules');
while(false !== ($mod_name = readdir($md)))
{
	if ($mod_name == '.' || $mod_name == '..' || $mod_name == '.htaccess' || $mod_name == 'index.php')
		continue;

	if (is_dir($boarddir . '/dreamportal/modules/' . $mod_name))
	{
		$mn = opendir($boarddir . '/dreamportal/modules/' . $mod_name);
		while (false !== ($mod_info = readdir($mn)))
		{
			if ($mod_info != 'info.xml')
				continue;

			$moduleInfo = file_get_contents($boarddir . '/dreamportal/modules/' . $mod_name . '/' . $mod_info);
			loadClassFile('Class-Package.php');
			$moduleInfo = new xmlArray($moduleInfo);
			
			$moduleInfo = $moduleInfo->path('module[0]');
			$module = $moduleInfo->to_array();
			
			if (empty($module['database']['uninstall']))
				break;
			else
			{
				if (file_exists($boarddir . '/dreamportal/modules/' . $mod_name . '/' . $module['database']['uninstall']))
				{
					global $txt, $boarddir, $user_info, $sourcedir, $modSettings, $context, $settings, $forum_version, $smcFunc;

					require_once($boarddir . '/dreamportal/modules/' . $mod_name . '/' . $module['database']['uninstall']);
				}
			}
		}
		closedir($mn);
	}
}
closedir($md);

// Finally, delete the dreamportal directory.
deltree($boarddir . '/dreamportal', true);

?>