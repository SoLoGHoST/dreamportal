<?php
/**************************************************************************************
* ManageDPLayouts.php                                                                 *
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

function loadGeneralLayoutParameters($subActions = array(), $defaultAction = '')
{
	global $context, $sourcedir;

	// If DreamModules doesn't exist, just skip it!
	loadLanguage('DreamModules', '', false);

	// These are required language files!
	loadLanguage('DreamHelp+ManageSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	// load the template and the style sheet needed
	loadTemplate('ManageDPLayouts', 'dreamportal');

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));

	// Manage Layouts section will have it's own unique template function!
	if ($_REQUEST['sa'] == 'dpmanlayouts')
		$context['sub_template'] = 'manage_layouts';
	else
		$context['sub_template'] = 'show_settings';

	$context['sub_action'] = $_REQUEST['sa'];
}

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function dpManageLayouts()
{
	global $context, $txt;

	// Do you have permission to do this?  Admins automatically have all permissions!
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		fatal_lang_error('dp_no_permission', false);

	$subActions = array(
		'dpmanlayouts' => 'ManageDPLayouts',
		'dplayoutsettings' => 'ModifyDPLayoutSettings',
		'dpsavelayout' => 'SaveDPLayout',
		'modifymod' => 'ModifyModule',
		'clonemod' => 'CloneDPMod',
		'dpaddlayout' => 'AddDPLayout',
		'dpaddlayout2' => 'AddDPLayout2',
		'dpdellayout' => 'DeleteDPLayout',
		'dpeditlayout' => 'EditDPLayout',
		'dpeditlayout2' => 'EditDPLayout2',
	);

	loadGeneralLayoutParameters($subActions, 'dpmanlayouts');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['dp_admin_dream_layouts'],
		'help' => 'dp_admin_layouts_help',
		'description' => $txt['dp_admin_layouts_manmodules_desc'],
		'tabs' => array(
			'dpmanlayouts' => array(
				'description' => $txt['dp_admin_layouts_manmodules_desc'],
			),
			'dplayoutsettings' => array(
				'description' => $txt['dp_admin_config_layoutsettings_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

/**
 * Loads the master module settings for Dream Portal so the admin can change them. Uses the sub template show_settings in Admin.template.php to display them.
 *
 * @param bool $return_config Determines whether or not to return the config array.
 * @return void|array The $config_vars if $return_config is true.
 * @since 1.0
 */
function ModifyDPLayoutSettings($return_config = false)
{
	global $txt, $boarddir, $scripturl, $context, $modSettings, $smcFunc;

	if (!allowedTo('admin_dplayouts'))
		fatal_lang_error('dp_no_permission', false);

	$config_vars = array(
			array('check', 'dp_disable_homepage', 'help' => 'dp_disable_homepage_help'),
		'',
			array('select', 'dp_module_display_style', array(&$txt['dp_module_display_style_blocks'], &$txt['dp_module_display_style_modular']), 'help' => 'dp_module_display_style_help'),
		'',
			array('check', 'dp_collapse_modules', 'help' => 'dp_collapse_modules_help'),
			array('check', 'dp_module_enable_animations', 'help' => 'dp_module_enable_animationshelp', 'onclick' => 'document.getElementById(\'dp_module_animation_speed\').disabled = !this.checked;'),
			array('select', 'dp_module_animation_speed', array(&$txt['dp_animation_speed_veryslow'], &$txt['dp_animation_speed_slow'], &$txt['dp_animation_speed_normal'], &$txt['dp_animation_speed_fast'], &$txt['dp_animation_speed_veryfast']), 'help' => 'dp_module_animation_speed_help'),
			array('callback', 'dpmodule_header_heights'),
			array('int', 'dp_module_title_char_limit', 'help' => 'dp_module_title_char_limit_help'),
		'',
			array('check', 'dp_disable_custommod_icons', 'help' => 'dp_disable_custommod_icons_help'),
			array('check', 'dp_enable_custommod_icons', 'help' => 'dp_enable_custommod_icons_help'),
			array('text', 'dp_icon_directory', 'size' => 40, 'help' => 'dp_icon_directory_help'),
	);

	if ($return_config)
		return $config_vars;

	// Disable the Animation speeds selectbox if animation is not disabled!
	$context['settings_post_javascript'] = 'document.getElementById(\'dp_module_animation_speed\').disabled = !document.getElementById(\'dp_module_enable_animations\').checked;';

	// Load all module header heights for all themes installed.
	$context['module_themes'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_theme, value, variable
		FROM {db_prefix}themes
		WHERE variable = {string:theme_url} || variable = {string:name}',
		array(
			'theme_url' => 'theme_url',
			'name' => 'name',
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['variable'] == 'name')
			$context['module_themes'][$row['id_theme']]['theme_name'] = $row['value'];
		elseif($row['variable'] == 'theme_url')
		{
			$context['module_themes'][$row['id_theme']]['name'] = substr(strrchr($row['value'], "/"), 1);
			$context['module_themes'][$row['id_theme']]['value'] = 28;

			// Overwrite the default value of 28, if the modSetting exists for this theme!
			if (!empty($modSettings['dp_mod_header' . $context['module_themes'][$row['id_theme']]['name']]))
				$context['module_themes'][$row['id_theme']]['value'] = (int) $modSettings['dp_mod_header' . $context['module_themes'][$row['id_theme']]['name']];
		}
	}
	$smcFunc['db_free_result']($request);

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();

		$save_vars = $config_vars;

		if (isset($save_vars['dpmodule_header_heights']))
			unset($save_vars['dpmodule_header_heights']);

		// Update the modSettings for the Theme-Specific Module Heights.
		foreach($context['module_themes'] as $theme_info)
		{
			$POST['dp_mod_header' . $theme_info['name']] = !empty($POST['dp_mod_header' . $theme_info['name']]) ? $POST['dp_mod_header' . $theme_info['name']] : 0;
			$save_vars[] = array('int', 'dp_mod_header' . $theme_info['name']);
		}

		// Handle any undefined index errors that SMF logs!
		if (empty($modSettings['dp_disable_homepage']))
			$modSettings['dp_disable_homepage'] = 0;

		if (empty($_POST['dp_disable_homepage']))
			$_POST['dp_disable_homepage'] = 0;

		if ($modSettings['dp_disable_homepage'] != $_POST['dp_disable_homepage'])
		{
			// Removing the Homepage layout requires deleting the [home] non-action, than updating the forum action, if exists.
			if (empty($modSettings['dp_disable_homepage']) && !empty($_POST['dp_disable_homepage']))
			{
				$smcFunc['db_query']('', 'DELETE FROM {db_prefix}dp_actions
					WHERE action={string:home_layout} AND id_group={int:group_id}',
					array(
						'home_layout' => '[home]',
						'group_id' => 1,
					)
				);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_actions
					SET
						action = {string:home_layout}
					WHERE action = {string:forum_layout} AND id_group = {int:group_id}
					LIMIT 1',
					array(
						'home_layout' => '[home]',
						'forum_layout' => 'forum',
						'group_id' => 1,
					)
				);

				// Removing the redirect integration here, as it's not needed.
				remove_integration_function('integrate_redirect', 'dreamRedirect');
			}
			else
			{
				// We are re-enabling the Homepage layout.  Update [home] non-action to forum instead, and insert the [home] non-action.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_actions
					SET
						action = {string:forum_layout}
					WHERE action = {string:home_layout} AND id_group = {int:group_id}
					LIMIT 1',
					array(
						'home_layout' => '[home]',
						'forum_layout' => 'forum',
						'group_id' => 1,
					)
				);

				$smcFunc['db_insert']('insert', '{db_prefix}dp_actions', array('id_group' => 'int', 'id_layout' => 'int', 'action' => 'string-255'), array(1, 1, '[home]'), array('id_action', 'id_group', 'id_layout'));

				// Need the redirect integration...
				add_integration_function('integrate_redirect', 'dreamRedirect');
			}
		}

		// No slashes to the left.
		if ($smcFunc['substr']($_POST['dp_icon_directory'], 0, 1) == '/')
			$_POST['dp_icon_directory'] = $smcFunc['substr']($_POST['dp_icon_directory'], 1, $smcFunc['strlen']($_POST['dp_icon_directory']) - 1);

		// No slashes to the right.
		if ($smcFunc['substr']($_POST['dp_icon_directory'], -1, 1) == '/')
			$_POST['dp_icon_directory'] = $smcFunc['substr']($_POST['dp_icon_directory'], 0, $smcFunc['strlen']($_POST['dp_icon_directory']) - 2);

		// If not a valid directory, load up the previous directory they had defined!
		if (!is_dir($boarddir . '/' . $_POST['dp_icon_directory']))
			$_POST['dp_icon_directory'] = $modSettings['dp_icon_directory'];

		saveDBSettings($save_vars);

		writeLog();
		redirectexit('action=admin;area=dplayouts;sa=dplayoutsettings');
	}

	$context['page_title'] = $txt['dp_admin_config_layoutsettings_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=dplayouts;save;sa=dplayoutsettings';
	$context['settings_title'] = $txt['dp_admin_layout_settings'];

	prepareDBSettingContext($config_vars);
}

/**
 * Loads the list of modules to manage.
 *
 * @since 1.0
 */
function ManageDPLayouts()
{
	global $context, $smcFunc, $txt, $scripturl, $settings, $modSettings;

	validateSession();

	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		fatal_lang_error('dp_no_permission', false);

	$context['page_title'] = $txt['dp_admin_manage_layouts_title'];

	$selected_layout = array('id_layout' => 1, 'name' => $txt['dp_homepage']);

	// If the selected layout session is empty, start it at the homepage!
	if (empty($_SESSION['selected_layout']))
		$_SESSION['selected_layout'] = $selected_layout;

	if (empty($_SESSION['layouts']) || !isset($_SESSION['layouts'][1]) || $_SESSION['layouts'][1] != $selected_layout['name'])
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				dl.id_layout, dl.name, dl.is_txt
			FROM {db_prefix}dp_layouts AS dl
				LEFT JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			WHERE dl.id_group = dg.id_group',
			array(
				'one' => 1,
				'zero' => 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$_SESSION['layouts'][$row['id_layout']] = !empty($row['is_txt']) && isset($txt[$row['name']]) ? $txt[$row['name']] : $row['name'];
	}
	
	// If we have more than 1 layout, don't bother showing the DP Homepage if it's disabled!
	if (count($_SESSION['layouts']) > 1 && !empty($modSettings['dp_disable_homepage']))
	{
		unset($_SESSION['layouts'][1]);

		$layout_keys = array_keys($_SESSION['layouts']);

		// Disabling the homepage requires a new selected_layout session if Homepage was selected, so let's go with the first 1 available in the layouts list!
		if (!in_array($_SESSION['selected_layout']['id_layout'], $layout_keys))
		{
			reset($_SESSION['layouts']);

			$_SESSION['selected_layout'] = array(
				'id_layout' => key($_SESSION['layouts']),
				'name' => current($_SESSION['layouts']),
			);
		}
	}

	// Be sure to order these layouts according to id_layout!
	ksort($_SESSION['layouts']);

	// If they selected a layout from the select box.
	if (!empty($_POST['layout_picker']))
		$_SESSION['selected_layout'] = array(
			'id_layout' => (int) $_POST['layout_picker'],
			'name' => $_SESSION['layouts'][$_POST['layout_picker']],
		);

	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dm.name AS mod_name, dm.title AS mod_title, dm.txt_var, dlp.column, dlp.row,
			dmp.position, dlp.enabled, dmp.id_position, dlp.id_layout_position, dlp.id_layout_position AS original_id_layout_position,
			dmc.id_clone, dmc.name AS clone_name, dmc.title AS clone_title, dmc.is_clone
		FROM {db_prefix}dp_layout_positions AS dlp
			LEFT JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			LEFT JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
			LEFT JOIN {db_prefix}dp_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position AND dmp.id_layout = dl.id_layout)
			LEFT JOIN {db_prefix}dp_module_clones AS dmc ON (dmp.id_clone = dmc.id_clone AND dmc.id_member = {int:zero})
			LEFT JOIN {db_prefix}dp_modules AS dm ON (dmp.id_module = dm.id_module)
			WHERE dlp.id_layout = dl.id_layout AND dlp.enabled != {int:invisible_layout}
		ORDER BY dlp.row',
		array(
			'one' => 1,
			'zero' => 0,
			'invisible_layout' => -2,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
		)
	);

	$old_row = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$is_clone = !empty($row['is_clone']) && !empty($row['id_clone']);

		if ($row['enabled'] == -1)
		{
			$row['id_layout_position'] = 0;
			$row['row'] = '0:0';
			$row['column'] = '0:0';
		}

		$smf = (int) $row['id_clone'] + (int) $row['id_module'];
		$smf_col = empty($smf) && !is_null($row['id_position']);

		$current_row = explode(':', $row['row']);
		$current_column = explode(':', $row['column']);
		$context['span']['rows'][$row['original_id_layout_position']] = ($current_row[1] >= 2 ? ' rowspan="' . $current_row[1] . '"' : '');
		$context['span']['columns'][$row['original_id_layout_position']] = ($current_column[1] >= 2 ? ' colspan="' . $current_column[1] . '"' : '');

		// We'll need to hold the $txt key names so we can output the default titles from the language file if it hasn't been changed ofcourse!
		$txt_names = array();

		if (!empty($row['mod_name']))
			$txt_names['mod'] = 'dpmod_' . $row['mod_name'];

		if (!empty($row['clone_name']))
			$txt_names['clone'] = 'dpmod_' . $row['clone_name'];

		if (!isset($dp_modules[$current_row[0]][$current_column[0]]) && !empty($row['id_layout_position']))
			$dp_modules[$current_row[0]][$current_column[0]] = array(
				'is_smf' => $smf_col,
				'id_layout_position' => $row['original_id_layout_position'],
				'column' => explode(':', $row['column']),
				'row' => explode(':', $row['row']),
				'enabled' => $row['enabled'],
				'disabled_module_container' => $row['enabled'] == -1,
			);

		if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
		{
			$dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']] = array(
				'is_smf' => empty($smf),
				'id' => $row['id_position'],
				'title' => !empty($row['txt_var']) && isset($txt[$row['mod_title']]) ? $txt[$row['mod_title']] : (empty($row['id_clone']) ? (trim($row['mod_title']) == '' && isset($txt_names['mod']) ? $txt[$txt_names['mod']] : $row['mod_title']) : (trim($row['clone_title']) == '' && isset($txt_names['clone']) ? $txt[$txt_names['clone']] : $row['clone_title'])),
				'is_clone' => $is_clone,
				'id_clone' => $row['id_clone'],
				'modify' => '<a href="' . $scripturl . '?action=admin;area=dplayouts;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['dp_admin_modules_manage_modify'] . '</a>',
				'clone' => '<a href="' . $scripturl . '?action=admin;area=dplayouts;sa=clonemod' . (!$is_clone ? ';mod' : '') . ';xml;' . (!empty($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . ($is_clone ? $txt['dpmodule_declone'] : $txt['dpmodule_clone']) . '</a>',
			);
			
			// Fix the title of the Module if it is too long for viewing in the small box (no more than 21 characters).
			if($smcFunc['strlen']($dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]['title']) >= 25)
				$dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]['title'] = $smcFunc['substr']($dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]['title'], 0, 22) . '...';
		}

		// Special case for disabled modules...
		if (!isset($dp_modules['disabled']) && empty($row['id_layout_position']))
			$dp_modules['disabled'] = array(
				'id_layout_position' => $row['original_id_layout_position'],
				'fake_id_layout_position' => $row['id_layout_position'],
				'column' => explode(':', $row['column']),
				'row' => explode(':', $row['row']),
				'enabled' => $row['enabled'],
			);

		if (!is_null($row['id_position']) && empty($row['id_layout_position']))
		{
			$dp_modules['disabled']['modules'][] = array(
				'id' => $row['id_position'],
				'title' => !empty($row['txt_var']) && isset($txt[$row['mod_title']]) ? $txt[$row['mod_title']] : (empty($row['id_clone']) ? (trim($row['mod_title']) == '' && isset($txt_names['mod']) ? $txt[$txt_names['mod']] : $row['mod_title']) : (trim($row['clone_title']) == '' && isset($txt_names['clone']) ? $txt[$txt_names['clone']] : $row['clone_title'])),
				'is_clone' => $is_clone,
				'id_clone' => $row['id_clone'],
				'modify' => '<a href="' . $scripturl . '?action=admin;area=dplayouts;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['dp_admin_modules_manage_modify'] . '</a>',
				'clone' => '<a href="' . $scripturl . '?action=admin;area=dplayouts;sa=clonemod' . (!$is_clone ? ';mod' : '') . ';xml;' . (!empty($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . ($is_clone ? $txt['dpmodule_declone'] : $txt['dpmodule_clone']) . '</a>',
			);

			$disabled_array_index = count($dp_modules['disabled']['modules']) - 1;

			// Fix the title of the Module if it is too long for viewing in the small box (no more than 21 characters).
			if($smcFunc['strlen']($dp_modules['disabled']['modules'][$disabled_array_index]['title']) >= 25)
				$dp_modules['disabled']['modules'][$disabled_array_index]['title'] = $smcFunc['substr']($dp_modules['disabled']['modules'][$disabled_array_index]['title'], 0, 22) . '...';
		}
		// Probably don't need to do this, but let's just be sure the array is wiped clean anyways!
		unset($txt_names);
	}

	if (!empty($dp_modules))
	{
		ksort($dp_modules);

		foreach ($dp_modules as $k => $dp_module_rows)
		{
			ksort($dp_modules[$k]);
			foreach ($dp_modules[$k] as $key => $dp)
				if (is_array($dp_modules[$k][$key]))
					foreach($dp_modules[$k][$key] as $pos => $mod)
					{
						if ($pos != 'modules' || !is_array($dp_modules[$k][$key][$pos]))
							continue;

						ksort($dp_modules[$k][$key][$pos]);
					}
		}

		$context['dp_columns'] = $dp_modules;
	}

	// The layout must not exist, probably in the session still from a previous DP installation, unset the session and fix the layout!
	if (!isset($context['dp_columns']))
	{
		unset($_SESSION['selected_layout']);
		unset($_SESSION['layouts']);
		redirectexit('action=admin;area=dplayouts;sa=dpmanlayouts');
	}

	$_SESSION['dlpIdpos'] = $context['dp_columns']['disabled']['id_layout_position'];

	// Add in the block_header class if needed.
	if (empty($context['has_dp_layout']))
	{
		$dpmodheight = 'dp_mod_header' . substr(strrchr($settings['theme_url'], "/"), 1);
		$context['html_headers'] .= '
	<style type="text/css">
		.block_header
		{
			height: ' . (!empty($modSettings[$dpmodheight]) ? (int) $modSettings[$dpmodheight] : '28') . 'px !important;
			margin-bottom: 0px !important;
		}
	</style>';
	}

	$context['html_headers'] .= '
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpManMods.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var dlpIdPos = "' . $_SESSION['dlpIdpos'] . '";
		var sessVar = "' . $context['session_var'] . '";
		var sessId = "' . $context['session_id'] . '";
		var errorString = "' . $txt['error_string'] . '";
		var cloneMade = "' . $txt['clone_made'] . '";
		var cloneDeleted = "' . $txt['clone_deleted'] . '";
		var modulePositionsSaved = "' . $txt['module_positions_saved'] . '";
		var clickToClose = "' . $txt['click_to_close'] . '";
	// ]]></script>';
}

