<?php
/**************************************************************************************
* DreamPortal.php                                                                     *
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

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	This is the main script Dream Portal uses to generate the portal
	and page content. It provides the following functions:

	void dp_init(init_action = '')
		- Responsible for loading up the Layout in Dream Portal.  Called from index.php
		- If init_action is set to a non-empty string, than we know that an action doesn't exist within the SMF $actionArray, and loads up the [home] layout!

	void dp_main()
		- Loads up the Dream Portal Homepage layout.
		- This function only gets called from index.php if the DP Homepage is enabled within Dream Portal Layout Settings.

	void dreamActions()
		- Handles all Sub-actions for index.php?action=dream
		- Subactions can be injected into a Module that this function will call upon!
		- This action can be used by modules to input xml routines as well.

	void dreamPages()
		- Used to load up pages that have been created via DP's Dream Pages section.

	void dreamFiles()
		- Responsible for outputting files via the file_input parameter type!
*/

if (file_exists($boarddir . '/dp_license.txt'))
	define('DP', 1);

function dp_init($init_action = '')
{
	global $context, $txt, $settings, $board, $topic, $sourcedir, $scripturl, $boarddir, $boardurl, $board_info, $ssi_theme;
	global $modSettings, $modules, $layout, $portal_ver, $maintenance, $forum_version, $user_info;

	// Dream Portal version number.
	$portal_ver = '1.1';

	// Unavailable, reserved filenames.
	$reservedNames = array('.', '..', '.htaccess', '.core', '.htpasswd', 'index.php');

	$context['dp_restricted_names'] = array(
		'modules' => array_merge(array('custom'), $reservedNames),
		'templates' => array_merge(array('default', 'default.php'), $reservedNames),
		'languages' => array_merge(array('english'), $reservedNames)
	);

	// XML mode? Save time (cut it in half) and CPU cycles by bailing out.
	if (isset($_REQUEST['xml']))
	{
		require_once($sourcedir . '/Subs-DreamPortal.php');

		// Avert a SMF bug with the menu...
		if (!loadLanguage('DreamPortal'))
			loadLanguage('DreamPortal');

		return;
	}
	
	// No need to load this function in this case.
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach' && (!empty($modSettings['allow_guestAccess']) && $user_info['is_guest']))
		return;

	// This is important to be loaded first.
	if (!loadLanguage('DreamPortal'))
		loadLanguage('DreamPortal');

	// Images. :D
	$context['dp_icon_url'] = $boardurl . '/dreamportal/module_icons/';
	$context['dpmod_image_url'] = $boardurl . '/dreamportal/module_images/';
	$context['dpmod_image_dir'] = $boarddir . '/dreamportal/module_images/';
	$context['dpadmin_image_url'] = $boardurl . '/dreamportal/images/admin';

	// Files and Modules
	$context['dpmod_files_url'] = $boardurl . '/dreamportal/module_files/';
	$context['dpmod_files_dir'] = $boarddir . '/dreamportal/module_files/';
	$context['dpmod_modules_dir'] = $boarddir . '/dreamportal/modules';
	$context['dpmod_module_actionsdir'] = $boarddir . '/dreamportal/module_actions';

	// DP icon directory and url for Module Icons.
	$context['dpmod_icon_url'] = $boardurl . '/' . $modSettings['dp_icon_directory'] . '/';
	$context['dpmod_icon_dir'] = $boarddir . '/' . $modSettings['dp_icon_directory'] . '/';

	// Templates
	$context['dpmod_template_dir'] = $boarddir . '/dreamportal/module_templates';

	// Is Dream Portal disabled? Can you view it?
	if (empty($modSettings['dp_portal_mode']) || !allowedTo('dream_portal_view'))
		return;

	// Load the sub-functions needed for Dream Portal, and Dream Modules.
	require_once($sourcedir . '/Subs-DreamModules.php');
	require_once($sourcedir . '/DreamModules.php');

	// Load the DreamModules Language File for all you Module Customizers out there :)
	// If the file doesn't exist, skip it!
	loadLanguage('DreamModules', '', false);

	// These puppies are evil >:D
	unset($_GET['PHPSESSID'], $_GET['theme']);

	// Default to Home Layout!
	$da_action = '[home]';
	
	if (!empty($_REQUEST['action']) || !empty($_REQUEST['page']) || !empty($board) || !empty($topic))
	{
		// We want the first item in the requested URI
		reset($_GET);
		$uri = key($_GET);

		$da_action = !empty($init_action) ? $init_action : (!empty($uri) ? (!empty($context['current_action']) || strtolower($uri) == 'action' ? $context['current_action'] : '[' . $uri . ']') : '');
	}

	$skipped_actions = array(
		'jsoption' => 0,
		'.xml' => 0,
		'xmlhttp' => 0,
		'dlattach' => 0,
		'helpadmin' => 0,
		'keepalive' => 0,
	);

	// Don't continue if we're wireless or on certain actions....
	if (WIRELESS || isset($skipped_actions[$da_action]))
		return;

	// Add Forum to the linktree.
	if ((!empty($modSettings['dp_portal_mode']) && allowedTo('dream_portal_view') && empty($modSettings['dp_disable_homepage'])) && (!empty($board) || !empty($topic) || $da_action == 'forum' || $da_action == 'collapse' || $da_action == 'unread' || $da_action == 'unreadreplies'))
	{
		// The forum is always the second item in the linktree right?
		if (count($context['linktree']) > 2)
		{
			// This is basically going to push everything one offset forward, duplicating the first item.
			foreach ($context['linktree'] as $offset => $link)
				$context['linktree'][$offset + 1] = array(
					'name' => $link['name'],
					'url' => $link['url'],
				);

			// And thus the forum is the second item in the linktree.
			$context['linktree'][1] = array(
				'name' => $txt['forum'],
				'url' => $scripturl . '?action=forum',
			);
		}
		else
			$context['linktree'][] = array(
				'name' => $txt['forum'],
				'url' => $scripturl . '?action=forum',
			);

		// Fix the linktree if a category was requested.
		foreach ($context['linktree'] as $key => $tree)
			if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
				$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
	}

	// Default Exception actions.
	$context['exceptions'] = array(
		'print' => 0,
		'clock' => 0,
		'about:unknown' => 0,
		'about:mozilla' => 0,
		'modifycat' => 0,
		'.xml' => 0,
		'xmlhttp' => 0,
		'dlattach' => 0,
		'dreamFiles' => 0,
		'printpage' => 0,
		'keepalive' => 0,
		'jseditor' => 0,
		'jsmodify' => 0,
		'jsoption' => 0,
		'suggest' => 0,
		'verificationcode' => 0,
		'viewsmfile' => 0,
		'viewquery' => 0,
		// Removing some known 2's here
		'editpoll2' => 0,
		'login2' => 0,
		'movetopic2' => 0,
		'post2' => 0,
		'quickmod2' => 0,
		'register2' => 0,
		'removetopic2' => 0
	);

	if (isset($context['exceptions'][$da_action]))
		return;
		
	$curr_action = !empty($da_action) ? $da_action : '[home]';
	$context['dp_home'] = $curr_action == '[home]';

	// Getting specific actions/non-actions now.
	$dp_layout_action = '';

	// Rebuild the action/non-action string that loads the layout!
	if (!empty($context['current_action']) || $curr_action == '[home]')
		 $dp_layout_action .= $curr_action;

	foreach($_GET as $g_key => $g_val)
	{
		// Do not want the current action nor do we want the session variables!
		if ($g_val == $curr_action || $curr_action == '[home]' || ($g_key == $context['session_var'] && $g_val == $context['session_id']))
			continue;

		$dp_layout_action .= '[' . $g_key . ']' . '=' . $g_val;
	}

	// Load up the layout action now, or get outta here if no layout for this page.
	if (!loadLayout($curr_action, $dp_layout_action) && empty($init_action))
		return;
	
	// Load the portal layer, making sure we didn't already add it.
	if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers']))
		// Checks if the forum is in maintenance, and if the portal is disabled.
		if (($maintenance && !allowedTo('admin_forum')) || empty($modSettings['dp_portal_mode']) || !allowedTo('dream_portal_view'))
			$context['template_layers'] = array('html', 'body');
		else
			$context['template_layers'][] = 'portal';

	if (!empty($context['has_dp_layout']))
	{
		// Uses the themes directory name for this, cause the id_theme is not reliable if uninstalled and re-installed again.
		$dpmodheight = 'dp_mod_header' . substr(strrchr($settings['theme_url'], "/"), 1);

		// Add the Module Headers if any exist!
		if (!empty($context['dp_module_headers']))
		{
			foreach($context['dp_module_headers'] as $mod_name => $header)
			{
				// Loading CSS Headers.
				if (!empty($header['css']))
				{
					// Thank You for fixing this in SMF 2.1!
					if (is_callable('loadCSSFile'))
						loadCSSFile($header['css']);
					else
						loadTemplate(false, $header['css']);
				}
	
				// Loading up JS Headers.
				if (!empty($header['js']))
					foreach($header['js'] as $path)
					{
						$script_src = !file_exists($settings['theme_dir'] . '/scripts/' . $path) ? $settings['default_theme_url'] . '/scripts/' . $path : $settings['theme_url'] . '/scripts/' . $path;
						$context['html_headers'] .= "\n\t" . '<script type="text/javascript" src="' . $script_src . '"></script>';
					}
			}
		}

		// Include the JS file, code, and css needed.
		$context['html_headers'] .= "\n\t" . '<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dreamportal.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			loadInfo(\'' . ($user_info['is_guest'] ? '0' : '1') . '\', \'' . $settings['images_url'] . '\', \'' . $context['session_id'] . '\');
		// ]]></script>
		<style type="text/css">
			#dream_container
			{
				display: table;
				width: 100%;
			}
			.block_header
			{
				height: ' . (!empty($modSettings[$dpmodheight]) ? (int) $modSettings[$dpmodheight] : '28') . 'px !important;
				margin-bottom: 0px !important;
			}
		</style>';
	}
}

