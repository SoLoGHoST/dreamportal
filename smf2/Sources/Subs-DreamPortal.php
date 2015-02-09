<?php
/**************************************************************************************
* Subs-DreamPortal.php                                                                *
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

/*	This file contains functions vital for the performance of Dream
	Portal. It provides the following functions:

	array loadDefaultModuleConfigs(array installed_mods = array(), boolean new_layout = false)
		- Initializes the default Module settings.
		- installed_mods get passed an array of the name, files, functions.  It is important
		  to note that these are modules that get installed via the Add Modules section.
		- new_layout is used to determine if this information is to be used for a newly created layout or just a module clone.

	string loadParameter(array file_input = array(), string param_type, string param_value)
		- Loads up the modules parameters ($params).
		- Reads the information for each parameter individually and returns a clean string based on the
		  parameter type sent to it.
		- returns the new value to be used for that parameter within the module's function as $params.

	string parseString(string str = '', string type = 'filepath', boolean replace = true)
		- Reads the input string (str) and returns either a new string or an integer value.
		- when replace = false, returns 1 if invalid characters are found within str, else 0.
		- when replace = true, replaces all invalid characters with ''.
		- Note:  Their are a few types that don't accept replace = false, in those types, if
		  replace is set to true, it will simply return str without doing anything to it.
		- valid types are as follows:  'name', 'folderpath', 'function_name', 'uploaded_file', 'phptags', 'filepath'.

	string module_error(string type = 'error', string error_type = 'general', boolean log_error = false, boolean echo = true)
		- Echoes an error message within a module if echo = true.  Note:  This module doesn't do any error handling
		  for you, you must do this yourself for your modules.  This just provides an error message of some sort for
		  you to use within your module.  Make sure you return after calling this function in your modules, or it will
		  continue running your code.
		- possible string types are:  error, mod_not_installed, not_allowed, no_language, query_error, empty.  You can
		  also define you own string to be passed in here that will output that message instead of any of the pre-defined
		  messages listed above.
		- possible error_type strings are: general, critical, database, undefined_vars, user, template, and debug.  If
		  critical is defined for the error_type, than the error message will output red colored text.
		- log_error = whether or not to log the error into the Admin -> Error Log.
		- If echo = true will output it directly, if false, returns the information to be used within a variable.

	array loadFiles(array file_input = array())
		- Loads up all files for any given id_param via the file_input parameter type.
		- Returns an array of all file data for that file that was uploaded via the file_input parameter.

	boolean createFile(array fileOptions)
		- Handles uploaded files via the file_input parameter type.
		- Places the information for each file uploaded into the dp_module_files database table.
		- Returns true if file was created successfully, otherwise, returns false.

	void dreamRedirect(string $setLocation, boolean $refresh)
		- An integration function
		- Responsible for loading up action=forum when Mark All Messages as Read button is clicked on in the Board Index
		  when the Homepage Layout is enabled!

	string AllowedFileExtensions(string file_mime)
		- Returns all possible extensions for any given mime type supplied within the file_mime separated by commas.
		- Used in conjunction with the file_input parameter type to be sure only that mime type is uploaded.

	string getFilename(string filename, string file_id, string path, boolean new = false, string file_hash = '')
		- Gets/Sets a files encrypted filename via the file_input parameter type.

	array GetDPModuleInfo(string scripts, string mod_functions, string headers, string dirname, string file, string name = '')
		- Gets all of the data from the info.xml file for a module.
		- Returns an array of data, or false if an error occurred such as mandatory fields are missing, etc..
		- scripts = all php file paths concenated with a +
		- headers = all header file paths concenated with a +

	array GetDPTemplateInfo(string script, string function, string dirname, string file, string name = '')
		- Gets all of the data from the info.xml file associated with a module template.
		- Returns an array of data, or false if an error occurred such as mandatory fields are missing, etc..	
	
	array GetDPAddedExtensions(array $extendVars, int $start = 0, int $limit = 0, string $byname = '')
		- Gets all Uploaded Module/Template information for output into the Add Modules or Add Templates section of the DP Admin.
		- Determines whether or not a module/template is installed.
		- $start and $limit used for the pagination of the page.
		- $byname allows to load the page # of where the module/template is located based on the name of the module/template defined in info.xml.
		- Returns an array of all information for the extension.

	array GetDPInstalledModules(array installed_mods = array())
		- If installed_mods is an empty array, than it will query the database to get the
		  information needed from installed modules.
		- Returns an array of all installed modules!

	boolean loadLayout(string $curr_action, string $dp_layout_action)
		- Main function responsible for loading the Dream Portal layout.
		- Initiates the global $context['has_dp_layout'] boolean variable that can be used to determine if a layout is present.
		- Called from dp_init() in DreamPortal.php
		- $curr_action holds the main action/non-action of the url (e.g.:  [home], profile, forum, etc.)
		- $dp_layout_action holds the entire url that is preparsed within the dp_init() function.
		- Calls ProcessModule() to prepare the module for use within the template.
		- Sets $context['dream_columns'] with the layout data.
		- Loads up all modules, including empty modules, and their parameters.
		- Returns false if no layout has been defined for the url.

	array loadModule(array $data)
		- Prepares the raw module data generated by loadLayout() for use in the template
		- Returns an array of the data to be used for the module.
		- If the function of the module is not in the database, than it checks within the info.xml file for that module instead!

	array array_insert_buttons(array $buttons, array $new_menu_buttons)
		- Inserts the Dream Menu Buttons into the SMF Menu array based on the position.
		- Sets up the $context array for Dream Pages that are linked into Menu buttons, to be passed into the setupMenuContext() function of Subs.php.
		- The $context array handles which parent Buttons are the active buttons when the Dream Page gets displayed.

	array load_dream_menu()
		- Prepares all the user added buttons for the menu
		- Returns an array of the data
	
	void dp_sortArray(array &$new_menu_buttons, array $sortArray, string $sort)
		- Sorts the Dream Menu buttons so that the parents come before all children of that parent for up to 2 Sub-Levels.

	void add_dp_menu_buttons(array &$menu_buttons)
		- Renames the Home Menu Button, if set in $modSettings, as well as the Forum Menu Button.
		- Adds in the Forum menu button which links to action=forum, if DP Homepage layout is enabled.
		- Gives permission to those who have it to be able to change settings in the DP Admin!

	void add_dp_admin_areas(array $admin_areas)
		- Loads up Dream Portals Admin areas.

	string dream_whos_online(array $actions)
		- Returns the page that users are located at.

	string menu_page_link($pages)
		- $pages can be an array, integer, or string (if int it's the id_page from dp_dream_pages table, if string, it's the page_name from dp_dream_pages table).
		- Function returns both id_page and page_name to be inputted into the dp_dream_menu table if the dream page exists so that we can link that page to the menu button!
		- If an array of id_page and/or page_name values, than this function will return both id_page and page_name values from the database for each array value, as array('name' => page_name, 'id' => id_page) returning all possible Dream Pages.

	string check_page_link(string $link = '')
		- This function checks the $link and determines if a page is defined in the $link or not.
		- Similar to using $_GET['page'], but must be after index.php?	
*/

function loadDefaultModuleConfigs($installed_mods = array(), $new_layout = false)
{
	global $txt;

	// Default Custom Module Configuration.
	$dreamModules = array(
		'custom' => array(
			'title' => $txt['dpmod_custom'],
			'files' => '',
			'header_files' => '',
			'target' => 1,
			'icon' => 'comments.png',
			'title_link' => '',
			'functions' => 'module_custom',
			'container' => 1,
			'empty' => 0,
			'params' => array(
				'code_type' => array(
					'type' => 'select',
					'value' => '0:BBC;HTML;PHP',
				),
				'code' => array(
					'type' => 'rich_edit',
					'value' => '',
				),
			),
		),
	);

	// Any modules installed?
	if (count($installed_mods) >= 1 || $new_layout)
		return array_merge($dreamModules, GetDPInstalledModules($installed_mods));
	else
		return $dreamModules;
}

function loadParameter($file_input = array(), $param_type, $param_value)
{
	global $context;

	// Loading up all files are we?
	if (count($file_input) >= 1)
	{
		$mod_param = loadFiles($file_input);
		return $mod_param;
	}

	// Need to handle all selects here.
	if (trim(strtolower($param_type)) == 'select' || trim(strtolower($param_type)) == 'select_function')
	{
		$select_params = array();
		$values = array();

		$select_params = explode(':', $param_value);
		if (!empty($select_params))
		{
			$opt_value = (int) $select_params[0];
			if (isset($select_params[1]))
				$values = explode(';', $select_params[1]);
		}

		// Need to make sure its fine before setting this.
		if (count($values) >= 1 && $opt_value < count($values))
			$mod_param = $values[$opt_value];
		else
			// Error, set to empty and let the module function handle this instead.
			$mod_param = '';
	}
	elseif(trim(strtolower($param_type)) == 'db_select')
	{
		// Only returning the selected value for this parameter.
		$db_select = explode(':', $param_value);
		if (isset($db_select[0]))
		{
			$db_select_value = explode(';', $db_select[0]);

			if (isset($db_select_value[0]))
				$mod_param = (string) $db_select_value[0];
			else
				$mod_param = '';
		}
		else
			$mod_param = '';
	}
	elseif(trim(strtolower($param_type)) == 'checklist')
	{
		$list_params = explode(':', $param_value);

		// Set a few booleans here.
		$has_checks = !empty($list_params) && isset($list_params[0]) && trim($list_params[0]) != '' && !stristr(trim($list_params[0]), 'order');
		$has_strings = isset($list_params[1]) && trim($list_params[1]) != '';
		$has_order = !empty($list_params[2]) && isset($list_params[2]) && strlen(stristr(trim($list_params[2]), 'order')) > 0;

		if ($has_strings)
		{
			// Gather an array of all values.
			$string_values = explode(';', $list_params[1]);

			if ($has_order)
			{
				// Order me timbers...
				$order = array();
				$order = explode(';', $list_params[2]);

				if (!empty($order[1]) && trim($order[1]) != '')
				{
					$c_order_keys = explode(',', $order[1]);
					$c_string_vals = $string_values;

					foreach($c_order_keys as $val)
					{
						if (isset($c_string_vals[$val]))
						{
							$mod_param['order'][] = $c_string_vals[$val];
							unset($c_string_vals[$val]);
						}
					}

					// Grab any values that remain, if any.
					$mod_param['order'] = array_merge($mod_param['order'], $c_string_vals);
				}
				else
					$mod_param['order'] = $string_values;
			}

			if ($has_checks)
			{
				$check_keys = explode(',', $list_params[0]);

				// Check it out y'all!
				if ($check_keys[0] != '-2')
				{
					// Make sure we obide by the order of things!
					if (!empty($c_order_keys))
					{
						$c_keys = array_intersect($c_order_keys, $check_keys);
						foreach($c_keys as $c_val)
							$mod_param['checked'][] = $string_values[$c_val];
					}
					else
					{
						foreach($check_keys as $value)
							$mod_param['checked'][] = $string_values[$value];
					}
				}
			}
			else
				// Set to empty and let the module function handle this.
				$mod_param = '';
		}
		else
			$mod_param = '';

		// We're done with this now.
		unset($list_params);
	}
	elseif(trim(strtolower($param_type)) == 'list_groups')
	{
		$group_params = explode(':', $param_value);

		if (!empty($group_params) && isset($group_params[0]) && trim($group_params[0]) != '' && !stristr(trim($group_params[0]), 'order'))
		{
			// Are there any group ids that are not allowed?
			if (isset($group_params[1]) && !stristr(trim($group_params[1]), 'order'))
			{
				$checked = explode(',', $group_params[0]);
				$unallowed = explode(',', $group_params[1]);

				// We have values not allowed, let's filter them out now.
				$checked = array_diff($checked, $unallowed);

				// Note:  If (value < -1), than nothing is checked for this in the Admin.
				// 		  But we will let the Customizer choose what to do about it and keep it's value as is!
				if (count($checked) >= 1)
				{
					// Rebuild the array keys.
					$checked = array_values($checked);

					// Put it back together and return it.
					$mod_param = implode(',', $checked);

					// No longer needed.
					unset($checked, $unallowed);
				}
				// Opps, no group ids are being used.  Let the module function handle this instead.
				else
					$mod_param = '';
			}
			else
				// All groups are enabled, return the values.
				$mod_param = $group_params[0];
		}
		else
			// Error, set to empty and let the module function handle this instead.
			$mod_param = '';

		// We're done with this now.
		unset($group_params);
	}
	elseif(trim(strtolower($param_type)) == 'list_bbc')
		$mod_param = $param_value;
	else
		$mod_param = trim(strtolower($param_type)) == 'html' ? html_entity_decode($param_value, ENT_QUOTES, $context['character_set']) : $param_value;

	return isset($mod_param) ? $mod_param : '';
}

function parseString($str = '', $type = 'filepath', $replace = true)
{
	if ($str == '')
		return '';

	switch ((string) $type)
	{
		// Only accepts replace.
		case 'name':
			$find = array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/');
			$replace_str = array('_', '_', '');
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// trims away the first and last slashes, or matches against it.
		case 'folderpath':
			$valid_str = $replace ? 0 : (strpos($str, ' ') !== false ? 1 : 0);
			$find = $replace ? '#^/|/$|[^A-Za-z0-9_\/s/\-/]#' : '#^(\w+/){0,2}\w+-$#';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : (!empty($valid_str) ? $valid_str : preg_match($find, $str));
			break;
		case 'function_name':
			$find = '~[^A-Za-z0-9_]+~s';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : preg_match($find, $str);
			break;
		// Only accepts replace.
		case 'uploaded_file':
			$find = array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/');
			$replace_str = array('_', '.', '');
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// Only accepts replace.  Strips out all php tags from a file and also removes additional line breaks, replacing with only 1 line break instead!
		case 'phptags':
			$find = array('/<\?php/s', '/\?>/s', '/<\?/s', '/(?:(?:\r\n|\r|\n)\s*){2}/s');
			$replace_str = array('', '', '', "\n");
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// Example: THIS STRING:  /my%root/p:a;t h/my file-english.php/  BECOMES THIS: myroot/path/myfile-english.php
		default:
			$valid_str = $replace ? 0 : (strpos($str, ' ') !== false ? 1 : 0);
			$find = $replace ? '#^/|/$|[^A-Za-z0-9_.\/s/\-/]#' : '#^(\w+/){0,2}\w+-\.\w+$#';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : (!empty($valid_str) ? $valid_str : preg_match($find, $str));
			break;
	}
	return $valid_str;
}