/**
 * Saves the list of modules.
 *
 * @since 1.0
 */
function SaveDPLayout()
{
	global $smcFunc;

	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		fatal_lang_error('dp_no_permission', false);

	foreach ($_POST as $dpcol_idb => $dpcol_data)
	{
		$dpcol_id = str_replace('dpcol_', '', $dpcol_idb);

		if (!is_bool(strpos($dpcol_idb, 'dpcol')))
			foreach ($dpcol_data as $position => $id_position)
				$newLayout[$dpcol_id][$id_position] = $position;

		if (!is_array($_POST[$dpcol_idb]))
			// Doing the enabled checkboxes...
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}dp_layout_positions
				SET
					enabled = {int:enabled_value}
				WHERE id_layout_position = {int:dpcol_id}',
				array(
					'dpcol_id' => (int)str_replace('column_', '', $dpcol_idb),
					'enabled_value' => (!empty($_POST[$dpcol_idb]) ? 1 : 0),
				)
			);
	}

	if (!empty($newLayout))
		foreach ($newLayout as $update_layout_key => $update_layout_value)
		{
			$update_query = '';
			$update_params = array();
			$current_positions = array();
			foreach ($update_layout_value as $update_key => $update_value)
			{
				$update_query .= '
						WHEN {int:current_position' . $update_key . '} THEN {int:new_position' . $update_key . '}';

				$update_params = array_merge($update_params, array(
					'current_position' . $update_key => $update_key,
					'new_position' . $update_key => $update_value,
				));
				$current_positions[] = $update_key;
			}

			if ($update_layout_key == 0)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_positions
					SET
						position = CASE id_position ' . $update_query . '
							END,
						id_layout_position = 0
					WHERE id_position IN({array_int:current_positions})',
					array_merge($update_params, array(
						'current_positions' => $current_positions,
					))
				);
			else
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_positions AS dmp, {db_prefix}dp_layout_positions AS dlp
					SET
						dmp.position = CASE dmp.id_position ' . $update_query . '
							END,
						dmp.id_layout_position = {int:new_column}
					WHERE dmp.id_position IN({array_int:current_positions})',
					array_merge($update_params, array(
						'new_column' => $update_layout_key,
						'current_positions' => $current_positions,
					))
				);
		}

	// Yep, that's all, folks!
	die();
}

/**
 * Modifies all the settings and optional parameters for a module/clone.
 *
 * @since 1.0
 */