function dp_main()
{
	global $context, $txt;

	// A mobile device doesn't require a portal...
	if (WIRELESS)
		redirectexit('action=forum');

	// Load the Dream Portal template file
	$context['sub_template'] = 'portal';

	// Set the page title
	$context['page_title'] = $context['forum_name'] . ' - ' . $txt['home'];
	$context['page_title_html_safe'] = $context['forum_name'] . ' - ' . $txt['home'];
}

function dreamActions()
{
	global $context, $boarddir, $modSettings;

	// If DP is disabled, or you don't have permission to view it, than we have no dream actions for you either...
	if (empty($modSettings['dp_portal_mode']) || !allowedTo('dream_portal_view'))
		redirectexit();
	
	// Get the subaction
	$sa = !empty($_GET['sa']) && isset($_GET['sa']) ? $_GET['sa'] : '';

	if (empty($sa))
		redirectexit();

	// We'll need the dream_action files (from modules) within this function.
	$actions_dir = $boarddir . '/dreamportal/module_actions';

	// We have a dream action here, so we need to grab all Dream Action Files from within $context['dpmod_module_actionsdir']
	if (is_dir($actions_dir))
	{
		$dh = @opendir($actions_dir);
		{
			while (false !== ($obj = readdir($dh)))
			{
				if($obj == '.' || $obj == '..' || $obj == '.htaccess' || $obj == 'index.php' || substr($obj, -5) == '.temp')
					   continue;

				$context['dp_action'][] = $actions_dir . '/' . $obj;
			}
			closedir($dh);
		}
	}

	if ($sa != 'insertcolumn' && $sa != 'dbSelect')
	{
		if (!empty($context['dp_action']))
		{
			foreach($context['dp_action'] as $action_file)
			{
				require_once($action_file);
				if (isset($context['dream_action'][$sa]['function']))
				{
					if (isset($context['dream_action'][$sa]['request']) && isset($context['dream_action'][$sa]['value']))
						$context['dream_action'][$sa]['function']($context['dream_action'][$sa]['request'], $context['dream_action'][$sa]['value']);
					elseif (isset($context['dream_action'][$sa]['request']) && !isset($context['dream_action'][$sa]['value']))
						$context['dream_action'][$sa]['function']($context['dream_action'][$sa]['request']);
					else
						$context['dream_action'][$sa]['function']();

					break;
				}
			}
		}
		else
			redirectexit();
	}
	else
	{
		// Here we have Dream Portal Specific XML Dream Actions!
		// $sa == 'insertcolumn' || $sa == 'dbSelect'

		if ($sa == 'insertcolumn')
			dp_insert_column();
		else
			dp_edit_db_select();
	}
}

