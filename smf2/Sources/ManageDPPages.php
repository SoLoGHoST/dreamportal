<?php
/**************************************************************************************
* ManageDPPages.php																	  *
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

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function dpManagePages()
{
	global $context, $txt, $sourcedir, $modSettings;

	// They need to have permission for this first!  Admins already have permission, so no need to check the admin_forum permission.
	if (!allowedTo('admin_dppages') || empty($modSettings['dp_portal_mode']))
		redirectexit();

	// checksession won't work from here for Menu buttons, so we use validateSession for now, and checkSession gets used later on in the code where it is needed!
	validateSession();

	// permission of 'admin_forum' is automatic, so we don't need to set these.
	$subActions = array(
		'dpmanpages' => 'ManageDPPages',
		'dpaddpage' => 'DreamPageAddEdit',
		'dppagesettings' => 'DreamPageSettings',
	);

	// Default to sub action 'dpmanpages'
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'dpmanpages';

	if ($_GET['sa'] == 'dppagesettings')
	{
		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
	}
	else
		loadTemplate('ManageDPPages');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['dp_admin_dream_pages'],
		'help' => 'dp_admin_pages_help',
		'tabs' => array(
			'dpmanpages' => array(
				'description' => $txt['dp_admin_pages_manpages_desc'],
			),
			'dpaddpage' => array(
				'description' => $txt['dp_admin_pages_addpage_desc'],
			),
			'dppagesettings' => array(
				'description' => $txt['dp_admin_pages_settings_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_GET['sa']]();
}

/**
 * Manages existing Dream Pages.
 *
 * @since 1.0
 */