function ModifyModule()
{
	global $context, $txt, $helptxt, $sourcedir, $smcFunc, $modSettings;

	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	$context[$context['admin_menu_name']]['current_subsection'] = 'dpmanlayouts';
	$context['page_title'] = $txt['dp_modify_mod'];
	$context['sub_template'] = 'modify_modules';

	// Skipping if it doesn't exist.
	loadLanguage('DreamTemplates', '', false);
	
	// Used for grabbing stored variables and the file_input parameter type.
	require_once($sourcedir . '/Subs-DreamPortal.php');

	// We need to know if they are modifying an original module or a clone.  Clones will be a simple module=id_clone
	$context['modid'] = isset($_REQUEST['modid']) && !isset($_REQUEST['module']) ? (int) $_REQUEST['modid'] : '';
	$cloneid = isset($_REQUEST['module']) && !isset($_REQUEST['modid']) ? (int) $_REQUEST['module'] : '';

	// They aren't modifying anything, error!
	if(empty($context['modid']) && empty($cloneid))
		fatal_lang_error('dp_module_not_installed', false);

	// Differientiate between the 2 types of modules.
	$context['is_clone'] = !empty($context['modid']) ? false : true;
	$context['dp_modid'] = !empty($context['modid']) ? (int) $context['modid'] : (int) $cloneid;

	// Build the query structure accordingly....
	if (!empty($cloneid))
	{
		$query = 'SELECT dmc.id_clone AS modid, dmc.title, dmc.title_link, dmc.target, dmc.name, dmc.icon, ' . (empty($modSettings['dp_module_display_style']) ? 'dmc.minheight, dmc.minheight_type, ' : '') . 'dmc.header_display, dmc.id_template, dmc.groups, dmp.id_param, dmp.id_clone AS id_module, dmp.name AS parameter_name, dmp.type AS parameter_type, dmp.value AS parameter_value, dmp.fieldset AS parameter_fieldset, dt.id_template AS template_id, dt.name AS template_name
			FROM {db_prefix}dp_module_clones AS dmc
			LEFT JOIN {db_prefix}dp_templates AS dt ON (dt.type={int:zero})
			LEFT JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_clone = dmc.id_clone)
			WHERE dmc.id_clone = {int:id_module} AND dmc.id_member = {int:zero}';
	}
	else
	{
		$query = 'SELECT dm.id_module AS modid, dm.title, dm.txt_var AS txt_title, dm.target, dm.name, dm.icon, ' . (empty($modSettings['dp_module_display_style']) ? 'dm.minheight, dm.minheight_type, ' : '') . 'dm.title_link, dm.header_display, dm.id_template, dm.groups, dmp.id_param, dmp.id_module, dmp.name AS parameter_name, dmp.type AS parameter_type, dmp.value AS parameter_value, dmp.txt_var AS txt_value, dmp.fieldset AS parameter_fieldset, dt.id_template AS template_id, dt.name AS template_name
			FROM {db_prefix}dp_modules AS dm
			LEFT JOIN {db_prefix}dp_templates AS dt ON (dt.type={int:zero})
			LEFT JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.id_clone = {int:zero})
			WHERE dm.id_module={int:id_module}';
	}

	// Load up the general stuff in here for showing all of the settings for each parameter.
	$request = $smcFunc['db_query']('', $query, array('zero' => 0, 'id_module' => $context['dp_modid']));

	// Can't load any settings to modify if the module doesn't exist.
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('dp_module_not_installed', false);

	$context['config_params'] = array();
	$fieldset_params = array();
	$context['dp_fieldset'] = array();
	$context['mod_info'] = array();
	$dpmod_templates = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Load dp_modules or dp_module_clones columns.
		if (!isset($context['mod_info'][$row['modid']]))
		{
			$context['mod_info'][$row['modid']] = array(
				'titlebar' => isset($txt['dpmod_' . $row['name']]) ? 'dpmod_' . $row['name'] : '',
				'title' => !empty($row['txt_title']) && isset($txt[$row['title']]) ? $txt[$row['title']] : (!empty($row['title']) ? $row['title'] : $txt['dpmod_' . $row['name']]),
				'txt_title' => !empty($row['txt_title']) && isset($txt[$row['title']]) ? 1 : 0,
				'target' => $row['target'],
				'icon' => moduleLoadIcon($row['icon']),
				'title_link' => $row['title_link'],
				'header_display' => !empty($row['header_display']) ? $row['header_display'] : 0,
				'name' => $row['name'],
				'help' => isset($helptxt['dpmod_' . $row['name']]) ? 'dpmod_' . $row['name'] : '',
				'info' => isset($txt['dpmodinfo_' . $row['name']]) ? $txt['dpmodinfo_' . $row['name']] : '',
				'id_template' => !empty($row['id_template']) ? (int) $row['id_template'] : 0,
				'groups' => isset($row['groups']) && $row['groups'] != '' ? ListGroups($row['groups'] == '-2' ? array('-2') : explode(',', $row['groups'])) : array(),
			);

			// This setting only needed for Block Style Layout mode.
			if (empty($modSettings['dp_module_display_style']))
				$context['mod_info'][$row['modid']] += array(
					'minheight' => !empty($row['minheight']) ? (int) $row['minheight'] : 0,
					'minheight_type' => !empty($row['minheight_type']) ? (int) $row['minheight_type'] : 0,
				);
		}

		// Load up all the module templates
		if (isset($row['template_name']))
			if (!isset($dpmod_templates[$row['template_name']]))
				$dpmod_templates[$row['template_name']] = array(
					'id' => $row['template_id'],
					'txt' => isset($txt['dptemp_' . $row['template_name'] . '_title']) ? $txt['dptemp_' . $row['template_name'] . '_title'] : $txt['dptemp_' . $row['template_name']],
				);

		// Loading up all possible parameters
		if (!isset($context['config_params'][$row['id_param']]) && !empty($row['id_param']))
		{
			$context['config_params'][$row['id_param']] = array();
			$color_picker = false;

			if (substr(ltrim($row['parameter_type']), 0, 6) == 'color;')
			{
				$color = explode(';', trim($row['parameter_type']));

				// Set the parameter type to color!
				$row['parameter_type'] = 'color';

				if (!empty($color[1]))
				{
					$color_picker = true;
					$color_vars = trim($color[1]);
				}
			}
			else
			{
				// No CAPS and/or spaces, you filthy Dream Module Customizers ;)
				$row['parameter_type'] = trim(strtolower($row['parameter_type']));
			}

			// Get all current files for this module if they exist.  Only looping through this 1 time, so we need to get it all!
			if ($row['parameter_type'] == 'file_input')
			{
				// Let the form know we have a file_input type here.
				$context['dp_file_input'] = true;

				// Need to know how many files they uploaded thus far.
				$result = @$smcFunc['db_query']('', '
					SELECT id_file, id_param, filename, file_hash
					FROM {db_prefix}dp_module_files
					WHERE id_param = {int:id_param} AND file_type = {int:not_thumb}',
					array(
						'id_param' => $row['id_param'],
						'not_thumb' => 0,
					)
				);

				$temp = array();
				while($rowData = $smcFunc['db_fetch_assoc']($result))
					$temp[$rowData['id_file']] = $rowData;

				$smcFunc['db_free_result']($result);

				// Sorts the array, much quicker than doing an ORDER BY.
				ksort($temp);

				// Get all files uploaded already.  Grabbing from the table.
				foreach($temp as $rowTemp)
				{
					$context['current_files'][$rowTemp['id_param']][] = array(
						'name' => $rowTemp['filename'],
						'id' => $rowTemp['id_file'],
						'file_hash' => $rowTemp['file_hash'],
					);
				}
			}
			// Prepare the select box values if we have any.
			if ($row['parameter_type'] == 'select')
			{
				$select_params = array();
				$options = array();
				if (!empty($row['parameter_value']))
				{
					$select_params = explode(':', $row['parameter_value']);
					$default = $select_params[0];
					$options = explode(';', $select_params[1]);
				}
				else
					$default = '';
			}

			// Prepare the db_select parameter type.
			if ($row['parameter_type'] == 'db_select')
			{
				if (!empty($row['parameter_value']))
				{
					$db_options = explode(':', $row['parameter_value']);
					$db_select_options = explode(';', $row['parameter_value']);
					$db_custom = isset($db_options[2]) && stristr(trim($db_options[2]), 'custom');

					if (isset($db_options[0], $db_options[1]))
					{
						$db_input = explode(';', $db_options[0]);
						$db_output = explode(';', $db_options[1]);

						if (isset($db_input[0], $db_input[1], $db_output[0], $db_output[1]))
						{
							$db_select = array();
							$db_select_params = '';
							$db_selected = $db_input[0];
							$db_select['select2'] = $db_input[1];

							if (isset($db_select_options[0], $db_select_options[1], $db_select_options[2]))
							{
								unset($db_select_options[0]);
								$db_select_params = implode(';', $db_select_options);
							}

							if (stristr(trim($db_output[0]), '{db_prefix}'))
							{
								$db_select['table'] = $db_output[0];
								$db_select['select1'] = $db_output[1];
							}
							elseif (stristr(trim($db_output[1]), '{db_prefix}'))
							{
								$db_select['table'] = $db_output[1];
								$db_select['select1'] = $db_output[0];
							}
							else
								unset($db_select);
						}
					}
				}
			}

			// What about any BBC selection boxes?
			if ($row['parameter_type'] == 'list_bbc')
			{
				$bbcChoice = explode(';', $row['parameter_value']);
				if (!empty($bbcChoice))
				{
					// What are the options, eh?
					$temp = parse_bbc(false);
					$bbcTags = array();
					foreach ($temp as $tag)
						$bbcTags[] = $tag['tag'];

					$bbcTags = array_unique($bbcTags);
					$totalTags = count($bbcTags);

					// The number of columns we want to show the BBC tags in.
					$numColumns = isset($context['num_bbc_columns']) ? $context['num_bbc_columns'] : 3;

					// Start working out the context stuff.
					$context['config_params'][$row['id_param']]['bbc_columns'] = array();
					$tagsPerColumn = ceil($totalTags / $numColumns);

					$col = 0; $i = 0;
					$bbc_columns = array();
					foreach ($bbcTags as $tag)
					{
						if ($i % $tagsPerColumn == 0 && $i != 0)
							$col++;

						$bbc_columns[$col][] = array(
							'tag' => $tag,
							'show_help' => isset($helptxt[$tag]),
						);

						$i++;
					}

					// Now put whatever BBC options we may have into context too!
					$bbc_sections = array();
					foreach ($bbcChoice as $bbc)
					{
						$bbc_sections[$bbc] = array(
							'title' => isset($txt['bbc_title_' . $bbc]) ? $txt['bbc_title_' . $bbc] : $txt['bbcTagsToUse_select'],
							'disabled' => empty($modSettings['bbc_disabled_' . $bbc]) ? array() : $modSettings['bbc_disabled_' . $bbc],
							'all_selected' => empty($modSettings['bbc_disabled_' . $bbc]),
						);
					}

					$all_selected = $bbcChoice === $bbcTags;
				}
			}

			// Prepare any list_groups parameter types that we might have.
			if ($row['parameter_type'] == 'list_groups' || $row['parameter_type'] == 'checklist')
			{
				$list_options = array();
				$check_strings = array();
				$checked = array();
				$unallowed = array();
				$order = array();
				$allow_order = false;

				if (trim($row['parameter_value']) != '')
				{
					if ($row['parameter_type'] == 'list_groups')
						$list_options = explode(':', trim(strtolower($row['parameter_value'])));
					else
					{
						$list_options = explode(':', trim($row['parameter_value']));
						if (isset($list_options[2]) && stristr(trim($list_options[2]), 'order'))
							$list_options[2] = strtolower($list_options[2]);
					}

					if (!empty($list_options) && isset($list_options[0]) && trim($list_options[0]) != '' && !stristr(trim($list_options[0]), 'order'))
					{
						if (isset($list_options[1]) && !stristr(trim($list_options[1]), 'order') && $row['parameter_type'] == 'list_groups')
						{
							$checked = explode(',', trim($list_options[0]));
							$unallowed = explode(',', trim($list_options[1]));

							// Should probably check for valid integer types also.
							foreach($checked as $group => $id)
								if (in_array($id, $unallowed))
									unset($checked[$group]);

							if (count($checked) >= 1)
								$checked = array_values($checked);
							else
								$checked = array('-2');
						}
						elseif (isset($list_options[1]) && $row['parameter_type'] == 'checklist')
						{
							$checked = explode(',', trim($list_options[0]));
							$check_strings = explode(';', trim($list_options[1]));

							// Any checked that are not in the list?  Uncheck all.
							foreach($checked as $check)
								if (!isset($check_strings[$check]))
									$checked = array('-2');

							if (isset($list_options[2]))
							{
								$get_order = explode(';', $list_options[2]);
								if (!empty($get_order))
								{
									if (trim($get_order[0]) == 'order')
									{
										$allow_order = true;
										if (!empty($get_order[1]) && trim($get_order[1]) != '')
										{
											// Grab the order first and than get the remaining strings.
											$order_keys = explode(',', trim($get_order[1]));
											$actual_keys = array_keys($check_strings);
											foreach ($order_keys as $val)
												if (isset($actual_keys[$val]))
													unset($actual_keys[$val]);

											$order = array_merge($order_keys, $actual_keys);
										}
										else
											$order = array_keys($check_strings);
									}
								}
							}
						}
						else
							$checked = explode(',', trim($list_options[0]));

						// Let's find out if they can be ordered or not.
						if (isset($list_options[2]) && $row['parameter_type'] != 'checklist')
						{
							$get_order = explode(';', $list_options[2]);

							if (!empty($get_order))
							{
								if (trim($get_order[0]) == 'order')
								{
									$allow_order = true;
	
									if (!empty($get_order[1]))
										$order = explode(',', trim($get_order[1]));
								}
							}
						}
						elseif (!isset($list_options[2]) && isset($list_options[1]) && $row['parameter_type'] == 'list_groups')
						{
							$get_order = explode(';', $list_options[1]);

							if (!empty($get_order))
								if (trim($get_order[0]) == 'order')
								{
									$allow_order = true;

									if (isset($get_order[1]))
										$order = explode(',', trim($get_order[1]));
								}
						}
					}
					else
						$checked = array('-2');
				}
				// Nothing checked, if no parameter value somehow is completely empty!
				else
					$checked = array('-2');
			}

			// Prepare the File input values if we have any.
			if ($row['parameter_type'] == 'file_input')
			{
				if (!empty($row['parameter_value']))
				{
					$file_input = explode(':', $row['parameter_value']);

					$file_count = isset($file_input[0]) && !empty($file_input[0]) ? (int) $file_input[0] : 0;

					$mimes = !empty($file_input[1]) ? $file_input[1] : 'image/gif;image/jpeg;image/png;image/bmp';
					$dimensions = isset($file_input[2]) ? (string) $file_input[2] : '';
				}
				// We can't have empty now can we?
				else
				{
					$file_count = 1;
					$mimes = 'image/gif;image/jpeg;image/png;image/bmp';
					$dimensions = '';
				}
			}

			// Build up the parameters array
			$context['config_params'][$row['id_param']] = array(
				'id' => $row['id_param'],
				'modid' => $context['dp_modid'],
				'label_id' => 'dp_' . $row['id_param'] . '_' . $row['parameter_name'],
				'label' => 'dpmod_' . $row['name'] . '_' . $row['parameter_name'],
				'bbc_columns' => $row['parameter_type'] == 'list_bbc' ? $bbc_columns : '',
				'bbc_sections' => $row['parameter_type'] == 'list_bbc' ? $bbc_sections : '',
				'bbc_all_selected' => $row['parameter_type'] == 'list_bbc' ? $all_selected : false,
				'file_mimes' => $row['parameter_type'] == 'file_input' ? (string) $mimes : '',
				'file_count' => $row['parameter_type'] == 'file_input' ? (int) $file_count : '',
				'file_dimensions' => $row['parameter_type'] == 'file_input' ? (string) $dimensions : '',
				'is_fieldset' => $row['parameter_type'] == 'fieldset',
				'help' => isset($helptxt['dpmod_' . $row['name'] . '_' . $row['parameter_name']]) ? 'dpmod_' . $row['name'] . '_' . $row['parameter_name'] : '',
				'db_select_options' => $row['parameter_type'] == 'db_select' ? (isset($db_select) ? ListDbSelects($db_select, $row['id_param']) : array()) : array(),
				'db_selected' => $row['parameter_type'] == 'db_select' ? $db_selected : '',
				'db_select_custom' => $row['parameter_type'] == 'db_select' ? $db_custom : false,
				'name' => $row['parameter_type'] == 'rich_edit' ? 'dp_' . $row['id_param'] . '_' . $row['parameter_name'] : $row['name'] . '_' . $row['parameter_name'],
				'param_name' => $row['parameter_name'],
				'size' => $row['parameter_type'] == 'int' ? '2' : ($row['parameter_type'] == 'large_text' ? '4' : ($row['parameter_type'] == 'small_text' || $row['parameter_type'] == 'color' ? '15' : '30')),
				'select_options' => $row['parameter_type'] == 'select' ? $options : ($row['parameter_type'] == 'list_boards' ? ListBoards() : ''),
				'check_order' => $row['parameter_type'] == 'list_groups' || $row['parameter_type'] == 'checklist' ? $allow_order : '',
				'options' => $row['parameter_type'] == 'select' && isset($select_params[1]) ? $select_params[1] : ($row['parameter_type'] == 'db_select' && isset($db_select_params) ? $db_select_params : ''),
				'select_value' => $row['parameter_type'] == 'select' ? (int) $default : $row['parameter_type'] == 'list_boards' ? $row['parameter_value'] : '',
				'type' => $row['parameter_type'],
				'color_vars' => $color_picker && !empty($color_vars) ? $color_vars : '',
				'value' => !empty($row['txt_value']) ? $txt[$row['parameter_value']] : ($row['parameter_type'] == 'html' ? html_entity_decode($row['parameter_value'], ENT_QUOTES) : $row['parameter_value']),
				'txt_value' => !empty($row['txt_value']) ? 1 : 0,
			);

				// Let's make sure each parameter gets sorted correctly!!
				ksort($context['config_params']);
				foreach($context['config_params'] as $key => $sort);
					ksort($context['config_params'][$key]);
					
				// Build the Group list or Checklist.
				$context['config_params'][$row['id_param']]['check_value'] = '';

				if ($row['parameter_type'] == 'list_groups')
				{
					if(!empty($unallowed))
					{
						$context['config_params'][$row['id_param']]['check_value'] = implode(',', $unallowed);
						if ($allow_order)
							$context['config_params'][$row['id_param']]['check_value'] .= ':order';
					}
					elseif(empty($unallowed))
						if ($allow_order)
							$context['config_params'][$row['id_param']]['check_value'] = ':order';
				}
				elseif ($row['parameter_type'] == 'rich_edit')
				{
					// Needed for the editor.
					require_once($sourcedir . '/Subs-Editor.php');

					// Now create the editor.
					$editorOptions = array(
						'id' => $context['config_params'][$row['id_param']]['label_id'],
						'value' => !empty($row['txt_value']) ? $txt[$row['parameter_value']] : $row['parameter_value'],
						'labels' => array(),
						'height' => '175px',
						'width' => '100%',
						'preview_type' => 2,
						'rich_active' => false,
					);

					create_control_richedit($editorOptions);
					$context['config_params'][$row['id_param']]['post_box_name'] = $editorOptions['id'];

					$context['controls']['richedit'][$context['config_params'][$row['id_param']]['label_id']]['rich_active'] = false;
				}
				elseif($row['parameter_type'] == 'checklist')
				{
					if (!empty($check_strings))
					{
						$context['config_params'][$row['id_param']]['check_value'] = implode(';', $check_strings);
						if ($allow_order)
							$context['config_params'][$row['id_param']]['check_value'] .= ':order';
					}
				}

				$context['config_params'][$row['id_param']]['check_options'] = array();

				if ($row['parameter_type'] == 'list_groups' && !empty($checked))
					$context['config_params'][$row['id_param']]['check_options'] = ListGroups($checked, $unallowed, $order, $row['id_param']);
				elseif($row['parameter_type'] == 'checklist' && !empty($checked))
					$context['config_params'][$row['id_param']]['check_options'] = ListChecks($checked, $check_strings, $order, $row['name'] . '_' . $row['parameter_name'], $row['id_param']);

			// Handle fieldsets here!
			if (!empty($row['parameter_fieldset']) && !isset($fieldset_params[$row['id_param']]))
			{
				$fieldset_params[$row['id_param']] = $context['config_params'][$row['id_param']];

				// Unsetting this won't help us here, so we create an empty array for this instead!
				$context['config_params'][$row['id_param']] = array();
			}
		}
	}
	$smcFunc['db_free_result']($request);

	// Load up any fieldsets we might have within a separate array here!
	foreach($context['config_params'] as $id_param => $param)
	{
		if (empty($context['config_params'][$id_param]))
			continue;

		if ($param['is_fieldset'])
		{
			// Store the parameters that are a part of a fieldset parameter.
			$fieldset = explode(":", $param['value']);

			// Make sure the order of the parameters in the fieldset are correctly ordered, using $f_key!
			foreach($fieldset as $f_key => $f_name)
			{
				foreach($fieldset_params as $id => $config_params)
					if ($config_params['param_name'] == $f_name)
						$context['dp_fieldset'][$id_param]['fieldset'][$f_key] = array('id' => $id, 'param' => $config_params);
			}
		}
	}

	// Putting all templates in Ascending order!
	if (!empty($dpmod_templates))
		uasort($dpmod_templates, create_function('$a,$b','return strnatcmp($a[\'txt\'], $b[\'txt\']);'));

	// Add the default template at the top of the list so it is easy to find and select!
	$context['dpmod_templates'] = array_merge(array('default' => array('id' => 0, 'txt' => $txt['dptemp_default'])), $dpmod_templates);

	// Saving?
	if (isset($_REQUEST['save']))
	{
		checkSession();

		if (!isset($context['dp_modid']) || empty($context['dp_modid']))
			fatal_lang_error('dp_module_not_installed', false);

		// Getting the db stored name of the module/clone.
		$module_name = (string) $_POST['modname'];

		// Get the title, target, target link, and the icon.
		$module_title = (string) html_entity_decode($_REQUEST['module_title'], ENT_QUOTES);

		if ($module_title == $txt['dpmod_' . $module_name])
			$module_title = '';

		$query_array = array(
			'module_title' => $smcFunc['htmlspecialchars'](un_htmlspecialchars($module_title)),
			'module_target' => !empty($_POST['module_link_target']) ? (int) $_POST['module_link_target'] : 0,
			'module_link' => (string) $_POST['module_link'],
			'module_icon' => isset($_POST['file']) ? (string) $_POST['file'] : (isset($_POST['cat']) ? (string) $_POST['cat'] : ''),
			'id_module' => $context['dp_modid'],
			'module_header_display' => !empty($_POST['module_header']) ? (int) $_POST['module_header'] : 0,
			'module_template' => !empty($_POST['module_template']) ? (int) $_POST['module_template'] : 0,
			'module_groups' => isset($_POST['groups']) && $_POST['groups'] != '' ? implode(',', $_POST['groups']) : '-2',
		);

		// Add in the min-height setting if we are in Block Style Layouts mode.
		if (empty($modSettings['dp_module_display_style']))
			$query_array += array(
				'module_minheight' => !empty($_POST['module_minheight']) ? (int) $_POST['module_minheight'] : 0,
				'module_minheight_type' => !empty($_POST['module_minheight_type']) ? (int) $_POST['module_minheight_type'] : 0,
			);

		if (!empty($context['mod_info'][$context['dp_modid']]['txt_title']))
			$query_array += array('module_txt_title' => 0);
			
		// Build the query.
		$query = 'UPDATE {db_prefix}' . (!empty($context['modid']) ? 'dp_modules' : 'dp_module_clones') . '
		SET title = {string:module_title}, title_link = {string:module_link},
			target = {int:module_target}, icon = {string:module_icon},' . (empty($modSettings['dp_module_display_style']) ? ' minheight = {int:module_minheight}, minheight_type = {int:module_minheight_type},' : '') . '
			header_display = {int:module_header_display}, id_template = {int:module_template},
			groups = {string:module_groups}' . (!empty($context['mod_info'][$context['dp_modid']]['txt_title']) ? ', txt_var = {int:module_txt_title}' : '') . '
		WHERE ' . (!empty($context['modid']) ? 'id_module' : 'id_clone') . ' = {int:id_module}';

		// Update the title, link and icon.
		$smcFunc['db_query']('', $query, $query_array);

		// Need to get all values of all hidden inputs
		if (isset($_POST['modparams_count']) && !empty($_POST['modparams_count']))
		{
			$params_count = (int) $_POST['modparams_count'];
			$param_names = array();
			$param_types = array();
			$param_txt_values = array();
			$txt_value = false;

			for($x=0; $x<=$params_count - 1; $x++)
			{
				if (isset($_POST['param_name' . ($x+1)]))
				{
					$param_names[intval($_POST['param_id' . ($x+1)])] = (string) $_POST['param_name' . ($x+1)];
					$param_types[intval($_POST['param_id' . ($x+1)])] = (string) $_POST['param_type' . ($x+1)];
					$param_txt_values[intval($_POST['param_id' . ($x+1)])] = (int) $_POST['param_txt_value' . ($x+1)];
				}
			}

			if (in_array(1, $param_txt_values))
				$txt_value = true;
			
			// Ok, check if we have a file_input type somewhere in here.
			if (in_array('file_input', $param_types))
			{
				// Set the file directory.
				$module_dir = $context['dpmod_files_dir'] . $_POST['modname'];

				// Ohhh, folder.... Where are you?
				if (!is_dir($module_dir))
					if (!mkdir($module_dir, 0755, true))
						fatal_error($txt['mod_folder_missing'] . ' /dreamportal/module_files/' . $_POST['modname'], false);

				// Protect the Folder.  Safety First!
				if (!file_exists($module_dir . '/index.php'))
					copy($context['dpmod_files_dir'] . 'index.php', $module_dir . '/index.php');

				// Secure it!
				if (!file_exists($module_dir . '/.htaccess'))
					copy($context['dpmod_files_dir'] . '.htaccess', $module_dir . '/.htaccess');

				// Begin to build the file input array.
				if (!isset($fileOptions))
					$fileOptions = array(
						'id_member' => 0,
						'folderpath' => $module_dir,
						'mod_id' => $context['dp_modid'],
					);
			}

			foreach($param_names as $id => $value)
			{
				if (!isset($filid))
					$filid = 0;

				$filid++;

				// Do we have any file inputs?
				if ($param_types[$id] == 'file_input')
				{
					$quantity = !empty($context['current_files'][$id]) ? (int) $context['current_files'][$id] : 0;
					$files = array();

					// Store the files parameter id value
					$fileOptions['id_param'] = $id;

					if (isset($files[$id-1]))
						unset($files[$id-1]);

					$files[$id] = array();

					// Getting the mime types, need the $filid variable.
					if (isset($_POST['file_mimes' . $filid]) && !empty($_POST['file_mimes' . $filid]))
						$files[$filid]['mimetypes'] = explode(';', (string) $_POST['file_mimes' . $filid]);
					else
						$files[$filid]['mimetypes'] = array('image/gif', 'image/jpeg', 'image/png', 'image/bmp');

					// Get the file count.
					if (isset($_POST['file_count' . $filid]))
						$files[$filid]['filecount'] = !empty($_POST['file_size' . $filid]) ? (int) $_POST['file_size' . $filid] : 0;

					// Get the dimensions if exists.
					if (isset($_POST['file_dimensions' . $filid]) && !empty($_POST['file_dimensions' . $filid]))
					{
						$files[$filid]['dimensions'] = !empty($_POST['file_dimensions' . $filid]) ? $_POST['file_dimensions' . $filid] : '';
						$width_height = explode(';', $files[$filid]['dimensions']);
					}

					// Get the dimensions to resize to, if specified, otherwise, don't bother resizing.
					if (!empty($width_height))
					{
						$resize = array();

						foreach($width_height as $dimension)
						{
							if (substr(trim(strtolower($dimension)), 0, 6) == 'width=')
							{
								$resize['width'] = substr($dimension, 6);
								continue;
							}
							elseif(substr(trim(strtolower($dimension)), 0, 7) == 'height=')
							{
								$resize['height'] = substr($dimension, 7);
								continue;
							}
							elseif(substr(trim(strtolower($dimension)), 0, 6) == 'strict')
							{
								$resize['is_strict'] = true;
								continue;
							}
						}
					}

					// Handle the removal of any current files within a parameter.
					if (isset($_POST['file_del' . $filid]) && !empty($_POST['file_del' . $filid]))
					{
						$del_temp = array();
						foreach ($_POST['file_del' . $filid] as $i => $dummy)
							$del_temp[$i] = (int) $dummy;

						foreach ($context['current_files'][$id] as $k => $dummy)
						{
							if (!in_array($dummy['id'], $del_temp))
							{
								// Let's remove the file first.
								$file = getFilename($dummy['name'], $dummy['id'], $module_dir, false, $dummy['file_hash']);
								@unlink($file);

								// Remove any thumbnail associations.
								$result = $smcFunc['db_query']('', '
									SELECT dmf.id_thumb, thumb.filename, thumb.file_hash
									FROM {db_prefix}dp_module_files AS dmf
									LEFT JOIN {db_prefix}dp_module_files AS thumb ON (thumb.id_file = dmf.id_thumb)
									WHERE dmf.id_file = {int:id_file} AND dmf.id_param = {int:id_param} AND dmf.file_type = {int:is_zero}
									LIMIT 1',
									array(
										'id_param' => $id,
										'is_zero' => 0,
										'id_file' => $dummy['id'],
								));

								list ($id_thumb, $thumb_name, $thumb_hash) = $smcFunc['db_fetch_row']($result);
								$smcFunc['db_free_result']($result);

								$thumb = array('is_zero' => 0);
								$query = 'id_file = {int:id_file} AND id_param = {int:id_param} AND file_type = {int:is_zero} LIMIT 1';

								if (!empty($id_thumb))
								{
									$thumb = array('id_thumb' => $id_thumb);
									$query = 'id_param = {int:id_param} AND (id_file = {int:id_file} OR id_file = {int:id_thumb}) LIMIT 2';
									// Delete the thumbnail.
									$file_thumb = getFilename($thumb_name, $id_thumb, $module_dir, false, $thumb_hash);
									unlink($file_thumb);
								}

								// Now Remove it from the database.
								$smcFunc['db_query']('', 'DELETE FROM {db_prefix}dp_module_files
								WHERE ' . $query,
								array_merge($thumb, array('id_file' => $dummy['id'], 'id_param' => (int) $id)));
							}
						}
					}

					// Are we adding any new files?
					if (isset($_FILES[$value]) && !empty($_FILES[$value]['name']))
					{
						// Getting all files for each parameter.
						foreach ($_FILES[$value]['tmp_name'] as $n => $dummy)
						{
							// Empty? Don't bother.
							if ($_FILES[$value]['name'][$n] == '')
								continue;

							// Is the path writable?
							if (!is_writable($module_dir))
								fatal_lang_error('module_files_no_write', false);

							// Problem uploading?
							if (!is_uploaded_file($_FILES[$value]['tmp_name'][$n]) || (@ini_get('open_basedir') == '' && !file_exists($_FILES[$value]['tmp_name'][$n])))
								fatal_lang_error('module_file_timeout', false);

							// Fix for PSD files.
							if ((in_array('image/psd', $files[$filid]['mimetypes']) || strtolower($files[$filid]['mimetypes'][0]) == 'all') && ($_FILES[$value]['type'][$n] == 'application/octet-stream' || $_FILES[$value]['type'][$n] == 'application/octetstream'))
							{
								// Get the extension of the file.
								$file_extension = strtolower(substr(strrchr($_FILES[$value]['name'][$n], '.'), 1));

								if ($file_extension == 'psd')
								{
									$psd = true;

									// if no size, than it's not a valid PSD file.
									$size = @getimagesize($_FILES[$value]['tmp_name'][$n]);

									if (empty($size))
										unset($psd);
								}
							}

							// Check for PHP Files
							if ((in_array('application/x-httpd-php', $files[$filid]['mimetypes']) || strtolower($files[$filid]['mimetypes'][0]) == 'all') && ($_FILES[$value]['type'][$n] == 'application/octet-stream' || $_FILES[$value]['type'][$n] == 'application/octetstream'))
							{
								$file_extension = strtolower(substr(strrchr($_FILES[$value]['name'][$n], '.'), 1));

								if ($file_extension == 'php')
								{
									// Reading the current php file to make sure it's a PHP File.
									$fo = fopen($_FILES[$value]['tmp_name'][$n], 'rb');
									while (!feof($fo))
									{
										$fo_output = fgets($fo, 16384);

										// look for a match
										if ((substr($fo_output, 0, 5) == '<?php' || substr($fo_output, 0, 2) == '<?') && substr($fo_output, 0, 5) != '<?xml')
										{
											$php = true;
											break;
										}
									}
									fclose($fo);
								}
							}

							// Try and get the mime if you dare!
							if (!isset($psd) && !isset($php))
							{
								if (!in_array($_FILES[$value]['type'][$n], $files[$filid]['mimetypes']) && strtolower($files[$filid]['mimetypes'][0]) != 'all')
									fatal_error(sprintf($txt['module_wrong_mime_type'], $_FILES[$value]['type'][$n]), false);
								else
									// Store the mime.
									$fileOptions['file_mime'] = $_FILES[$value]['type'][$n];
							}
							elseif (isset($php))
							{
								// Do 1 more check here.
								if (isPHPFile($_FILES[$value]['tmp_name'][$n]))
									$fileOptions['file_mime'] = 'application/x-httpd-php';
								else
									$fileOptions['file_mime'] = $_FILES[$value]['type'][$n];
							}
							else
								$fileOptions['file_mime'] = 'image/psd';

							$quantity++;

							// Check the filecount quantity.
							if (!empty($files[$filid]['filecount']) && $quantity > $files[$filid]['filecount'])
								fatal_lang_error('module_file_limit', false);

							// These need to be set on a per file basis.
							$fileOptions['id_file'] = 0;
							$fileOptions['name'] = $_FILES[$value]['name'][$n];
							$fileOptions['tmp_name'] = $_FILES[$value]['tmp_name'][$n];
							$fileOptions['size'] = $_FILES[$value]['size'][$n];

							// Check if there are dimensions defined and set this accordingly.
							if (!empty($files[$filid]['dimensions']))
							{
								// All Valid image mime types.
								$image_mimes = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/tiff', 'image/psd');

								// Eureka, we have a compatible image mime.
								if (in_array($_FILES[$value]['type'][$n], $image_mimes))
								{
									// Check if it must be resized or not.
									if(!empty($resize['width']) || !empty($resize['height']))
									{
										$fileOptions['resizeWidth'] = !empty($resize['width']) ? $resize['width'] : 0;
										$fileOptions['resizeHeight'] = !empty($resize['height']) ? $resize['height'] : 0;
										$fileOptions['strict'] = !empty($resize['is_strict']) ? true : false;
									}
								}
							}

							// Get all of the allowed extensions for this mime type.
							$fileOptions['fileExtensions'] = AllowedFileExtensions($fileOptions['file_mime']);
							
							if (!createFile($fileOptions))
							{
								// Error Somewhere...
								if (in_array('files_no_write', $fileOptions['errors']))
									fatal_lang_error('module_folderpath_error', true);
								if (in_array('could_not_upload', $fileOptions['errors']))
									fatal_lang_error('restricted_unexists', true);
								if (in_array('file_timeout', $fileOptions['errors']))
									fatal_lang_error('file_timeout', false);
								if (in_array('bad_extension', $fileOptions['errors']))
									fatal_lang_error('file_bad_extension', false);
							}

							// Free up some unneccessary stuff!
							if (isset($fileOptions['resizeWidth']))
								unset($fileOptions['resizeWidth']);
							if (isset($fileOptions['resizeHeight']))
								unset($fileOptions['resizeHeight']);
							if (isset($fileOptions['strict']))
								unset($fileOptions['strict']);
							unset($fileOptions['file_mime']);
							unset($fileOptions['id_file']);
							unset($fileOptions['name']);
							unset($fileOptions['tmp_name']);
							unset($fileOptions['size']);
						}
					}
					// Are we done with the file_input?  And I was just getting warmed up ;)
					continue;
				}

				// Dealing with Listed Group Checkboxes or Checklists now!
				if ($param_types[$id] == 'list_groups' || $param_types[$id] == 'checklist')
				{
					$checked_groups = array();
					$unallowed_groups = array();
					$check_strings = array();
					$conval = array();
					$ordered = false;
					$group_order = '';

					$listgroup = $param_types[$id] == 'list_groups' ? true : false;
					$checkid = $param_types[$id] == 'list_groups' ? 'grp' : 'chk';
					$checkname = $param_types[$id] == 'list_groups' ? 'groups' : 'checks';

					if(isset($_POST['conval' . $checkid . '_' . $filid]))
						$conval = explode(':', $_POST['conval' . $checkid . '_' . $filid]);

					if(!empty($conval))
					{
						if (isset($conval[0]) && trim(strlen($conval[0])) >= 1 && !empty($conval[1]))
						{
							$ordered = $conval[1] == 'order' ? true : false;

							if ($listgroup)
								$unallowed_groups = explode(',', $conval[0]);
							else
								$check_strings = explode(';', $conval[0]);
						}
						else
						{
							// We are dealing with only 1 value in the array.  Could be either or.  So check it.
							if ($conval[0] == 'order' && $listgroup)
							{
								$ordered = true;
							}
							elseif (trim(strlen($conval[0])) >= 1 && $conval[0] != 'order' && $listgroup)
								$unallowed_groups = explode(',', $conval[0]);
							elseif (!$listgroup)
								$check_strings = explode(';', $conval[0]);
						}
					}

					// We should know this by now.
					if ($ordered)
					{
						if (isset($_POST['order' . $checkid . '_' . $filid]) && $_POST['order' . $checkid . '_' . $filid] != '')
							$check_order = (string) $_POST['order' . $checkid . '_' . $filid];
						else
						{
							$ordered = false;
							$check_order = '';
						}
					}

					// Build the checked list.
					$checked_list = array();

					if (!empty($_POST[$checkname . $filid]))
						foreach ($_POST[$checkname . $filid] as $checks)
						{
							if ($listgroup)
							{
								if (!in_array($checks, $unallowed_groups))
									$checked_list[] = (int) $checks;
							}
							else
								$checked_list[] = (int) $checks;
						}
					else
						$checked_list = array('-2');

					// Just in case all checked are $unallowed.  Don't ask how!
					if (empty($checked_list))
						$checked_list = array('-2');

					$checkStr = !empty($checked_list) ? implode(',', $checked_list) : '-2';

					// Build the list of either unallowed or check strings
					if ($listgroup)
						$the_list = !empty($unallowed_groups) ? ':' . implode(',', $unallowed_groups) : '';
					else
					{
						// If no first string, unset.
						if (!isset($check_strings[0]) || strlen(trim($check_strings[0])) <= 0)
							unset($check_strings);

						$the_list = !empty($check_strings) ? ':' . implode(';', $check_strings) : '';
					}

					// Put it all together now!
					$check_value = $checkStr . $the_list . ($ordered ? ':order' . ($check_order != '' ? ';' . $check_order : '') : '');
				}
				else
				{
					// Clear any previous checklists if we have none for this parameter setting.
					if (isset($check_value))
						unset($check_value);

					$daValue = isset($_POST[$value]) ? ($param_types[$id] == 'int' || $param_types[$id] == 'check' ? (empty($_POST[$value]) || intval($_POST[$value]) < 0 ? '0' : (int) $_POST[$value]) : (string) $_POST[$value]) : '';
				}
				// Handle all selects.
				if ($param_types[$id] == 'select' || $param_types[$id] == 'db_select')
				{
					if (isset($_POST['param_opts' . $id]))
						$param_opts = (string) $_POST['param_opts' . $id];

					if (isset($param_opts) && strlen($param_opts) > 0)
						$daValue = $daValue . ($param_types[$id] == 'db_select' ? ';' : ':') . $param_opts;
				}

				if ($param_types[$id] == 'db_select' && isset($_POST[$value . '_db_custom']))
				{
					$new_db_vals = array();
					foreach ($_POST[$value . '_db_custom'] as $insert_value)
					{
						$insert_value = $smcFunc['htmlspecialchars'](un_htmlspecialchars(strip_tags(trim($insert_value))));

						if (!empty($insert_value))
							if (count($_POST[$value . '_db_custom']) == 1)
								$new_db_vals[] = $insert_value;
							else
								$new_db_vals[] = array($insert_value);
					}

					// Now let's get the column and table for our insert.
					if (!empty($daValue))
					{
						$db_options = explode(':', $daValue);
						$db_select_options = explode(';', $row['parameter_value']);
						$db_custom = isset($db_options[2]) && stristr(trim($db_options[2]), 'custom');

						if (isset($db_options[0], $db_options[1]))
						{
							$db_input = explode(';', $db_options[0]);
							$db_output = explode(';', $db_options[1]);

							if (isset($db_input[0], $db_input[1], $db_output[0], $db_output[1]))
							{
								$db_select = array();
								$db_select_params = '';
								$db_selected = $db_input[0];
								$db_select['select2'] = $db_input[1];

								if (isset($db_select_options[0], $db_select_options[1], $db_select_options[2]))
								{
									unset($db_select_options[0]);
									$db_select_params = implode(';', $db_select_options);
								}

								if (stristr(trim($db_output[0]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[0];
									$db_select['select1'] = $db_output[1];
								}
								elseif (stristr(trim($db_output[1]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[1];
									$db_select['select1'] = $db_output[0];
								}
								else
									unset($db_select);
							}
						}
					}

					// Needed for db_list_indexes...
					db_extend('packages');

					$columns = array(
						$db_select['select1'] => 'string',
					);

					$values = $new_db_vals;

					$keys = array(
						$smcFunc['db_list_indexes']($db_select['table']),
					);

					$smcFunc['db_insert']('insert', $db_select['table'], $columns, $values, $keys);
				}

				// Did they request removal of one of these db_select values?
				if (isset($_POST['dpDeletedDbSelects_' . $id]))
					foreach ($_POST['dpDeletedDbSelects_' . $id] as $key)
					{
						$smcFunc['db_query']('', '
							DELETE FROM ' . $db_select['table'] . '
							WHERE {raw:query_select} = {string:key}',
							array(
								'key' => $key,
								'query_select' =>  $db_select['select1'],
							)
						);
					}

				// Now do the list_bbc type...
				if ($param_types[$id] == 'list_bbc')
					if (isset($_POST[$value . '_enabledTags']))
						$daValue = implode(';', $_POST[$value . '_enabledTags']);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_parameters
					SET value = {string:value}' . (!empty($context['modid']) && $txt_value ? ', txt_var = {int:is_zero}' : '') . '
					WHERE ' . ($context['is_clone'] ? 'id_clone={int:id_module}' : 'id_module={int:id_module}') . ' AND id_param = {int:id_param}',
					array(
						'value' => isset($check_value) && $check_value != '' ? $check_value : $smcFunc['htmlspecialchars'](un_htmlspecialchars($daValue)),
						'id_module' => $context['dp_modid'],
						'id_param' => (int) $id,
						'is_zero' => 0,
					)
				);
			}
		}

		// All done, now that was F U N!
		$base = 'action=admin;area=dplayouts;sa=modifymod;';
		$redirect = $base . (!$context['is_clone'] ? 'modid' : 'module') . '=' . $context['dp_modid'];

		redirectexit($redirect);
	}
}

/**
 * Determines if a file is really a PHP file.
 *
 * @param string $file path to the file to check.
 * @return bool true if it was successfully checked as a PHP file; false otherwise.
 * @since 1.0
 */
function isPHPFile($file)
{
    if(!$content = file_get_contents($file))
        return false;

	$get_tokens = @token_get_all($content);

    foreach($get_tokens as $token)
        if(is_array($token) && in_array(current($token), array(T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO)))
            return true;

    return false;
}

/**
 * Finds all values for the db_select parameter type.
 *
 * @param array $db_select parsed parameter. Default is an empty array.
 * @param int $param_id the parameter's ID.
 * @return array all the fields retrieved from the database table; empty array if something went wrong.
 * @since 1.0
 */
function ListDbSelects($db_select = array(), $param_id)
{
	global $smcFunc, $db_connection;

	if (!is_array($db_select) || count($db_select) <= 1)
		return array();

	// Check to make sure they aren't the same column.
	if (trim($db_select['select1']) == trim($db_select['select2']))
		$query_select = $db['select1'];
	else
		$query_select = $db_select['select1'] . ', ' . $db_select['select2'];

	// Build the query, grabbing all results.
	$query = 'SELECT ' . $query_select . '
				FROM ' . $db_select['table'] . '
				ORDER BY NULL';

	// Execute the query, can't have any errors.
	$request = @$smcFunc['db_query']('',
		$query,
		array(
			'db_error_skip' => true,
		)
	);

	$db_error = $smcFunc['db_error']($db_connection);

	// Error with query somewhere.
	if (!empty($db_error))
		return array();

	// Table is empty.
	if ($smcFunc['db_num_rows']($request) == 0)
		return array();

	$return = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
		if (!isset($return[$param_id][$row[$db_select['select2']]]))
		{
			$return_val = (string) $smcFunc['htmlspecialchars'](un_htmlspecialchars($row[$db_select['select1']]));
			if (trim($return_val) != '')
				$return[$param_id][$row[$db_select['select2']]] = $return_val;
		}

	$smcFunc['db_free_result']($request);

	if (count($return) >= 1)
	{
		$return[$param_id] = array_unique($return[$param_id]);
		return $return[$param_id];
	}
	else
		return array();
}

/**
 * Gets a list of boards grouped by their categories.
 *
 * @return array a list of boards grouped by their categories.
 * @since 1.0
 */
function ListBoards()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name AS bName, c.id_cat, c.name AS cName
		FROM {db_prefix}boards AS b, {db_prefix}categories AS c
		WHERE b.id_cat = c.id_cat
		ORDER BY c.cat_order, b.board_order',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($all_boards[$row['id_cat']]))
			$all_boards[$row['id_cat']] = array(
				'category' => $row['cName'],
				'board' => array(),
			);

		$all_boards[$row['id_cat']]['board'][$row['id_board']] = $row['bName'];
	}
	$smcFunc['db_free_result']($request);

	// Return the array of categories and boards.
	return $all_boards;
}