function module_error($type = 'error', $error_type = 'general', $log_error = false, $echo = true)
{
	global $txt;

	// All possible pre-defined types.
	$valid_types = array(
		'mod_not_installed' => $type == 'mod_not_installed' ? 1 : 0,
		'not_allowed' => $type == 'not_allowed' ? 1 : 0,
		'no_language' => $type == 'no_language' ? 1 : 0,
		'query_error' => $type == 'query_error' ? 1 : 0,
		'empty' => $type == 'empty' ? 1 : 0,
		'error' => $type == 'error' ? 1 : 0,
	);

	$error_string = !empty($valid_types[$type]) ? $txt['dp_module_' . $type] : $type;
	$error_html = $error_type == 'critical' ? array('<p class="error">', '</p>') : array('', '');

	// Don't need this anymore!
	unset($valid_types);

	// Should it be logged?
	if ($log_error)
		log_error($error_string, $error_type);

	$return = implode($error_string, $error_html);

	// Echo...? Echo...?
	if ($echo)
		echo $return;
	else
		return $return;
}

function loadFiles($file_input = array())
{
	global $txt, $scripturl, $smcFunc;

	if(count($file_input) <= 0)
		return '';

	// Calling all files for that module/clone!
	$request = $smcFunc['db_query']('', '
		SELECT id_file, id_thumb, file_type, filename, file_hash, fileext, size, downloads, width, height, mime_type
		FROM {db_prefix}dp_module_files
		WHERE id_param = {int:id_param}',
		array(
			'id_param' => $file_input['id_param'],
		)
	);

	$mod_type = $file_input['is_clone'] ? 'clone' : 'mod';

	$fileData = array();

	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Getting all info.
		if (empty($row['file_type']))
			$fileData[$row['id_file']] = array(
				'name' => preg_replace('~&amp;#(\\d{1,7}|x[0-9a-fA-F]{1,6});~', '&#\\1;', htmlspecialchars($row['filename'])),
				'extension' => $row['fileext'],
				'size' => round($row['size'] / 1024, 2) . ' ' . $txt['kilobyte'],
				'byte_size' => $row['size'],
				'width' => $row['width'],
				'height' => $row['height'],
				'downloads' => $row['downloads'],
				'mime_type' => $row['mime_type'],
				'href' => $scripturl . '?action=dreamFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_file'],
				'link' => '<a href="' . $scripturl . '?action=dreamFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_file'] . '">' . htmlspecialchars($row['filename']) . '</a>',
				'is_image' => !empty($row['width']) && !empty($row['height']),
				'has_thumb' => !empty($row['id_thumb']),
				'thumb_href' => !empty($row['id_thumb']) ? $scripturl . '?action=dreamFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_thumb'] . ';image' : '',
			);
	}

	// Order it correctly.
	ksort($fileData, SORT_NUMERIC);

	// Rebuild it.
	$fileData = array_values($fileData);

	return $fileData;
}

function createFile(&$fileOptions)
{
	global $sourcedir, $smcFunc;

	$file_dir = $fileOptions['folderpath'];

	$fileOptions['errors'] = array();

	if (!isset($fileOptions['id_file']))
		$fileOptions['id_file'] = 0;

	$file_restricted = @ini_get('open_basedir') != '';

	// Make sure the file actually exists...
	if (!$file_restricted && !file_exists($fileOptions['tmp_name']))
	{
		$fileOptions['errors'] = array('could_not_upload');
		return false;
	}

	// These are the only valid image types.
	$validImageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpg', 14 => 'iff');

	if (!$file_restricted)
	{
		$size = @getimagesize($fileOptions['tmp_name']);
		list ($fileOptions['width'], $fileOptions['height']) = $size;

		// If it's an image get the mime type right.
		if (empty($fileOptions['mime_type']) && $fileOptions['width'])
		{
			// Got a proper mime type?
			if (!empty($size['mime']))
				$fileOptions['mime_type'] = $size['mime'];
			// Otherwise a valid one?
			elseif (isset($validImageTypes[$size[2]]))
				$fileOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
		}
	}

	// Get the hash if no hash has been given yet.
	if (empty($fileOptions['file_hash']))
		$fileOptions['file_hash'] = getFilename($fileOptions['name'], false, null, true);

	// Check the extension, it must be valid.
	$allowed = explode(',', $fileOptions['fileExtensions']);
	foreach ($allowed as $k => $dummy)
		$allowed[$k] = trim($dummy);

	if (!in_array(strtolower(substr(strrchr($fileOptions['name'], '.'), 1)), $allowed))
		$fileOptions['errors'] = array('bad_extension');

	if (!is_writable($file_dir))
		$fileOptions['errors'] = array('files_no_write');

	// Return if errors detected somewhere.
	if (!empty($fileOptions['errors']))
		return false;

	// Assuming no-one set the extension let's take a look at it.
	if (empty($fileOptions['fileext']))
	{
		$fileOptions['fileext'] = strtolower(strrpos($fileOptions['name'], '.') !== false ? substr($fileOptions['name'], strrpos($fileOptions['name'], '.') + 1) : '');
		if (strlen($fileOptions['fileext']) > 8 || '.' . $fileOptions['fileext'] == $fileOptions['name'])
			$fileOptions['fileext'] = '';
	}

	// If strict, skip this and go directly to a thumbnail, ONLY if it is bigger than the dimensions specified.
	if (isset($fileOptions['resizeWidth']) && isset($fileOptions['resizeHeight']))
	{
		$resize = ($fileOptions['width'] > $fileOptions['resizeWidth'] && !empty($fileOptions['resizeWidth'])) || ($fileOptions['height'] > $fileOptions['resizeHeight'] && !empty($fileOptions['resizeHeight'])) ? true : false;
		$not_strict = (!empty($fileOptions['strict']) && !$resize) || empty($fileOptions['strict']) ? true : false;
	}
	else
	{
		$resize = false;
		$not_strict = true;
	}

	$smcFunc['db_insert']('',
		'{db_prefix}dp_module_files',
		array(
			'id_param' => 'int', 'id_member' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
			'size' => 'int', 'width' => 'int', 'height' => 'int',
			'mime_type' => 'string-255',
		),
		array(
			(int) $fileOptions['id_param'], $fileOptions['id_member'], $fileOptions['name'], $fileOptions['file_hash'], $fileOptions['fileext'],
			(int) $fileOptions['size'], (empty($fileOptions['width']) ? 0 : (int) $fileOptions['width']), (empty($fileOptions['height']) ? '0' : (int) $fileOptions['height']),
			(!empty($fileOptions['mime_type']) ? $fileOptions['mime_type'] : (!empty($fileOptions['file_mime']) ? $fileOptions['file_mime'] : '')),
		),
		array('id_file')
	);

	$fileOptions['id'] = $smcFunc['db_insert_id']('{db_prefix}dp_module_files', 'id_file');

	$fileOptions['destination'] = getFilename(basename($fileOptions['name']), $fileOptions['id'], $fileOptions['folderpath'], false, $fileOptions['file_hash']);

	if ($not_strict)
	{
		// Move the file to where it needs to go.
		if (!move_uploaded_file($fileOptions['tmp_name'], $fileOptions['destination']))
		{
			$fileOptions['error'] = array('file_timeout');
			return false;
		}
		// We couldn't access the file before...
		elseif ($file_restricted)
		{
			$size = @getimagesize($fileOptions['destination']);
			list ($fileOptions['width'], $fileOptions['height']) = $size;

			// Have a go at getting the right mime type.
			if (empty($fileOptions['mime_type']) && $fileOptions['width'])
			{
				if (!empty($size['mime']))
					$fileOptions['mime_type'] = $size['mime'];
				elseif (isset($validImageTypes[$size[2]]))
					$fileOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
			}

			if (!empty($fileOptions['width']) && !empty($fileOptions['height']))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_files
					SET
						width = {int:width},
						height = {int:height},
						mime_type = {string:mime_type}
					WHERE id_file = {int:id_file}',
					array(
						'width' => (int) $fileOptions['width'],
						'height' => (int) $fileOptions['height'],
						'id_file' => $fileOptions['id'],
						'mime_type' => empty($fileOptions['mime_type']) ? '' : $fileOptions['mime_type'],
					)
				);
		}
		// Attempt to chmod it.
		@chmod($fileOptions['destination'], 0644);

		// No Thumbnails to create!
		if (!$resize)
			return true;
	}

	// Ready to create the thumbnails
	if (!$not_strict || $resize)
	{
		if (!empty($fileOptions['strict']))
			move_uploaded_file($fileOptions['tmp_name'], $fileOptions['destination']);

		require_once($sourcedir . '/Subs-Graphics.php');
		if (createThumbnail($fileOptions['destination'], $fileOptions['resizeWidth'], $fileOptions['resizeHeight']))
		{
			// Strict?
			if (!empty($fileOptions['strict']))
			{
				if (@rename($fileOptions['destination'] . '_thumb', $fileOptions['destination']))
				{
					$destination = $fileOptions['destination'];
					$filename = $fileOptions['name'];
				}
				else
				// Just in case we have trouble renaming the file.
				{
					$destination = $fileOptions['destination'] . '_thumb';
					$filename = $fileOptions['name'] . '_thumb';
				}
			}
			else
			{
				$destination = $fileOptions['destination'] . '_thumb';
				$filename = $fileOptions['name'] . '_thumb';
			}

			// Figure out how big we actually made it.
			$size = @getimagesize($destination);
			list ($thumb_width, $thumb_height) = $size;

			if (!empty($size['mime']))
				$thumb_mime = $size['mime'];
			elseif (isset($validImageTypes[$size[2]]))
				$thumb_mime = 'image/' . $validImageTypes[$size[2]];
			// Lord only knows how this happened...
			else
				$thumb_mime = '';

			$thumb_filename = $filename;
			$thumb_size = filesize($destination);
			$thumb_file_hash = getFilename($thumb_filename, false, null, true);

			if (empty($fileOptions['strict']))
			{
				// To the database we go!
				$smcFunc['db_insert']('',
					'{db_prefix}dp_module_files',
					array(
						'id_param' => 'int', 'id_member' => 'int', 'file_type' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
						'size' => 'int', 'width' => 'int', 'height' => 'int', 'mime_type' => 'string-255',
					),
					array(
						(int) $fileOptions['id_param'], $fileOptions['id_member'], 1, $thumb_filename, $thumb_file_hash, $fileOptions['fileext'],
						$thumb_size, $thumb_width, $thumb_height, $thumb_mime,
					),
					array('id_file')
				);
				$fileOptions['thumb'] = $smcFunc['db_insert_id']('{db_prefix}dp_module_files', 'id_file');

				if (!empty($fileOptions['thumb']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}dp_module_files
						SET id_thumb = {int:id_thumb}
						WHERE id_file = {int:id_file}',
						array(
							'id_thumb' => $fileOptions['thumb'],
							'id_file' => $fileOptions['id'],
						)
					);

					rename($fileOptions['destination'] . '_thumb', getFilename($thumb_filename, $fileOptions['thumb'], $fileOptions['folderpath'], false, $thumb_file_hash));
				}
			}
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}dp_module_files
					SET
						width = {int:width},
						height = {int:height},
						size = {int:size},
						mime_type = {string:mime_type}
					WHERE id_file = {int:id_file}',
					array(
						'width' => (int) $thumb_width,
						'height' => (int) $thumb_height,
						'id_file' => $fileOptions['id'],
						'size' => (int) $thumb_size,
						'mime_type' => $thumb_mime,
					)
				);

				// Attempt to chmod it.
				@chmod($fileOptions['destination'], 0644);
			}
		}
	}
	return true;
}

function dreamRedirect(&$setLocation, &$refresh)
{
	global $scripturl;

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'markasread' && isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'all')
	{
		$setLocation = $scripturl . '?action=forum';
		$refresh = false;
	}
}

function dreamBuffer($buffer)
{
	global $portal_ver, $context, $forum_copyright;

	if (isset($_REQUEST['xml']))
		return $buffer;

	// SMF RC Version?
	if (strpos($forum_copyright, 'LLC') !== FALSE)
	{
		$search = array(
			', Simple Machines LLC</a>',
			'class="copywrite"',
		);

		$replace = array(
			', Simple Machines LLC</a><br /><a class="new_win" href="http://dream-portal.net/" target="_blank">Dream Portal ' . $portal_ver . ' &copy; 2009&ndash;' . strftime('%Y', forum_time()) . ' Dream Portal Team</a>',
			'class="copywrite" style="line-height: 1;"',
		);

		if (!empty($context['has_dp_layout']))
		{
			// Prevent the DP table from overrflowing the SMF theme
			$search += array('<body>', '</body>');
			$replace += array('<body><div id="dream_container">', '</div></body>');
		}
		return str_replace($search, $replace, $buffer);
	}
	else	// We have SMF 2.0 GOLD!
	{
		global $settings;

		// Need to adjust the replace for specific themes...
		$replace = dpCheckThemes($settings['theme_url']);
		$search = 'Simple Machines</a>';

		if ($replace == '')
			$replace = 'Simple Machines</a><br class="clear" /><span style="display: inline; visibility: visible; font-family: Verdana, Arial, sans-serif;"><a href="http://dream-portal.net/" target="_blank" class="new_win">Dream Portal ' . $portal_ver . ' &copy; 2009&ndash;' . strftime('%Y', forum_time()) . ' Dream Portal Team</a></span>';

		$begin_footer = strrpos($buffer, $forum_copyright);

		$footer = substr($buffer, $begin_footer, strlen($buffer));
		$buffer = substr($buffer, 0, $begin_footer) . str_replace($search, $replace, $footer);
		return $buffer;
	}
}

function dpCheckThemes($theme_url)
{
	global $portal_ver;

	$theme_pos = strrpos(strtolower($theme_url), 'themes/');

	if ($theme_pos !== FALSE)
		$theme = substr($theme_url, $theme_pos + 7);
	else
		return '';

	// Here's a list of themes that we'll need to change the replace value for...
	$specificThemes = array(
		'blackrainv3_20g_', 
		'blackrainv2_20g', 
		'core', 
		'smooth', 
		'carbonate202b', 
		'carbonate202b',
		'enterprise_smf20final',
		'green_theme_v5',
		'green_theme_v2_rc4',
		'new_look_2-0',
		'red_theme_v2_2',
		'yabb_se_classic_2',
	);

	if (in_array($theme, $specificThemes))
		return 'Simple Machines</a></span></li></ul><ul class="reset"><li class="copyright"><span class="smalltext" style="display: inline; visibility: visible; font-family: Verdana, Arial, sans-serif;"><a href="http://dream-portal.net/" target="_blank" class="new_win">Dream Portal ' . $portal_ver . ' &copy; 2009&ndash;' . strftime('%Y', forum_time()) . ' Dream Portal Team</a>'; 
	else
		return '';
}