function dreamPages()
{
	global $context, $modSettings, $smcFunc, $user_info;
	
	// Let's make it plain and simple: we don't want mobile devices!
	if (WIRELESS)
		redirectexit('action=forum');

	// If DP is disabled, or Dream Pages is disabled, or Dream Pages is in Maintenance Mode and you don't have permission to manage Dream Pages?  Than we don't have any page to go to, now do we?
	if (empty($modSettings['dp_portal_mode']) || empty($modSettings['dp_pages_mode']) || !allowedTo('dream_portal_page_view') || (!empty($modSettings['dp_pages_maintenance_mode']) && !allowedTo('admin_dppages')))
		redirectexit();

	// Let's see what page name or id they put in, if blank, send em to the home page.
	$call = isset($_GET['page']) ? $_GET['page'] : redirectexit();

	// Put it in the session to prevent it from being logged when they refresh the page.
	$_SESSION['last_page_id'] = $call;

	// We need to make sure we don't confuse page ids with page names.
	if (!is_numeric($call) || stristr($call, '.') || stristr($call, 'e'))
		$query = 'page_name = {string:page}';
	else
		$query = 'id_page = {int:page}';

	// Let's grab the content from the DB.
	$request = $smcFunc['db_query']('', '
		SELECT title, type, body, permissions, status, page_views
		FROM {db_prefix}dp_dream_pages
		WHERE ' . $query . '
		LIMIT 1',
		array(
			'page' => $smcFunc['htmlspecialchars']($call),
		)
	);

	// If nothing gets returned, exit and prevent any errors.
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('dp_pages_not_exist', false);

	$row = $smcFunc['db_fetch_assoc']($request);
	$context['page_data'] = array(
		'title' => $row['title'],
		'permissions' => explode(',', $row['permissions']),
		'status' => $row['status'],
		'type' => $row['type'],
		'page_views' => $row['page_views']
	);

	// If maintenance mode is enabled, the Admin and those who have permission to manage Dream Pages can see all pages!
	// Otherwise, only those who have permission to see the page(s)!
	if ((!empty($modSettings['dp_pages_maintenance_mode']) && allowedTo(array('admin_forum', 'admin_dppages'))) || (array_intersect($user_info['groups'], $context['page_data']['permissions']) && $context['page_data']['status'] == 1))
	{
		// Modify the body according to the page type
		switch ($context['page_data']['type'])
		{
			// BBC
			case 2:
				$context['page_data']['body'] = dreamportal_code_content($row['body'], 'BBC', false);
				break;

			// HTML
			case 1:
				$context['page_data']['body'] = dreamportal_code_content($row['body'], 'HTML', false);
				break;

			// PHP...
			case 0:
				$context['page_data']['body'] = dreamportal_code_content($row['body'], 'PHP', false, array('function' => 'module_error', 'params' => array('empty')), array('permissions' => allowedTo('admin_dppages'), 'function' => 'fatal_lang_error', 'params' => array('dp_pages_php_error', false)));
				break;
			default:
				break;
		}

		$context['page_title'] = $context['page_data']['title'];
		$context['page_title_html_safe'] = $context['page_data']['title'];

		if (!isset($_SESSION['viewed_page_' . $call]))
		{
			$smcFunc['db_query']('','
				UPDATE {db_prefix}dp_dream_pages
				SET page_views = page_views + 1
				WHERE ' . $query,
				array(
					'page' => $smcFunc['htmlspecialchars']($call),
				)
			);

			$_SESSION['viewed_page_' . $call] = '1=1';
		}

		// Finally, display the content.
		$context['sub_template'] = 'dream_pages';
	}
	else
		fatal_lang_error('dp_pages_no_access', false);
}