/**
 * Parses a checklist.
 *
 * @param array $checked integer list of all items to be checked (have a mark in the checkbox). Default is an empty array.
 * @param array $checkStrings a list of items for the checklist. These items are the familiar $txt indexes used in language files. Default is an empty array.
 * @param array $order integer list specifying the order of items for the checklist. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the items parsed for displaying the checklist; empty array if something went wrong.
 * @since 1.0
 */
function ListChecks($checked = array(), $checkStrings = array(), $order = array(), $param_name, $param_id)
{
	global $context, $txt;

	if (empty($checked) || empty($checkStrings))
		return array();

	$all_checks['checks'][$param_id] = array();

	// Build the array
	foreach(array_keys($checkStrings) as $name)
	{
		// Ordering?
		if (!empty($order))
		{
			$all_checks['checks'][$param_id][$order[$name]] = array(
				'id' => $order[$name],
				'name' => $txt['dpmod_' . $param_name . '_' . $checkStrings[$order[$name]]],
				'checked' => in_array($order[$name], $checked) ? true : false,
			);
		}
		else
			$all_checks['checks'][$param_id][] = array(
				'id' => $name,
				'name' => $txt['dpmod_' . $param_name . '_' . $checkStrings[$name]],
				'checked' => in_array($name, $checked) ? true : false,
			);
	}

	// Let's sort these arrays accordingly!
	if (!empty($order))
		$context['check_order' . $param_id] = implode(',', $order);
	else
		$context['check_order' . $param_id] = implode(',', array_keys($checkStrings));

	return $all_checks['checks'][$param_id];
}