function ManageDPPages()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	// Get rid of all of em!
	if (!empty($_POST['removeAll']))
	{
		checkSession('request');

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}dp_dream_pages');
	}

	// User pressed the 'remove selection button'.
	if (!empty($_POST['removePages']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession('request');

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		// Delete the page!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_dream_pages
			WHERE id_page IN ({array_int:page_list})',
			array(
				'page_list' => $_POST['remove'],
			)
		);
	}

	// Our options for our list.
	$listOptions = array(
		'id' => 'dp_page_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=dppages;sa=dpmanpages',
		'default_sort_col' => 'id_page',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => 'list_getPages',
		),
		'get_count' => array(
			'function' => 'list_getNumPages',
		),
		'no_items_label' => $txt['dp_dream_pages_no_page'],
		'columns' => array(
			'id_page' => array(
				'header' => array(
					'value' => $txt['dp_dream_pages_page_id'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?page=%1$d" target="_blank">%1$d</a>',
						'params' => array(
							'id_page' => false,
						),
					),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'dpp.id_page',
					'reverse' => 'dpp.id_page DESC',
				),
			),
			'page_name' => array(
				'header' => array(
					'value' => $txt['dp_dream_pages_page_name'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?page=%1$s" target="_blank">%1$s</a>',
						'params' => array(
							'page_name' => false,
						),
					),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'dpp.page_name',
					'reverse' => 'dpp.page_num DESC',
				),
			),
			'type' => array(
				'header' => array(
					'value' => $txt['dptext_admin_type'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// The possible types a page can be.
						$types = array(
							0 => $txt[\'dp_dream_pages_page_php\'],
							1 => $txt[\'dp_dream_pages_page_html\'],
							2 => $txt[\'dp_dream_pages_page_bbc\'],
						);

						// Return what type they\'re using.
						return $types[$rowData[\'type\']];
					'),
					'class' => 'smalltext centertext',
				),
				'sort' => array(
					'default' => 'dpp.type',
					'reverse' => 'dpp.type DESC',
				),
			),
			'title' => array(
				'header' => array(
					'value' => $txt['dptext_admin_title'],
				),
				'data' => array(
					'db' => 'title',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'dpp.title',
					'reverse' => 'dpp.title DESC',
				),
			),
			'page_views' => array(
				'header' => array(
					'value' => $txt['dp_dream_pages_page_views'],
				),
				'data' => array(
					'db' => 'page_views',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'dpp.page_views',
					'reverse' => 'dpp.page_views DESC',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['dptext_admin_status'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// Tell them the status of their page.
						if ($rowData[\'status\'])
							return sprintf(\'<span style="color: green;">%1$s</span>\', $txt[\'dptext_admin_active\']);
						else
							return sprintf(\'<span style="color: red;">%1$s</span>\', $txt[\'dptext_admin_nonactive\']);
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'dpp.status',
					'reverse' => 'dpp.status DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['dp_dream_pages_actions'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=dppages;sa=dpaddpage;edit;pid=%1$d;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_page' => false,
						),
					),
					'class' => 'centertext',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="remove[]" value="%1$d" class="input_check" />',
						'params' => array(
							'id_page' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=dppages;sa=dpmanpages;' . $context['session_var'] . '=' . $context['session_id'],
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="removePages" value="' . $txt['dp_dream_pages_remove_selected'] . '" onclick="return confirm(\'' . $txt['dp_dream_pages_remove_confirm'] . '\');" class="button_submit" />
					<input type="submit" name="removeAll" value="' . $txt['dp_dream_pages_remove_all'] . '" onclick="return confirm(\'' . $txt['dp_dream_pages_remove_all_confirm'] . '\');" class="button_submit" />',
					'class' => 'righttext',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['page_title'] = $txt['dp_admin_pages_manage_title'];
	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'dp_page_list';
}

// Prepares the context for Adding or Editing a Dream Page.
function DreamPageAddEdit()
{
	global $context, $smcFunc, $txt, $sourcedir;

	// Are we saving a Dream Page?
	if (isset($_REQUEST['submit']))
	{
		// Just in case...
		checkSession('post');

		$post_errors = array();
		$required_fields = array(
			'page_name',
			'title',
			'body',
		);

		// Make sure we grab all of the content
		$id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
		$name = isset($_REQUEST['page_name']) ? parseString($_REQUEST['page_name'], 'function_name') : '';
		$real_name = isset($_REQUEST['real_page_name']) ? strtolower($_REQUEST['real_page_name']) : '';
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		$title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
		$groups = isset($_REQUEST['permissions']) ? implode(',', $_REQUEST['permissions']) : '';
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

		// If we came from WYSIWYG then turn it back into BBC code.
		if (!empty($_REQUEST['body_mode']) && isset($_REQUEST['body']))
		{
			require_once($sourcedir . '/Subs-Editor.php');

			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$body = $_REQUEST['body'];
		}
		else
			$body = isset($_REQUEST['body']) ? $_REQUEST['body'] : '';

		// These fields are required!
		foreach ($required_fields as $required_field)
			if (!isset($_POST[$required_field]) || trim($_POST[$required_field]) == '')
				$post_errors[$required_field] = $required_field == 'title' ? 'dptext_admin_empty_title' : 'dp_dream_pages_empty_' . $required_field;

		if (trim($name) == '')
			$post_errors['page_name'] = 'dp_dream_pages_invalid_name';

		// Stop making numeric page names!
		if (is_numeric($name))
			$post_errors['page_name'] = 'dp_dream_pages_numeric';

		// Let's make sure you're not trying to make a page name that's already taken.
		$query = $smcFunc['db_query']('', '
			SELECT id_page, page_name
			FROM {db_prefix}dp_dream_pages
			WHERE page_name = {string:name} AND page_name != {string:real_name}',
			array(
				'name' => $name,
				'real_name' => $real_name,
			)
		);

		if ($smcFunc['db_num_rows']($query) !== 0 && $real_name != $name)
			$post_errors['page_name'] = 'dp_dream_pages_mysql';

		$smcFunc['db_free_result']($query);

		if (empty($post_errors))
		{
			$title = htmlentities(trim($title), ENT_QUOTES, $context['character_set']);

			// I see you made it to the final stage, my young padawan.
			if (!empty($id))
			{
				// Ok, looks like we're modifying, so let's edit the existing page!
				$smcFunc['db_query']('','
					UPDATE {db_prefix}dp_dream_pages
					SET page_name = {string:name}, type = {int:type}, title = {string:title}, permissions = {string:groups}, status = {int:status}, body = {string:body}
					WHERE id_page = {int:id}',
					array(
						'id' => (int) $id,
						'name' => $name,
						'type' => (int) $type,
						'title' => $title,
						'groups' => $groups,
						'status' => (int) $status,
						'body' => htmlspecialchars($body, ENT_QUOTES),
					)
				);
			}
			else
			{
				// Adding a brand new page? Ok!
				$smcFunc['db_insert']('insert',
					'{db_prefix}dp_dream_pages',
					array(
						'page_name' => 'string-255', 'type' => 'int', 'title' => 'string-255', 'permissions' => 'string-255', 'status' => 'int', 'body' => 'string',
					),
					array(
						$name, (int) $type, $title, $groups, (int) $status, htmlspecialchars($body, ENT_QUOTES),
					),
					array('id_page', 'page_name', 'id_button')
				);

				$iid = $smcFunc['db_insert_id']('{db_prefix}dp_dream_pages', 'id_page');

				// Checking if the page name or page id exists within Dream Menu, if so, update the Dream Page.
				$request = $smcFunc['db_query']('',
					'SELECT id_button, link 
					FROM {db_prefix}dp_dream_menu
					WHERE type = {int:is_empty}',
					array(
						'is_empty' => 0,
					)
				);

				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					$page_link = check_page_link($row['link']); 
					
					if ($page_link == $name || $page_link == $iid)
					{
						$update_button = $row['id_button'];
						break;
					}
				}

				$smcFunc['db_free_result']($request);

				if (isset($update_button))
					$smcFunc['db_query']('','
						UPDATE {db_prefix}dp_dream_pages
						SET id_button = {int:menu_button}
						WHERE id_page = {int:page_id}',
						array(
							'page_id' => $iid,
							'menu_button' => (int) $update_button,
						)
					);
			}

			redirectexit('action=admin;area=dppages;' . $context['session_var'] . '=' . $context['session_id']);
		}
		else
		{
			$context['post_error'] = $post_errors;
			$context['error_title'] = sprintf($txt['dp_dream_pages_errors_title'], empty($id) ? $txt['dp_creating'] : $txt['dp_modifying']);

			// Now create the editor.
			$editorOptions = array(
				'id' => 'body',
				'labels' => array(),
				'value' => $body,
				'height' => '250px',
				'width' => '100%',
				'preview_type' => 2,
				'rich_active' => false,
			);

			// Needed for ListGroups()
			require_once($sourcedir . '/ManageDPLayouts.php');

			// Needed for the editor.
			require_once($sourcedir . '/Subs-Editor.php');

			$context['page_data'] = array(
				'page_name' => $name,
				'real_page_name' => $real_name,
				'type' => $type,
				'title' => htmlentities(trim($title), ENT_QUOTES, $context['character_set']),
				'permissions' => ListGroups(!empty($_POST['permissions']) ? $_POST['permissions'] : array()),
				'status' => $status,
				'id' => $id,
			);

			create_control_richedit($editorOptions);
			$context['page_content'] = $editorOptions['id'];
			$context['page_title'] = !empty($id) ? $txt['dp_dream_pages_edit_title'] : $txt['dp_dream_pages_add_title'];
		}
	}
	else
	{
		// Needed for ListGroups()
		require_once($sourcedir . '/ManageDPLayouts.php');

		// Needed for the editor.
		require_once($sourcedir . '/Subs-Editor.php');

		// Now create the editor.
		$editorOptions = array(
			'id' => 'body',
			'labels' => array(
			),
			'height' => '250px',
			'width' => '100%',
			'preview_type' => 2,
			'rich_active' => false,
		);

		if (isset($_GET['pid']))
		{
			$request = $smcFunc['db_query']('', '
				SELECT page_name, type, title, body, permissions, status
				FROM {db_prefix}dp_dream_pages
				WHERE id_page = {int:page}
				LIMIT 1',
				array(
					'page' => (int) $_GET['pid'],
				)
			);

			// If nothing gets returned, exit... right now.
			if ($smcFunc['db_num_rows']($request) == 0)
				fatal_lang_error($txt['dp_dream_pages_not_found']);

			$row = $smcFunc['db_fetch_assoc']($request);

			$context['page_data'] = array(
				'page_name' => $row['page_name'],
				'real_page_name' => $row['page_name'],
				'type' => $row['type'],
				'title' => $row['title'],
				'permissions' => ListGroups(explode(',', $row['permissions'])),
				'status' => $row['status'],
				'id' => $_GET['pid'],
			);

			$editorOptions['value'] = $row['body'];

			$context['page_title'] = $txt['dp_dream_pages_edit_title'];
		}
		else
		{
			$context['page_data'] = array(
				'page_name' => '',
				'real_page_name' => '',
				'type' => 2,
				'title' => '',
				'permissions' => ListGroups(array('-3')),
				'status' => 1,
				'id' => 0,
			);

			$editorOptions['value'] = '';

			$context['page_title'] = $txt['dp_dream_pages_add_title'];
		}

		create_control_richedit($editorOptions);
		$context['page_content'] = $editorOptions['id'];
	}
}

/**
 * Loads the list of Dream Pages for createList().
 *
 * @param int $start determines where to start getting pages. Used in SQL's LIMIT clause.
 * @param int $items_peer_page determines how many pages are returned. Used in SQL's LIMIT clause.
 * @param string $sort determines which column to sort by. Used in SQL's ORDER BY clause.
 * @return array the associative array returned by $smcFunc['db_fetch_assoc']().
 * @since 1.0
 */
function list_getPages($start, $items_per_page, $sort)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT dpp.id_page, dpp.page_name, dpp.type, dpp.title, dpp.page_views, dpp.status
		FROM {db_prefix}dp_dream_pages AS dpp
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$dp_pages = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$dp_pages[] = $row;

	$smcFunc['db_free_result']($request);

	return $dp_pages;
}

/**
 * Gets the total number of Dream Pages for createList().
 *
 * @return int the total number of Dream Pages
 * @since 1.0
 */
function list_getNumPages()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS num_pages
		FROM {db_prefix}dp_dream_pages',
		array(
		)
	);

	list ($numPages) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numPages;
}

function DreamPageSettings($return_config = false)
{
	global $context, $txt, $scripturl;

	$config_vars = array(
		array('check', 'dp_pages_maintenance_mode', 'help' => 'dp_pages_maintenance_mode_help'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']) && !empty($config_vars))
	{
		checkSession();

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=dppages;sa=dppagesettings;' . $context['session_var'] . '=' . $context['session_id']);
	}

	$context['page_title'] = $txt['dp_admin_pagesettings_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=dppages;save;sa=dppagesettings';
	$context['settings_title'] = $txt['dp_dream_page_settings_header'];

	prepareDBSettingContext($config_vars);
}

?>