function AllowedFileExtensions($file_mime)
{
	switch ((string) $file_mime)
	{
		case 'x-world/x-3dmf':
			return '3dm, 3dmf, qd3, qd3d';
			break;
		case 'application/octet-stream':
		case 'application/octetstream':
			return 'a, arc, arj, bin, com, dump, exe, lha, lhx, lzh, lzx, o, saveme, uu, zip, zoo';
			break;
		case 'image/psd':
			return 'psd';
			break;
		case 'application/x-authorware-bin':
			return 'aab';
			break;
		case 'application/x-authorware-map':
			return 'aam';
			break;
		case 'application/x-authorware-seg':
			return 'aas';
			break;
		case 'text/vnd.abc':
			return 'abc';
			break;
		case 'text/html':
			return 'acgi, htm, html, htmls, htx, shtml';
			break;
		case 'video/animaflex':
			return 'afl';
			break;
		case 'application/postscript':
			return 'ai, eps, ps';
			break;
		case 'audio/aiff':
		case 'audio/x-aiff':
			return 'aif, aifc, aiff';
			break;
		case 'application/x-aim':
			return 'aim';
			break;
		case 'text/x-audiosoft-intra':
			return 'aip';
			break;
		case 'application/x-navi-animation':
			return 'ani';
			break;
		case 'application/x-nokia-9000-communicator-add-on-software':
			return 'aos';
			break;
		case 'application/mime':
			return 'aps';
			break;
		case 'application/arj':
			return 'arj';
			break;
		case 'image/x-jg':
			return 'art';
			break;
		case 'video/x-ms-asf':
			return 'asf, asx';
			break;
		case 'video/x-ms-asf-plugin':
		case 'application/x-mplayer2':
			return 'asx';
			break;
		case 'text/x-asm':
			return 'asm, s';
			break;
		case 'text/asp':
			return 'asp';
			break;
		case 'audio/basic':
			return 'au, snd';
			break;
		case 'audio/x-au':
			return 'au';
			break;
		case 'application/x-troff-msvideo':
		case 'video/avi':
		case 'video/msvideo':
		case 'video/x-msvideo':
			return 'avi';
			break;
		case 'video/avs-video':
			return 'avs';
			break;
		case 'application/x-bcpio':
			return 'bcpio';
			break;
		case 'application/mac-binary':
		case 'application/macbinary':
		case 'application/x-binary':
		case 'application/x-macbinary':
			return 'bin';
			break;
		case 'image/bmp':
			return 'bm, bmp';
			break;
		case 'image/x-windows-bmp':
			return 'bmp';
			break;
		case 'application/book':
			return 'boo, book';
			break;
		case 'application/x-bzip2':
			return 'boz, bz2';
			break;
		case 'application/x-bsh':
			return 'bsh, sh, shar';
			break;
		case 'application/x-bzip':
			return 'bz';
			break;
		case 'text/plain':
			return 'c, c++, cc, com, conf, cxx, def, f, f90, for, g, h, hh, idc, jav, java, list, log, lst, m, mar, pl, sdml, text, txt';
			break;
		case 'text/x-c':
			return 'c, cc, cpp';
			break;
		case 'application/vnd.ms-pki.seccat':
			return 'cat';
			break;
		case 'application/clariscad':
			return 'ccad';
			break;
		case 'application/x-cocoa':
			return 'cco';
			break;
		case 'application/cdf':
		case 'application/x-cdf':
			return 'cdf';
			break;
		case 'application/x-netcdf':
			return 'cdf, nc';
			break;
		case 'application/x-x509-user-cert':
			return 'crt';
			break;
		case 'application/pkix-cert':
			return 'cer, crt';
			break;
		case 'application/x-x509-ca-cert':
			return 'cer, crt, der';
			break;
		case 'application/x-chat':
			return 'cha, chat';
			break;
		case 'application/java':
		case 'application/java-byte-code':
		case 'application/x-java-class':
			return 'class';
			break;
		case 'application/x-cpio':
			return 'cpio';
			break;
		case 'application/mac-compactpro':
		case 'application/x-compactpro':
		case 'application/x-cpt':
			return 'cpt';
			break;
		case 'application/pkcs-crl':
		case 'application/pkix-crl':
			return 'crl';
			break;
		case 'application/x-csh':
		case 'text/x-script.csh':
			return 'csh';
			break;
		case 'application/x-pointplus':
		case 'text/css':
			return 'css';
			break;
		case 'application/x-director':
			return 'dcr, dir, dxr';
			break;
		case 'application/x-deepv':
			return 'deepv';
			break;
		case 'video/x-dv':
			return 'dif, dv';
			break;
		case 'video/dl':
		case 'video/x-dl':
			return 'dl';
			break;
		case 'application/msword':
			return 'doc, dot, w6w, wiz, word';
			break;
		case 'application/commonground':
			return 'dp';
			break;
		case 'application/drafting':
			return 'drw';
			break;
		case 'application/x-dvi':
			return 'dvi';
			break;
		case 'drawing/x-dwf':
		case 'model/vnd.dwf':
			return 'dwf';
			break;
		case 'application/acad':
			return 'dwg';
			break;
		case 'image/vnd.dwg':
		case 'image/x-dwg':
			return 'dwg, dxf, svf';
			break;
		case 'application/dxf':
			return 'dxf';
			break;
		case 'text/x-script.elisp':
			return 'el';
			break;
		case 'application/x-bytecode.elisp':
		case 'application/x-elc':
			return 'elc';
			break;
		case 'application/x-envoy':
			return 'env, evy';
			break;
		case 'application/x-esrehber':
			return 'es';
			break;
		case 'text/x-setext':
			return 'etx';
			break;
		case 'application/envoy':
			return 'evy';
			break;
		case 'text/x-fortran':
			return 'f77, f90, for, f';
			break;
		case 'application/vnd.fdf':
			return 'fdf';
			break;
		case 'application/fractals':
			return 'fif';
			break;
		case 'image/fif':
			return 'fif';
			break;
		case 'video/fli':
		case 'video/x-fli':
			return 'fli';
			break;
		case 'image/florian':
			return 'flo, turbot';
			break;
		case 'text/vnd.fmi.flexstor':
			return 'flx';
			break;
		case 'video/x-atomic3d-feature':
			return 'fmf';
			break;
		case 'image/vnd.fpx':
		case 'image/vnd.net-fpx':
			return 'fpx';
			break;
		case 'application/freeloader':
			return 'frl';
			break;
		case 'audio/make':
			return 'funk, my, pfunk';
			break;
		case 'image/g3fax':
			return 'g3';
			break;
		case 'image/gif':
			return 'gif';
			break;
		case 'video/gl':
		case 'video/x-gl':
			return 'gl';
			break;
		case 'audio/x-gsm':
			return 'gsd, gsm';
			break;
		case 'application/x-gsp':
			return 'gsp';
			break;
		case 'application/x-gss':
			return 'gss';
			break;
		case 'application/x-gtar':
			return 'gtar';
			break;
		case 'application/x-compressed':
			return 'gz, tgz, z, zip';
			break;
		case 'application/x-gzip':
			return 'gz, gzip';
			break;
		case 'multipart/x-gzip':
			return 'gzip';
			break;
		case 'text/x-h':
			return 'h, hh';
			break;
		case 'application/x-hdf':
			return 'hdf';
			break;
		case 'application/x-helpfile':
			return 'help, hlp';
			break;
		case 'application/vnd.hp-hpgl':
			return 'hgl, hpg, hpgl';
			break;
		case 'text/x-script':
			return 'hlb';
			break;
		case 'application/hlp':
		case 'application/x-winhelp':
			return 'hlp';
			break;
		case 'application/binhex':
		case 'application/binhex4':
		case 'application/mac-binhex':
		case 'application/mac-binhex40':
		case 'application/x-binhex40':
		case 'application/x-mac-binhex40':
			return 'hqx';
			break;
		case 'application/hta':
			return 'hta';
			break;
		case 'text/x-component':
			return 'htc';
			break;
		case 'text/webviewhtml':
			return 'htt';
			break;
		case 'x-conference/x-cooltalk':
			return 'ice';
			break;
		case 'image/x-icon':
			return 'ico';
			break;
		case 'image/ief':
			return 'ief, iefs';
			break;
		case 'application/iges':
		case 'model/iges':
			return 'iges, igs';
			break;
		case 'application/x-ima':
			return 'ima';
			break;
		case 'application/x-httpd-imap':
			return 'imap';
			break;
		case 'application/inf':
			return 'inf';
			break;
		case 'application/x-internett-signup':
			return 'ins';
			break;
		case 'application/x-ip2':
			return 'ip';
			break;
		case 'video/x-isvideo':
			return 'isu';
			break;
		case 'audio/it':
			return 'it';
			break;
		case 'application/x-inventor':
			return 'iv';
			break;
		case 'i-world/i-vrml':
			return 'ivr';
			break;
		case 'application/x-livescreen':
			return 'ivy';
			break;
		case 'audio/x-jam':
			return 'jam';
			break;
		case 'text/x-java-source':
			return 'jav, java';
			break;
		case 'application/x-java-commerce':
			return 'jcm';
			break;
		case 'image/jpeg':
			return 'jfif, jfif-tbnl, jpe, jpeg, jpg';
			break;
		case 'image/pjpeg':
			return 'jfif, jpe, jpeg, jpg';
			break;
		case 'image/x-jps':
			return 'jps';
			break;
		case 'application/x-javascript':
			return 'js';
			break;
		case 'image/jutvision':
			return 'jut';
			break;
		case 'audio/midi':
			return 'kar, mid, midi';
			break;
		case 'music/x-karaoke':
			return 'kar';
			break;
		case 'application/x-ksh':
		case 'text/x-script.ksh':
			return 'ksh';
			break;
		case 'audio/nspaudio':
		case 'audio/x-nspaudio':
			return 'la, lma';
			break;
		case 'audio/x-liveaudio':
			return 'lam';
			break;
		case 'application/x-latex':
			return 'latex, ltx';
			break;
		case 'application/lha':
		case 'application/x-lha':
			return 'lha';
			break;
		case 'application/x-lisp':
		case 'text/x-script.lisp':
			return 'lsp';
			break;
		case 'text/x-la-asf':
			return 'lsx';
			break;
		case 'application/x-lzh':
			return 'lzh';
			break;
		case 'application/lzx':
		case 'application/x-lzx':
			return 'lzx';
			break;
		case 'text/x-m':
			return 'm';
			break;
		case 'video/mpeg':
			return 'm1v, m2v, mp2, mp3, mpa, mpe, mpeg, mpg';
			break;
		case 'audio/mpeg':
			return 'm2a, mp2, mp3, mpa, mpg, mpga';
			break;
		case 'audio/x-mpequrl':
			return 'm3u';
			break;
		case 'application/x-troff-man':
			return 'man';
			break;
		case 'application/x-navimap':
			return 'map';
			break;
		case 'application/mbedlet':
			return 'mbd';
			break;
		case 'application/x-magic-cap-package-1.0':
			return 'mc$';
			break;
		case 'application/mcad':
		case 'application/x-mathcad':
			return 'mcd';
			break;
		case 'image/vasa':
		case 'text/mcf':
			return 'mcf';
			break;
		case 'application/netmc':
			return 'mcp';
			break;
		case 'application/x-troff-me':
			return 'me';
			break;
		case 'message/rfc822':
			return 'mht, mhtml, mime';
			break;
		case 'application/x-midi':
		case 'audio/x-mid':
		case 'audio/x-midi':
		case 'music/crescendo':
		case 'x-music/x-midi':
			return 'mid, midi';
			break;
		case 'application/x-frame':
		case 'application/x-mif':
			return 'mif';
			break;
		case 'www/mime':
			return 'mime';
			break;
		case 'audio/x-vnd.audioexplosion.mjuicemediafile':
			return 'mjf';
			break;
		case 'video/x-motion-jpeg':
			return 'mjpg';
			break;
		case 'application/base64':
			return 'mm, mme';
			break;
		case 'application/x-meme':
			return 'mm';
			break;
		case 'audio/mod':
		case 'audio/x-mod':
			return 'mod';
			break;
		case 'video/quicktime':
			return 'moov, mov, qt';
			break;
		case 'video/x-sgi-movie':
			return 'movie, mv';
			break;
		case 'audio/x-mpeg':
		case 'video/x-mpeq2a':
			return 'mp2';
			break;
		case 'video/x-mpeg':
			return 'mp2, mp3';
			break;
		case 'audio/mpeg3':
		case 'audio/x-mpeg-3':
			return 'mp3';
			break;
		case 'application/x-project':
			return 'mpc, mpt, mpv, mpx';
			break;
		case 'application/vnd.ms-project':
			return 'mpp';
			break;
		case 'application/marc':
			return 'mrc';
			break;
		case 'application/x-troff-ms':
			return 'ms';
			break;
		case 'application/x-vnd.audioexplosion.mzz':
			return 'mzz';
			break;
		case 'image/naplps':
			return 'nap, naplps';
			break;
		case 'application/vnd.nokia.configuration-message':
			return 'ncm';
			break;
		case 'image/x-niff':
			return 'nif, niff';
			break;
		case 'application/x-mix-transfer':
			return 'nix';
			break;
		case 'application/x-conference':
			return 'nsc';
			break;
		case 'application/x-navidoc':
			return 'nvd';
			break;
		case 'application/oda':
			return 'oda';
			break;
		case 'application/x-omc':
			return 'omc';
			break;
		case 'application/x-omcdatamaker':
			return 'omcd';
			break;
		case 'application/x-omcregerator':
			return 'omcr';
			break;
		case 'text/x-pascal':
			return 'p';
			break;
		case 'application/pkcs10':
		case 'application/x-pkcs10':
			return 'p10';
			break;
		case 'application/pkcs-12':
		case 'application/x-pkcs12':
			return 'p12';
			break;
		case 'application/x-pkcs7-signature':
			return 'p7a';
			break;
		case 'application/pkcs7-mime':
		case 'application/x-pkcs7-mime':
			return 'p7c, p7m';
			break;
		case 'application/x-pkcs7-certreqresp':
			return 'p7r';
			break;
		case 'application/pkcs7-signature':
			return 'p7s';
			break;
		case 'application/pro_eng':
			return 'part, prt';
			break;
		case 'text/pascal':
			return 'pas';
			break;
		case 'image/x-portable-bitmap':
			return 'pbm';
			break;
		case 'application/vnd.hp-pcl':
		case 'application/x-pcl':
			return 'pcl';
			break;
		case 'image/x-pict':
			return 'pct';
			break;
		case 'image/x-pcx':
			return 'pcx';
			break;
		case 'chemical/x-pdb':
			return 'pdb, xyz';
			break;
		case 'application/pdf':
			return 'pdf';
			break;
		case 'audio/make.my.funk':
			return 'pfunk';
			break;
		case 'image/x-portable-graymap':
		case 'image/x-portable-greymap':
			return 'pgm';
			break;
		case 'application/x-httpd-php':
			return 'php';
			break;
		case 'image/pict':
			return 'pic, pict';
			break;
		case 'application/x-newton-compatible-pkg':
			return 'pkg';
			break;
		case 'application/vnd.ms-pki.pko':
			return 'pko';
			break;
		case 'text/x-script.perl':
			return 'pl';
			break;
		case 'application/x-pixclscript':
			return 'plx';
			break;
		case 'image/x-xpixmap':
			return 'pm, xpm';
			break;
		case 'text/x-script.perl-module':
			return 'pm';
			break;
		case 'application/x-pagemaker':
			return 'pm4, pm5';
			break;
		case 'image/png':
			return 'png, x-png';
			break;
		case 'application/x-portable-anymap':
		case 'image/x-portable-anymap':
			return 'pnm';
			break;
		case 'application/mspowerpoint':
			return 'pot, pps, ppt, ppz';
			break;
		case 'application/vnd.ms-powerpoint':
			return 'pot, ppa, pps, ppt, pwz';
			break;
		case 'model/x-pov':
			return 'pov';
			break;
		case 'image/x-portable-pixmap':
			return 'ppm';
			break;
		case 'application/powerpoint':
		case 'application/x-mspowerpoint':
			return 'ppt';
			break;
		case 'application/x-freelance':
			return 'pre';
			break;
		case 'paleovu/x-pv':
			return 'pvu';
			break;
		case 'text/x-script.phyton':
			return 'py';
			break;
		case 'application/x-bytecode.python':
			return 'pyc';
			break;
		case 'audio/vnd.qcelp':
			return 'qcp';
			break;
		case 'image/x-quicktime':
			return 'qif, qti, qtif';
			break;
		case 'video/x-qtc':
			return 'qtc';
			break;
		case 'audio/x-pn-realaudio':
			return 'ra, ram, rm, rmm, rmp';
			break;
		case 'audio/x-pn-realaudio-plugin':
			return 'ra, rmp, rpm';
			break;
		case 'audio/x-realaudio':
			return 'ra';
			break;
		case 'application/x-cmu-raster':
		case 'image/x-cmu-raster':
			return 'ras';
			break;
		case 'image/cmu-raster':
			return 'ras, rast';
			break;
		case 'text/x-script.rexx':
			return 'rexx';
			break;
		case 'image/vnd.rn-realflash':
			return 'rf';
			break;
		case 'image/x-rgb':
			return 'rgb';
			break;
		case 'application/vnd.rn-realmedia':
			return 'rm';
			break;
		case 'audio/mid':
			return 'rmi';
			break;
		case 'application/ringing-tones':
		case 'application/vnd.nokia.ringing-tone':
			return 'rng';
			break;
		case 'application/vnd.rn-realplayer':
			return 'rnx';
			break;
		case 'application/x-troff':
			return 'roff, t, tr';
			break;
		case 'image/vnd.rn-realpix':
			return 'rp';
			break;
		case 'text/richtext':
			return 'rt, rtf, rtx';
			break;
		case 'text/vnd.rn-realtext':
			return 'rt';
			break;
		case 'application/rtf':
			return 'rtf, rtx';
			break;
		case 'application/x-rtf':
			return 'rtf';
			break;
		case 'video/vnd.rn-realvideo':
			return 'rv';
			break;
		case 'audio/s3m':
			return 's3m';
			break;
		case 'application/x-tbook':
			return 'sbk, tbk';
			break;
		case 'application/x-lotusscreencam':
		case 'text/x-script.guile':
		case 'text/x-script.scheme':
		case 'video/x-scm':
			return 'scm';
			break;
		case 'application/sdp':
		case 'application/x-sdp':
			return 'sdp';
			break;
		case 'application/sounder':
			return 'sdr';
			break;
		case 'application/sea':
		case 'application/x-sea':
			return 'sea';
			break;
		case 'application/set':
			return 'set';
			break;
		case 'text/sgml':
		case 'text/x-sgml':
			return 'sgm, sgml';
			break;
		case 'application/x-sh':
		case 'text/x-script.sh':
			return 'sh';
			break;
		case 'application/x-shar':
			return 'sh, shar';
			break;
		case 'text/x-server-parsed-html':
			return 'shtml, ssi';
			break;
		case 'audio/x-psid':
			return 'sid';
			break;
		case 'application/x-sit':
		case 'application/x-stuffit':
			return 'sit';
			break;
		case 'application/x-koan':
			return 'skd, skm, skp, skt';
			break;
		case 'application/x-seelogo':
			return 'sl';
			break;
		case 'application/smil':
			return 'smi, smil';
			break;
		case 'audio/x-adpcm':
			return 'snd';
			break;
		case 'application/solids':
			return 'sol';
			break;
		case 'application/x-pkcs7-certificates':
			return 'spc';
			break;
		case 'text/x-speech':
			return 'spc, talk';
			break;
		case 'application/futuresplash':
			return 'spl';
			break;
		case 'application/x-sprite':
			return 'spr, sprite';
			break;
		case 'application/x-wais-source':
			return 'src, wsrc';
			break;
		case 'application/streamingmedia':
			return 'ssm';
			break;
		case 'application/vnd.ms-pki.certstore':
			return 'sst';
			break;
		case 'application/step':
			return 'step, stp';
			break;
		case 'application/sla':
		case 'application/vnd.ms-pki.stl':
		case 'application/x-navistyle':
			return 'stl';
			break;
		case 'application/x-sv4cpio':
			return 'sv4cpio';
			break;
		case 'application/x-sv4crc':
			return 'sv4crc';
			break;
		case 'application/x-world':
			return 'svr, wrl';
			break;
		case 'x-world/x-svr':
			return 'svr';
			break;
		case 'application/x-shockwave-flash':
			return 'swf';
			break;
		case 'application/x-tar':
			return 'tar';
			break;
		case 'application/toolbook':
			return 'tbk';
			break;
		case 'application/x-tcl':
		case 'text/x-script.tcl':
			return 'tcl';
			break;
		case 'text/x-script.tcsh':
			return 'tcsh';
			break;
		case 'application/x-tex':
			return 'tex';
			break;
		case 'application/x-texinfo':
			return 'texi, texinfo';
			break;
		case 'application/plain':
			return 'text';
			break;
		case 'application/gnutar':
			return 'tgz';
			break;
		case 'image/tiff':
		case 'image/x-tiff':
			return 'tif, tiff';
			break;
		case 'audio/tsp-audio':
			return 'tsi';
			break;
		case 'application/dsptype':
		case 'audio/tsplayer':
			return 'tsp';
			break;
		case 'text/tab-separated-values':
			return 'tsv';
			break;
		case 'text/x-uil':
			return 'uil';
			break;
		case 'text/uri-list':
			return 'uni, unis, uri, uris';
			break;
		case 'application/i-deas':
			return 'unv';
			break;
		case 'application/x-ustar':
		case 'multipart/x-ustar':
			return 'ustar';
			break;
		case 'text/x-uuencode':
			return 'uu, uue';
			break;
		case 'application/x-cdlink':
			return 'vcd';
			break;
		case 'text/x-vcalendar':
			return 'vcs';
			break;
		case 'application/vda':
			return 'vda';
			break;
		case 'video/vdo':
			return 'vdo';
			break;
		case 'application/groupwise':
			return 'vew';
			break;
		case 'video/vivo':
		case 'video/vnd.vivo':
			return 'viv, vivo';
			break;
		case 'application/vocaltec-media-desc':
			return 'vmd';
			break;
		case 'application/vocaltec-media-file':
			return 'vmf';
			break;
		case 'audio/voc':
		case 'audio/x-voc':
			return 'voc';
			break;
		case 'video/vosaic':
			return 'vos';
			break;
		case 'audio/voxware':
			return 'vox';
			break;
		case 'audio/x-twinvq-plugin':
			return 'vqe, vql';
			break;
		case 'audio/x-twinvq':
			return 'vqf';
			break;
		case 'application/x-vrml':
			return 'vrml';
			break;
		case 'model/vrml':
		case 'x-world/x-vrml':
			return 'vrml, wrl, wrz';
			break;
		case 'x-world/x-vrt':
			return 'vrt';
			break;
		case 'application/x-visio':
			return 'vsd, vst, vsw';
			break;
		case 'application/wordperfect6.0':
			return 'w60, wp5';
			break;
		case 'application/wordperfect6.1':
			return 'w61';
			break;
		case 'audio/wav':
		case 'audio/x-wav':
			return 'wav';
			break;
		case 'application/x-qpro':
			return 'wb1';
			break;
		case 'image/vnd.wap.wbmp':
			return 'wbmp';
			break;
		case 'application/vnd.xara':
			return 'web';
			break;
		case 'application/x-123':
			return 'wk1';
			break;
		case 'windows/metafile':
			return 'wmf';
			break;
		case 'text/vnd.wap.wml':
			return 'wml';
			break;
		case 'application/vnd.wap.wmlc':
			return 'wmlc';
			break;
		case 'text/vnd.wap.wmlscript':
			return 'wmls';
			break;
		case 'application/vnd.wap.wmlscriptc':
			return 'wmlsc';
			break;
		case 'application/wordperfect':
			return 'wp, wp5, wp6, wpd';
			break;
		case 'application/x-wpwin':
			return 'wpd';
			break;
		case 'application/x-lotus':
			return 'wq1';
			break;
		case 'application/mswrite':
		case 'application/x-wri':
			return 'wri';
			break;
		case 'text/scriplet':
			return 'wsc';
			break;
		case 'application/x-wintalk':
			return 'wtk';
			break;
		case 'image/x-xbitmap':
		case 'image/x-xbm':
		case 'image/xbm':
			return 'xbm';
			break;
		case 'video/x-amt-demorun':
			return 'xdr';
			break;
		case 'xgl/drawing':
			return 'xgz';
			break;
		case 'image/vnd.xiff':
			return 'xif';
			break;
		case 'application/excel':
			return 'xl, xla, xlb, xlc, xld, xlk, xll, xlm, xls, xlt, xlv, xlw';
			break;
		case 'application/x-excel':
			return 'xla, xlb, xlc, xld, xlk, xll, xlm, xls, xlt, xlv, xlw';
			break;
		case 'application/x-msexcel':
			return 'xla, xls, xlw';
			break;
		case 'application/vnd.ms-excel':
			return 'xlb, xlc, xll, xlm, xls, xlw';
			break;
		case 'audio/xm':
			return 'xm';
			break;
		case 'application/xml':
		case 'text/xml':
			return 'xml';
			break;
		case 'xgl/movie':
			return 'xmz';
			break;
		case 'application/x-vnd.ls-xpix':
			return 'xpix';
			break;
		case 'image/xpm':
			return 'xpm';
			break;
		case 'video/x-amt-showrun':
			return 'xsr';
			break;
		case 'image/x-xwd':
		case 'image/x-xwindowdump':
			return 'xwd';
			break;
		case 'application/x-compress':
			return 'z';
			break;
		case 'application/x-zip-compressed':
		case 'application/zip':
		case 'multipart/x-zip':
			return 'zip';
			break;
		case 'text/x-script.zsh':
			return 'zsh';
			break;
		default:
			return '';
			break;
	}
}