/**
 * Gets all membergroups and filters them according to the parameters.
 *
 * @param array $checked integer list of all id_groups to be checked (have a mark in the checkbox). Default is an empty array.
 * @param array $unallowed integer list of all id_groups that are skipped. Default is an empty array.
 * @param array $order integer list specifying the order of id_groups to be displayed. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
 * @since 1.0
 */
function ListGroups($checked = array(), $unallowed = array(), $order = array(), $param_id = 0)
{
	global $context, $smcFunc, $txt;

	// We'll need this for loading up the names of each group.
	if (!loadLanguage('ManageBoards'))
		loadLanguage('ManageBoards');

	$dp_groups = array();

	if (!in_array('-1', $unallowed))
		// Guests
		$dp_groups = array(
			-1 => array(
				'id' => '-1',
				'name' => $txt['parent_guests_only'],
				'checked' => in_array('-1', $checked) || in_array('-3', $checked),
				'is_post_group' => false,
			)
		);

	if (!in_array('0', $unallowed))
	{
		// Regular Members
		if (!empty($dp_groups))
			$dp_groups += array(
				0 => array(
					'id' => '0',
					'name' => $txt['parent_members_only'],
					'checked' => in_array('0', $checked) || in_array('-3', $checked),
					'is_post_group' => false,
				)
			);
		else
			$dp_groups = array(
				0 => array(
					'id' => '0',
					'name' => $txt['parent_members_only'],
					'checked' => in_array('0', $checked) || in_array('-3', $checked),
					'is_post_group' => false,
				)
			);
	}

	// Load membergroups.
	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group > {int:is_zero}',
		array(
			'is_zero' => 0,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!in_array($row['id_group'], $unallowed))
		{
			$dp_groups[(int) $row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => trim($row['group_name']),
				'checked' => in_array($row['id_group'], $checked) || in_array('-3', $checked),
				'is_post_group' => $row['min_posts'] != -1,
			);
		}
	}
	$smcFunc['db_free_result']($request);

	// Let's sort these arrays accordingly!
	if (!empty($order))
	{
		$dp_groups = sortGroups($dp_groups, $order);

		if (!empty($param_id))
			$context['group_order' . $param_id] = implode(',', $order);
	}
	else
	{
		sort($dp_groups);

		if (!empty($param_id))
		{
			$context['group_order' . $param_id] = '';
			$x = 0;
			foreach ($dp_groups as $key => $value)
			{
				$x++;
				$context['group_order' . $param_id] .= $x < count($dp_groups) ? $value['id'] . ',' : $value['id'];
			}
		}
	}

	return $dp_groups;
}

/**
 * Sorts checkboxes in an order defined by the $orderArray. Used by {@link ListGroups()}.
 *
 * @since 1.0
 * @todo finish this document
 */
function sortGroups($array, $orderArray)
{
	if (isset($ordered))
		unset($ordered);

    $ordered = array();
    foreach($orderArray as $key => $value)
	{
        if(array_key_exists($value, $array))
		{
			$ordered[$key] = array(
				'id' => $array[$value]['id'],
				'name' => $array[$value]['name'],
				'checked' => $array[$value]['checked'],
				'is_post_group' => $array[$value]['is_post_group'],
			);
			unset($array[$value]);
        }
    }
    return $ordered + $array;
}

/**
 * Loads module's icon.
 *
 * @since 1.0
 */
function moduleLoadIcon($icon)
{
	global $context, $boarddir, $modSettings;

	// Default context.
	$context['module']['icon'] = array(
		'selection' => $icon == '' ? '' : $icon,
	);

	if (file_exists($boarddir . '/' . $modSettings['dp_icon_directory'] . '/' . $icon))
		$context['module']['icon'] += array(
			'server_pic' => $icon == '' ? '' : $icon
		);
	else
		$context['module']['icon'] += array(
			'server_pic' => ''
		);

	// Get a list of all of the icons.
	$context['dpicon_list'] = array();
	$context['icons'] = is_dir($boarddir . '/' . $modSettings['dp_icon_directory']) ? getDPIcons('', 0) : array();

	// Second level selected icon...
	$context['icon_selected'] = substr(strrchr($context['module']['icon']['server_pic'], '/'), 1);

	return true;
}

/**
 * Recursive function to retrieve dream portal icon files. Used by {@link moduleLoadIcon()}.
 *
 * @since 1.0
 */
function getDPIcons($directory, $level)
{
	global $context, $txt, $modSettings, $boarddir;

	$result = array();

	// Open the directory..
	$dir = dir($boarddir . '/' . $modSettings['dp_icon_directory'] . (!empty($directory) ? '/' : '') . $directory);
	$dirs = array();
	$files = array();

	if (!$dir)
		return array();

	while ($line = $dir->read())
	{
		if (in_array($line, array('.', '..', 'index.php')))
			continue;

		if (is_dir($boarddir . '/' . $modSettings['dp_icon_directory'] . '/' . $directory . (!empty($directory) ? '/' : '') . $line))
			$dirs[] = $line;
		else
			$files[] = $line;
	}
	$dir->close();

	// Sort the results...
	natcasesort($dirs);
	natcasesort($files);

	if ($level == 0)
	{
		$result[] = array(
			'filename' => '',
			'checked' => empty($context['module']['icon']['server_pic']),
			'name' => $txt['no_icon'],
			'is_dir' => false
		);
	}

	foreach ($dirs as $line)
	{
		$tmp = getDPIcons($directory . (!empty($directory) ? '/' : '') . $line, $level + 1);
		if (!empty($tmp))
			$result[] = array(
				'filename' => htmlspecialchars($line),
				'checked' => strpos($context['module']['icon']['server_pic'], $line . '/') !== false,
				'name' => '[' . htmlspecialchars(str_replace('_', ' ', $line)) . ']',
				'is_dir' => true,
				'files' => $tmp
		);
		unset($tmp);
	}

	foreach ($files as $line)
	{
		$filename = substr($line, 0, (strlen($line) - strlen(strrchr($line, '.'))));
		$extension = substr(strrchr($line, '.'), 1);

		// Make sure it is an image.
		if (strcasecmp($extension, 'gif') != 0 && strcasecmp($extension, 'jpg') != 0 && strcasecmp($extension, 'jpeg') != 0 && strcasecmp($extension, 'png') != 0 && strcasecmp($extension, 'bmp') != 0)
			continue;

		$result[] = array(
			'filename' => htmlspecialchars($line),
			'checked' => $line == $context['module']['icon']['server_pic'],
			'name' => htmlspecialchars(str_replace('_', ' ', $filename)),
			'is_dir' => false
		);
		if ($level == 1)
			$context['dpicon_list'][] = $directory . '/' . $line;
	}
	return $result;
}

// Clones a module.
function CloneDPMod()
{
	global $context, $smcFunc;

	// Just some extra security here!
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		return;

	validateSession();

	$request = $smcFunc['db_query']('', '
		SELECT
			dlp.id_layout, dlp.id_layout_position
		FROM {db_prefix}dp_layout_positions AS dlp
			LEFT JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			LEFT JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
		WHERE dlp.id_layout = dl.id_layout AND dlp.enabled = -1',
		array(
			'one' => 1,
			'zero' => 0,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
		)
	);

	list($id_layout, $id_layout_position) = $smcFunc['db_fetch_row']($request);

	// We need to know if they are modifying an original module or a clone.  Clones will be a simple id=id_clone
	$context['modid'] = isset($_REQUEST['modid']) && !isset($_REQUEST['module']) ? (int) $_REQUEST['modid'] : '';
	$cloneid = isset($_REQUEST['module']) && !isset($_REQUEST['modid']) ? (int) $_REQUEST['module'] : '';
	$not_clone = !empty($cloneid) && isset($_REQUEST['mod']);

	// They aren't modifying anything, error!
	if(empty($context['modid']) && empty($cloneid))
		fatal_lang_error('dp_module_not_installed', false);

	//Which type is it?
	$context['is_clone'] = !empty($context['modid']) ? false : true;
	$context['dp_modid'] = !empty($context['modid']) ? (int) $context['modid'] : (int) $cloneid;

	if ($not_clone)
		DPClone($id_layout_position, 0, array($cloneid));
	else
	{
		if (!$context['is_clone'])
			CloneDPModules($id_layout, $id_layout_position, array($context['dp_modid']));
		else
			DPDeclone(array($cloneid), array(), 2);
	}

	redirectexit('action=admin;area=dplayouts;sa=dpmanlayouts');
}