//!!! accessed via the file_input parameter type ( index.php?action=dreamFiles )
function dreamFiles()
{
	global $smcFunc, $txt, $modSettings, $context;

	if (empty($modSettings['dp_portal_mode']) || !allowedTo('dream_portal_view'))
		fatal_lang_error('dp_unable_to_view_file', false);

	$_REQUEST['id'] = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : fatal_lang_error('no_access', false);

	// Which type are we dealing with, hmmm.
	$mod = isset($_REQUEST['mod']) ? (int) $_REQUEST['mod'] : 0;
	$clone = isset($_REQUEST['clone']) ? (int) $_REQUEST['clone'] : 0;

	// Can't have both, must have 1 or the other.
	if ((!empty($mod) && !empty($clone)) || (empty($mod) && empty($clone)))
		fatal_lang_error('no_access', false);

	$is_clone = !empty($clone) ? true : false;

	$name = $is_clone ? 'dmc.' : 'dm.';

	// Build a partial query
	$query = ' AND ' . ($is_clone ? 'dmp.id_clone = {int:id_clone})' : 'dmp.id_module = {int:id_module} AND dmp.id_clone = {int:zero})');
	$query .= $is_clone ? ' INNER JOIN {db_prefix}dp_module_clones AS dmc ON (dmc.id_clone = dmp.id_clone AND dmc.id_clone = {int:id_clone} AND dmc.id_member = {int:id_member})' : ' INNER JOIN {db_prefix}dp_modules AS dm ON (dm.id_module = dmp.id_module AND  dm.id_module = {int:id_module})';

	// Getting the files.
	$request = $smcFunc['db_query']('', '
		SELECT dmf.filename, dmf.file_hash, dmf.fileext, dmf.id_file, dmf.file_type, dmf.mime_type, ' . $name . 'name
		FROM {db_prefix}dp_module_files AS dmf
		INNER JOIN {db_prefix}dp_module_parameters AS dmp ON (dmp.id_param = dmf.id_param' . $query . '
		WHERE dmf.id_member = {int:id_member} AND dmf.id_file = {int:file}',
		array(
			'zero' => 0,
			'id_clone' => $clone,
			'id_module' => $mod,
			'file' => $_REQUEST['id'],
			'id_member' => 0,
		)
	);

	// Not allowed or doesn't exist, exit!
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access', false);

	list ($real_filename, $file_hash, $file_ext, $id_file, $file_type, $mime_type, $mod_name) = $smcFunc['db_fetch_row']($request);

	$smcFunc['db_free_result']($request);

	// Get the module directory.
	$module_dir = $context['dpmod_files_dir'] . $mod_name;

	// Update the download counters (unless it's a thumbnail).
	if ($file_type != 1)
		$smcFunc['db_query']('', '
			UPDATE LOW_PRIORITY {db_prefix}dp_module_files
			SET downloads = downloads + 1
			WHERE id_file = {int:id_file}',
			array(
				'id_file' => $id_file,
			)
		);

	$filename = getFilename($real_filename, $_REQUEST['id'], $module_dir, false, $file_hash);

	// Clear any output that was made before now!
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// File doesn't exist, so just exit!
	if (!file_exists($filename))
	{
		loadLanguage('Errors');

		header('HTTP/1.0 404 ' . $txt['file_not_found']);
		header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['file_not_found']);
	}

	if (function_exists("apache_request_headers"))
		$headers = apache_request_headers();
	else
	{
	  $headers = array();

	  // Grab the IF_MODIFIED_SINCE header
	  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		$headers['If-Modified-Since'] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
	}

	if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) >= filemtime($filename)))
	{
		ob_end_clean();

		// Client's cache IS current, so we just respond '304 Not Modified'.
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT', true, 304);
		exit;
	}

	// Checking E-Tag, maybe Cache based on that.
	$file_md5 = '"' . md5_file($filename) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $file_md5) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');
	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Connection: close');
	header('ETag: ' . $file_md5);

	// Set the mime-type
	header('Content-Type: ' . $mime_type);

	if (!isset($_REQUEST['image']))
		header('Content-Disposition: attachment; filename="' . $real_filename . '"');

	// Image extension set, but not an image request.
	if (!isset($_REQUEST['image']) && in_array($file_ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg', 'tiff')))
		header('Cache-Control: no-cache');
	else
		header('Cache-Control: max-age=' . (525600 * 60) . ', private');

	header('Content-Length: ' . filesize($filename));

	// Buying time to execute.
	@set_time_limit(0);

	if (@readfile($filename) == null)
		if (!file_get_contents($filename))
		{
			$fp = fopen($filename, 'rb');
			while (!feof($fp))
			{
				echo @fread($fp, 8192);
				flush();
			}
			fclose($fp);
		}

	obExit(false);
}

?>