function getFilename($filename, $file_id, $path, $new = false, $file_hash = '')
{
	global $smcFunc;

	// Just make up a nice hash...
	if ($new)
		return sha1(md5($filename . time()) . mt_rand());

	// Grab the file hash if it wasn't added.
	if ($file_hash === '')
	{
		$request = $smcFunc['db_query']('', '
			SELECT file_hash
			FROM {db_prefix}dp_module_files
			WHERE id_file = {int:id_file}',
			array(
				'id_file' => (int) $file_id,
		));

		if ($smcFunc['db_num_rows']($request) === 0)
			return false;

		list ($file_hash) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	return $path . '/' . $file_id . '_' . $file_hash;
}

function GetDPModuleInfo($scripts, $mod_functions, $headers, $dirname, $file, $name = '')
{
	global $boarddir, $context, $modSettings, $txt;

	// Are we allowed to use this name?
	if (in_array($file, $context['dp_restricted_names']['modules'])) return false;

	// Optional check: does it exist? (Mainly for installation)
	if (!empty($name) && $name != $file) return false;

	// If the required info file does not exist let's silently die...
	if (!file_exists($dirname . '/' . $file . '/info.xml')) return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($dirname . '/' . $file . '/info.xml');

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$module_info1 = new xmlArray($file_info);
	$module_info1 = $module_info1->path('module[0]');

	$container = true;

	if (!$module_info1->exists('name')) return false;
	if ($module_info1->exists('@container'))
		if ($module_info1->fetch('@container') == 'empty')
			$container = false;

	if ($module_info1->exists('target'))
	{
		switch ($module_info1->fetch('target'))
		{
			case '_self':
				$target = 1;
				break;
			case '_parent':
				$target = 2;
				break;
			case '_top':
				$target = 3;
				break;
			case '_blank':
				$target = 0;
				break;
			default:
				$target = 0;
				break;
		}
	}
	else
		$target = 0;

	// Only calling this when it is needed, mainly when we install the module!
	if (empty($scripts) && empty($mod_functions))
	{
		$other_functions = array();
		$php_files = array();
		$header_files = array();
		$all_functions = array();
		$main_function = array();

		// Getting all functions and files.
		if ($module_info1->exists('file'))
		{
			$filetag = $module_info1->set('file');

			foreach ($filetag as $modfiles => $filepath)
			{
				// The path attribute is Mandatory for the <file> tag!
				if ($filepath->exists('@path'))
					$currPath = $filepath->fetch('@path');
				else
					return false;

				if ($filepath->exists('function'))
				{
					// Store the Path of the file!
					$php_files[] = $currPath;

					$functag = $filepath->set('function');

					foreach($functag as $func => $function)
					{
						if ($function->exists('main'))
							$main_function[] = $function->fetch('main');
						else
							$other_functions[] = $function->fetch('');
					}
				}
				else
					if ($filepath->exists('@type') && $filepath->fetch('@type') == 'header')
						$header_files[] = strpos($currPath, '/') !== false ? substr($currPath, strrpos($currPath, '/') + 1) : $currPath;
			}

			$all_functions = array_merge($main_function, $other_functions);
		}
	}

	// And now for the parameters. Remember, they are optional!
	$param_array = array();
	if ($module_info1->exists('param'))
	{
		$params = $module_info1->set('param');
		foreach ($params as $name => $param)
		{
			if ($param->exists('@name') && $param->exists('@type'))
			{
				$param_array[$param->fetch('@name')] = array(
					'type' => $param->fetch('@type'),
					'value' => $param->fetch('.'),
				);

				if ($param->exists('@extend'))
					$param_array[$param->fetch('@name')] += array('extend' => $param->fetch('@extend'));

				if ($param->exists('@fieldset'))
					$param_array[$param->fetch('@name')] += array('fieldset' => $param->fetch('@fieldset'));
			}
		}
	}

	// When viewing our modules in the Add Module section, we need the name of the module, not the title of it!
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'dpaddmodules')
		$mod_title = isset($txt['dpmod_' . $module_info1->fetch('name')]) ? $txt['dpmod_' . $module_info1->fetch('name')] : '';
	else
		$mod_title = isset($txt['dpmodtitle_' . $module_info1->fetch('name')]) ? $txt['dpmodtitle_' . $module_info1->fetch('name')] : (isset($txt['dpmod_' . $module_info1->fetch('name')]) ? $txt['dpmod_' . $module_info1->fetch('name')] : '');

	// Grabbing it from the database here.
	if (!empty($scripts) && !empty($mod_functions))
	{
		return array(
			'title' => $mod_title,
			'files' => $scripts,
			'header_files' => $headers,
			'target' => $target,
			'icon' => ($module_info1->exists('icon') ? $module_info1->fetch('icon') : ''),
			'title_link' => ($module_info1->exists('url') ? $module_info1->fetch('url') : ''),
			'functions' => $mod_functions,
			'params' => $param_array,
			'container' => $container ? 1 : 0,
		);
	}
	else
	{
		return array(
			'title' => $mod_title,
			'description' => isset($txt['dpmoddesc_' . $module_info1->fetch('name')]) ? parse_bbc($txt['dpmoddesc_' . $module_info1->fetch('name')]) : (isset($txt['dpmodinfo_' . $module_info1->fetch('name')]) ? $txt['dpmodinfo_' . $module_info1->fetch('name')] : ''),
			'icon_link' => ($module_info1->exists('icon') ? $boarddir . '/' . $modSettings['dp_icon_directory'] . '/' . $module_info1->fetch('icon') : ''),
			'icon' => ($module_info1->exists('icon') ? $module_info1->fetch('icon') : ''),
			'target' => $target,
			'files' => count($php_files) == 1 ? $php_files[0] : implode('+', $php_files),
			'header_files' => count($header_files) == 1 ? $header_files[0] : implode('+', $header_files),
			'functions' => implode('+', $all_functions),
			'title_link' => ($module_info1->exists('url') ? $module_info1->fetch('url') : ''),
			'version' => ($module_info1->exists('version') ? $module_info1->fetch('version') : ''),
			'params' => $param_array,
			'container' => $container ? 1 : 0,
		);
	}
}