/*
	Stops execution of the adding of layouts form if there are errors detected.
*/
function layoutPostError($layout_errors, $sub_template, $layout_name = '', $curr_actions = array(), $selected_layout = 0)
{
	global $context, $txt, $smcFunc;

	$context['page_title'] = $txt[$sub_template . '_title'];

	$context['sub_template'] = $sub_template;

	$context['current_actions'] = array();
	$context['layout_error'] = array(
		'messages' => array(),
	);

	foreach ($layout_errors as $error_type)
	{
		$context['layout_error'][$error_type] = true;
		if (isset($txt['dp_' . $error_type]))
			$context['layout_error']['messages'][] = $txt['dp_' . $error_type];
	}

	if (!empty($curr_actions))
		$context['current_actions'] += $curr_actions;

	if (!empty($layout_name))
		$context['layout_name'] = $layout_name;

	$context['layout_styles'] = array(
		1 => 'dream_portal',
		2 => 'omega',
	);

	$context['selected_layout'] = !empty($selected_layout) ? $selected_layout : 0;

	$exceptions = array(
		'print',
		'clock',
		'about:unknown',
		'about:mozilla',
		'modifycat',
		'.xml',
		'xmlhttp',
		'dlattach',
		'dreamaction',
		'dreamFiles',
		'printpage',
		'keepalive',
		'jseditor',
		'jsmodify',
		'jsoption',
		'suggest',
		'verificationcode',
		'viewsmfile',
		'viewquery',
		'editpoll2',
		'login2',
		'movetopic2',
		'post2',
		'quickmod2',
		'register2',
		'removetopic2'
	);

	if (isset($context['edit_layout']))
		$context['edit_layout'] = true;

	$edit_layout_query = array('curr_layout' => $_SESSION['selected_layout']['id_layout']);
	$query_array = isset($context['edit_layout']) ? array_merge(array('group' => 1, 'member' => 0), $edit_layout_query) : array('group' => 1, 'member' => 0);

	// Need to add current action to the exceptions within this group, so we don't add them twice.
	$request = $smcFunc['db_query']('', '
		SELECT da.action
		FROM {db_prefix}dp_groups AS dg, {db_prefix}dp_actions AS da
		WHERE dg.id_member = {int:member} AND dg.id_group = {int:group} AND da.id_group = dg.id_group' . (isset($context['edit_layout']) ? ' AND da.id_layout != {int:curr_layout}' : ''),
		$query_array
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$exceptions = array_merge($exceptions, array($row['action']));

	$smcFunc['db_free_result']($request);
		
	$countActions = count($context['smf_actions']);

	$remove_all = array();
	for ($i = 0; $i < $countActions; $i++)
	{
		// Remove the 2's.
		if (substr($context['smf_actions'][$i], -1) == '2')
			if (!in_array($context['smf_actions'][$i], $exceptions))
				$remove_all[] = $context['smf_actions'][$i];
	}

	if (!empty($remove_all))
		$remove_all += $exceptions;
	else
		$remove_all = $exceptions;

	$context['available_actions'] = array_diff($context['smf_actions'], $remove_all);

	// We do this so the user can type in 2's if they need them.
	$context['unallowed_actions'] = $exceptions;

	sort($context['available_actions']);

	// No check for the previous submission is needed.
	checkSubmitOnce('free');

	// Acquire a new form sequence number.
	checkSubmitOnce('register');
}

/**
 * Loads the form for the admin to add a layout.
 *
 * @since 1.0
 */
function AddDPLayout()
{
	global $context, $txt, $smcFunc;
	
	// Just a few precautionary measures.
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  fatal_lang_error('dp_no_permission', false);

	 validateSession();

	$context['page_title'] = $txt['add_layout_title'];
	$context['sub_template'] = 'add_layout';

	// Setting some defaults.
	$context['selected_layout'] = 1;
	$context['layout_error'] = array();
	$context['layout_name'] = '';
	$context['current_actions'] = array();

	// Load up the 2 predefined layout styles.
	$context['layout_styles'] = array(
		1 => 'dream_portal',
		2 => 'omega',
	);

	$exceptions = array(
		'print',
		'clock',
		'about:unknown',
		'about:mozilla',
		'modifycat',
		'.xml',
		'xmlhttp',
		'dlattach',
		'dream',
		'dreamFiles',
		'printpage',
		'keepalive',
		'jseditor',
		'jsmodify',
		'jsoption',
		'suggest',
		'verificationcode',
		'viewsmfile',
		'viewquery',
		'editpoll2',
		'login2',
		'movetopic2',
		'post2',
		'quickmod2',
		'register2',
		'removetopic2'
	);
	
	$edit_layout_query = array('curr_layout' => $_SESSION['selected_layout']['id_layout']);
	$query_array = isset($context['edit_layout']) ? array_merge(array('group' => 1, 'member' => 0), $edit_layout_query) : array('group' => 1, 'member' => 0);

	// Need to add current action to the exceptions within this group, so we don't add them twice.
	$request = $smcFunc['db_query']('', '
		SELECT da.action
		FROM {db_prefix}dp_groups AS dg, {db_prefix}dp_actions AS da
		WHERE dg.id_member = {int:member} AND dg.id_group = {int:group} AND da.id_group = dg.id_group' . (isset($context['edit_layout']) ? ' AND da.id_layout != {int:curr_layout}' : ''),
		$query_array
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$exceptions = array_merge($exceptions, array($row['action']));

	$smcFunc['db_free_result']($request);

	$countActions = count($context['smf_actions']);

	$remove_all = array();
	for ($i = 0; $i < $countActions; $i++)
	{
		// Remove the 2's.
		if (substr($context['smf_actions'][$i], -1) == '2')
			if (!in_array($context['smf_actions'][$i], $exceptions))
				$remove_all[] = $context['smf_actions'][$i];
	}

	if (!empty($remove_all))
		$remove_all += $exceptions;
	else
		$remove_all = $exceptions;

	$context['available_actions'] = array_diff($context['smf_actions'], $remove_all);

	// We do this so the user can type in 2's if they need them.
	$context['unallowed_actions'] = $exceptions;

	sort($context['available_actions']);

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

/*
	Adds the layout specified in the form from {@link AddDPLayout()}.
*/
function AddDPLayout2()
{
	global $smcFunc;

	// Just a few precautionary measures.
	 if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We need to pass the user's ID (zero, admin :P)
	$_POST['id_member'] = 0;

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = 0;

	if (isset($_POST['layout_name']) && !empty($_POST['layout_name']))
		$layout_name = trim($_POST['layout_name']);
	else
	{
		$layout_name = '';
		$layout_errors[] = 'no_layout_name';
	}

	// We need to make sure that the layout name doesn't exist in any of the other layouts.
	if (!empty($layout_name))
	{
		$request = $smcFunc['db_query']('', '
			SELECT dl.id_layout
			FROM {db_prefix}dp_groups AS dg
			INNER JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND LOWER(dl.name) = {string:layout_name})
			WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
			array(
				'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
				'zero' => 0,
				'layout_name' => strtolower($layout_name),
			)
		);
		if ($smcFunc['db_num_rows']($request) !== 0)
		{
			$layout_errors[] = 'layout_exists';
			$layout_name = '';
		}

		$smcFunc['db_free_result']($request);
		// Now let's use html_entities on it before placing into the database.
		$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars($layout_name));
	}

	$i = 0;

	if (!empty($_POST['layout_actions']))
		foreach($_POST['layout_actions'] as $laction)
		{
			$layout_actions[] = $laction;
		}
	else
		$layout_errors[] = 'no_actions';

	// Finally get the layout style they chose.
	// Should compare this to the advanced layout style selection POST, if advanced is selected, $layout_style = -1;
	$selected_layout = !empty($_POST['layout_style']) ? (int) $_POST['layout_style'] : 0;

	if (count($layout_errors) >= 1)
		return layoutPostError($layout_errors, 'add_layout', $layout_name, $layout_actions, $selected_layout);

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// For now, id_group will always = 1, will change when we switch to Member Layouts ofcourse.
	$id_group = 1;

	$selected_layout = !empty($_POST['layout_style']) ? (int) $_POST['layout_style'] : 0;

	if (!empty($selected_layout))
		$insert_positions = dpPredefined_Layouts($selected_layout);
	else
		fatal_lang_error('dp_layout_unknown', false);

	$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name'])));

	// Add the module info to the database
	$columns = array(
		'name' => 'string-65',
		'id_group' => 'int',
	);

	$keys = array(
		'id_layout',
		'id_group'
	);

	$data = array(
		$layout_name,
		$id_group,
	);

	$smcFunc['db_insert']('insert', '{db_prefix}dp_layouts',  $columns, $data, $keys);

	// We need to tell the positions table which ID was inserted
	$iid = $smcFunc['db_insert_id']('{db_prefix}dp_layouts', 'id_layout');

	// Add in all actions to the dp_actions table.
	foreach ($layout_actions as $action)
		$smcFunc['db_insert']('insert', '{db_prefix}dp_actions',
			array('id_group' => 'int', 'id_layout' => 'int', 'action' => 'string-255'),
			array((int) $id_group, (int) $iid, $action),
			array('id_action', 'id_group', 'id_layout')
		);
	
	// One more to go - insert the layout.
	$columns = array(
		'id_layout' => 'int',
		'column' => 'string',
		'row' => 'string',
		'enabled' => 'int',
	);

	$keys = array(
		'id_layout',
		'id_layout_position',
	);

	// Add the Disabled Modules section to the layout style.
	$insert_positions = array_merge($insert_positions, array(array('column' => '0:0', 'row' => '0:0', 'enabled' => -1)));

	foreach ($insert_positions as $insert_position)
	{
		$data = array(
			$iid,
			$insert_position['column'],
			$insert_position['row'],
			$insert_position['enabled'],
		);

		$smcFunc['db_insert']('insert', '{db_prefix}dp_layout_positions',  $columns, $data, $keys);

		// We need to get the id_layout_position of the SMF section.
		if (isset($insert_position['smf']))
			$smf_id = $smcFunc['db_insert_id']('{db_prefix}dp_layout_positions', 'id_layout_position');
	}

	$iid2 = $smcFunc['db_insert_id']('{db_prefix}dp_layout_positions', 'id_layout_position');

	$_SESSION['selected_layout'] = array(
		'id_layout' => (int) $iid,
		'name' => $layout_name,
	);

	$_SESSION['layouts'][$iid] = $layout_name;

	// Only needs 1 parameter passed to it since the rest is in the $_SESSION.
	// and don't bother with actual modules, we are cloning from an array instead!
	DPClone($iid2, $smf_id);

	redirectexit('action=admin;area=dplayouts;sa=dpmanlayouts');
}

/**
 * Clones one or more modules.
 *
 * @param int $id_layout the layout to point the clones to.
 * @param int @id_layout_position the section to point the clones to.
 * @param array $id_modules contains the IDs of the modules to clone. If it is blank, ALL module ids are assumed.
 * @since 1.0
 */
function CloneDPModules($id_layout, $id_layout_position, $id_modules = array())
{
	global $context, $scripturl, $smcFunc, $txt, $options;

	 // Just a few precautionary measures.
	 if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  return;

	 // This is kinda important.
	 validateSession();

	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dm.name, dm.title, dm.title_link, dm.txt_var AS txt_title, dm.target, dm.minheight, dm.minheight_type, dm.icon, dm.files, dm.header_files, dm.functions, dm.container,
			dmp.id_param, dmp.name AS param_name, dmp.type AS param_type, dmp.value AS param_value, dmp.fieldset AS param_fieldset, dmp.txt_var AS txt_value, dl.id_layout, dmp2.position
		FROM {db_prefix}dp_modules AS dm
			INNER JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			INNER JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
			LEFT JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.id_clone = {int:zero})
			LEFT JOIN {db_prefix}dp_module_positions AS dmp2 ON (dmp2.id_layout = dl.id_layout AND dmp2.id_layout_position = {int:id_layout_pos})' . (!empty($id_modules) ? '
		WHERE dm.id_module IN({array_int:id_modules})' : ''),
		array(
			'id_layout_pos' => $id_layout_position,
			'one' => 1,
			'zero' => 0,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
			'id_modules' => $id_modules,
		)
	);

	$params2clone = array();
	$disabled_count = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($mod2clone[$row['id_module']]))
			$mod2clone[$row['id_module']] = array(
				'name' => $row['name'],
				'title' => !empty($row['txt_title']) && isset($txt[$row['title']]) ? $txt[$row['title']] : (!empty($row['title']) ? $row['title'] : $txt['dpmod_' . $row['name']]),
				'title_link' => $row['title_link'],
				'target' => $row['target'],
				'icon' => $row['icon'],
				'minheight' => $row['minheight'],
				'minheight_type' => $row['minheight_type'],
				'files' => $row['files'],
				'header_files' => $row['header_files'],
				'functions' => $row['functions'],
				'container' => !empty($row['container']) ? 1 : 0,
			);

		if (!isset($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]))
			if (!empty($row['id_param']))
				$mod2clone[$row['id_module']]['params2clone'][$row['id_param']] = array(
					'name' => $row['param_name'],
					'type' => $row['param_type'],
					'value' => !empty($row['txt_value']) && isset($txt[$row['param_value']]) ? $txt[$row['param_value']] : $row['param_value'],
					'fieldset' => $row['param_fieldset']
			);

		if (!is_null($row['position']))
			$disabled_count++;


		// Wouldn't want params to be ordered differently from the other modules ( 1st Pass )!
		if (!empty($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]) && count($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]) >= 1)
			ksort($mod2clone[$row['id_module']]['params2clone'][$row['id_param']], SORT_NUMERIC);
	}

	// We'll want to free this up and not waste memory.
	$smcFunc['db_free_result']($request);

	$i = 0;
	foreach ($mod2clone as $mod2clone_key => $mod2clone_value)
	{
		// Add the module info to the database
		$columns = array(
			'id_module' => 'int',
			'name' => 'string',
			'title' => 'string',
			'title_link' => 'string',
			'target' => 'int',
			'icon' => 'string',
			'minheight' => 'int',
			'minheight_type' => 'int',
			'files' => 'string',
			'header_files' => 'string',
			'functions' => 'string',
			'container' => 'int',
			'id_member' => 'int',
			'is_clone' => 'int',
		);

		$keys = array(
			'id_clone',
			'id_module',
			'id_member'
		);
		
		$data = array(
			$mod2clone_key,
			$mod2clone_value['name'],
			$mod2clone_value['title'],
			$mod2clone_value['title_link'],
			$mod2clone_value['target'],
			$mod2clone_value['icon'],
			$mod2clone_value['minheight'],
			$mod2clone_value['minheight_type'],
			$mod2clone_value['files'],
			$mod2clone_value['header_files'],
			$mod2clone_value['functions'],
			!empty($mod2clone_value['container']) ? 1 : 0,
			0,
			1,
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_clones',  $columns, $data, $keys);

		// We need to tell the parameters table which ID was inserted
		$iid = $smcFunc['db_insert_id']('{db_prefix}dp_module_clones', 'id_clone');

		// In case we have parameters...
		if (isset($mod2clone_value['params2clone']))
		{
			$columns = array(
				'id_clone' => 'int',
				'id_module' => 'int',
				'name' => 'string-255',
				'type' => 'string-255',
				'value' => 'string-65536',
				'fieldset' => 'string-255',
			);

			$keys = array(
				'id_param',
				'id_clone',
				'id_module',
			);

			// Ensure the parameters are ordered correctly ( 2nd pass )!
			ksort($mod2clone_value['params2clone']);

			foreach($mod2clone_value['params2clone'] as $key => $param)
			{
				if (isset($param['extend']))
				{
					// Extending the value of the parameter from within a function!
					if ($param['extend'] == 'function')
					{
						$extend = explode(':', $param['value']);
						// Require the file.
						if (file_exists($context['dpmod_modules_dir'] . '/' . $mod2clone_value['name'] . '/' . $extend[0]))
						{
							require_once($context['dpmod_modules_dir'] . '/' . $mod2clone_value['name'] . '/' . $extend[0]);

							// Set the value to the returned result of the function.
							if (function_exists($extend[1]))
								$param['value'] = $extend[1]();
						}
					}
				}

				// Insert the parameters, we'll need to insert the module id also.
				$data = array(
					$iid,
					$mod2clone_key,
					$param['name'],
					$param['type'],
					$param['value'],
					$param['fieldset'],
				);
				$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_parameters', $columns, $data, $keys);
			}
		}

		// One more to go - insert the layout.
		$columns = array(
			'id_layout_position' => 'int',
			'id_layout' => 'int',
			'id_module' => 'int',
			'id_clone' => 'int',
			'position' => 'int',
			'empty' => 'int',
		);

		$data = array(
			$id_layout_position,
			$id_layout,
			0,
			$iid,
			(empty($id_modules) ? $i : $disabled_count),
			(empty($mod2clone_value['container']) ? 1 : 0),
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_positions',  $columns, $data, $keys);

		$i++;
	}


	$diid = $smcFunc['db_insert_id']('{db_prefix}dp_module_positions', 'id_position');

	// That's all she wrote.
	if (isset($_GET['xml']))
		die('
				<div class="DragBox clonebox' . (!empty($options['dp_mod_color']) ? $options['dp_mod_color'] : '1') . ' draggable_module" id="dreammod_' . $diid . '" style="text-align: center;">
						<p>' . $mod2clone[$context['dp_modid']]['title'] . '</p>
						<p class="dp_inner"><a href="' . $scripturl . '?action=admin;area=dplayouts;sa=modifymod;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['dp_admin_modules_manage_modify'] . '</a> | <a href="' . $scripturl . '?action=admin;area=dplayouts;sa=clonemod;xml;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . $txt['dpmodule_declone'] . '</a></p>
				</div>');
	else
		return;
}

/**
 * Calls {@link DPDeleteLayout()} to delete a layout specified in $_POST['layout_picker'].
 *
 * @since 1.0
 */
function DeleteDPLayout()
{
	global $txt;

	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  fatal_lang_error('dp_no_permission', false);

	checkSession('get');

	$id_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('no_layout_selected', false);

	if (!DPDeleteLayout($id_layout))
		fatal_lang_error('no_layout_selected', false);
	else
		redirectexit('action=admin;area=dplayouts;sa=dpmanlayouts');
}

/**
 * Loads the form for the admin to edit a layout.
 *
 * @since 1.0
 */
function EditDPLayout()
{
	global $context, $smcFunc, $txt, $modSettings;

	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We are editing a layout, not adding one.
	$context['edit_layout'] = true;

	// Variables in here are recycled
	AddDPLayout();

	$context['page_title'] = $txt['edit_layout_title'];
	$context['sub_template'] = 'edit_layout';

	$selected_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('cant_find_layout_id', false);

	if (!isset($context['row_pos_error_ids']))
	{
		$context['row_pos_error_ids'] = array();
		$context['col_pos_error_ids'] = array();
		$context['rowspans_error_ids'] = array();
		$context['colspans_error_ids'] = array();
	}

	$request = $smcFunc['db_query']('', '
		SELECT dl.name, da.action, da.id_action, dlp.id_layout_position, dlp.row, dlp.column, dlp.enabled, dmp.id_module, dmp.id_clone, dmp.id_position
		FROM {db_prefix}dp_groups AS dg
			INNER JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
			INNER JOIN {db_prefix}dp_actions AS da ON (da.id_layout = dl.id_layout AND da.id_group = dl.id_group)
			LEFT JOIN {db_prefix}dp_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout' . (!empty($context['layout_errors']) ? '' : ' AND dlp.enabled != {int:invisible_layout}') . ')
			LEFT JOIN {db_prefix}dp_module_positions AS dmp ON (dmp.id_layout = dl.id_layout AND dmp.id_layout_position = dlp.id_layout_position)
		WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
		array(
			'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
			'zero' => 0,
			'invisible_layout' => -2,
			'id_layout' => $selected_layout,
		)
	);

	$context['total_columns'] = 0;
	$context['total_rows'] = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['enabled'] != -1)
		{
			if (isset($_POST['remove_positions']))
			{
				$was_deleted = strstr($_POST['remove_positions'], $row['id_layout_position']);
				if ($was_deleted !== false)
					continue;
			}

			$context['layout_name'] = un_htmlspecialchars($row['name']);
			if (!in_array($row['action'], $context['current_actions']))
				$context['current_actions'][] = $row['action'];

			$cols = explode(':', $row['column']);
			$rows = explode(':', $row['row']);
			$cols[0] = !empty($cols[0]) ? $cols[0] : 0;
			$rows[0] = !empty($rows[0]) ? $rows[0] : 0;

			$smf = (int) $row['id_clone'] + (int) $row['id_module'];
			$smf_col = empty($smf) && !is_null($row['id_position']);

			if (!isset($context['current_sections'][$rows[0]][$cols[0]]))
				$context['total_columns']++;

			if (!isset($context['current_sections'][$rows[0]]))
				$context['total_rows']++;

			$context['current_sections'][$rows[0]][$cols[0]] = array(
				'is_smf' => $smf_col,
				// 'has_modules' => !empty($smf),
				'id_layout_position' => $row['id_layout_position'],
				'id_position' => $row['id_position'],
				'colspans' => !empty($cols[1]) ? $cols[1] : 0,
				'rowspans' => !empty($rows[1]) ? $rows[1] : 0,
				'enabled' => !empty($row['enabled']),
			);
		}
		else
			if (!isset($context['disabled_section']))
				$context['disabled_section'] = $row['id_layout_position'];
	}

	$smcFunc['db_free_result']($request);

	ksort($context['current_sections']);
	foreach ($context['current_sections'] as $key => $value)
		ksort($context['current_sections'][$key]);

	$show_smf = in_array('[home]', $context['current_actions']);
		
	$context['show_smf'] =  (!$show_smf && empty($modSettings['dp_disable_homepage'])) || !empty($modSettings['dp_disable_homepage']);
	$_SESSION['show_smf'] = (!$show_smf && empty($modSettings['dp_disable_homepage'])) || !empty($modSettings['dp_disable_homepage']);

	if (isset($_POST['colspans']))
		$context = array_merge($context, $_POST);
}

