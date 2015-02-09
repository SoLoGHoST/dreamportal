<?php
/**************************************************************************************
* ManageDPExtend.php                                                                  *
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

function loadGeneralExtendParameters($subActions = array(), $defaultAction = '')
{
	global $context, $sourcedir;

	// If DreamModules or DreamTemplates doesn't exist, just skip it!
	loadLanguage('DreamModules', '', false);
	loadLanguage('DreamTemplates', '', false);

	// These are required language files!
	loadLanguage('DreamHelp+ManageSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	// load the template and the style sheet needed
	loadTemplate('ManageDPExtend', 'dreamportal');

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));

	$context['sub_action'] = $_REQUEST['sa'];
}
	
function dpManageExtend()
{
	global $context, $dpfiles, $txt;

	// Do you have permission to be here?!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	$dpfiles['languages'] = array('DreamHelp', 'DreamPermissions', 'DreamPortal', 'ManageDP');

	$subActions = array(
		// Add Modules section
		'dpaddmodules' => 'AddDPModules',
		'dpinstallmodule' => 'InstallDPModule',
		'dpuninstallmodule' => 'UninstallDPModule',
		'dpdeletemodule' => 'DeleteDPModule',
		'dpaddtemplates' => 'AddDPTemplates',
		'dpinstalltemplate' => 'InstallDPTemplate',
		'dpuninstalltemplate' => 'UninstallDPTemplate',
		'dpdeletetemplate' => 'DeleteDPTemplate',
		'dpaddlanguages' => 'AddDPLanguages',
		'dpdeletelanguage' => 'DeleteDPLanguage',
	);

	loadGeneralExtendParameters($subActions, 'dpaddmodules');

	if ($context['sub_action'] != 'uploadmod')
		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => &$txt['dp_admin_extend'],
			'help' => 'dp_admin_extend_help',
			'tabs' => array(
				'dpaddmodules' => array(
					'description' => $txt['dp_admin_extend_addmodules_desc'],
				),
				'dpaddtemplates' => array(
					'description' => $txt['dp_admin_extend_addtemplates_desc'],
				),
				'dpaddlanguages' => array(
					'description' => $txt['dp_admin_extend_addlanguages_desc'],
				),
			),
		);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// Handles installation of a module and custom creation of a module.
function AddDPModules()
{
	global $context, $txt, $smcFunc, $scripturl, $modSettings;

	// Just some extra security here!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	$context['page_title'] = $txt['dp_admin_title_add_modules'];
	$context['dp_extension_vars'] = array(
		'none_exist' => sprintf($txt['no_extensions_exist'], $txt['dptext_module'], $txt['dptext_modules']),
		'name_col' => sprintf($txt['dp_extension_name'], $txt['dptext_module']),
		'sa' => 'dpaddmodules',
		'modsettings_var' => 'dp_add_modules_limit',
		'input_name' => 'dp_modules',
		'upload_txt' => sprintf($txt['dp_upload_extension'], $txt['dptext_module']),
		'extension_to_upload' => sprintf($txt['extension_to_upload'], $txt['dptext_module']),
	);	
	
	// Setup the array to be used.
	$moduleVars = array(
		'type' => 'modules',
		'dir' => $context['dpmod_modules_dir'],
		'has_settings' => true,
		'settings_href' => 'area=dplayouts;sa=modifymod;modid=',
		'sa' => array(
			'install' => 'dpinstallmodule',
			'uninstall' => 'dpuninstallmodule',
			'delete' => 'dpdeletemodule',
		),
		'query' => array(
			'select' => array('id' => 'id_module', 'name' => 'name'),
			'table' => 'dp_modules',
		),
	);
	
	
	if (!empty($modSettings['dp_add_modules_limit']))
	{
		$_REQUEST['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$mod_name = isset($_GET['dpmod']) ? trim($_GET['dpmod']) : '';

		$dp_add_module = array(
			'start' => $mod_name == '' ? $_REQUEST['start'] : GetDPAddedExtensions($moduleVars, 0, $modSettings['dp_add_modules_limit'], $mod_name),
			'limit' => $modSettings['dp_add_modules_limit'],
		);

		$context['extension_info'] = GetDPAddedExtensions($moduleVars, $dp_add_module['start'], $dp_add_module['limit']);

		if (!empty($context['extension_info']))
			$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=dpextend;sa=dpaddmodules', $dp_add_module['start'], $context['dpextend_total_modules'], $dp_add_module['limit'], false);

	}
	else
		$context['extension_info'] = GetDPAddedExtensions($moduleVars);

	// Saving?
	if (isset($_POST['upload']))
	{
		// Get all Installed functions.
		$request = $smcFunc['db_query']('', '
		SELECT
			name, functions
		FROM {db_prefix}dp_modules
		WHERE functions != {string:empty_string}',
			array(
				'empty_string' => '',
			)
		);

		$installed_functions = array();
		$installed_names = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_functions[] = $row['functions'];
			$installed_names[] = $row['name'];
		}
		$smcFunc['db_free_result']($request);

		$modVars = array(
			'type' => 'module',
			'dir' => $context['dpmod_modules_dir'],
			'post_name' => 'dp_modules',
			'sa' => 'dpaddmodules',
			'lang_vars' => array(
				'filename' => 'DreamModules',
				'type' => 'Module',
				'title' => 'dpmod_',
				'desc' => array('dpmodinfo_', 'dpmoddesc_'),
			),
		);

		UploadExtension($modVars, array_merge($context['dp_restricted_names']['modules'], $installed_names), $installed_functions);
	}
}

// Installs an added module into all layouts for the admin, placing them into the disabled modules section!
function InstallDPModule()
{
	global $context, $sourcedir, $smcFunc, $txt, $modSettings;

	// Only the Admin here...
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We want to define our variables now...
	$name = $_GET['name'];

	if (is_dir($context['dpmod_modules_dir']))
	{
        $dir = @opendir($context['dpmod_modules_dir']);

		$dirs = array();
		while ($file = readdir($dir))
		{
			$retVal = GetDPModuleInfo('', '', '', $context['dpmod_modules_dir'], $file, $name);
			if ($retVal === false)
				continue;
			else
				$module_info[$file] = $retVal;
		}
	}

	// Gives us all functions for that module, separated by a "+" sign.
	$file_functions = $module_info[$name]['functions'];

	// Now let's get all installed functions from modules.
	$request = $smcFunc['db_query']('', '
		SELECT
			functions
		FROM {db_prefix}dp_modules
		WHERE functions != {string:empty_string}',
		array(
				'empty_string' => '',
		)
	);

	$installed_functions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$installed_functions[] = $row['functions'];

	$smcFunc['db_free_result']($request);

	// Check for duplicate module function names, if found, can not install.
	foreach($installed_functions as $key => $func)
	{
		foreach (explode('+', $installed_functions[$key]) as $fName)
			if (in_array($fName, explode('+', $file_functions)))
				fatal_lang_error('dp_extend_function_duplicates', false, array($txt['dptext_module_lower']));
	}

	// Installing...
	$request = $smcFunc['db_query']('', '
		SELECT
			dg.id_group, dl.id_layout, dlp.id_layout_position, dmp.position
		FROM {db_prefix}dp_groups AS dg
			LEFT JOIN {db_prefix}dp_layouts AS dl ON (dl.id_group = dg.id_group)
			LEFT JOIN {db_prefix}dp_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout AND dlp.enabled = {int:disabled})
			LEFT JOIN {db_prefix}dp_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position)
		WHERE dg.id_member = {int:zero}',
		array(
				'zero' => 0,
				'disabled' => -1,
		)
	);

	$disabled_sections = array();
	$positions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($disabled_sections[$row['id_group']][$row['id_layout']]))
			$disabled_sections[$row['id_group']][$row['id_layout']] = array(
				'info' => $module_info[$name],
				'id_layout_position' => $row['id_layout_position']
			);

		// Increment the positions...
		if (!is_null($row['position']))
		{
			if (!isset($positions[$row['id_layout']][$row['id_layout_position']]))
				$positions[$row['id_layout']][$row['id_layout_position']] = 1;
			else
				$positions[$row['id_layout']][$row['id_layout_position']]++;
		}
		else
			$positions[$row['id_layout']][$row['id_layout_position']] = 0;
	}

	$smcFunc['db_free_result']($request);

	ksort($disabled_sections, SORT_NUMERIC);
		foreach($disabled_sections as $g => $layout)
			ksort($disabled_sections[$g], SORT_NUMERIC);

	foreach($disabled_sections as $group => $gLayout)
	{
		foreach($disabled_sections[$group] as $id => $module)
		{
			$default_layout = $group == 1 && $id == 1 ? true : false;

			// We really need an id_module for clones.
			if (!$default_layout && !isset($id_module))
				continue;

			// Add the module info to the database
			$columns = array(
				'name' => 'string',
				'title' => 'string',
				'title_link' => 'string',
				'target' => 'int',
				'icon' => 'string',
				'functions' => 'string',
				'files' => 'string',
				'header_files' => 'string',
				'container' => 'int',
			);

			$data = array(
				(string) $name,
				$module['info']['title'],
				!empty($module['info']['title_link']) ? $module['info']['title_link'] : '',
				!empty($module['info']['target']) ? $module['info']['target'] : 0,
				$module['info']['icon'],
				$file_functions,
				$module['info']['files'],
				$module['info']['header_files'],
				!empty($module['info']['container']) ? 1 : 0,
			);

			if (!$default_layout)
			{
				$columns = array_merge($columns, array('id_module' => 'int', 'id_member' => 'int'));
				$data = array_merge($data, array((int) $id_module, 0));
			}

			$keys = $default_layout ? array('id_module', 'name') : array('id_clone', 'id_module', 'id_member');

			$table_name = $default_layout !== false ? 'dp_modules' : 'dp_module_clones';

			$smcFunc['db_insert']('ignore', '{db_prefix}' . $table_name,  $columns, $data, $keys);

			// We need to tell the parameters table which ID was inserted
			$iid = $smcFunc['db_insert_id']('{db_prefix}' . $table_name, $default_layout !== false ? 'id_module' : 'id_clone');

			if ($default_layout)
				$id_module = $iid;

			// parameters
			$columns = array(
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

			if (!$default_layout)
				$columns = array_merge(array('id_clone' => 'int'), $columns);

			// Any parameters that came with the module are also processed
			foreach ($module['info']['params'] as $param_name => $param)
			{
				// If we have a function that returns the value for this parameter, than we need to call it.
				if (isset($param['extend']))
				{
					// Extending the value of the parameter from within a function!
					if ($param['extend'] == 'function')
					{
						$extend = explode(':', $param['value']);
						// Require the file.
						if (file_exists($context['dpmod_modules_dir'] . '/' . $name . '/' . $extend[0]))
						{
							require_once($context['dpmod_modules_dir'] . '/' . $name . '/' . $extend[0]);

							// Set the value to the returned result of the function.
							if (function_exists($extend[1]))
								$param['value'] = $extend[1]();
						}
					}
				}

				$data = array(
					$iid,
					$id_module,
					$param_name,
					$param['type'],
					$param['value'],
					isset($param['fieldset']) ? $param['fieldset'] : '',
				);

				if ($default_layout)
				{
					unset($data[1]);
					$data = array_values($data);
				}

				$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_parameters', $columns, $data, $keys);
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
				(int) $module['id_layout_position'],
				(int) $id,
				$default_layout !== false ? (int) $iid : 0,
				$default_layout !== false ? 0 : (int) $iid,
				empty($positions[$id][$module['id_layout_position']]) ? 0 : (int) $positions[$id][$module['id_layout_position']],
				empty($module['info']['container']) ? 1 : 0,
			);

			$keys = array(
				'id_position',
				'id_layout_position',
				'id_layout',
				'id_module',
				'id_clone',
			);
			$smcFunc['db_insert']('ignore', '{db_prefix}dp_module_positions',  $columns, $data, $keys);
		}
	}

	// Module is installed, now lets fix any Dream Action files and make them available once again!
    if (is_dir($context['dpmod_module_actionsdir']))
	{
		$actionsdir = @opendir($context['dpmod_module_actionsdir']);

		while (false !== ($obj = readdir($actionsdir)))
		{
			if($obj == '.' || $obj == '..' || $obj == '.htaccess' || $obj == 'index.php')
				continue;

			if (substr($obj, -5) == '.temp' && substr($obj, 0, strlen($name) + 1) == $name . '_')
				rename($context['dpmod_module_actionsdir'] . '/' . $obj, $context['dpmod_module_actionsdir'] . '/' . substr($obj, 0, strlen($obj) - 5));
		}
		closedir($actionsdir);
	}

	// Time to go...
	$redirect = 'action=admin;area=dpextend;sa=dpaddmodules' . (!empty($modSettings['dp_add_modules_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : '');
	redirectexit($redirect);
}

// Uninstalls an added module from all layouts.
function UninstallDPModule()
{
	global $context, $smcFunc, $txt, $modSettings;

	// Extra security!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	// isset is better for this.
	if (isset($_GET['name']))
		$name = $_GET['name'];
	elseif (isset($context['delete_modname']) && trim($context['delete_modname']) != '')
		$name = $context['delete_modname'];

	// Can't seem to find it.
	if (!isset($name))
		fatal_lang_error('dp_extend_uninstall_error', false, array($txt['dptext_module_lower']));

	// Does it exist, eg. is it installed?
	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dmp.id_param, dmc.id_clone
		FROM {db_prefix}dp_modules AS dm
			LEFT JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.type = {string:file_input})
			LEFT JOIN {db_prefix}dp_module_clones AS dmc ON (dmc.id_module = dm.id_module)
		WHERE dm.name = {string:name}',
		array(
			'zero' => 0,
			'file_input' => 'file_input',
			'name' => $name,
		)
	);

	// Trying to uninstall something that doesn't exist!
	if ($smcFunc['db_num_rows']($request) == 0)
		if (isset($context['delete_modname']))
			return;
		else
			redirectexit('action=admin;area=dpextend;sa=dpaddmodules' . (!empty($modSettings['dp_add_modules_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));

	$module_info = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($module_info['id']))
		{
			$module_info['id'] = !empty($row['id_module']) ? $row['id_module'] : '';
			$module_info['params'] = array();
			$module_info['clones'] = array();
		}

		// Getting all file_input param ids.
		if (!empty($row['id_param']))
			$module_info['params'][] = $row['id_param'];

		if (!empty($row['id_clone']))
			$module_info['clones'][] = $row['id_clone'];
	}
	$smcFunc['db_free_result']($request);

	// Check to be sure we have a module id value before continuing.
	if (empty($module_info['id']))
		if (isset($context['delete_modname']))
			return;
		else
			redirectexit('action=admin;area=dpextend;sa=dpaddmodules' . (!empty($modSettings['dp_add_modules_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));

	// Selecting the positions.
	if (isset($module_info['clones'][0]))
		$query = 'id_module = {int:id_module} || id_clone IN ({array_int:id_clones})';
	else
		$query = 'id_module = {int:id_module}';

	$request = $smcFunc['db_query']('', '
		SELECT
			id_position, id_layout_position, id_layout, position
		FROM {db_prefix}dp_module_positions
		WHERE ' . $query,
		array(
			'zero' => 0,
			'id_module' => $module_info['id'],
			'id_clones' => $module_info['clones'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_info['position'][$row['id_layout']]['pos' . $row['id_position'] . $row['position'] . '_' . $row['id_layout_position']] = $row['position'];
		$module_info['id_positions'][] = $row['id_position'];
	}

	$smcFunc['db_free_result']($request);

	// Remove all module and clone positions from the layout!
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_module_positions
		WHERE id_position IN ({array_int:id_positions})',
		array(
			'id_positions' => $module_info['id_positions'],
		)
	);

	foreach($module_info['position'] as $id_layout => $id_layout_pos)
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

	// Let's remove rows via file_input parameter type.
	if (isset($module_info['params'][0]))
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Package.php');
		
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_module_files
			WHERE id_param IN ({array_int:id_params})',
			array(
				'id_params' => $module_info['params'],
			)
		);

		// Remove module's files via file_input.
		deltree($context['dpmod_files_dir'] . $name);
	}

	// Remove all clones
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_module_clones
		WHERE id_module={int:id_module}',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Remove the actual module now.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_modules
		WHERE id_module = {int:id_module}
		LIMIT 1',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Remove the parameters
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_module_parameters
		WHERE id_module = {int:id_module}',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Deleting a module?
	if (isset($context['delete_modname']))
		return;
	else
	{
		// Uninstall any Dream Actions that are defined!
        if (is_dir($context['dpmod_module_actionsdir']))
		{
            $actionsdir = @opendir($context['dpmod_module_actionsdir']);

			while (false !== ($obj = readdir($actionsdir)))
			{
				if($obj == '.' || $obj == '..' || $obj == '.htaccess' || $obj == 'index.php')
					continue;

				if (substr($obj, -5) != '.temp' && substr($obj, 0, strlen($name) + 1) == $name . '_')
					rename($context['dpmod_module_actionsdir'] . '/' . $obj, $context['dpmod_module_actionsdir'] . '/' . $obj . '.temp');
			}
			closedir($actionsdir);
		}
	}

	// Where did they uninstall from?
	$redirect = 'action=admin;area=dpextend;sa=dpaddmodules' . (!empty($modSettings['dp_add_modules_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : '');
	redirectexit($redirect);
}

// Removes a module with all its files from the filesystem.
function DeleteDPModule()
{
	global $context, $modSettings, $txt, $settings, $boarddir, $sourcedir;

	// Extra security here.
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We want to define our variables now...
	$name = $_GET['name'];

	// Before deleting, is it uninstalled?
	$context['delete_modname'] = $name;
	UninstallDPModule();
	unset($context['delete_modname']);

	// Now we need to get the language and strings that need to be removed.
	$moduleInfo = file_get_contents($context['dpmod_modules_dir'] . '/' . $name . '/info.xml');
	loadClassFile('Class-Package.php');
	$moduleInfo = new xmlArray($moduleInfo);

	// !!! Error message of some sort?
	if (!$moduleInfo->exists('module[0]'))
		fatal_lang_error('dp_extend_package_corrupt', false, array($txt['dptext_module_lower']));

	$moduleInfo = $moduleInfo->path('module[0]');
	$module = $moduleInfo->to_array();

	// Checking the database tag to be sure it is set right!
	if (isset($module['database']['uninstall']))
	{
		if (parseString($module['database']['uninstall'], 'filepath', false) != 1)
		{
			if (file_exists($context['dpmod_modules_dir'] . '/' . $name . '/' . $module['database']['uninstall']))
			{
				global $txt, $boarddir, $user_info, $sourcedir, $modSettings, $context, $settings, $forum_version, $smcFunc;

				require_once($context['dpmod_modules_dir'] . '/' . $name . '/' . $module['database']['uninstall']);
			}
		}
		else
			fatal_lang_error('invalid_database_filepath', false);
	}

	if (isset($module['languages']) && is_array($module['languages']))
	{
		$languages_dir = $settings['default_theme_dir'] . '/languages';
		$mod_langs = array();
		$mod_langs = $module['languages'];
		// So we'll do all languages they have defined in here.
		foreach($mod_langs as $lang => $langFile)
		{
			// the language... english, british_english, russian, etc. etc.
			$language = $lang;

			foreach ($langFile as $utfType => $value)
			{
				$utf8 = $utfType == 'utf8' ? '-utf8' : '';

				// This holds the current file we are working on.
				$curr_lang_file = $languages_dir . '/DreamModules.' . $language . $utf8 . '.php';

				// We can't read from the file if it doesn't exist.
				if (!file_exists($curr_lang_file))
					continue;

				// This helps to remove the language strings for the module, since $name is unique!
				$module_begin_comment = '// ' . ' Dream Portal Module - ' . $name . ' BEGIN...';
				$module_end_comment = '// ' . ' Dream Portal Module - ' . $name . ' END!';

				$fp = fopen($curr_lang_file, 'rb');
				$content = fread($fp, filesize($curr_lang_file));
				fclose($fp);

				// Searching within the string, extracting only what we need.
				$start = strpos($content, $module_begin_comment);
				$end = strpos($content, $module_end_comment);

				// We can't do this unless both are found.
				if ($start !== false && $end !== false)
				{
					$begin = substr($content, 0, $start);
					$finish = substr($content, $end + strlen($module_end_comment));

					$new_content = $begin . $finish;

					// Write it into the file, or create the file.
					$fo = fopen($curr_lang_file, 'wb');
					@fwrite($fo, $new_content);
					fclose($fo);
				}
			}
		}
	}

	require_once($sourcedir . '/Subs-Package.php');

	// Removing icons?
	$delete_icons = empty($modSettings['dp_enable_custommod_icons']);

	if ($delete_icons)
		deltree($context['dpmod_icon_dir'] . $name);

	// Delete any Dream Action Files by this module...
	$dh = @opendir($context['dpmod_module_actionsdir']);
	while (false !== ($action_file = readdir($dh)))
	{
		if($action_file == '.' || $action_file == '..' || $action_file == '.htaccess' || $action_file == 'index.php')
			continue;

		if (substr($action_file, 0, strlen($name) + 1) == $name . '_')
			@unlink($context['dpmod_module_actionsdir'] . '/' . $action_file);
	}
	closedir($dh);

	// Check all themes and remove css/js headers as needed.
	$which = array();
	$td = opendir($boarddir . '/Themes');
	while (false !== ($theme_name = readdir($td)))
	{
		if (is_dir($boarddir . '/Themes/' . $theme_name . '/dreamportal/modules/' . $name))
			$which[] = $theme_name . '/dreamportal/modules/' . $name;

		if (is_dir($boarddir . '/Themes/' . $theme_name . '/css/dreamportal/modules/' . $name))
			$which[] = $theme_name . '/css/dreamportal/modules/' . $name;
			
		if (is_dir($boarddir . '/Themes/' . $theme_name . '/scripts/dreamportal/modules/' . $name))
			$which[] = $theme_name . '/scripts/dreamportal/modules/' . $name;
	}
	closedir($td);

	// Remove all directories for css and scripts within all Themes that they exist in!
	foreach($which as $mPath)
		deltree($boarddir . '/Themes/' . $mPath);

	// Last, but not least, remove the images and files used for this module!
	deltree($context['dpmod_image_dir'] . $name);
	deltree($context['dpmod_modules_dir'] . '/' . $name);

	// A light heart and an easy step paves the way ;)
	redirectexit('action=admin;area=dpextend;sa=dpaddmodules' . (!empty($modSettings['dp_add_modules_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));
}

// Uploads a module or template package.
function UploadExtension($extendVars, $reservedNames = array(), $installed_functions = array())
{
	global $txt, $context, $modSettings, $settings, $sourcedir, $boarddir;
	
	// Just some extra security here!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		return;

	validateSession();

	require_once($sourcedir . '/Subs-Package.php');

	if ($_FILES[$extendVars['post_name']]['error'] === UPLOAD_ERR_OK)
	{
		// Check for tar.gz or zip files.
		$tar_gz_pos = strpos(strtolower($_FILES[$extendVars['post_name']]['name']), '.tar.gz');
		$zip_pos = strpos(strtolower($_FILES[$extendVars['post_name']]['name']), '.zip');

		if (($tar_gz_pos === false || $tar_gz_pos != strlen($_FILES[$extendVars['post_name']]['name']) - 7) && ($zip_pos === false || $zip_pos != strlen($_FILES[$extendVars['post_name']]['name']) - 4))
			fatal_lang_error('dp_extend_upload_error_type', false);

		// Make sure it has a valid filename.
		$_FILES[$extendVars['post_name']]['name'] = parseString($_FILES[$extendVars['post_name']]['name'], 'uploaded_file');

		// Extract it to this directory.
		$pathinfo = pathinfo($_FILES[$extendVars['post_name']]['name']);
		$extend_path = $extendVars['dir'] . '/' . basename($_FILES[$extendVars['post_name']]['name'],'.'.$pathinfo['extension']) . '_temp';

		// Check if name already exists, or restricted, or doesn't have a name.
		if (is_dir($extend_path) || in_array(substr($_FILES[$extendVars['post_name']]['name'], 0, strpos($_FILES[$extendVars['post_name']]['name'], '.')), $reservedNames) || substr($_FILES[$extendVars['post_name']]['name'], 0, strpos($_FILES[$extendVars['post_name']]['name'], '.')) == '')
			fatal_lang_error('dp_extend_restricted_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));

		// Extract the package.
		$context['extracted_files'] = read_tgz_file($_FILES[$extendVars['post_name']]['tmp_name'], $extend_path);

		foreach ($context['extracted_files'] as $file)
			if (basename($file['filename']) == 'info.xml')
			{
				// Parse it into an xmlArray.
				loadClassFile('Class-Package.php');
				$extendInfo = new xmlArray(file_get_contents($extend_path . '/' . $file['filename']));

				// !!! Error message of some sort?
				if (!$extendInfo->exists($extendVars['type'] . '[0]'))
				{
					deltree($extend_path);
					fatal_lang_error('dp_extend_package_corrupt', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
				}

				// End the loop. We found our man!
				break;
			}

		if (!isset($extendInfo))
		{
			deltree($extend_path);
			fatal_lang_error('dp_extend_infoxml_missing', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
		}

		$extendInfo = $extendInfo->path($extendVars['type'] . '[0]');
		$extend = $extendInfo->to_array();

		if (isset($extend['name']) && trim($extend['name']) != '')
		{
			if (!isset($p_extend_name))
				$p_extend_name = $extend['name'];
		}
		else
		{
			deltree($extend_path);
			fatal_lang_error('dp_extend_restricted_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
		}

		// So, the real modules path is here!
		$real_extendpath = $extendVars['dir'] . '/' . $p_extend_name;

		// Extension already exists, Remove the temp directory and error out.
		if (is_dir($real_extendpath))
		{
			deltree($extend_path);
			fatal_lang_error('dp_extend_restricted_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
		}

		// Make sure we have a version and name.
		if (!isset($extend['name']))
		{
			deltree($extend_path);
			fatal_lang_error('dp_extend_has_no_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower'], $txt['dptext_' . $extendVars['type'] . '_plural_lower']));
		}

		if (!isset($extend['version']))
		{
			deltree($extend_path);
			fatal_lang_error('dp_extend_has_no_version', false, array($txt['dptext_' . $extendVars['type'] . '_lower'], $txt['dptext_' . $extendVars['type'] . '_plural_lower']));
		}	

		$all_functions = array();

		if ($extendVars['type'] == 'module')
		{
		
			// Module Icons
			if (isset($extend['iconsdir']) && trim($extend['iconsdir']) != '')
			{
				$extend['iconsdir'] = trim($extend['iconsdir']);
				$extend['iconsdir'] = parseString($extend['iconsdir'], 'folderpath');
			}

			// Module Images
			if (isset($extend['imagesdir']) && trim($extend['imagesdir']) != '')
			{
				$extend['imagesdir'] = trim($extend['imagesdir']);
				$extend['imagesdir'] = parseString($extend['imagesdir'], 'folderpath');
			}
			
			// Checking the database tags to be sure they are all set right and the files exist!
			if (isset($extend['database'], $extend['database']['install']))
			{
				// Database uninstall file is MANDATORY!
				if (isset($extend['database']['uninstall']))
				{
					if (parseString($extend['database']['install'], 'filepath', false) != 1 && parseString($extend['database']['uninstall'], 'filepath', false) != 1)
					{
						if (file_exists($extend_path . '/' . $extend['database']['install']) && file_exists($extend_path . '/' . $extend['database']['uninstall']))
						{
							global $txt, $boarddir, $user_info, $sourcedir, $modSettings, $context, $settings, $forum_version, $smcFunc;

							require_once($extend_path . '/' . $extend['database']['install']);
						}
						else
						{
							deltree($extend_path);
							fatal_lang_error('database_files_no_exist', false);
						}
					}
					else
					{
						deltree($extend_path);
						fatal_lang_error('invalid_database_filepath', false);
					}
				}
				else
				{
					deltree($extend_path);
					fatal_lang_error('database_uninstall_missing', false);
				}
			}

			// Now checking for files, headers, theme files, actions, and functions.
			$main_count = 0;
			$all_files = array();
			$func_files = array();
			$theme_files = array();
			$dp_action_files = array();

			// Whoaa, some MAJOR ERROR CHECKING HERE!
			if ($extendInfo->exists('file'))
			{
				$filetag = $extendInfo->set('file');

				foreach ($filetag as $files => $path)
				{
					if ($path->exists('function'))
					{
						$functag = $path->set('function');

						foreach($functag as $func => $function)
						{
							if ($function->exists('main'))
							{
								$main_func = $function->fetch('main');

								// We'll need to check the function name and see if it's safe to use.
								if (trim($main_func) == '')
								{
									deltree($extend_path);
									fatal_lang_error('invalid_function_name', false);
								}

								// Only letters, numbers, and underscores for function names.
								if (parseString($main_func, 'function_name', false) == 1)
								{
									deltree($extend_path);
									fatal_lang_error('invalid_function_name', false);
								}

								$all_functions[] = $main_func;

								$main_count++;
							}
							else
							{
								$other_funcs = $function->fetch('');

								if (trim($other_funcs) == '')
								{
									deltree($extend_path);
									fatal_lang_error('dp_extend_invalid_function_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
								}

								// Only letters, numbers, and underscores for function names.
								if (parseString($other_funcs, 'function_name', false) == 1)
								{
									deltree($extend_path);
									fatal_lang_error('dp_extend_invalid_function_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
								}

								$all_functions[] = $other_funcs;
							}
						}
					}

					// Now checking all filepaths.
					if (!$path->exists('@path'))
					{
						deltree($extend_path);
						fatal_lang_error('module_missing_files', false);
					}
					else
					{
						$filepath = $path->fetch('@path');
						$filepath = trim($filepath);
						$has_header = false;
						$has_dp_action = false;

						if ($path->exists('@type'))
						{
							if ($path->fetch('@type') == 'header')
								$has_header = true;
							elseif($path->fetch('@type') == 'dream_action')
								$has_dp_action = true;
						}

						// Checking for a valid filepath here.
						if (parseString($filepath, 'filepath', false) == 1 || !file_exists($extend_path . '/' . $filepath))
						{
							deltree($extend_path);
							fatal_lang_error('dp_extend_invalid_filename', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
						}

						// Get the extension of the file!
						$extension = trim(strtolower(substr(strrchr(basename($filepath), '.'), 1)));

						// Must have an extension!
						if ($extension !== false && $extension != '' && $filepath != '')
						{
							if (in_array($filepath, $all_files))
							{
								deltree($extend_path);
								fatal_lang_error('module_has_file_defined_already', false);
							}
							else
							{
								$all_files[] = $filepath;

								if ($has_dp_action)
									$dp_action_files[] = $filepath;
								elseif ($extension == 'php')
								{
									foreach($all_functions as $funcVal)
										$func_files[$funcVal] = $filepath;
								}
								
								if ($has_header)
								{
									$mod_theme = $path->exists('@theme') ? $path->fetch('@theme') : 'default';
									$mod_theme = trim($mod_theme) == '' ? 'default' : $mod_theme;

									if ($extension == 'css' || $extension == 'js')
									{
										if (!isset($theme_files[$extension][$mod_theme]))
												$theme_files[$extension][$mod_theme] = array();

											$theme_files[$extension][$mod_theme][] = $filepath;
									}
								}
								elseif(!$has_dp_action)
								{
									// This allows us to throw files into any theme within the dreamportal/modules/$module_name directory.
									if ($path->exists('@theme') && trim($path->fetch('@theme')) != '')
									{
										$mod_theme = $path->fetch('@theme');
										$theme_files['other_files'][$mod_theme][] = $filepath;
									}
								}
							}
						}
						else
						{
							deltree($extend_path);
							fatal_lang_error('dp_extend_invalid_filename', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
						}
					}
				}
			}

			if (count($all_files) < 1)
			{
				deltree($extend_path);
				fatal_lang_error('module_has_no_files', false);
			}

			// Only allowed 1 MAIN function for your modules ofcourse, but MUST be defined!
			if (empty($main_count) || $main_count >= 2)
			{
				deltree($extend_path);
				fatal_lang_error('module_has_no_main_function', false);
			}
			
		}
		elseif ($extendVars['type'] == 'template')
		{
			if (isset($extend['file']) && trim($extend['file']) != '')
			{
				if (parseString($extend['file'], 'filepath', false) == 1 || !file_exists($extend_path . '/' . $extend['file']))
				{
					deltree($extend_path);
					fatal_lang_error('dp_extend_invalid_filename', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
				}
				elseif (trim(strtolower(substr(strrchr(basename($extend['file']), '.'), 1))) != 'php')
				{
					deltree($extend_path);
					fatal_lang_error('dp_templates_invalid_filename', false);
				}
			}
			else
			{
				// No file exists, mandatory!
				deltree($extend_path);
				fatal_lang_error('dp_extend_package_corrupt', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
			}

			if (isset($extend['function']) && trim($extend['function']) != '')
			{
				if (parseString($extend['function'], 'function_name', false) == 1)
				{
					deltree($extend_path);
					fatal_lang_error('dp_extend_invalid_function_name', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
				}
				$all_functions[] = $extend['function'];
			}
			else
			{
				// No function exists, mandatory!
				deltree($extend_path);
				fatal_lang_error('dp_extend_package_corrupt', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
			}		
		}

		// Checking current functions throughout SMF and, possibly, beyond.
		foreach($all_functions as $funcName)
			if (function_exists($funcName))
			{
				deltree($extend_path);
				fatal_lang_error('dp_extend_function_already_exists', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
			}

		// Checking ONLY functions for modules that are installed, since functions get loaded at runtime we'll be needing this!
		if (count($installed_functions) > 1)
		{
			foreach ($installed_functions as $tempfunc)
			{
				$split_functions = explode('+', $tempfunc);
				foreach ($split_functions as $temp)
					if (in_array($temp, $all_functions))
					{
						deltree($extend_path);
						fatal_lang_error('dp_extend_function_already_exists', false, array($txt['dptext_' . $extendVars['type'] . '_lower']));
					}
			}
		}

		if (isset($extend['languages']['english']['main']) && parseString($extend['languages']['english']['main'], 'filepath', false) != 1 && is_array($extend['languages']))
			$languages_dir = $settings['default_theme_dir'] . '/languages';
		else
		{
			deltree($extend_path);
			fatal_lang_error('invalid_language_filepath', false);
		}

		// Make sure the language files are good and add them to the master file.
		foreach($extend['languages'] as $lang => $langFile)
			foreach ($langFile as $type => $value)
			{
				$lFile = trim($value);

				if (parseString($lFile, 'filepath', false) == 1 || !file_exists($extend_path . '/' . $lFile))
				{
					deltree($extend_path);
					fatal_lang_error('invalid_language_filepath', false);
				}

				$wLanguage = writeLanguage($extendVars['lang_vars'], $extend_path, $lFile, strtolower(trim($lang)) . (strtolower(trim($type)) == 'utf8' ? '-utf8' : ''), $extend['name']);

				// We can not upload a module without a title or description, version is defined within the info.xml file.
				if (isset($wLanguage['key'], $wLanguage['sprintf']))
				{
					deltree($extend_path);
					fatal_lang_error($wLanguage['key'], false, $wLanguage['sprintf']);
				}
			}

		if ($extendVars['type'] == 'module')
		{
			// Handling the icons and the icon path.
			$valid_images = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

			// Now we are ready to begin placing the files...
			foreach ($context['extracted_files'] as $file)
			{
				$file_contents = file_get_contents($extend_path . '/' . $file['filename']);
				$filename = basename($file['filename']);
				$is_icon = isset($extend['iconsdir']) ? $file['filename'] != $extend['iconsdir'] . '/' && strpos($file['filename'], $extend['iconsdir'] . '/') !== false : false;
				$is_image = isset($extend['imagesdir']) ? $file['filename'] != $extend['imagesdir'] . '/' && strpos($file['filename'], $extend['imagesdir'] . '/') !== false : false;
				$extension = $is_icon || $is_image ? substr(strrchr($filename, '.'), 1) : '';

				// Uploading icons...
				if (!empty($extension) && ((!empty($extend['iconsdir']) && empty($modSettings['dp_disable_custommod_icons'])) || (!empty($extend['imagesdir']) && $is_image)))
				{
					// All valid icons/images.
					if (in_array(strtolower($extension), $valid_images))
					{
						$dir = $is_image ? $context['dpmod_image_dir'] : $context['dpmod_icon_dir'];
						$path = $dir . $extend['name'];

						// Only if the directory doesn't exist already.
						if (!is_dir($path))
							@mkdir($path, 0755);

						// Protect the new images/icons directory!
						if (!file_exists($path . '/index.php'))
							@copy($dir . 'index.php', $path . '/index.php');

						// Cache it!
						if (!file_exists($path . '/.htaccess'))
							@copy($dir . '.htaccess', $path . '/.htaccess');

						// Place the icons.
						file_put_contents($path . '/' . $filename, $file_contents);

						// Set the rights
						@chmod($path . '/' . $filename, 0666);

						// Escape outta here.
						continue;
					}
				}

				if ((isset($extend['iconsdir']) && $file['filename'] == $extend['iconsdir'] . '/') || (isset($extend['imagesdir']) && $file['filename'] == $extend['imagesdir'] . '/'))
					continue;
			}
		}
	}
	else
	{
		if (!empty($txt['dpamerr_' . $_FILES[$extendVars['post_name']]['error']]))
			fatal_lang_error('dpamerr_' . $_FILES[$extendVars['post_name']]['error'], false);
		else
			fatal_lang_error('dpamerr_unknown', false);
	}

	// Now the extension package is ready to be transformed!
	if (!is_dir($real_extendpath))
		rename($extend_path, $real_extendpath);

	if ($extendVars['type'] == 'module')
	{
		if (!empty($dp_action_files))
		{
			foreach($dp_action_files as $action_file)
			{
				// Grab the actual name of the file only!!
				$action_filepath = $context['dpmod_modules_dir'] . '/' . $extend['name'] . '/' . $action_file;
				$act_file = basename($action_filepath);

				// Copy over the file, than delete it from where we got it at!
				@copy($action_filepath, $context['dpmod_module_actionsdir'] . '/' . $extend['name'] . '_' . $act_file . '.temp');
				@unlink($action_filepath);
			}
		}

		// Are there any theme-related files?
		if (!empty($theme_files))
		{
			foreach($theme_files as $type => $theme)
			{
				$type_dir = $type == 'css' ? 'css' : ($type == 'js' ? 'scripts' : '');

				foreach($theme as $theme_name => $theme_file)
				{
					$themedir = $boarddir . '/Themes/' . $theme_name;

					// Don't send the file if the theme doesn't exist!
					if (!is_dir($themedir))
						continue;

					$theme_moddir = $themedir . '/' . ($type_dir != '' ? $type_dir . '/' : '') . 'dreamportal/modules/' . $extend['name'];
					
					// Gives us the root of where we start building at!
					$theme_typedir = $themedir . ($type_dir != '' ? '/' . $type_dir : '');
					
					if ($type_dir != '')
					{
						// Creating the directory path we'll need if not created already!
						if (!is_dir($themedir . '/' . $type_dir))
						{
							@mkdir($themedir . '/' . $type_dir, 0755);

							// Protect the directory
							@copy($context['dpmod_modules_dir'] . '/index.php', $themedir . '/' . $type_dir . '/index.php');
						}
						elseif (!file_exists($themedir . '/' . $type_dir . '/index.php'))
						{
							// Sometimes Theme authors do not include index.php within the css/script directory, THEY SHOULD though!!!
							@copy($context['dpmod_modules_dir'] . '/index.php', $themedir . '/' . $type_dir . '/index.php');
						}
					}

					if (!is_dir($theme_typedir . '/dreamportal'))
					{
						@mkdir($theme_typedir . '/dreamportal', 0755);

						// Protect it!
						@copy($context['dpmod_modules_dir'] . '/index.php', $theme_typedir . '/dreamportal/index.php');

						// This .htaccess file relies on mod_rewrite being enabled, but provides even further security!
						@copy($boarddir . '/dreamportal/.htaccess', $theme_typedir . '/dreamportal/.htaccess');
					}

					if (!is_dir($theme_typedir . '/dreamportal/modules'))
					{
						@mkdir($theme_typedir . '/dreamportal/modules', 0755);

						// Protect the directory
						@copy($context['dpmod_modules_dir'] . '/index.php', $theme_typedir . '/dreamportal/modules/index.php');
					}

					// Create the modules directory now.
					if (!is_dir($theme_moddir))
					{
						@mkdir($theme_moddir, 0755);

						// And we protect it yet again!
						@copy($context['dpmod_modules_dir'] . '/index.php', $theme_moddir . '/index.php');
					}

					// Let's transfer over all theme files now.
					foreach ($theme_file as $theme_filepath)
					{
						$mod_filepath = $context['dpmod_modules_dir'] . '/' . $extend['name'] . '/' . $theme_filepath;

						// If the file doesn't exist get out of here.
						if (!file_exists($mod_filepath))
							continue;

						$theme_filename = strpos($theme_filepath, '/') !== false ? mb_substr($theme_filepath, mb_strrpos($theme_filepath, '/') + 1) : $theme_filepath;	

						// place the css/js files
						@copy($mod_filepath, $theme_moddir . '/' . $theme_filename);
					}
				}
			}
		}

		/*
			Removal of unnecessary files/folders!  Cleaning Up here!
		*/

		// Theme files from within the Modules directory, no longer needed!
		if (!empty($theme_files))
		{
			if (!empty($theme_files['css']))
				foreach($theme_files['css'] as $css_header)
					foreach($css_header as $css_file)
						if (@unlink($real_extendpath . '/' . $css_file))
							@rmdir(dirname($real_extendpath . '/' . $css_file));

			if (!empty($theme_files['js']))
				foreach($theme_files['js'] as $js_header)
					foreach($js_header as $js_file)
						if (@unlink($real_extendpath . '/' . $js_file))
							@rmdir(dirname($real_extendpath . '/' . $js_file));
							
			if (!empty($theme_files['other_files']))
				foreach($theme_files['other_files'] as $other_files)
					foreach($other_files as $other_file)
						if (@unlink($real_extendpath . '/' . $other_file))
							@rmdir(dirname($real_extendpath . '/' . $other_file));
		}

		// Icons
		if (!empty($extend['iconsdir']) && is_dir($real_extendpath . '/' . $extend['iconsdir']))
			deltree($real_extendpath . '/' . $extend['iconsdir']);

		// Images
		if (!empty($extend['imagesdir']) && is_dir($real_extendpath . '/' . $extend['imagesdir']))
			deltree($real_extendpath . '/' . $extend['imagesdir']);
	}
	
	// Language Files
	foreach($extend['languages'] as $lang => $langFile)
		foreach ($langFile as $type => $value)
			if (@unlink($real_extendpath . '/' . $value))
				@rmdir(dirname($real_extendpath . '/' . $value));

	global $dp_dir_walk;

	// A Recursive create_function, how cool is that?
	$dp_dir_walk = create_function('$dir, $copy_path', '
		if (is_dir($dir))
		{
			$dh = opendir($dir);
			while (($file = readdir($dh)) !== false)
			{
				if ($file === \'.\' || $file === \'..\')
					continue;

				if(is_dir($dir . $file))
				{
					@copy($copy_path . \'/index.php\', $dir . $file . \'/index.php\');
					call_user_func_array($GLOBALS["dp_dir_walk"], array($dir . $file . \'/\', $copy_path));
				}
			}
			closedir($dh);
		 }
	');

	// Add some security to all directories that remain!
	@copy($extendVars['dir'] . '/index.php', $real_extendpath . '/index.php');
	$dp_dir_walk($real_extendpath . '/', $extendVars['dir']); 

	// Clean the cache one more time to be sure!
	clean_cache();

	if($extendVars['type'] == 'module')
		$rName = !empty($modSettings['dp_add_modules_limit']) ? ';dpmod=' . $extend['name'] : '';
	elseif ($extendVars['type'] == 'template')
		$rName = !empty($modSettings['dp_add_templates_limit']) ? ';dptemp=' . $extend['name'] : '';

	// Time to go...
	redirectexit('action=admin;area=dpextend;sa=' . $extendVars['sa'] . $rName);
}

/*
 * Adds module and template language strings to the main language file
 * We do this instead of using "require" so as to reduce the amount of memory that the modules/templates use
 * as well as the amount of files just to load the language strings.
 * @param string $dir the path to the file you want to write using file_put_contents.
 * @param string $contents the file data.
 */

function writeLanguage($langVars, $path, $lFile, $language, $name)
{
	global $settings, $txt;

	$languages_dir = $settings['default_theme_dir'] . '/languages';

	// Let's increase the memory limit for PHP for when users have tons of modules uploaded!
	@ini_set('memory_limit','128M');

	// This holds the current file we are working on.
	$curr_lang_file = $languages_dir . '/' . $langVars['filename'] . '.' . $language . '.php';

	// Open for reading the contents...
	$lang = file_get_contents($path . '/' . $lFile);

	// Strip out php tags if they exist.
	$lang = parseString($lang, 'phptags');

	$has_title = false;
	$has_desc = false;
	$title = '$txt[\'' . $langVars['title'] . $name . '\']';

	// Is there a title defined?
	if (strpos($lang, $title) !== false)
		$has_title = true;

	foreach ($langVars['desc'] as $desc_value)
		if (strpos($lang, '$txt[\'' . $desc_value . $name . '\']') !== false)
		{
			$has_desc = true;
			break;
		}

	// Any errors?
	if (!$has_title && !$has_desc)
		$error = array('key' => 'dp_extend_no_title_desc', 'sprintf' => array($txt['dptext_' . strtolower($langVars['type']) . '_lower']));
	elseif (!$has_title || !$has_desc)
	{
		if (!$has_title)
			$error = array('key' => 'dp_extend_no_title', 'sprintf' => array($txt['dptext_' . strtolower($langVars['type']) . '_lower']));
		else
			$error = array('key' => 'dp_extend_no_desc', 'sprintf' => array($txt['dptext_' . strtolower($langVars['type']) . '_lower'], $txt['dptext_' . strtolower($langVars['type']) . '_plural_lower']));
	}

	if (isset($error))
		return $error;

	if (trim($lang) != '')
	{
		$code = 'global $helptxt;';

		// Try and read from the file if it exists.
		$output = @file_get_contents($curr_lang_file);

		if ($output)
			$code = parseString($output, 'phptags');

		// Placeholder comments so that we can remove these strings easily!
		$begin_comment = '// ' . ' Dream Portal ' . $langVars['type'] . ' - ' . $name . ' BEGIN...';
		$end_comment = '// ' . ' Dream Portal ' . $langVars['type'] . ' - ' . $name . ' END!';

		$data = '<?php' . "\n" . $code . "\n" . $begin_comment . "\n" . $lang . "\n" . $end_comment . "\n\n" . '?>';

		file_put_contents($curr_lang_file, $data);
	}

	// Clean the cache so that the language strings are ready to be used.
	clean_cache();

	return '';
}

// For adding new templates to be used for either Modules or Dream Pages.
function AddDPTemplates()
{
	global $context, $txt, $sourcedir, $smcFunc, $scripturl, $modSettings;

	// Just some extra security here!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	$context['page_title'] = $txt['dp_admin_title_add_templates'];
	$context['dp_extension_vars'] = array(
		'none_exist' => sprintf($txt['no_extensions_exist'], $txt['dptext_template'], $txt['dptext_templates']),
		'name_col' => sprintf($txt['dp_extension_name'], $txt['dptext_template']),
		'sa' => 'dpaddtemplates',
		'modsettings_var' => 'dp_add_templates_limit',
		'input_name' => 'dp_templates',
		'upload_txt' => sprintf($txt['dp_upload_extension'], $txt['dptext_template']),
		'extension_to_upload' => sprintf($txt['extension_to_upload'], $txt['dptext_template']),
	);

	$templateVars = array(
		'type' => 'templates',
		'dir' => $context['dpmod_template_dir'],
		'has_settings' => false,
		'sa' => array(
			'install' => 'dpinstalltemplate',
			'uninstall' => 'dpuninstalltemplate',
			'delete' => 'dpdeletetemplate',
		),
		'query' => array(
			'select' => array('id' => 'id_template', 'name' => 'name'),
			'table' => 'dp_templates',
		),
	);

	if (!empty($modSettings['dp_add_templates_limit']))
	{
		$_REQUEST['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$temp_name = isset($_GET['dptemp']) ? trim($_GET['dptemp']) : '';

		$dp_add_template = array(
			'start' => $temp_name == '' ? $_REQUEST['start'] : GetDPAddedExtensions($templateVars, 0, $modSettings['dp_add_templates_limit'], $temp_name),
			'limit' => $modSettings['dp_add_templates_limit'],
		);

		$context['extension_info'] = GetDPAddedExtensions($templateVars, $dp_add_template['start'], $dp_add_template['limit']);

		if (!empty($context['extension_info']))
			$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=dpextend;sa=dpaddtemplates', $dp_add_template['start'], $context['dpextend_total_templates'], $dp_add_template['limit'], false);
	}
	else
		$context['extension_info'] = GetDPAddedExtensions($templateVars);

	// Saving?
	if (isset($_POST['upload']))
	{
		// Get all Installed functions.
		$request = $smcFunc['db_query']('', '
		SELECT
			name, function
		FROM {db_prefix}dp_templates
		WHERE type = {int:zero}',
			array(
				'zero' => 0,
			)
		);

		$installed_functions = array();
		$installed_names = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_functions[] = $row['function'];
			$installed_names[] = $row['name'];
		}
		$smcFunc['db_free_result']($request);

		$tempVars = array(
			'type' => 'template',
			'dir' => $context['dpmod_template_dir'],
			'post_name' => 'dp_templates',
			'sa' => 'dpaddtemplates',
			'lang_vars' => array(
				'filename' => 'DreamTemplates',
				'type' => 'Template',
				'title' => 'dptemp_',
				'desc' => array('dptempinfo_', 'dptempdesc_'),
			),
		);

		UploadExtension($tempVars, array_merge($context['dp_restricted_names']['templates'], $installed_names), $installed_functions);
	}	
}

function InstallDPTemplate()
{
	global $context, $sourcedir, $smcFunc, $txt, $modSettings;

	// Only the Admin here...
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	// We want to define our variables now...
	$name = $_GET['name'];

	if (is_dir($context['dpmod_template_dir']))
	{
        $dir = @opendir($context['dpmod_template_dir']);

		$dirs = array();
		while ($file = readdir($dir))
		{
			$retVal = GetDPTemplateInfo('', '', $context['dpmod_template_dir'], $file, $name);
			if ($retVal === false)
				continue;
			else
				$template_info[$file] = $retVal;
		}
	}

	// Gives us the function for the template.
	$file_function = $template_info[$name]['function'];

	// Now let's get all installed functions from templates.
	$request = $smcFunc['db_query']('', '
		SELECT
			function
		FROM {db_prefix}dp_templates
		WHERE type = {int:zero}',
		array(
				'zero' => 0,
		)
	);

	$installed_functions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$installed_functions[] = $row['function'];

	$smcFunc['db_free_result']($request);

	// Check for duplicate module function names, if found, can not install.
	foreach($installed_functions as $func)
		if ($func == $file_function)
			fatal_lang_error('dp_extend_function_duplicates', false, array($txt['dptext_template_lower']));

	// Installing the template...
	$smcFunc['db_insert']('',
		'{db_prefix}dp_templates',
		array('name' => 'string', 'file' => 'string', 'function' => 'string'),
		array($name, $template_info[$name]['file'], $template_info[$name]['function']),
		array('id_template', 'name')
	);

	// Time to go...
	$redirect = 'action=admin;area=dpextend;sa=dpaddtemplates' . (!empty($modSettings['dp_add_templates_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : '');
	redirectexit($redirect);
}

function UninstallDPTemplate()
{
	global $context, $smcFunc, $txt, $modSettings;

	// Extra security!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	// isset is better for this.
	if (isset($_GET['name']))
		$name = $_GET['name'];
	elseif (isset($context['delete_tempname']) && trim($context['delete_tempname']) != '')
		$name = $context['delete_tempname'];

	// Can't seem to find it.
	if (!isset($name))
		fatal_lang_error('dp_extend_uninstall_error', false, array($txt['dptext_template_lower']));

	// Does it exist, and is it defined in any modules and/or clones?
	$request = $smcFunc['db_query']('', '
		SELECT
			dt.id_template, dm.id_module, dmc.id_clone
		FROM {db_prefix}dp_templates AS dt
			LEFT JOIN {db_prefix}dp_modules AS dm ON (dm.id_template = dt.id_template)
			LEFT JOIN {db_prefix}dp_module_clones AS dmc ON (dmc.id_template = dt.id_template)
		WHERE dt.name = {string:template_name} AND dt.type = {int:zero}',
		array(
			'zero' => 0,
			'template_name' => (string) $name,
		)
	);

	// Trying to uninstall something that doesn't exist!
	if ($smcFunc['db_num_rows']($request) == 0)
		if (isset($context['delete_tempname']))
			return;
		else
			redirectexit('action=admin;area=dpextend;sa=dpaddtemplates' . (!empty($modSettings['dp_add_templates_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));

	$template_info = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($template_info['id']))
		{
			$template_info = array(
				'id' => (int) $row['id_template'],
				'mods' => array(),
				'clones' => array(),
			);
		}

		if (!empty($row['id_module']) && !isset($template_info['mods'][$row['id_module']]))
			$template_info['mods'][$row['id_module']] = $row['id_module'];
		
		if (!empty($row['id_clone']) && !isset($template_info['clones'][$row['id_clone']]))
			$template_info['clones'][$row['id_clone']] = $row['id_clone'];
	}
	$smcFunc['db_free_result']($request);

	// Check to be sure we have a module id value before continuing.
	if (empty($template_info['id']))
		if (isset($context['delete_tempname']))
			return;
		else
			redirectexit('action=admin;area=dpextend;sa=dpaddtemplates' . (!empty($modSettings['dp_add_templates_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));

	// Remove the template from any modules and/or clones that are using it!
	if (!empty($template_info['mods']))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}dp_modules
			SET
				id_template = {int:zero}
			WHERE id_module IN ({array_int:id_modules})',
			array(
				'zero' => 0,
				'id_modules' => $template_info['mods'],
			)
		);

	if (!empty($template_info['clones']))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}dp_module_clones
			SET
				id_template = {int:zero}
			WHERE id_clone IN ({array_int:id_clones})',
			array(
				'zero' => 0,
				'id_clones' => $template_info['clones'],
			)
		);

	// Now remove the template from the dp_templates table!
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_templates
		WHERE id_template = {int:template_id}
		LIMIT 1',
		array(
			'template_id' => $template_info['id'],
		)
	);

	if (isset($context['delete_tempname']))
		return;
	else
	{
		// Where did they uninstall from?
		$redirect = 'action=admin;area=dpextend;sa=dpaddtemplates' . (!empty($modSettings['dp_add_templates_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : '');
		redirectexit($redirect);
	}
}

// Removes a Template with all it's files from the filesystem!
function DeleteDPTemplate()
{
	global $context, $modSettings, $txt, $settings, $boarddir, $sourcedir;

	// Extra security here.
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	//	Grabbing the name.
	$name = (string) $_GET['name'];

	// Before deleting, is it uninstalled?
	$context['delete_tempname'] = $name;
	UninstallDPTemplate();
	unset($context['delete_tempname']);

	// Now we need to get the language and strings that need to be removed.
	$templateInfo = file_get_contents($context['dpmod_template_dir'] . '/' . $name . '/info.xml');
	loadClassFile('Class-Package.php');
	$templateInfo = new xmlArray($templateInfo);

	// Package corrupt!
	if (!$templateInfo->exists('template[0]'))
		fatal_lang_error('dp_extend_package_corrupt', false, array($txt['dptext_template_lower']));

	$templateInfo = $templateInfo->path('template[0]');
	$template = $templateInfo->to_array();

	// Used for the deltree function.
	require_once($sourcedir . '/Subs-Package.php');

	if (isset($template['languages']) && is_array($template['languages']))
	{
		$languages_dir = $settings['default_theme_dir'] . '/languages';
		$temp_langs = array();
		$temp_langs = $template['languages'];
		// So we'll do all languages they have defined in here.
		foreach($temp_langs as $lang => $langFile)
		{
			// the language... english, british_english, russian, etc. etc.
			$language = $lang;

			foreach ($langFile as $utfType => $value)
			{
				$utf8 = $utfType == 'utf8' ? '-utf8' : '';

				// This holds the current file we are working on.
				$curr_lang_file = $languages_dir . '/DreamTemplates.' . $language . $utf8 . '.php';

				// Can't read from the file if it doesn't exist now can we?
				if (!file_exists($curr_lang_file))
					continue;

				// This helps to remove the language strings for the template, since $name is unique!
				$template_begin_comment = '// ' . ' Dream Portal Template - ' . $name . ' BEGIN...';
				$template_end_comment = '// ' . ' Dream Portal Template - ' . $name . ' END!';

				$fp = fopen($curr_lang_file, 'rb');
				$content = fread($fp, filesize($curr_lang_file));
				fclose($fp);

				// Searching within the string, extracting only what we need.
				$start = strpos($content, $template_begin_comment);
				$end = strpos($content, $template_end_comment);

				// We can't do this unless both are found.
				if ($start !== false && $end !== false)
				{
					$begin = substr($content, 0, $start);
					$finish = substr($content, $end + strlen($template_end_comment));

					$new_content = $begin . $finish;

					// Write it into the file.
					$fo = fopen($curr_lang_file, 'wb');
					@fwrite($fo, $new_content);
					fclose($fo);
				}
			}
		}
	}

	deltree($context['dpmod_template_dir'] . '/' . $name);
	redirectexit('action=admin;area=dpextend;sa=dpaddtemplates' . (!empty($modSettings['dp_add_templates_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));
}

function UploadLanguage($installed_langs = array(), $dp_version, $dplangfiles)
{
	global $txt, $context, $modSettings, $settings, $smcFunc, $sourcedir, $boarddir;

	// Just some extra security here!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		return;

	validateSession();

	require_once($sourcedir . '/Subs-Package.php');

	if ($_FILES['dp_languages']['error'] === UPLOAD_ERR_OK)
	{
		// Check for tar.gz or zip files.
		$tar_gz_pos = strpos(strtolower($_FILES['dp_languages']['name']), '.tar.gz');
		$zip_pos = strpos(strtolower($_FILES['dp_languages']['name']), '.zip');

		if (($tar_gz_pos === false || $tar_gz_pos != strlen($_FILES['dp_languages']['name']) - 7) && ($zip_pos === false || $zip_pos != strlen($_FILES['dp_languages']['name']) - 4))
			fatal_lang_error('dp_extend_upload_error_type', false);

		// Make sure it has a valid filename.
		$_FILES['dp_languages']['name'] = parseString($_FILES['dp_languages']['name'], 'uploaded_file');

		// Extract it to this directory.
		$pathinfo = pathinfo($_FILES['dp_languages']['name']);
		$lang_path = $boarddir . '/dreamportal/temp_language';

		// Clear a way, just in case!
		if (is_dir($lang_path))
			deltree($lang_path);

		@mkdir($lang_path, 0755);

		// Make sure it has an 
		if (!isset($_FILES['dp_languages']['name']) || trim($_FILES['dp_languages']['name']) == '' || substr($_FILES['dp_languages']['name'], 0, strpos($_FILES['dp_languages']['name'], '.')) == '' || trim($_FILES['dp_languages']['tmp_name']) == '')
			fatal_lang_error('dp_lang_no_filename', false);

		// Extract the package.
		$context['extracted_files'] = read_tgz_file($_FILES['dp_languages']['tmp_name'], $lang_path);

		foreach ($context['extracted_files'] as $file)
			if (basename($file['filename']) == 'info.xml')
			{
				$languageInfo = GetDPLanguageInfo($lang_path . '/' . $file['filename'], $dp_version);

				// End the loop. We found our man!
				break;
			}

		// Handle any errors that may have occurred!
		if (!isset($languageInfo))
		{
			deltree($lang_path);
			fatal_lang_error('dp_extend_infoxml_missing', false, array($txt['dptext_language_lower']));
		}

		if (!is_array($languageInfo) || !$languageInfo)
		{
			deltree($lang_path);
			fatal_lang_error('dp_lang_pack_error', false);
		}

		// Check to be sure the version of the Language file isn't already installed!
		foreach ($installed_langs as $id_lang => $installed)
		{
			if ($installed['name'] == $languageInfo['name'] && $installed['version'] == $languageInfo['version'] && $installed['utf8'] == $languageInfo['utf8'])
			{
				deltree($lang_path);
				fatal_lang_error('dp_lang_pack_already_installed', false);
			}
			elseif ($installed['name'] == $languageInfo['name'] && $installed['utf8'] == $languageInfo['utf8'] && $installed['version'] != $languageInfo['version'])
			{
				$update = true;
				break;
			}
		}

		// Attempt to fix the directory path if there are problems with it.
		$languageInfo['langsdir'] = parseString($languageInfo['langsdir'], 'folderpath');

		// Form half a name here, going with lowercase file extensions only here.
		$lang_suffix = $languageInfo['name'] . ($languageInfo['utf8'] ? '-utf8' : '') . '.php';
		$langs_allowed = array();
		foreach($dplangfiles as $dp_langs)
			$langs_allowed[] = $dp_langs . '.' . $lang_suffix;

		if (is_dir($lang_path . '/' . $languageInfo['langsdir']))
		{
			// Loop through all files in here and copy them to SMF's default theme language directory.
			foreach($langs_allowed as $lang_name)
				if (file_exists($lang_path . '/' . $languageInfo['langsdir'] . '/' . $lang_name))
				{
					@copy($lang_path . '/' . $languageInfo['langsdir'] . '/' . $lang_name, $settings['default_theme_dir'] . '/languages/' . $lang_name);
					@chmod($settings['default_theme_dir'] . '/languages/' . $lang_name, 0666);
				}
		}
		else
		{
			deltree($lang_path);
			fatal_lang_error('dp_lang_dir_invalid', false);
		}

		// Remove the directory and insert the database row for this language pack.
		deltree($lang_path);

		if (isset($update))
		{
			// Update the database for this.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}dp_languages
				SET
					dp_version = {string:dp_version},
					version = {string:version},
					translator = {string:translator},
					title = {string:title}
				WHERE name = {string:name}
				LIMIT 1',
				array(
					'dp_version' => $languageInfo['dp_version'],
					'version' => $languageInfo['version'],
					'translator' => htmlentities($languageInfo['translator'], ENT_QUOTES, $context['character_set']),
					'title' => htmlentities($languageInfo['title'], ENT_QUOTES, $context['character_set']),
					'name' => $languageInfo['name'],
				)
			);
		}
		else
		{
			// Insert the new language into the dp_languages database table.
			$smcFunc['db_insert']('ignore',
				'{db_prefix}dp_languages',
				array(
					'name' => 'string-255', 'title' => 'string', 'translator' => 'string', 'version' => 'string-255', 'is_utf8' => 'int', 'dp_version' => 'string-255',
				),
				array(
					$languageInfo['name'], htmlentities($languageInfo['title'], ENT_QUOTES, $context['character_set']), htmlentities($languageInfo['translator'], ENT_QUOTES, $context['character_set']), $languageInfo['version'], $languageInfo['utf8'] ? 1 : 0, $languageInfo['dp_version'],
				),
				array('id_language', 'name')
			);
		}
	}
	else
	{
		if (!empty($txt['dpamerr_' . $_FILES['dp_languages']['error']]))
			fatal_lang_error('dpamerr_' . $_FILES['dp_languages']['error'], false);
		else
			fatal_lang_error('dpamerr_unknown', false);
	}

	$rName = !empty($modSettings['dp_add_languages_limit']) ? ';dplang=' . $languageInfo['name'] : '';

	// Time to go...
	redirectexit('action=admin;area=dpextend;sa=dpaddlanguages' . $rName);
}

// For Adding in Dream Portal Languages (thinking of creating the ability to add in Module Languages as well), but definitely
// Dream Portal language files will be found in here and the ability to add them also.
function AddDPLanguages()
{
	global $context, $txt, $sourcedir, $boarddir, $smcFunc, $scripturl, $modSettings, $portal_ver, $dpfiles;

	// Just some extra security here!
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	$context['page_title'] = $txt['dp_admin_title_add_languages'];
	$context['dp_extension_vars'] = array(
		'none_exist' => sprintf($txt['no_extensions_exist'], $txt['dptext_language'], $txt['dptext_languages']),
		'name_col' => $txt['dptext_language'],
		'sa' => 'dpaddlanguages',
		'modsettings_var' => 'dp_add_languages_limit',
		'input_name' => 'dp_languages',
		'upload_txt' => sprintf($txt['dp_upload_extension'], $txt['dptext_language']),
		'extension_to_upload' => sprintf($txt['extension_to_upload'], $txt['dptext_language']),
	);

	if (!empty($modSettings['dp_add_languages_limit']))
	{
		$_REQUEST['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$lang_name = isset($_GET['dplang']) ? trim($_GET['dplang']) : '';

		$dp_add_language = array(
			'start' => $lang_name == '' ? $_REQUEST['start'] : GetDPAddedLanguages($portal_ver, 0, $modSettings['dp_add_languages_limit'], $lang_name),
			'limit' => $modSettings['dp_add_languages_limit'],
		);

		$context['extension_info'] = GetDPAddedLanguages($portal_ver, $dp_add_language['start'], $dp_add_language['limit']);

		if (!empty($context['extension_info']))
			$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=dpextend;sa=dpaddlanguages', $dp_add_language['start'], $context['dp_total_languages'], $dp_add_language['limit'], false);
	}
	else
		$context['extension_info'] = GetDPAddedLanguages($portal_ver);

	// Saving?
	if (isset($_POST['upload']))
	{
		// Get all Installed languages.
		$request = $smcFunc['db_query']('', '
		SELECT
			id_language, name, version, dp_version, is_utf8
		FROM {db_prefix}dp_languages',
			array(
			)
		);

		$installed_langs = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_langs[$row['id_language']] = array(
				'name' => $row['name'],
				'version' => $row['version'],
				'dp_version' => $row['dp_version'],
				'utf8' => !empty($row['is_utf8']),
			);
		}
		$smcFunc['db_free_result']($request);

		UploadLanguage($installed_langs, $portal_ver, $dpfiles['languages']);
	}
}

function GetDPLanguageInfo($filename, $dp_version)
{
	global $context, $txt;

	if (!file_exists($filename)) return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($filename);

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$language_info = new xmlArray($file_info);
	$language_info = $language_info->path('language[0]');

	// All tags are mandatory except translators tag.
	if (!$language_info->exists('name')) return false;
	if (!$language_info->exists('title')) return false;
	if (!$language_info->exists('version')) return false;
	if (!$language_info->exists('package')) return false;

	$is_utf8 = false;

	// Check within the package tag for a langsdir tag
	$package = $language_info->set('package');

	foreach ($package as $name => $langpack)
	{
		if ($langpack->exists('@version') && $langpack->exists('langsdir'))
		{
			if ($langpack->fetch('@version') != $dp_version || isset($lang_array))
				continue;
			
			if ($context['character_set'] == 'UTF-8')
			{
				if (!$langpack->exists('@type') || $langpack->fetch('@type') != 'utf8')
					continue;

					$is_utf8 = true;
			}

			// Set the lang_array variable here!
			$lang_array = array(
				'langsdir' => $langpack->fetch('langsdir'),
			);

			if ($langpack->exists('translator'))
			{
				$translator = $langpack->set('translator');
				foreach($translator as $trans)
					if (!isset($lang_array['translator']))
						$lang_array['translator'] = $trans->exists('@parsebbc') && $trans->fetch('@parsebbc') == 'true' ? parse_bbc($trans->fetch('')) : $trans->fetch('');
			}
		}
	}

	// Dream Portal version is not supported, or no languages directory (langsdir) is set!
	if (!isset($lang_array)) return false;

	// Get the title, should it use parse_bbc function?
	$lang_title = $language_info->set('title');
	foreach($lang_title as $key => $l_title)
		if (!isset($title))
			$title = $l_title->exists('@parsebbc') && $l_title->fetch('@parsebbc') == 'true' ? parse_bbc($l_title->fetch('')) : $l_title->fetch('');

	// Returning all of the information for this to be inserted into the database!
	return array(
		'name' => $language_info->fetch('name'),
		'title' => $title,
		'langsdir' => $lang_array['langsdir'],
		'translator' => isset($lang_array['translator']) ? $lang_array['translator'] : $txt['dp_lang_unknown'],
		'version' => htmlentities($language_info->fetch('version'), ENT_QUOTES, $context['character_set']),
		'dp_version' => $dp_version,
		'utf8' => $is_utf8,
	);
}

function GetDPAddedLanguages($dp_version, $start = 0, $limit = 0, $byname = '')
{
	global $context, $scripturl, $smcFunc;

	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		return array();

	// Grabbing all languages here.
	$request = $smcFunc['db_query']('', '
		SELECT id_language, name, title, translator, version, dp_version
		FROM {db_prefix}dp_languages
		ORDER BY NULL',
		array()
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		return array();

	$return = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['name']] = array(
			'id_language' => $row['id_language'],
			'name' => $row['name'],
			'title' => ($row['dp_version'] != $dp_version ? '<span class="dp_lang_update" title="' . $txt['dp_lang_update_needed'] . '">' . $txt['dp_exclamation'] . '</span> ' : '') . html_entity_decode($row['title'], ENT_QUOTES, $context['character_set']),
			'translator' => html_entity_decode($row['translator'], ENT_QUOTES, $context['character_set']),
			'version' => html_entity_decode($row['version'], ENT_QUOTES, $context['character_set']),
		);
	}

	$smcFunc['db_free_result']($request);

	// Sort it by title.
	uasort($return, create_function('$a,$b','return strnatcmp($a[\'title\'], $b[\'title\']);'));

	// We are uploading the language, grab the new start for this and return it!
	if (!empty($limit) && empty($start) && $byname != '')
	{
		$i = array_search($byname, array_keys($return));

		if (floor($i/$limit) <= 0)
			$start = 0;
		else
			$start = (floor($i/$limit) * $limit);

		return $start;
	}

	$tcount = count($return) - 1;

	// Loop through the array, adding in the delete href...
	foreach(array_keys($return) as $lang)
	{
		$return[$lang] += array(
			'delete_href' => $scripturl . '?action=admin;area=dpextend;sa=dpdeletelanguage;name=' . $lang . (!empty($limit) ? ';start=' . (!empty($start) && count($return) > $start ? ($tcount == $start ? $start - $limit : $start) : '0') : '') . ';' . $context['session_var'] . '=' . $context['session_id'],
		);
	}

	$context['dp_total_languages'] = count($return);

	if (!empty($limit) && count($return) > $limit)
		$return = array_slice($return, $start, $limit, true);

	return $return;
}


function DeleteDPLanguage()
{
	global $smcFunc, $modSettings, $context, $settings, $dpfiles;

	// Extra security here.
	if (!allowedTo(array('admin_forum', 'admin_dpextend')))
		fatal_lang_error('dp_no_permission', false);

	validateSession();

	//	Grabbing the name.
	$name = (string) $_GET['name'];

	if (trim($name) == '')
		return;

	// Make sure it exists
	$request = $smcFunc['db_query']('', '
		SELECT id_language, name, is_utf8
		FROM {db_prefix}dp_languages
		WHERE name = {string:lang_name}
		LIMIT 1',
		array(
			'lang_name' => $name,
		)
	);

	// No language found!
	if ($smcFunc['db_num_rows']($request) == 0)
		return;

	list ($id_language, $lang_name, $utf8) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Remove the files, than delete from the database.
	foreach($dpfiles['languages'] as $dp_langs)
		@unlink($settings['default_theme_dir'] . '/languages/' . $dp_langs . '.' . $lang_name . (!empty($utf8) ? '-utf8' : '') . '.php');

	// Remove the language from the database.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}dp_languages
		WHERE id_language = {int:id_lang}
		LIMIT 1',
		array(
			'id_lang' => (int) $id_language,
		)
	);
	
	redirectexit('action=admin;area=dpextend;sa=dpaddlanguages' . (!empty($modSettings['dp_add_languages_limit']) ? (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0') : ''));
}

?>