function GetDPTemplateInfo($script, $function, $dirname, $file, $name = '')
{
	global $context, $txt;

	// Are we allowed to use this name?
	if (in_array($file, $context['dp_restricted_names']['templates'])) return false;

	// Optional check: does it exist? (Mainly for installation)
	if (!empty($name) && $name != $file) return false;

	// If the required info file does not exist let's silently die...
	if (!file_exists($dirname . '/' . $file . '/info.xml')) return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($dirname . '/' . $file . '/info.xml');

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$template_info = new xmlArray($file_info);
	$template_info = $template_info->path('template[0]');

	// Templates have a lot of mandatory tags.
	if (!$template_info->exists('name')) return false;
	if (!$template_info->exists('file')) return false;
	if (!$template_info->exists('function')) return false;
	if (!$template_info->exists('version')) return false;

	// When viewing our templates in the Add Templates section, we need the name of the template, not the title of it!
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'dpaddtemplates')
		$temp_title = isset($txt['dptemp_' . $template_info->fetch('name')]) ? $txt['dptemp_' . $template_info->fetch('name')] : '';
	else
		$temp_title = isset($txt['dptemptitle_' . $template_info->fetch('name')]) ? $txt['dptemptitle_' . $template_info->fetch('name')] : (isset($txt['dptemp_' . $template_info->fetch('name')]) ? $txt['dptemp_' . $template_info->fetch('name')] : '');	

	// Grabbing it from the database here.
	if (!empty($script) && !empty($function))
	{
		return array(
			'title' => $temp_title,
			'file' => $script,
			'function' => $function,
		);
	}
	else
	{
		return array(
			'title' => $temp_title,
			'description' => isset($txt['dptempdesc_' . $template_info->fetch('name')]) ? parse_bbc($txt['dptempdesc_' . $template_info->fetch('name')]) : (isset($txt['dptempinfo_' . $template_info->fetch('name')]) ? $txt['dptempinfo_' . $template_info->fetch('name')] : ''),
			'file' => $template_info->fetch('file'),
			'function' => $template_info->fetch('function'),
			'version' => $template_info->fetch('version'),
		);
	}
}