/**
 * Edits the layout socified in the form loded from {@link EditDPLayout()}.
 *
 * @since 1.0
 */
function EditDPLayout2()
{
	global $context, $smcFunc;

	// Just a few precautionary measures.
	 if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
	  fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We need to pass the user's ID (zero, admin :P)
	$_POST['id_member'] = 0;

	// 	die(var_dump($_POST));
	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('cant_find_layout_id', false);

	if ($_SESSION['show_smf'])
	{
		if (isset($_POST['layout_name']) && !empty($_POST['layout_name']) )
			$layout_name = trim($_POST['layout_name']);
		else
		{
			$layout_name = '';
			$layout_errors[] = 'no_layout_name';
		}

		// We need to make sure that the layout name doesn't exist in any of the other layouts.
		if (!empty($layout_name))
		{
			$request = $smcFunc['db_query']('', '
				SELECT dl.id_layout
				FROM {db_prefix}dp_groups AS dg
				INNER JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND LOWER(dl.name) = {string:layout_name} AND dl.id_layout != {int:id_layout})
				WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
				array(
					'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
					'zero' => 0,
					'layout_name' => strtolower($layout_name),
					'id_layout' => $selected_layout,
				)
			);
			if ($smcFunc['db_num_rows']($request) !== 0)
			{
				$layout_errors[] = 'layout_exists';
				$layout_name = '';
			}

			$smcFunc['db_free_result']($request);

			// Now let's use html_entities on it before placing into the database.
			$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars($layout_name));
		}

		$i = 0;

		if (!empty($_POST['layout_actions']))
			foreach($_POST['layout_actions'] as $laction)
			{
				$layout_actions[] = $laction;
			}
		else
			$layout_errors[] = 'no_actions';
	}

	$update_query = '';
	$update_params = array();
	$id_layout_positions = array();
	$regulatory_check = array();
	$val = 0;
	$context['row_pos_error_ids'] = array();
	$context['col_pos_error_ids'] = array();
	$context['colspans_error_ids'] = array();

	$update_query .= '
			dlp.column = CASE dlp.id_layout_position';

	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);
		if (!is_numeric($_POST['colspans'][$data[2]]))
		{
			$context['colspans_error_ids'][] = $data[2];
			$layout_errors[104] = 'colspans_invalid';
		}

		if (!isset($regulatory_check[$data[0]]))
			$val = 0;

		$val = $val + ($_POST['colspans'][$data[2]] == 0 ? 1 : $_POST['colspans'][$data[2]]);

		$regulatory_check[$data[0]] = $val;

		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:column' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'column' . $data[2] => $data[1] . ':' . $_POST['colspans'][$data[2]],
		));

		$id_layout_positions[] = $data[2];
	}

	$update_query .= '
				END,
			dlp.row = CASE dlp.id_layout_position';

	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);

		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:row' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'row' . $data[2] => $data[0] . ':0',
		));

		$id_layout_positions[] = $data[2];
	}

	$update_query .= '
				END,
			dlp.enabled = CASE dlp.id_layout_position';
	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);
		if (!empty($_POST['enabled'][$data[2]]))
			$value = 1;
		else
			$value = 0;

		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:enabled' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'enabled' . $data[2] => $value,
		));
	}

	foreach ($regulatory_check as $key => $compare)
		if (isset($regulatory_check[$key + 1]) && $compare != $regulatory_check[$key + 1])
			$layout_errors[42] = 'layout_invalid';

	if (count($layout_errors) >= 1)
	{
		$context['layout_errors'] = true;
		EditDPLayout();
		return layoutPostError($layout_errors, 'edit_layout', $layout_name, $layout_actions);
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// Needs to change once we go live with Member Layouts!
	$id_group = 1;

	$dp_actions = ($_SESSION['show_smf'] ? $layout_actions : array());
	$layout_name = ($_SESSION['show_smf'] ? $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name']))) : '');

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}dp_layouts AS dl, {db_prefix}dp_layout_positions AS dlp
		SET ' . ($_SESSION['show_smf'] ? 'dl.name = {string:layout_name},' : '') . $update_query . '
				END
		WHERE dl.id_layout = {int:id_layout} AND dlp.id_layout_position IN({array_int:id_layout_positions})',
		array_merge($update_params, array(
			'layout_name' => $layout_name,
			'id_layout' => $selected_layout,
			'id_layout_positions' => $id_layout_positions,
		))
	);

	// Delete all actions that aren't defined for this layout of this id_group, than place in the new actions foreach($dp_actions);
	if (!empty($dp_actions))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_actions
			WHERE id_layout = {int:id_layout} AND id_group = {int:id_group}', // AND action NOT IN({array_string:actions})',
			array(
				'id_group' => $id_group,
				'id_layout' => $selected_layout,
				'actions' => $dp_actions,
			)
		);

		foreach ($dp_actions as $action)
			$smcFunc['db_insert']('insert', '{db_prefix}dp_actions',
				array('id_group' => 'int', 'id_layout' => 'int', 'action' => 'string-255'),
				array((int) $id_group, (int) $selected_layout, $action),
				array('id_action', 'id_group', 'id_layout')
			);
	}

	if ($_SESSION['show_smf'] && $_POST['old_smf_pos'] != $_POST['smf_radio'])
	{
		/*
			The Admin has chosen to move SMF - this is done in a three step manner.
			First we get rid of the old position, then any modules standing in the
			way must move; and finally, we insert the new position into the database.
		*/

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_module_positions
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $_POST['old_smf_pos'],
			)
		);

		// Make way for the mighty SMF, O ye little modules!
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}dp_module_positions AS dmp, {db_prefix}dp_layout_positions AS dlp
			SET dmp.id_layout_position = dlp.id_layout_position, dmp.position = dmp.id_position
			WHERE dmp.id_layout = {int:selected_layout}
				AND dlp.id_layout = {int:selected_layout}
				AND dlp.enabled = -1
				AND dmp.id_layout_position = {int:id_layout_position}',
			array(
				'selected_layout' => $selected_layout,
				'id_layout_position' => $_POST['smf_radio'],
			)
		);

		$columns = array(
			'id_layout_position' => 'int',
			'id_layout' => 'int',
			'id_module' => 'int',
			'id_clone' => 'int',
			'position' => 'int',
		);

		$values = array(
			$_POST['smf_radio'],
			$selected_layout,
			0,
			0,
			0,
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('insert', '{db_prefix}dp_module_positions', $columns, $values, $keys);
	}

	if (!empty($_POST['remove_positions']))
	{
		// The Admin has chosen to remove some columns.
		$killdata = explode('_', $_POST['remove_positions']);

		// Remove the empty item
		unset($killdata[0]);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_layout_positions
			WHERE id_layout_position IN({array_int:remove_ids})',
			array(
				'remove_ids' => $killdata,
			)
		);

		// Any modules that were in these deleted sections must be moved to the disabled section.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}dp_module_positions AS dmp, {db_prefix}dp_layout_positions AS dlp
			SET dmp.id_layout_position = dlp.id_layout_position, dmp.position = dmp.id_position
			WHERE dmp.id_layout = {int:selected_layout}
				AND dlp.id_layout = {int:selected_layout}
				AND dlp.enabled = -1
				AND dmp.id_layout_position IN({array_int:remove_ids})',
			array(
				'selected_layout' => $selected_layout,
				'remove_ids' => $killdata,
			)
		);
	}

	// Cleanup...
	unset($_SESSION['show_smf']);
	unset($regulatory_check);
	unset($val);

	// We need to empty the cache now, but make sure it is in the correct format, first.
	foreach ($_POST['layout_actions'] as $action)
		if (is_array(cache_get_data('dream_columns_' . md5(md5($action)), 3600)))
			cache_put_data('dream_columns_' . md5(md5($action)), 0, 3600);

	// Update the session with the new name.
	if (!empty($layout_name))
	{
		$_SESSION['selected_layout'] = array(
			'id_layout' => (int) $selected_layout,
			'name' => $layout_name,
		);

		$_SESSION['layouts'][$selected_layout] = $layout_name;
	}

	redirectexit('action=admin;area=dplayouts;sa=dpmanlayouts');
}

/**
 * Loads all the section values minus the disabled modules section for any pre-defined layouts.
 *
 * @param int $style specifies which prese layout style to use.
 * - 1 - Default Dream Portal Layout)
 * - 2 - (OMEGA Layout) <--- This actually covers all layout styles, so no need for anymore!
 * @return array the layout formatted according to $style.
 *
 * @since 1.0
 */