function GetDPAddedExtensions($extendVars, $start = 0, $limit = 0, $byname = '')
{
	global $context, $scripturl, $smcFunc;

	// We want to define our variables now...
	$added_extensions = array();

	// Let's loop through each folder and get their data. If anything goes wrong we shall skip it.
	if (is_dir($extendVars['dir']))
	{
		$dir = @opendir($extendVars['dir']);
		{
			while ($file = readdir($dir))
			{
				if ($extendVars['type'] == 'modules')
					$retVal = GetDPModuleInfo('', '', '', $extendVars['dir'], $file);
				else
					$retVal = GetDPTemplateInfo('', '', $extendVars['dir'], $file);

				if ($retVal === false)
						continue;
				else
				{
						$added_extensions[] = $file;
						$return[$file] = $retVal;
				}
			}
		}
	}

	if (isset($return))
	{
		// Sort it by title.
		uasort($return, create_function('$a,$b','return strnatcmp($a[\'title\'], $b[\'title\']);'));

		// We are uploading the module/template, grab the new start for this and return it!
		if (!empty($limit) && empty($start) && $byname != '')
		{
			$i = array_search($byname, array_keys($return));

			if (floor($i/$limit) <= 0)
				$start = 0;
			else
				$start = (floor($i/$limit) * $limit);

			return $start;
		}

		// Find out if any of these are installed.
		$request = $smcFunc['db_query']('', '
			SELECT ' . implode(', ', $extendVars['query']['select']) . '
			FROM {db_prefix}' . $extendVars['query']['table'] . '
			WHERE name IN ({array_string:extend_names})',
			array(
				'extend_names' => $added_extensions,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$return[$row[$extendVars['query']['select']['name']]] += array(
				'uninstall_href' => $scripturl . '?action=admin;area=dpextend;sa=' . $extendVars['sa']['uninstall'] . ';name=' . $row[$extendVars['query']['select']['name']] . (!empty($limit) ? ';start=' . (!empty($start) ? $start : '0') : '') . ';' . $context['session_var'] . '=' . $context['session_id'],
			);

			if ($extendVars['has_settings'])
				$return[$row[$extendVars['query']['select']['name']]] += array(
					'settings_href' => $scripturl . '?action=admin;' . $extendVars['settings_href'] . $row[$extendVars['query']['select']['id']] . ';' . $context['session_var'] . '=' . $context['session_id'],
				);
		}

		$tcount = count($return) - 1;
		
		// Loop through the array, adding install/delete links as needed...
		foreach(array_keys($return) as $extend)
		{
			// Add Delete link to all of these
			$return[$extend] += array(
				'delete_href' => $scripturl . '?action=admin;area=dpextend;sa=' . $extendVars['sa']['delete'] . ';name=' . $extend . (!empty($limit) ? ';start=' . (!empty($start) && count($return) > $start ? ($tcount == $start ? $start - $limit : $start) : '0') : '') . ';' . $context['session_var'] . '=' . $context['session_id'],
			);
			if (!isset($return[$extend]['uninstall_href']))
				$return[$extend] += array(
					'install_href' => $scripturl . '?action=admin;area=dpextend;sa=' . $extendVars['sa']['install'] . ';name=' . $extend . (!empty($limit) ? ';start=' . (!empty($start) ? $start : '0') : '') . ';' . $context['session_var'] . '=' . $context['session_id'],
				);
		}

		$context['dpextend_total_' . $extendVars['type']] = count($return);

		if (!empty($limit) && count($return) > $limit)
			$return = array_slice($return, $start, $limit, true);

		return $return;
	}
	else
		return array();
}

function GetDPInstalledModules($installed_mods = array())
{
	global $smcFunc, $context;

	// We'll need to build up a list of modules that are installed.
	if (count($installed_mods) < 1)
	{
		$installed_mods = array();
		// Let's collect all installed modules...
		$request = $smcFunc['db_query']('', '
			SELECT name, files, functions, header_files
			FROM {db_prefix}dp_modules
			WHERE files != {string:empty_string} AND functions != {string:empty_string}',
			array(
				'empty_string' => '',
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_mods[] = array(
				'name' => $row['name'],
				'files' => $row['files'],
				'header_files' => $row['header_files'],
				'functions' => $row['functions'],
			);
		}

		$smcFunc['db_free_result']($request);
	}

	foreach ($installed_mods as $installed)
	{
		$retVal = GetDPModuleInfo($installed['files'], $installed['functions'], $installed['header_files'], $context['dpmod_modules_dir'], $installed['name'], $installed['name']);
		if ($retVal === false)
			continue;

		$module_info[$installed['name']] = $retVal;
	}

	return isset($module_info) ? $module_info : array();
}

function dp_insert_column()
{
	global $smcFunc, $context;

	if (!allowedTo(array('admin_forum', 'manage_dplayouts', 'admin_dplayouts')))
		return;

	if (!isset($_GET['insert'], $_GET['layout']))
		return;

	$sdata = explode('_', $_GET['insert']);

	$columns = array(
		'id_layout' => 'int',
		'column' => 'string',
		'row' => 'string',
		'enabled' => 'int',
	);

	$data = array(
		$_GET['layout'],
		$sdata[1] . ':0',
		$sdata[0] . ':0',
		-2,
	);

	$keys = array(
		'id_layout_position',
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}dp_layout_positions',  $columns, $data, $keys);

	$iid = $smcFunc['db_insert_id']('{db_prefix}dp_layout_positions', 'id_layout_position');

	loadTemplate('Xml');
	$context['sub_template'] = 'generic_xml';
	$xml_data = array(
		'items' => array(
			'identifier' => 'item',
			'children' => array(
				array(
					'attributes' => array(
						'insertid' => $iid,
					),
					'value' => $_GET['insert'] . '_' . $iid,
				),
			),
		),
	);
	$context['xml_data'] = $xml_data;
}

function dp_edit_db_select()
{
	global $smcFunc, $context;

	if (!allowedTo(array('admin_forum', 'manage_dplayouts', 'admin_dplayouts')))
		return;
		
	if (!isset($_POST['config_id']))
		return;

	// Make sure we have a valid parameter ID of the right type.
	$request = $smcFunc['db_query']('', '
		SELECT
			dmp.value
		FROM {db_prefix}dp_module_parameters AS dmp
		WHERE dmp.id_param = {int:config_id} AND dmp.type = {string:type}',
		array(
			'config_id' => $_POST['config_id'],
			'type' => 'db_select',
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);

	$db_options = explode(':', $row['value']);
	$db_select_options = explode(';', $row['value']);
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

	if (isset($_POST['data']))
	{
		if (!isset($_POST['key']))
			return;

		$key = explode('_', $_POST['key']);

		$smcFunc['db_query']('', '
			UPDATE ' . $db_select['table'] . '
			SET {raw:query_select} = {string:data}
			WHERE {raw:key_select} = {string:key}',
			array(
				'data' => $_POST['data'],
				'key' => $key[count($key) - 1],
				'query_select' =>  $db_select['select1'],
				'key_select' =>  $db_select['select2'],
			)
		);

		die();
	}
	else
	{
		if (!isset($_GET['insert']))
			return;

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

		$iid = $smcFunc['db_insert_id']('{db_prefix}dp_layout_positions', 'id_layout_position');

		loadTemplate('Xml');
		$context['sub_template'] = 'generic_xml';
		$xml_data = array(
			'items' => array(
				'identifier' => 'item',
				'children' => array(
					array(
						'value' => $_GET['insert'] . '_' . $iid,
					),
				),
			),
		);
		$context['xml_data'] = $xml_data;
	}
}

function loadLayout($curr_action, $dp_layout_action)
{
	global $smcFunc, $context, $scripturl, $txt, $user_info, $boardurl, $modSettings;

	// Set the layout to false by default...
	$context['has_dp_layout'] = false;

	// So, we'll need to take some "action" here! ;)
	$request = $smcFunc['db_query']('', '
		SELECT
			da.id_action, da.action
		FROM {db_prefix}dp_actions AS da
			LEFT JOIN {db_prefix}dp_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
		WHERE da.id_group = dg.id_group AND da.action LIKE {string:current_action}',
		array(
			'current_action' => $curr_action . '%',
			'one' => 1,
			'zero' => 0,
		)
	);

	$num2 = $smcFunc['db_num_rows']($request);
	if (empty($num2))
		return false;

	$dp_actions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$dp_actions[$row['id_action']] = $row['action'];

	$smcFunc['db_free_result']($request);

	// We must not have any layouts associated with this page, get outta here!
	if (empty($dp_actions))
		return false;

	// Checking for topics first (e.g. [topic]=1, [topic]=2, [topic]=5.8 returns [topic]=5)
	if (preg_match('/\[topic\]=(?P<topic>\d+)/', $dp_layout_action, $matches))
	{
		$topic = '[topic]=' . (int) $matches['topic'];
		if (in_array($topic, $dp_actions))
			$match = $topic;
	}

	if (!isset($match))
	{
		// Situations where [topic][board]=2, when we include the ENTIRE BOARD!
		$dp_temp_action = preg_replace('/(?<=\])\=[^\[\Z]*(?=\[)/', '', $dp_layout_action);

		// Set the break action/non-action if it exists.
		if (in_array($dp_temp_action, $dp_actions))
			$dp_layout_action = $dp_temp_action;

		$result_arr = array_intersect(array($dp_layout_action, $curr_action), $dp_actions);

		if (!empty($result_arr))
		{
			// This approach should work exactly the same as the commented approach below it, should test for performance (speed) though to see
			// which 1 is quicker!
			if (in_array($dp_layout_action, $result_arr))
				$match = $dp_layout_action;
			else
				if (in_array($curr_action, $result_arr))
					$match = $curr_action;

			/*
			foreach($result_arr as $l_value)
			{
				if ($l_value == $curr_action)
					$match = $l_value;
				elseif ($l_value == $dp_layout_action)
				{
					$match = $l_value;
					break;
				}
			}
			*/
		}
		else
			return false;
	}

	if (empty($match))
		return false;

	// Let's grab the data necessary to show the correct layout!
	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module AS id_mod, dm.name AS mod_name, dm.title AS mod_title, dm.txt_var AS txt_title, dlp.column, dlp.row,
			dm.title_link AS mod_title_link, dm.target AS mod_target, dm.icon AS mod_icon, dm.minheight AS mod_minheight, dm.minheight_type AS mod_minheight_type, dm.files AS mod_files, dm.header_files AS mod_header_files, dm.functions AS mod_functions, dm.header_display AS mod_header_display, dm.id_template AS mod_id_template, dm.groups AS mod_groups, dm.container AS mod_container,
			dmp.position, dmp.empty, dlp.enabled, da.action, dmp.id_position, dlp.id_layout_position, dmc.id_clone, dmc.container AS clone_container,
			dmc.title_link AS clone_title_link, dmc.target AS clone_target, dmc.icon AS clone_icon, dmc.minheight AS clone_minheight, dmc.minheight_type AS clone_minheight_type, dmc.files AS clone_files, dmc.header_files AS clone_header_files, dmc.functions AS clone_functions, dmc.header_display AS clone_header_display, dmc.id_template AS clone_id_template, dmc.groups AS clone_groups,
			dmc.name AS clone_name, dmc.title AS clone_title, dmc.is_clone,
			dt.name AS template_name, dt.file AS template_file, dt.function AS template_function,
			dmp2.id_param, dmp2.name AS pName, dmp2.type, dmp2.value, dmp2.txt_var AS txt_value
			FROM {db_prefix}dp_groups AS dg, {db_prefix}dp_layouts AS dl
				INNER JOIN {db_prefix}dp_actions AS da ON (da.id_group = dl.id_group AND da.id_layout = dl.id_layout AND da.action = {string:current_action})
				JOIN {db_prefix}dp_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout AND dlp.enabled NOT IN({array_int:invisible_layouts}))
				JOIN {db_prefix}dp_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position)
				LEFT JOIN {db_prefix}dp_module_clones AS dmc ON (dmc.id_member = {int:zero} AND dmc.id_clone = dmp.id_clone)
				LEFT JOIN {db_prefix}dp_modules AS dm ON (dm.id_module = dmp.id_module)
				LEFT JOIN {db_prefix}dp_templates AS dt ON (dt.type = {int:zero} AND (dt.id_template = dm.id_template || dt.id_template = dmc.id_template))
				LEFT JOIN {db_prefix}dp_module_parameters AS dmp2 ON ((dmp2.id_module = dm.id_module AND dmp2.id_clone = {int:zero}) || dmp2.id_clone = dmc.id_clone)
			WHERE
				dg.id_member = {int:zero} AND dg.active = {int:one} AND dl.id_group = dg.id_group',
		array(
			'one' => 1,
			'zero' => 0,
			'invisible_layouts' => array(-1, -2),
			'current_action' => $match,
		)
	);

	$num = $smcFunc['db_num_rows']($request);
	if (empty($num))
		return false;

	$old_row = 0;
	$view_groups = array();
	$context['dp_module_headers'] = array();
	$header_files = array();
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$type = !empty($row['id_clone']) ? 'clone' : 'mod';
		$is_clone = !empty($row['is_clone']) && !empty($row['id_clone']);

		$smf = (int) $row['id_clone'] + (int) $row['id_mod'];
		$smf_col = empty($smf) && !is_null($row['id_position']);

		if (!$smf_col && $row['enabled'] == 0)
			continue;

		// Who can view it?
		$view_groups = isset($row[$type.'_groups']) && $row[$type.'_groups'] != '' ? explode(',', $row[$type.'_groups']) : array();

		// -3 is for everybody...
		if (in_array('-3', $view_groups))
			$view_groups = $user_info['groups'];

		// Match the current group(s) with the parameter to determine if the user may access this, Admins can select not to view Modules also here.
		$view_groups = array_intersect($user_info['groups'], $view_groups);

		// Shucks, you can't view it
		if (!$view_groups && !$smf_col)
			continue;

		$current_row = explode(':', $row['row']);
		$current_column = explode(':', $row['column']);

		// Load up Empty Modules here!
		if (!empty($row['empty']))
		{
			if (!empty($row[$type.'_name']))
			{
				if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
				{
					$mod_title = !empty($row['txt_title']) && $type == 'mod' && !empty($row['mod_title']) && isset($txt[$row['mod_title']]) ? $txt[$row['mod_title']] : (trim($row[$type.'_title']) == '' ? $txt['dpmod_' . $row[$type.'_name']] : $row[$type.'_title']);

					if(!empty($modSettings['dp_module_title_char_limit']))
						if($smcFunc['strlen']($mod_title) >= (int) $modSettings['dp_module_title_char_limit'])
							$mod_title = $smcFunc['substr']($mod_title, 0, $modSettings['dp_module_title_char_limit'] - 3) . '...';

					// Storing $context variables for empty modules, so that they can use these variables within their module functions.
					if (!isset($context['empty_modules'][$row['id_position']]))
					{
						if (empty($context['dp_mod_' . $row[$type . '_name']]))
							$context['dp_mod_' . $row[$type . '_name']] = array();

						$context['dp_mod_' . $row[$type . '_name']]['unique_id'][] = $row[$type . '_name'] . '_' . $type . '_' . $row['id_' . $type];

						/*
							Empty Modules are somewhat lacking for the title, icon, title link, target for title link, modules template and modules header.
							So we'll provide a means to grab this information from within the module's code itself, and let the module author decide to use it or not!
						*/
						$http = strpos(strtolower($row[$type.'_title_link']), 'http://') === 0 ? true : (strpos(strtolower($row[$type.'_title_link']), 'www.') === 0 ? true : false);
						$title_href = $http ? $row[$type.'_title_link'] : $scripturl . '?' . $row[$type.'_title_link'];

						$context['dp_mod_' . $row[$type . '_name']][$row[$type . '_name'] . '_' . $type . '_' . $row['id_' . $type]] = array(
							'title' => $mod_title,
							'title_href' => $title_href,
							'title_target' => $row[$type.'_target'],
							'title_link' => '<a href="' . $title_href . '" target="' . $row[$type.'_target'] . '" onfocus="if(this.blur)this.blur();">' . $mod_title . '</a>',
							'icon' => !empty($row[$type.'_icon']) ? $context['dpmod_icon_url'] . $row[$type.'_icon'] : '',
							'header_display' => $row[$type.'_header_display'],
							'groups' => !empty($row[$type.'_groups']) ? explode(',', $row[$type.'_groups']) : array(),
						);

						if (is_dir($context['dpmod_modules_dir'] . '/' . $row[$type . '_name']))
						{
							// Store the modules url path for accessing other files ("modulesurl")
							if (!isset($context['dp_mod_' . $row[$type . '_name']]['modulesurl']))
								$context['dp_mod_' . $row[$type . '_name']]['modulesurl'] = $boardurl . '/dreamportal/modules/' . $row[$type . '_name'];

							// Store the modules dir path ("modulesdir")
							if (!isset($context['dp_mod_' . $row[$type . '_name']]['modulesdir']))
								$context['dp_mod_' . $row[$type . '_name']]['modulesdir'] = $context['dpmod_modules_dir'] . '/' . $row[$type . '_name'];
						}

						// Storing the image url of the module/clone, based on the name, so that images can be accessed easily. ("imagesurl")
						if (!isset($context['dp_mod_' . $row[$type . '_name']]['imagesurl']) && is_dir($context['dpmod_image_dir'] . $row[$type . '_name']))
							$context['dp_mod_' . $row[$type . '_name']]['imagesurl'] = $context['dpmod_image_url'] . $row[$type . '_name'];
							
						// The headers to be added, if any are defined!
						if (!empty($row[$type . '_header_files']))
						{
							if (!isset($context['dp_module_headers'][$row[$type . '_name']]))
								$context['dp_module_headers'][$row[$type . '_name']] = array();

							// Determine the type of header we have here.
							$module_headers = explode('+', $row[$type . '_header_files']);
							foreach (array_keys($module_headers) as $header)
							{
								$head_ext = trim(strtolower(substr(strrchr(basename($module_headers[$header]), '.'), 1)));
								$head_file = $head_ext == 'css' ? substr($module_headers[$header], 0, strlen($module_headers[$header]) - (strlen($head_ext) + 1)) : $module_headers[$header];

								// Make sure we only have the path defined 1 time in here (for clones)!
								if (!in_array($row[$type . '_name'] . '/' . $head_file, $header_files))
									$context['dp_module_headers'][$row[$type . '_name']][$head_ext][] = 'dreamportal/modules/' . $row[$type . '_name'] . '/' . $head_file;

								// Add the file to the array to be checked!
								$header_files[] = $row[$type . '_name'] . '/' . $head_file;
							}
						}
					}

					$context['empty_modules'][$row['id_position']] = array(
						'files' => $row[$type.'_files'],
						'type' => $type,
						'name' => $row[$type.'_name'],
						'id_position' => $row['id_position'],
						'is_clone' => $is_clone,
						'functions' => $row[$type.'_functions'],
					);

					$params[$row['id_position']][] = array(
						'id' => $row['id_' . $type],
						'file_input' => (strtolower($row['type']) == 'file_input' ? array('id_param' => $row['id_param'], 'id' => $row['id_' . $type], 'is_clone' => !empty($row['id_clone'])) : array()),
						'name' => $row['pName'],
						'type' => $row['type'],
						'value' => !empty($row['txt_value']) && isset($txt[$row['value']]) && $type == 'mod' ? $txt[$row['value']] : $row['value'],
					);
				}
				$context['empty_modules'][$row['id_position']]['params'] = $params[$row['id_position']];
			}
			continue;
		}

		// Load up non-empty modules now.
		if (!isset($dp_modules[$current_row[0]][$current_column[0]]) && !empty($row['id_layout_position']))
		{
			$dp_modules[$current_row[0]][$current_column[0]] = array(
				'is_smf' => $smf_col,
				'id_layout_position' => $row['id_layout_position'],
				'html' => ($current_column[1] >= 2 ? ' colspan="' . $current_column[1] . '"' : '') . ($current_row[1] >= 2 ? ' rowspan="' . $current_row[1] . '"' : '') . ($current_column[1] <= 1 && $context['dp_home'] && $current_column[0] != 1 || !$context['dp_home'] && $current_column[1] <= 1 && !$smf_col ? ' style="width: 200px; min-width: 200px; max-width: 200px;"' : ' style="width: 100%;"'),
				'enabled' => $row['enabled'],
				'disabled_module_container' => $row['enabled'] == -1,
			);
		}

		if (!empty($row[$type.'_name']))
		{
			if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
			{
				// Store $context variables for each module.  Mod Authors can use these within their module functions: unique ID values, access the images url, etc.
				if (!isset($dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]))
				{
					if (empty($context['dp_mod_' . $row[$type . '_name']]))
						$context['dp_mod_' . $row[$type . '_name']] = array();

					$context['dp_mod_' . $row[$type . '_name']]['unique_id'][] = $row[$type . '_name'] . '_' . $type . '_' . $row['id_' . $type];

					if (is_dir($context['dpmod_modules_dir'] . '/' . $row[$type . '_name']))
					{
						// Store the modules url path for accessing other files ("modulesurl")
						if (!isset($context['dp_mod_' . $row[$type . '_name']]['modulesurl']))
							$context['dp_mod_' . $row[$type . '_name']]['modulesurl'] = $boardurl . '/dreamportal/modules/' . $row[$type . '_name'];

						// Store the modules dir path ("modulesdir")
						if (!isset($context['dp_mod_' . $row[$type . '_name']]['modulesdir']))
							$context['dp_mod_' . $row[$type . '_name']]['modulesdir'] = $context['dpmod_modules_dir'] . '/' . $row[$type . '_name'];
					}

					// Storing the image url of the module/clone, based on the name, so that images can be accessed easily. ("imagesurl")
					if (!isset($context['dp_mod_' . $row[$type . '_name']]['imagesurl']) && is_dir($context['dpmod_image_dir'] . $row[$type . '_name']))
						$context['dp_mod_' . $row[$type . '_name']]['imagesurl'] = $context['dpmod_image_url'] . $row[$type . '_name'];

					// The headers to be added, if any are defined!
					if (!empty($row[$type . '_header_files']))
					{
						if (!isset($context['dp_module_headers'][$row[$type . '_name']]))
							$context['dp_module_headers'][$row[$type . '_name']] = array();

						// Determine the type of header we have here.
						$module_headers = explode('+', $row[$type . '_header_files']);
						foreach (array_keys($module_headers) as $header)
						{
							$head_ext = trim(strtolower(substr(strrchr(basename($module_headers[$header]), '.'), 1)));
							$head_file = $head_ext == 'css' ? substr($module_headers[$header], 0, strlen($module_headers[$header]) - (strlen($head_ext) + 1)) : $module_headers[$header];

							// Make sure we only have the path defined 1 time in here (for clones)!
							if (!in_array($row[$type . '_name'] . '/' . $head_file, $header_files))
								$context['dp_module_headers'][$row[$type . '_name']][$head_ext][] = 'dreamportal/modules/' . $row[$type . '_name'] . '/' . $head_file;

							// Add the file to the array to be checked!
							$header_files[] = $row[$type . '_name'] . '/' . $head_file;
						}
					}
				}

				$mod_title = !empty($row['txt_title']) && $type == 'mod' && !empty($row['mod_title']) && isset($txt[$row['mod_title']]) ? $txt[$row['mod_title']] : (trim($row[$type.'_title']) == '' ? $txt['dpmod_' . $row[$type.'_name']] : $row[$type.'_title']);

				if(!empty($modSettings['dp_module_title_char_limit']))
					if($smcFunc['strlen']($mod_title) >= (int) $modSettings['dp_module_title_char_limit'])
						$mod_title = $smcFunc['substr']($mod_title, 0, $modSettings['dp_module_title_char_limit'] - 3) . '...';

				$dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']] = array(
					'is_smf' => empty($smf),														// Returns true or false; Is this the mighty SMF that we should bow down to? :P
					'is_clone' => $is_clone,														// Returns true or false; determines if it really is a clone or not.
					'modify_link' => allowedTo(array('admin_dplayouts', 'manage_dplayouts')) ? '<a href="' . $scripturl . '?action=admin;area=dplayouts;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_mod']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['modify'] . '</a>' : '',
					'type' => $type,																// Returns either 'mod' or 'clone'.
					'id' => $row['id_position'],													// The unique position ID of the clone/module.
					'id_position' => $row['id_position'],											// The unique position ID of the clone/module.
					'name' => $row[$type.'_name'],													// Name of clone or module.
					'title' => $mod_title,															// Title of clone/module on titlebar.
					'title_link' => $row[$type.'_title_link'],										// Link associated with the title.
					'minheight' => array(															// The minimum height value and unit of measurement for the module.
						'value' => !empty($row[$type.'_minheight']) ? (int) $row[$type.'_minheight'] : 0,
						'unit' => !empty($row[$type.'_minheight_type']) ? (int) $row[$type.'_minheight_type'] : 0,
					),
					'target' => $row[$type.'_target'],												// Target of clone/module (int value).
					'icon' => $row[$type.'_icon'],													// Icon associated with the module/clone.
					'files' => $row[$type.'_files'],												// File, if any, for the function of that module/clone.
					'functions' => $row[$type.'_functions'],										// Any functions for that module/clone.
					'header_display' => $row[$type.'_header_display'],								// Shall we show the title?
					'template' => array(															// Which template to use?
						'id' => !empty($row[$type.'_id_template']) ? (int) $row[$type.'_id_template'] : 0,
						'function' => isset($row['template_function']) ? $row['template_function'] : '',
						'filepath' => isset($row['template_name'], $row['template_file']) ? $context['dpmod_template_dir'] . '/' . $row['template_name'] . '/' . $row['template_file'] : '',
					),
					'groups' => !empty($row[$type.'_groups']) ? explode(',', $row[$type.'_groups']) : array(),		// The membergroups that can view this.
				);

				$params[$row['id_position']][] = array(
					'id' => $row['id_' . $type],
					'file_input' => (strtolower($row['type']) == 'file_input' ? array('id_param' => $row['id_param'], 'id' => $row['id_' . $type], 'is_clone' => !empty($row['id_clone'])) : array()),
					'name' => $row['pName'],
					'type' => $row['type'],
					'value' => !empty($row['txt_value']) && isset($txt[$row['value']]) ? $txt[$row['value']] : $row['value'],
				);
			}

			$dp_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]['params'] = $params[$row['id_position']];
		}
	}

	// Load up any Empty Modules first!
	if (!empty($context['empty_modules']))
	{
		$context['has_dp_layout'] = true;
		foreach(array_keys($context['empty_modules']) as $module_info)
			if (!empty($context['empty_modules'][$module_info]['name']))
				$context['empty_modules'][$module_info] = loadModule($context['empty_modules'][$module_info], true);
	}

	// Shouldn't be empty, but we check anyways!
	if (!empty($dp_modules))
	{
		$context['has_dp_layout'] = true;
		ksort($dp_modules);

		// array_keys consumes less PHP memory instead of making a copy of each array bound with a foreach on the actual array.
		foreach (array_keys($dp_modules) as $dp_module_rows)
		{
			ksort($dp_modules[$dp_module_rows]);
			foreach (array_keys($dp_modules[$dp_module_rows]) as $dp)
				if (is_array($dp_modules[$dp_module_rows][$dp]))
					foreach(array_keys($dp_modules[$dp_module_rows][$dp]) as $mod)
					{
						if ($mod != 'modules' || !is_array($dp_modules[$dp_module_rows][$dp][$mod]))
							continue;

						ksort($dp_modules[$dp_module_rows][$dp][$mod]);
					}
		}

		foreach (array_keys($dp_modules) as $row_data)
			foreach (array_keys($dp_modules[$row_data]) as $column_data)
				if (isset($dp_modules[$row_data][$column_data]['modules']))
						foreach(array_keys($dp_modules[$row_data][$column_data]['modules']) as $module_data)
							if (!empty($dp_modules[$row_data][$column_data]['modules'][$module_data]['name']))
								$dp_modules[$row_data][$column_data]['modules'][$module_data] = loadModule($dp_modules[$row_data][$column_data]['modules'][$module_data]);

		$context['dream_columns'] = $dp_modules;
	}

	// Success!
	return true;
}