function dpPredefined_Layouts($style)
{
	switch ((int) $style)
	{
		case 2:
			// OMEGA
			return array(
				// row 0
				array(
					'column' => '0:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '1:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '2:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				// row 1
				array(
					'column' => '0:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				array(
					'smf' => true,
					'column' => '1:2',
					'row' => '1:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// row 2
				array(
					'column' => '0:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '1:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '2:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '2:0',
					'enabled' => 1,
				)
			);
			break;
		// Default - Dream Portal
		default:
			return array(
				// top
				array(
					'column' => '0:3',
					'row' => '0:0',
					'enabled' => 1,
				),
				// left
				array(
					'column' => '0:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// middle
				array(
					'smf' => true,
					'column' => '1:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// right
				array(
					'column' => '2:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// bottom
				array(
					'column' => '0:3',
					'row' => '2:0',
					'enabled' => 1,
				)
			);
			break;
	}
}

// Takes the clones id_clone value to clone it from.
// For when we need to clone an actual clone or create a layout!
function DPClone($id_layout_position, $smf_id = 0, $id_clones = array())
{
	global $context, $scripturl, $smcFunc, $txt, $options;

	// Just some extra security here!
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		return;

	checkSession('get');

	$disabled_count = 0;

	if (count($id_clones) >= 1)
	{
		// We are just cloning 1 Clone.
		// I placed this into a separate query since we may want to account for modules that are allowed, but only for Non-Admins.
		$query = 'SELECT dmc.id_clone, dmc.is_clone, dmc.id_module, dmc.name, dmc.title, dmc.title_link, dmc.target, dmc.icon, dmc.minheight, dmc.minheight_type, dmc.files, dmc.header_files, dmc.functions, dmc.container,
		dmp.id_param, dmp.name AS param_name, dmp.type AS param_type, dmp.value AS param_value, dmp.fieldset AS param_fieldset, dl.id_layout, dmp2.position
	FROM {db_prefix}dp_module_clones AS dmc
		INNER JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
		INNER JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
		LEFT JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_clone = dmc.id_clone)
		LEFT JOIN {db_prefix}dp_module_positions AS dmp2 ON (dmp2.id_layout = dl.id_layout AND dmp2.id_layout_position = {int:id_layout_pos})
		WHERE dmc.id_clone IN ({array_int:id_clones}) AND dmc.id_member = {int:id_member} AND dmc.is_clone = {int:zero}';

		// Begin the Query.
		$request = $smcFunc['db_query']('', $query,
			array(
				'one' => 1,
				'zero' => 0,
				'id_layout_pos' => $id_layout_position,
				'id_member' => 0,
				'id_layout' => $_SESSION['selected_layout']['id_layout'],
				'id_clones' => $id_clones,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($clone[$row['id_clone']]))
				$clone[$row['id_clone']] = array(
					'id_module' => $row['id_module'],
					'container' => !empty($row['container']) ? 1 : 0,
					'name' => $row['name'],
					'title' => !empty($row['title']) ? $row['title'] : $txt['dpmod_' . $row['name']],
					'title_link' => $row['title_link'],
					'target' => $row['target'],
					'icon' => $row['icon'],
					'minheight' => $row['minheight'],
					'minheight_type' => $row['minheight_type'],
					'files' => $row['files'],
					'header_files' => $row['header_files'],
					'functions' => $row['functions'],
				);

			if (!isset($clone[$row['id_clone']]['params'][$row['id_param']]))
				if (!empty($row['id_param']))
					$clone[$row['id_clone']]['params'][$row['id_param']] = array(
						'name' => $row['param_name'],
						'type' => $row['param_type'],
						'value' => $row['param_value'],
						'fieldset' => $row['param_fieldset'],
				);

			if (!is_null($row['position']))
				$disabled_count++;

			// make sure the params are ordered correctly for when we insert them.
			// wouldn't want params to be ordered differently from the others.
			if (!empty($clone[$row['id_clone']]['params'][$row['id_param']]) && count($clone[$row['id_clone']]['params'][$row['id_param']]) >= 1)
				ksort($clone[$row['id_clone']]['params'], SORT_NUMERIC);
		}
		// Uses less memory when we free the result here, since we could be grabbing an array of id_clones to clone.
		$smcFunc['db_free_result']($request);
	}
	else
	{
		// Creating a whole new Layout! :)
		global $sourcedir;

		require_once($sourcedir . '/Subs-DreamPortal.php');

		$modules = loadDefaultModuleConfigs(array(), true);

		// We'll want to select all modules if it's the Admin, else only the allowed modules.
		// We are grabbing here from MODULE IDS, not clone ids!
		$request = $smcFunc['db_query']('', '
			SELECT
				dm.id_module, dm.name
				FROM {db_prefix}dp_modules AS dm
				LEFT JOIN {db_prefix}dp_groups AS dg ON (id_member = {int:id_member} AND dg.id_group = {int:curr_group})
				LEFT JOIN {db_prefix}dp_layouts AS dl ON (dl.id_layout = {int:id_layout} AND dl.id_group = dg.id_group)
				ORDER BY NULL',
			array(
				'id_member' => 0,
				'curr_group' => 1,
				'id_layout' => $_SESSION['selected_layout']['id_layout'],
				'one' => 1,
			)
		);

		$clone = array();
		$clone_name = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($clone[$row['id_module']]))
			{
				$clone[$row['id_module']] = $modules[$row['name']];
				$clone_name[$row['id_module']] = $row['name'];
			}
		}

		$smcFunc['db_free_result']($request);
	}

	$i = 0;
	foreach ($clone as $clone_key => $clone_value)
	{
		// Add the module info to the database
		$columns = array(
			'name' => 'string',
			'title' => 'string',
			'title_link' => 'string',
			'target' => 'int',
			'icon' => 'string',
			'files' => 'string',
			'header_files' => 'string',
			'functions' => 'string',
			'container' => 'int',
			'id_member' => 'int',
			'id_module' => 'int',
			'is_clone' => 'int',
		);

		if (isset($clone_value['minheight'], $clone_value['minheight_type']))
			$columns = array_merge($columns, array('minheight' => 'int', 'minheight_type' => 'int'));

		$keys = array(
			'id_clone',
			'id_module',
			'id_member'
		);

		$data = array(
			empty($id_clones) ? $clone_name[$clone_key] : $clone_value['name'],
			$clone_value['title'],
			$clone_value['title_link'],
			$clone_value['target'],
			$clone_value['icon'],
			$clone_value['files'],
			$clone_value['header_files'],
			$clone_value['functions'],
			!empty($clone_value['container']) ? 1 : 0,
			0,
			empty($id_clones) ? $clone_key : $clone_value['id_module'],
			empty($id_clones) ? 0 : 1,
		);

		// Adding in the minheight values here.
		if (isset($clone_value['minheight'], $clone_value['minheight_type']))
			$data = array_merge($data, array(!empty($clone_value['minheight']) ? $clone_value['minheight'] : 0, !empty($clone_value['minheight_type']) ? $clone_value['minheight_type'] : 0));

		$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_clones',  $columns, $data, $keys);

		// We need to tell the parameters table which ID was inserted
		$iid = $smcFunc['db_insert_id']('{db_prefix}dp_module_clones', 'id_clone');

		// In case we have parameters...
		if (isset($clone_value['params']))
		{
			$columns = array(
				'id_clone' => 'int',
				'id_module' => 'int',
				'name' => 'string-255',
				'type' => 'string-255',
				'value' => 'string-65536',
				'fieldset' => 'string-255'
			);

			$keys = array(
				'id_param',
				'id_clone',
				'id_module',
			);

			// Also clone the parameters, if any.
			foreach($clone_value['params'] as $key => $param)
			{
				if (isset($param['extend']))
				{
					// Extending the value of the parameter from within a function!
					if ($param['extend'] == 'function')
					{
						$extend = explode(':', $param['value']);
						// Require the file.
						if (file_exists($context['dpmod_modules_dir'] . '/' . (empty($id_clones) ? $clone_name[$clone_key] : $clone_value['name']) . '/' . $extend[0]))
						{
							require_once($context['dpmod_modules_dir'] . '/' . (empty($id_clones) ? $clone_name[$clone_key] : $clone_value['name']) . '/' . $extend[0]);

							// Set the value to the returned result of the function.
							if (function_exists($extend[1]))
								$param['value'] = $extend[1]();
						}
					}
				}

				// Insert the parameters, we'll need to insert the module id also.
				$data = array(
					$iid,
					empty($id_clones) ? $clone_key : $clone_value['id_module'],
					empty($id_clones) ? $key : $param['name'],
					$param['type'],
					$param['value'],
					!empty($param['fieldset']) ? $param['fieldset'] : '',
				);
				$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_parameters', $columns, $data, $keys);
			}
		}

		// One more to go - insert the layout.
		$columns = array(
			'id_layout_position' => 'int',
			'id_layout' => 'int',
			'id_module' => 'int',
			'id_clone' => 'int',
			'position' => 'int',
			'empty' => 'int',
		);

		$data = array(
			$id_layout_position,
			(int) $_SESSION['selected_layout']['id_layout'],
			0,
			$iid,
			empty($id_clones) ? $i : $disabled_count,
			empty($clone_value['container']) ? 1 : 0,
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_positions',  $columns, $data, $keys);

		$i++;
	}

	$diid = $smcFunc['db_insert_id']('{db_prefix}dp_module_positions', 'id_position');

	// Lastly, throw in the SMF section.
	$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_positions',
		array('id_layout_position' => 'int', 'id_layout' => 'int', 'id_module' => 'int', 'id_clone' => 'int', 'position' => 'int'),
		array((int) $smf_id, (int) $_SESSION['selected_layout']['id_layout'], 0, 0, 0),
		$keys
	);

	// That's all she wrote.
	if (isset($_GET['xml']))
		die('
				<div class="DragBox clonebox' . (!empty($options['dp_mod_color']) ? $options['dp_mod_color'] : '1') . ' draggable_module centertext" id="dreammod_' . $diid . '">
						<p>' . $clone[$context['dp_modid']]['title'] . '</p>
						<p class="dp_inner"><a href="' . $scripturl . '?action=admin;area=dplayouts;sa=modifymod;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['dp_admin_modules_manage_modify'] . '</a> | <a href="' . $scripturl . '?action=admin;area=dplayouts;sa=clonemod' . (empty($id_clones) ? ';mod' : '') . ';xml;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . $txt['dpmodule_declone'] . '</a></p>
				</div>');
	else
		return;
}

/**
 * This function will remove clones and all their properties from the id_clone value OR the id_module value.
 *
 * @param int $admin values are:
 * - 0 = Remove ALL Clones, including the Admins for that module!
 * - 2 = Remove ONLY the Admins Clones for that module!
 * Default set to remove all clones, cept for the Admins clones
 * @since 1.0
 */

//!!! Removes Clones based on clone value(s), or module value(s).
function DPDeclone($clones = array(), $modules = array(), $admin = 1)
{
	 global $context, $smcFunc;

	// Just some extra security here!
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		return;

	checkSession('get');

	// Must be an Admin, heh ;)
	if ($admin != 1)
	{
		if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		 	return;
	}

	 // Nothing to remove!
	 if (count($modules) + count($clones) <= 0)
	  return;

	// May need more work later, if we allow people to remove all clones only from a layout.
	// Even though, technically they are all clones anyways.
	$layout_delete = count($clones) > 1;

	 $ids = array();
	 $ids = count($clones) > 0 ? $clones : $modules;

	 $where = (count($clones) > 0 ? 'dmc.id_clone' : 'dmc.id_module') . ' IN ({array_int:ids})' . (!empty($admin) ? ' AND ' . ($admin == 1 ? 'dmc.id_member != 0' : 'dmc.id_member = 0') : '');

	 // Does it exist, eg. is it installed?
	 $request = $smcFunc['db_query']('', '
	  SELECT
		dmc.id_clone, dmc.name, dmp2.id_param, dmp.id_position' . (!$layout_delete ? ', dmp.id_layout, dmp.id_layout_position, dmp.position' : '') . '
	  FROM {db_prefix}dp_module_clones AS dmc
		LEFT JOIN {db_prefix}dp_module_parameters AS dmp2 ON (dmc.id_clone = dmp2.id_clone AND dmp2.type={string:file_input})
		LEFT JOIN {db_prefix}dp_module_positions AS dmp ON (dmc.id_clone = dmp.id_clone)
	  WHERE ' . $where,
	  array(
		'ids' => $ids,
		'file_input' => 'file_input',
	  )
	 );

	 // No clones exist, so return outta here.
	 if ($smcFunc['db_num_rows']($request) == 0)
	  return;

	 $clone_info = array();

	 while ($row = $smcFunc['db_fetch_assoc']($request))
	 {
		if (!isset($clone_info['cloneids'][$row['id_clone']]))
		{
			$clone_info['cloneids'][$row['id_clone']] = $row['id_clone'];
			$clone_info['params'] = array();

			if(!isset($clone_info['name'][$row['id_clone']]))
			$clone_info['name'][$row['id_clone']] = $row['name'];

			if(!isset($clone_info['id_position'][$row['id_clone']]))
			$clone_info['id_position'][$row['id_clone']] = $row['id_position'];

			if (!$layout_delete)
				$clone_info['position'][$row['id_layout']]['pos' . $row['id_position'] . $row['position'] . '_' . $row['id_layout_position']] = $row['position'];
		}

		// Getting all file_input param ids.
		if (!empty($row['id_param']))
		{
			$clone_info['params'][] = $row['id_param'];
			if (!isset($clone_info['folderName'][$row['id_param']]))
				$clone_info['folderName'][$row['id_param']] = $row['name'];
		}
	 }

	 $smcFunc['db_free_result']($request);

	 // Removing all clone positions from the layout!
	 $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_module_positions
		WHERE id_clone IN ({array_int:id_clones}) AND id_module={int:zero}',
		array(
			'id_clones' => $clone_info['cloneids'],
			'zero' => 0,
		)
	 );

	if (!$layout_delete)
		foreach($clone_info['position'] as $id_layout => $id_layout_pos)
		{
			foreach($id_layout_pos as $key => $position_val)
			{
				$lPos = explode('_', $key);
				$lPosId = (int) $lPos[1];

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_positions
					SET
						position = position - 1
					WHERE position > {int:position} AND id_layout = {int:id_layout} AND id_layout_position = {int:id_layout_position}',
					array(
						'id_layout' => (int) $id_layout,
						'position' => (int) $position_val,
						'id_layout_position' => $lPosId,
					)
				);
			}
		}

	 // Are there any files to remove?
	 if (isset($clone_info['params'][0]))
	 {
	 	global $sourcedir;

		require_once($sourcedir . '/Subs-DreamPortal.php');

		$request = $smcFunc['db_query']('', '
			SELECT
			id_file, filename, id_param, file_hash
			FROM {db_prefix}dp_module_files
			WHERE id_param IN ({array_int:params})',
			array(
				'params' => $clone_info['params'],
			)
		);
		$filename = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$filename[$row['id_file']] = getFilename($row['filename'], $row['id_file'], $context['dpmod_files_dir'] . $clone_info['folderName'][$row['id_param']], false, $row['file_hash']);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_module_files
			WHERE id_param IN ({array_int:id_params})',
			array(
				'id_params' => $clone_info['params'],
			)
		);

		// Delete the files associated with the clone(s).
		foreach($filename as $file)
			@unlink($file);
	 }

	 // Removing parameters
	 $smcFunc['db_query']('', '
		  DELETE FROM {db_prefix}dp_module_parameters
		  WHERE id_clone IN ({array_int:id_clones})',
		  array(
			'id_clones' => $clone_info['cloneids'],
		  )
	 );

	 // Last but not least!
	 $smcFunc['db_query']('', '
		  DELETE FROM {db_prefix}dp_module_clones
		  WHERE id_clone IN ({array_int:id_clones})',
		  array(
			'id_clones' => $clone_info['cloneids'],
		  )
	 );

	 // That's all she wrote.
	 if (isset($_GET['xml']))
	 	die('deleted' . $clone_info['id_position'][$clones[0]]);
	 else
		return;
}

/**
 * Removes all traces of a layout.
 *
 * @param int $id_layout the layout to delete
 * @return bool true on success; false  otherwise.
 * @since 1.0
 */
function DPDeleteLayout($id_layout)
{
	global $smcFunc;

	// Just some extra security here!
	if (!allowedTo(array('manage_dplayouts', 'admin_dplayouts')))
		fatal_lang_error('dp_no_permission', false);

	checkSession('get');

	$member_opt = 2;

	$delete_modules = array();
	$delete_clones = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_clone FROM {db_prefix}dp_module_positions
		WHERE id_layout = {int:id_layout}',
		array(
			'id_layout' => $id_layout,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		return false;

	while ($row = $smcFunc['db_fetch_assoc']($request))
		if (!empty($row['id_clone']))
			$delete_clones[] = $row['id_clone'];

	DPDeclone($delete_clones, array(), $member_opt);

	foreach (array('layouts', 'actions', 'layout_positions', 'module_positions') as $table_name)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_' . $table_name . '
			WHERE id_layout = {int:id_layout}',
			array(
				'id_layout' => $id_layout,
			)
		);

	// Clear the sessions.
	unset($_SESSION['selected_layout']);
	unset($_SESSION['layouts']);
	return true;
}

?>