function loadModule($data = array(), $empty_module = false)
{
	global $context, $modSettings, $options, $txt;

	if (!empty($data['files']))
	{
		// We don't want any warnings to show, so we'll check if the file exists first.
		$mod_files = explode('+', $data['files']);
		foreach(array_keys($mod_files) as $mFile)
			if (file_exists($context['dpmod_modules_dir'] . '/' . $data['name'] . '/' . $mod_files[$mFile]))
				require_once($context['dpmod_modules_dir'] . '/' . $data['name'] . '/' . $mod_files[$mFile]);
			else
			{
				// File doesn't exist, TODO://  needs more work done here!!!!  Should return out of here cause it found an error, but needs to also, return something so that it is proper in the module.
				// Log it into the error log...
				module_error(sprintf($txt['dp_modfile_not_exist'], $data['name'], $context['dpmod_modules_dir'] . '/' . $data['name'] . '/' . $mod_files[$mFile]), 'critical', true, false);
				// $error_name[$data['name']] = $data['name'];
			}
	}

	if (!$empty_module)
	{
		// If template doesn't exist or we aren't using it, set it to the default template!
		if (empty($data['template']['id']) || !file_exists($data['template']['filepath']))
			$data['template'] = array(
				'filepath' => $context['dpmod_template_dir'] . '/default.php',
				'function' => 'dp_template_module_default',
			);

		require_once($data['template']['filepath']);

		// Grab the minheight value and unit.
		if (!empty($data['minheight']['value']))
		{
			$unit = 'px';

			// available units:  px, %, em, rem (CSS3), ex, pt
			// recommended units:  px, %, em, rem
			switch ((int) $data['minheight']['unit'])
			{
				case 1:
					$unit = '%';
					break;
				case 2:
					$unit = 'em';
					break;
				case 3:
					$unit = 'rem';
					break;
				case 4:
					$unit = 'ex';
					break;
				case 5:
					$unit = 'pt';
					break;
				default:
					$unit = 'px';
					break;
			}

			// Change the variable to hold the information we need templates to use.
			$data['minheight'] = 'min-height: ' . $data['minheight']['value'] . $unit . ';';
		}
		else
			$data['minheight'] = '';
			
		
		
		// Correct the title target...
		switch ((int) $data['target'])
		{
			case 1:
				$data['target'] = '_self';
				break;
			case 2:
				$data['target'] = '_parent';
				break;
			case 3:
				$data['target'] = '_top';
				break;
			default:
				$data['target'] = '_blank';
				break;
		}

		// Load up the icon if there is one to load.
		$data['icon'] = !empty($data['icon']) ? $context['dpmod_icon_url'] . $data['icon'] : '';

		// Load up the link for the title.
		// Checking for either an 'action' or a 'url'.
		if (isset($data['title_link']))
		{
			$http = (strpos(strtolower($data['title_link']), 'http://') === 0 ? true : (strpos(strtolower($data['title_link']), 'www.') === 0 ? true : false));

			if ($http)
			{
				$data = array_merge($data, array(
					'url' => !empty($data['url']) ? $data['url'] : '<a href="' . $data['title_link'] . '" target="' . $data['target'] . '" onfocus="if(this.blur)this.blur();">',
					'action' => '',
				));
			}
			else
			{
				$data = array_merge($data, array(
					'url' => '',
					'action' => $data['title_link'],
				));
			}
		}
	}

	// Check for any parameters...
	if (!empty($data['params']))
	{
		$params = $data['params'];
		$data['params'] = array();

		// Looping through the params.
		$countParams = count($params);
		for ($i = 0; $i < $countParams; $i++)
			$data['params'][$params[$i]['name']] = loadParameter($params[$i]['file_input'], $params[$i]['type'], $params[$i]['value']);
	}

	// Main module function will always be the first function in the list of functions.
	if (empty($data['functions']))
	{
		// No functions in the database table, so let's try and get the functions from the info.xml file instead.
		$dp_modules = loadDefaultModuleConfigs(array(), true);
		if (isset($dp_modules[$data['name']], $dp_modules[$data['name']]['functions']))
			$main_function = explode('+', $dp_modules[$data['name']]['functions']);
	}
	else
		$main_function = explode('+', $data['functions']);

	$data['function'] = $main_function[0];

	/*
		Just in here in case I decide to change this to this approach instead of calling the function within the template file for empty modules.
	if ($empty_module)
	{
		if (!empty($data['params']))
			$data['function']($data['params']);
		else
			$data['function']();
	}
	*/

	if (!$empty_module)
	{
		$data['is_collapsed'] = $context['user']['is_guest'] ? !empty($_COOKIE[$data['type'] . 'module_' . $data['id']]) : !empty($options[$data['type'] . 'module_' . $data['id']]);

		if ($data['header_display'] == 2)
		{
			$data['is_collapsed'] = false;
			$data['hide_upshrink'] = true;
		}

		// Which function to call?
		$toggleModule = !empty($modSettings['dp_module_enable_animations']) ? 'toggleModuleAnim('  : 'toggleModule(';
		$toggleModule .= '\'' . $data['type'] . '\', \'' . $data['id'] . '\'';

		if (!empty($modSettings['dp_module_enable_animations']))
			$toggleModule .= ', \'' . (intval($modSettings['dp_module_animation_speed']) + 1) . '\');';
		else
			$toggleModule .= ');';

		$data['toggle'] = $toggleModule;
	}
		
	return $data;
}

function array_insert_buttons($buttons, $new_menu_buttons)
{
	global $context;

	$context['dp_menu'] = array();
	$context['dp_menu_parent'] = array();
	$context['dp_menu_slugs'] = array();

	foreach(array_keys($new_menu_buttons) as $new)
	{
		$slug = $new_menu_buttons[$new]['slug'];
		$parent = $new_menu_buttons[$new]['parent'];
		$pos = $new_menu_buttons[$new]['position'];
		$dream_page = !empty($new_menu_buttons[$new]['dream_page']['name']) && !empty($new_menu_buttons[$new]['dream_page']['id']) ? $new_menu_buttons[$new]['dream_page'] : array();

		// Unsetting no longer needed keys ;)
		unset($new_menu_buttons[$new]['slug'], $new_menu_buttons[$new]['parent'], $new_menu_buttons[$new]['position'], $new_menu_buttons[$new]['id_button']);

		if (isset($new_menu_buttons[$new]['dream_page']))
			unset($new_menu_buttons[$new]['dream_page']);

		$keys = array_keys($buttons);
		$search = array_search($parent, $keys);
		$position = (int) $search;

		if ($pos == 'after')
			$position++;

		// Create the new array in the correct format, using the slug!
		$new_button = array();
		$new_button[$slug] = $new_menu_buttons[$new];

		if ($pos == 'child_of')
		{
			// 1st level child_of
			if (isset($buttons[$parent]))
			{
				if (!empty($dream_page))
				{
					$context['dp_menu'][$dream_page['id']] = $slug;
					$context['dp_menu'][$dream_page['name']] = $slug;

					$context['dp_menu_parents'][$slug] = $parent;
				}

				if (!isset($buttons[$parent]['sub_buttons']))
					$buttons[$parent]['sub_buttons'] = array();

				$buttons[$parent]['sub_buttons'] = array_merge($buttons[$parent]['sub_buttons'], $new_button);
			}
			else
			{
				// 2nd level Sub-Menus
				foreach($buttons as $k => $v)
					if (isset($v['sub_buttons']) && array_key_exists($parent, $v['sub_buttons']))
					{
						if (!isset($buttons[$k]['sub_buttons'][$parent]['sub_buttons']))
							$buttons[$k]['sub_buttons'][$parent]['sub_buttons'] = array();

						if (!empty($dream_page) && !isset($context['dp_menu'][$dream_page['name']]))
						{
							$context['dp_menu'][$dream_page['name']] = $slug;
							$context['dp_menu'][$dream_page['id']] = $slug;

							$context['dp_menu_parents'][$slug] = $k;
						}
						$buttons[$k]['sub_buttons'][$parent]['sub_buttons'] = array_merge($buttons[$k]['sub_buttons'][$parent]['sub_buttons'], $new_button);
					}
			}
		}
		else
		{
			// It's going after or before, but is it after/before a parent or a sub menu?
			if ($search !== false)
			{
				if (!empty($dream_page))
				{
					$context['dp_menu'][$dream_page['name']] = $slug;
					$context['dp_menu'][$dream_page['id']] = $slug;

					$context['dp_menu_parents'][$slug] = $slug;
				}

				// Parent Menu after/before
				$buttons = array_merge(
					array_slice($buttons, 0, $position),
					$new_button,
					array_slice($buttons, $position)
				);
			}
			else
			{
				// Submenu after/before.
				foreach($buttons as $k => $v)
				{
					if (!empty($v['sub_buttons']))
					{
						if (array_key_exists($parent, $v['sub_buttons']))
						{
							$keys = array_keys($v['sub_buttons']);
							$position = (int) array_search($parent, $keys);

							if ($pos == 'after')
								$position++;

							if (!empty($dream_page) && !isset($context['dp_menu'][$dream_page['name']]))
							{
								$context['dp_menu'][$dream_page['name']] = $slug;
								$context['dp_menu'][$dream_page['id']] = $slug;

								$context['dp_menu_parents'][$slug] = $k;
							}

							$buttons[$k]['sub_buttons'] = array_merge(array_slice($buttons[$k]['sub_buttons'], 0, $position),
								$new_button,
								array_slice($buttons[$k]['sub_buttons'], $position)
							);
						}
						else
						{
							// 2nd level sub-buttons after/before...
							if (!empty($v['sub_buttons']))
							{
								foreach($v['sub_buttons'] as $a => $b)
								{
									if (isset($b['sub_buttons']) && is_array($b['sub_buttons']) && array_key_exists($parent, $b['sub_buttons']))
									{
										$keys = array_keys($b['sub_buttons']);
										$position = (int) array_search($parent, $keys);

										if ($pos == 'after')
											$position++;

										if (!empty($dream_page) && !isset($context['dp_menu'][$dream_page['name']]))
										{
											$context['dp_menu'][$dream_page['name']] = $slug;
											$context['dp_menu'][$dream_page['id']] = $slug;

											$context['dp_menu_parents'][$slug] = $k;
										}

										$buttons[$k]['sub_buttons'][$a]['sub_buttons'] = array_merge(array_slice($buttons[$k]['sub_buttons'][$a]['sub_buttons'], 0, $position),
											$new_button,
											array_slice($buttons[$k]['sub_buttons'][$a]['sub_buttons'], $position)
										);
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return $buttons;
}

function add_dream_actions(&$actionArray)
{
	global $modSettings;

	// Just add Dream Portal's actions in here.
	$actionArray += array(
		'dream' => array('DreamPortal.php', 'dreamActions'),
		'dreamFiles' => array('DreamPortal.php', 'dreamFiles'),
	);

	// Only need this action if the Homepage Layout is not disabled!
	if (empty($modSettings['dp_disable_homepage']))
		$actionArray += array(
			'forum' => array('BoardIndex.php', 'BoardIndex'),
		);
}

function load_dream_menu($menu_buttons)
{
	global $smcFunc, $user_info, $db_connection, $txt, $modSettings;

	if (!allowedTo('dream_portal_menu_view') || (!empty($modSettings['dp_menu_maintenance_mode']) && !allowedTo('admin_dpmenu')))
		return $menu_buttons;

	// We need to catch any errors here and return out of this function if there are any...
	$request = $smcFunc['db_query']('', '
		SELECT id_button, name, target, link, status, slug, type, position, permissions, parent, is_txt
		FROM {db_prefix}dp_dream_menu
		ORDER BY NULL',
		array(
			'db_error_skip' => true,
		)
	);

	// Not empty if error on database query.
	$db_error = $smcFunc['db_error']($db_connection);

	// They must be uninstalling Dream Portal or the db table doesn't exist anymore for whatever reason.
	// return out of here with the users Non DP Menu, menu buttons!
	if (!empty($db_error))
		return $menu_buttons;

	$new_menu_buttons = array();
	$temp_buttons = array();
	$page_links = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Building the menu buttons to be added to the array!
		$permissions = explode(',', $row['permissions']);

		// Used for setting the active page button in the Dream Menu.
		if (isset($_GET['page']))
		{
			$check_page = check_page_link($row['link']);

			if ($check_page != '')
				$page_links[$row['id_button']] = $check_page;
		}

		$new_menu_buttons[$row['id_button']] = array(
			'id_button' => $row['id_button'],
			'parent' => $row['parent'],
			'position' => $row['position'],
			'slug' => $row['slug'],
			'title' => !empty($row['is_txt']) ? $txt[$row['name']] : $row['name'],
			'href' => $row['link'],
			'show' => !empty($modSettings['dp_menu_maintenance_mode']) && allowedTo('admin_dpmenu') || (array_intersect($user_info['groups'], $permissions) && !empty($row['status']) && empty($modSettings['dp_menu_maintenance_mode'])),
			'target' => $row['target'],
			'active_button' => false,
		);

		// Used for sorting purposes!
		$temp_buttons[$row['parent']] = $row['slug'];
	}

	// Grabbing all pages available here...
	if (!empty($page_links))
	{
		$dream_pages = menu_page_link($page_links);
		if (!empty($dream_pages))
			foreach (array_keys($dream_pages) as $key)
				$new_menu_buttons[$key] += array('dream_page' => $dream_pages[$key]);
	}
	
	dp_sortArray($new_menu_buttons, $temp_buttons, 'slug');

	// Add the Menu items to the SMF Menu!
	$menu_buttons = array_insert_buttons($menu_buttons, $new_menu_buttons);
	return $menu_buttons;
}

function dp_sortArray(&$new_menu_buttons, $sortArray, $sort)
{
	$new_array = array();
	$temp = array();
	foreach (array_keys($new_menu_buttons) as $menuitem)
	{
		if (isset($sortArray[$new_menu_buttons[$menuitem][$sort]]))
		{
			$new_array[] = $new_menu_buttons[$menuitem];
			$temp[$new_menu_buttons[$menuitem]['parent']] = $new_menu_buttons[$menuitem]['slug'];
			unset($new_menu_buttons[$menuitem]);
		}	
	}

	$ordered = array();
	$ordered2 = array();

	if (!empty($new_array))
	{
		$temp2 = array();
		foreach (array_keys($new_array) as $menuitem)
		{
			if (isset($temp[$new_array[$menuitem][$sort]]))
			{
				$ordered[] = $new_array[$menuitem];
				$temp2[$new_array[$menuitem]['parent']] = $new_array[$menuitem]['slug'];
				unset($new_array[$menuitem]);
			}
		}

		if (!empty($ordered))
		{
			foreach (array_keys($ordered) as $menuitem)
			{
				if (isset($temp2[$ordered[$menuitem][$sort]]))
				{
					$ordered2[] = $ordered[$menuitem];
					unset($ordered[$menuitem]);
				}
			}
		}
	}
	else
	{
		$new_menu_buttons = $new_menu_buttons;
		return;
	}

	$new_menu_buttons = array_merge($ordered2, $ordered, $new_array, $new_menu_buttons);
}

function add_dp_menu_buttons(&$menu_buttons)
{
	global $txt, $scripturl, $modSettings;

	// Rename the Home Menu Button if we have it set to a title.
	if (!empty($modSettings['dp_home_menu_title']))
		$menu_buttons['home']['title'] = $modSettings['dp_home_menu_title'];
	
	// Dream Portal Administrate Permissions...
	$dp_permissions = array('manage_dplayouts', 'admin_dplayouts', 'admin_dpextend');

	if (!empty($modSettings['dp_menu_mode']))
		$dp_permissions = array_merge($dp_permissions, array('admin_dpmenu'));

	if (!empty($modSettings['dp_pages_mode']))
		$dp_permissions = array_merge($dp_permissions, array('admin_dppages'));

	// Check if the Admin button should be showing if it's not showing already!
	$menu_buttons['admin']['show'] = !$menu_buttons['admin']['show'] ? allowedTo($dp_permissions) : $menu_buttons['admin']['show'];
	
	// Adding the Forum button to the main menu, but only if the Homepage is Enabled!.
	if (empty($modSettings['dp_disable_homepage']))
	{
		$forum_button = array(
			'title' => (!empty($modSettings['dp_forum_menu_title']) ? $modSettings['dp_forum_menu_title'] : $txt['forum']),
			'href' => $scripturl . '?action=forum',
			'show' => (!empty($modSettings['dp_portal_mode']) && allowedTo('dream_portal_view') ? true : false),
			'active_button' => false,
		);

		$new_menu_buttons = array();
		foreach (array_keys($menu_buttons) as $area)
		{
			$new_menu_buttons[$area] = $menu_buttons[$area];
			if ($area == 'home')
				$new_menu_buttons['forum'] = $forum_button;
		}

		$menu_buttons = $new_menu_buttons;
	}
}

function add_dp_admin_areas(&$admin_areas)
{
	global $txt, $modSettings;

	$permissions = array('admin_forum', 'manage_dplayouts', 'admin_dplayouts', 'admin_dpextend');

	if (!empty($modSettings['dp_menu_mode']))
		$permissions = array_merge($permissions, array('admin_dpmenu'));

	if (!empty($modSettings['dp_pages_mode']))
		$permissions = array_merge($permissions, array('admin_dppages'));

	// Building the Dream Portal admin areas
	$dreamportal = array(
		'title' => $txt['dream_portal'],
		'permission' => $permissions,
		'areas' => array(
			'dpgeneral' => array(
				'label' => $txt['dp_admin_general'],
				'file' => 'ManageDPSettings.php',
				'function' => 'dpManageSettings',
				'permission' => array('admin_forum'),
				'subsections' => array(
					'dpinfo' => array($txt['dp_admin_general_info'], 'admin_forum'),
					'dpconfig' => array($txt['dp_admin_general_config'], 'admin_forum'),
				),
			),
			'dplayouts' => array(
				'label' => $txt['dp_admin_dream_layouts'],
				'file' => 'ManageDPLayouts.php',
				'function' => 'dpManageLayouts',
				'permission' => array('admin_forum', 'manage_dplayouts', 'admin_dplayouts'),
				'subsections' => array(
					'dpmanlayouts' => array($txt['dp_admin_manage_layouts']),
					'dplayoutsettings' => array($txt['dp_admin_layout_settings'], 'admin_dplayouts'),
				),
			),
		),
	);

	if (!empty($modSettings['dp_menu_mode']))
		$dreamportal['areas']['dpmenu'] = array(
			'label' => $txt['dp_admin_dream_menu'],
			'file' => 'ManageDPMenu.php',
			'function' => 'dpManageMenu',
			'permission' => array('admin_forum', 'admin_dpmenu'),
			'subsections' => array(
				'dpmanmenu' => array($txt['dp_admin_manage_dream_menu']),
				'dpaddbutton' => array($txt['dp_admin_add_dream_button']),
				'dpmenusettings' => array($txt['dp_admin_dream_menu_settings']),
			),
		);

	if (!empty($modSettings['dp_pages_mode']))
		$dreamportal['areas']['dppages'] = array(
			'label' => $txt['dp_admin_dream_pages'],
			'file' => 'ManageDPPages.php',
			'function' => 'dpManagePages',
			'permission' => array('admin_forum', 'admin_dppages'),
			'subsections' => array(
				'dpmanpages' => array($txt['dp_admin_manage_dream_pages']),
				'dpaddpage' => array($txt['dp_admin_add_dream_page']),
				'dppagesettings' => array($txt['dp_admin_dream_page_settings']),
			),
		);

	// Add in the Extend Dream Portal - DP admin area!
	$dreamportal['areas']['dpextend'] = array(
		'label' => $txt['dp_admin_extend'],
		'file' => 'ManageDPExtend.php',
		'function' => 'dpManageExtend',
		'permission' => array('admin_forum', 'admin_dpextend'),
		'subsections' => array(
			'dpaddmodules' => array($txt['dp_admin_add_modules']),
			'dpaddtemplates' => array($txt['dp_admin_add_templates']),
			'dpaddlanguages' => array($txt['dp_admin_add_languages']),
		),
	);

	$new_admin_areas = array();
	foreach ($admin_areas as $area => $info)
	{
		$new_admin_areas[$area] = $info;
		if ($area == 'config')
			$new_admin_areas['portal'] = $dreamportal;
	}

	$admin_areas = $new_admin_areas;
}

function add_dp_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context;

	loadLanguage('DreamPermissions');

	$dream_portal = array(
		'membergroup' => array(
			'dream_portal_view' => array(false, 'dream_portal', 'dream_portal'),
			'dream_portal_menu_view' => array(false, 'dream_portal', 'dream_portal'),
			'dream_portal_page_view' => array(false, 'dream_portal', 'dream_portal'),
			'manage_dplayouts' => array(false, 'dream_portal', 'dream_portal'),
			'admin_dplayouts' => array(false, 'dream_portal', 'dream_portal'),
			'admin_dpmenu' => array(false, 'dream_portal', 'dream_portal'),
			'admin_dppages' => array(false, 'dream_portal', 'dream_portal'),
			'admin_dpextend' => array(false, 'dream_portal', 'dream_portal'),
		),
	);

	// Update the permission list.
	$permissionList['membergroup'] = array_merge($dream_portal['membergroup'], $permissionList['membergroup']);

	// Set the permissions that can't be given to guests!!
	$context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], array('manage_dplayouts', 'admin_dplayouts', 'admin_dpmenu', 'admin_dppages', 'admin_dpextend'));
}

function dream_whos_online($actions)
{
	global $txt, $smcFunc, $user_info;

	$data = array();

	if (isset($actions['page']))
	{
		$data = $txt['who_hidden'];

		if (is_numeric($actions['page']))
			$where = 'id_page = {int:numeric_id}';
		else
			$where = 'page_name = {string:name}';

		$result = $smcFunc['db_query']('', '
			SELECT id_page, page_name, title, permissions, status
			FROM {db_prefix}dp_dream_pages
			WHERE ' . $where,
			array(
				'numeric_id' => $actions['page'],
				'name' => $actions['page'],
			)
		);
		$row = $smcFunc['db_fetch_assoc']($result);

		// Invalid page? Bail.
		if (empty($row))
			return $data;

		// Skip this turn if they cannot view this...
		if ((!array_intersect($user_info['groups'], explode(',', $row['permissions'])) || !allowedTo(array('admin_forum', 'admin_dppages'))) && ($row['status'] != 1 || !allowedTo(array('admin_forum', 'admin_dppages'))))
			return $data;

		$page_data = array(
			'id' => $row['id_page'],
			'page_name' => $row['page_name'],
			'title' => $row['title'],
		);

		// Good. They are allowed to see this page, so let's list it!
		if (is_numeric($actions['page']))
			$data = sprintf($txt['dp_who_page'], $page_data['id'], censorText($page_data['title']));
		else
			$data = sprintf($txt['dp_who_page'], $page_data['page_name'], censorText($page_data['title']));
	}

	return $data;
}

function menu_page_link($pages)
{
	global $smcFunc;

	if (is_array($pages))
	{
		$array_pages = array();

		// Sort the types of dream pages first.
		foreach($pages as $key => $dpage)
		{
			if (trim($dpage) == '')
				continue;
	
			if (is_numeric($dpage))
				$array_pages['ids'][$key] = (int) $dpage;
			else
				$array_pages['names'][$key] = strtolower($dpage);
		}

		if (isset($array_pages['ids']) && isset($array_pages['names']))
		{
			$where = 'LOWER(page_name) IN ({array_string:page_names}) || id_page IN ({array_int:page_ids})';
			$query_array = array('page_names' => $array_pages['names'], 'page_ids' => $array_pages['ids']);
		}
		elseif (isset($array_pages['ids']) && !isset($array_pages['names']))
		{
			$where = 'id_page IN ({array_int:page_ids})';
			$query_array = array('page_ids' => $array_pages['ids']);
		}
		elseif (isset($array_pages['names']) && !isset($array_pages['ids']))
		{
			$where = 'LOWER(page_name) IN ({array_string:page_names})';
			$query_array = array('page_names' => $array_pages['names']);
		}
		else
			return array();
		
		// We have atleast 1 Dream Page here, go get em' tiger!
		$request = $smcFunc['db_query']('', '
			SELECT id_page, page_name, id_button
			FROM {db_prefix}dp_dream_pages
			WHERE ' . $where . '
			ORDER BY NULL',
			$query_array
		);
		
		$return = array();
		
		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($return[$row['id_button']]))
				$return[$row['id_button']] = array(
					'name' => strtolower($row['page_name']),
					'id' => $row['id_page'],
				);
		}

		$smcFunc['db_free_result']($request);

		return $return;
	}
	else
	{
		$page = trim($pages);

		if (!empty($pages))
		{
			$where = is_numeric($pages) ? 'page_name' : 'id_page';
			$type = is_numeric($pages) ? '{int:page}' : 'LOWER({string:page})';

			// We need to get the page_name or id_page value here to be compatible with both pages of that dream page!
			$request = $smcFunc['db_query']('', '
				SELECT page_name, id_page
				FROM {db_prefix}dp_dream_pages
				WHERE ' . ($where == 'page_name' ? 'id_page' : 'LOWER(page_name)') . ' = ' . $type . '
				LIMIT 1',
				array(
					'page' => $pages,
					'is_zero' => 0,
				)
			);

			// No page found!
			if ($smcFunc['db_num_rows']($request) == 0)
				return array('name' => '', 'id' => 0);

			list ($page_name, $id_page) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			return array('name' => $page_name, 'id' => $id_page);
		}
		else
			return array('name' => '', 'id' => 0);
	}
}

function check_page_link($link = '')
{
	global $scripturl;

	$link = trim($link);

	if ($link == '')
		return '';

	$is_page = substr($link, 0, 15) == 'index.php?page=' || substr($link, 0, strlen($scripturl) + 6) == $scripturl . '?page=';

	if ($is_page)
	{
		$pUrl = parse_url($scripturl . substr($link, 9));
		parse_str($pUrl['query'], $page_output);
	}

	if (isset($page_output['page']))
	{
		if (strpos($page_output['page'], ';') !== false)
		{
			$page_found = explode(';', $page_output['page']);
			return $page_found[0];
		}
		else
			return $page_output['page'];
	}
	else
		return '';
}

?>