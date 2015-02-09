<?php
/**************************************************************************************
* ManageDPMenu.php																	  *
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

// Main function for Managing the Dream Menu!
function dpManageMenu()
{
	global $context, $txt, $sourcedir, $modSettings;

	// They need to have permission for this first!  Admins already have permission, so no need to check the admin_forum permission.
	if (!allowedTo('admin_dpmenu') || empty($modSettings['dp_portal_mode']))
		redirectexit();

	// checksession won't work from here for Menu buttons linking to here, so we use validateSession for now, and checkSession gets used later on in the code where it is needed!
	validateSession();

	$subActions = array(
		'dpmanmenu' => 'ManageDreamMenu',
		'dpaddbutton' => 'DreamMenuAddEdit',
		'dpmenusettings' => 'DreamMenuSettings',
	);

	// Default to sub action 'dpmanmenu'
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'dpmanmenu';

	if ($_GET['sa'] == 'dpmenusettings')
	{
		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
	}
	else
		loadTemplate('ManageDPMenu');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['dp_admin_dream_menu'],
		'help' => 'dp_admin_menu_help',
		'description' => $txt['dp_admin_menu_desc'],
		'tabs' => array(
			'dpmanmenu' => array(
				'description' => $txt['dp_admin_manage_menu_desc'],
			),
			'dpaddbutton' => array(
				'description' => $txt['dp_admin_menu_add_button_desc'],
			),
			'dpmenusettings' => array(
				'description' => $txt['dp_admin_menu_settings_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_GET['sa']]();

}

// Manages existing Dream Menu item Buttons.
function ManageDreamMenu()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	// Validate the session.
	validateSession();

	// Get rid of all of em!
	if (!empty($_POST['removeAll']))
	{
		// Just in case...
		checkSession('request');

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}dp_dream_menu',
			array(
			)
		);
		
		// Update all Dream Pages, removing Dream Menu button associations, if any.
		$smcFunc['db_query']('','
			UPDATE {db_prefix}dp_dream_pages
			SET id_button = {int:is_zero}
			WHERE id_button != {int:is_zero}',
			array(
				'is_zero' => 0,
			)
		);
	}

	// User pressed the 'remove selection button'.
	if (!empty($_POST['removeButtons']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		// Just in case...
		checkSession('request');

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $menu_id)
			$_POST['remove'][(int) $index] = (int) $menu_id;

		// Delete the Menu Buttons!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}dp_dream_menu
			WHERE id_button IN ({array_int:button_list})',
			array(
				'button_list' => $_POST['remove'],
			)
		);

		// Update the Dream Page Menu button associations for buttons being removed.
		$smcFunc['db_query']('','
			UPDATE {db_prefix}dp_dream_pages
			SET id_button = {int:is_zero}
			WHERE id_button IN ({array_int:button_list})',
			array(
				'is_zero' => 0,
				'button_list' => $_POST['remove'],
			)
		);

		redirectexit('action=admin;area=dpmenu;' . $context['session_var'] . '=' . $context['session_id']);
	}

	loadLanguage('ManageBoards');

	// Our options for our list.
	$listOptions = array(
		'id' => 'dp_menu_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=dpmenu;sa=dpmanmenu',
		'default_sort_col' => 'id_button',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => 'list_getMenu',
		),
		'get_count' => array(
			'function' => 'list_getNumButtons',
		),
		'no_items_label' => $txt['dp_dream_menu_no_buttons'],
		'columns' => array(
			'id_button' => array(
				'header' => array(
					'value' => $txt['dp_dream_menu_button_id'],
				),
				'data' => array(
					'db' => 'id_button',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.id_button',
					'reverse' => 'men.id_button DESC',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['dptext_admin_title'],
				),
				'data' => array(
					'db' => 'name',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.name',
					'reverse' => 'men.name DESC',
				),
			),
			'position' => array(
				'header' => array(
					'value' => $txt['dp_dream_menu_button_position'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt, $context;

						return isset($rowData[\'parent_name\']) ? \'<strong>\' . $txt[\'mboards_order_\' . $rowData[\'position\']] . \'</strong><br />\' . $rowData[\'parent_name\'] : \'<span class="error">\' . $txt[\'dream_menu_parent_unavailable\'] . \'</span>\';
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.position',
					'reverse' => 'men.position DESC',
				),
			),
			'type' => array(
				'header' => array(
					'value' => $txt['dptext_admin_type'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						$type = empty($rowData[\'type\']) ? \'dreampage\' : ($rowData[\'type\'] == 1 ? \'forum\' : \'external\');
						
						return $txt[\'dpdm_\' . $type . \'_link\'];
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.type',
					'reverse' => 'men.type DESC',
				),
			),
			'link' => array(
				'header' => array(
					'value' => $txt['dp_dream_menu_button_link'],
				),
				'data' => array(
					'db_htmlsafe' => 'link',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.link',
					'reverse' => 'men.link DESC',
				),
			),
			'page_name' => array(
				'header' => array(
					'value' => $txt['dp_dream_menu_dream_page_assoc'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt, $scripturl;

						$check_page =  check_page_link($rowData[\'link\']);

						if ($check_page != \'\')
							$menu_link = menu_page_link($check_page);
						
						if (!empty($menu_link[\'id\']) && !empty($menu_link[\'name\']))
							$data = \'<a href="\' . $scripturl . \'?page=\' . $menu_link[\'name\'] . \'" target="_blank" title="\' . $txt[\'dp_dream_menu_page_name\'] . \'">\' . $menu_link[\'name\'] . \'</a><br /><a href="\' . $scripturl . \'?page=\' . $menu_link[\'id\'] . \'" target="_blank" title="\' . $txt[\'dp_dream_menu_page_id\'] . \'">\' . $menu_link[\'id\'] . \'</a>\';
						else
							$data = \'<span style="color: red;">\' . $txt[\'dp_dream_menu_not_dream_page\'] . \'</span>\';

						return $data;
					'),
					'class' => 'centertext',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['dptext_admin_status'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// Tell them the status of their button.
						if (!empty($rowData[\'status\']))
							return sprintf(\'<span style="color: green;">%1$s</span>\', $txt[\'dptext_admin_active\']);
						else
							return sprintf(\'<span style="color: red;">%1$s</span>\', $txt[\'dptext_admin_nonactive\']);
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.status',
					'reverse' => 'men.status DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['dp_dream_menu_actions'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=dpmenu;sa=dpaddbutton;edit;bid=%1$d;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_button' => false,
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
							'id_button' => false,
						),
					),
					'class' => 'centertext',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=dpmenu;sa=dpmanmenu;' . $context['session_var'] . '=' . $context['session_id'],
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="removeButtons" value="' . $txt['dp_dream_menu_remove_selected'] . '" onclick="return confirm(\'' . $txt['dp_dream_menu_remove_confirm'] . '\');" class="button_submit" />
					<input type="submit" name="removeAll" value="' . $txt['dp_dream_menu_remove_all'] . '" onclick="return confirm(\'' . $txt['dp_dream_menu_remove_all_confirm'] . '\');" class="button_submit" />',
					'class' => 'righttext',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['page_title'] = $txt['dp_admin_menu_manage_title'];
	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'dp_menu_list';
}

// Prepares the context for Adding or Editing a Dream Menu item button.
function DreamMenuAddEdit()
{
	global $context, $smcFunc, $txt, $sourcedir;

	// Load all of the Menu Buttons!
	setupMenuContext();

	// Saving...
	if (isset($_REQUEST['submit']))
	{
		// Just in case...
		checkSession('post');

		$post_errors = array();
		$required_fields = array(
			'name',
			'link',
			'parent',
		);

		if (isset($_REQUEST['menu_mode'], $_REQUEST['dream_pages']) && $_REQUEST['menu_mode'] == 'dream_page')
		{
			$auto_page = explode('::', $_REQUEST['dream_pages']);
			$link = 'index.php?page=' . $auto_page[0];
			$dream_page = array('name' => $auto_page[0], 'id' => $auto_page[1]);
		}
		else
			$link = isset($_REQUEST['link']) ? $_REQUEST['link'] : '';

		// Make sure we grab all of the content
		$id = isset($_REQUEST['bid']) ? (int) $_REQUEST['bid'] : '';
		$position = isset($_REQUEST['position']) ? $_REQUEST['position'] : '';
		$type = !empty($_REQUEST['type']) ? (int) $_REQUEST['type'] : 0;
		$page = check_page_link($link);
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$permissions = isset($_REQUEST['permissions']) ? implode(',', $_REQUEST['permissions']) : '';
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 0;
		$parent = isset($_REQUEST['parent']) ? $_REQUEST['parent'] : '';
		$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : 0;

		// These fields are required!
		foreach ($required_fields as $required_field)
		{
			if (!isset($_POST[$required_field]) || trim($_POST[$required_field]) == '')
				if (($required_field == 'link' && !isset($dream_page)) || $required_field != 'link')
					$post_errors[$required_field] = $required_field == 'name' ? 'dptext_admin_empty_title'  : 'dp_dream_menu_empty_' . $required_field;
		}

		// No numeric names!
		if (is_numeric($name))
			$post_errors['name'] = 'dp_dream_menu_numeric';

		// So let's check it to be sure it is an actual dream page.
		// And this tells us if the page is found in the dp_dream_pages table also!
		if (!isset($dream_page))
			$dream_page = menu_page_link($page);

		// Check to be sure if it's a dream page that the dream page isn't already linked into the menu!
		if (!empty($dream_page['id']) && $page != '')
		{
			$result = $smcFunc['db_query']('', '
				SELECT dm.id_button, dm.name
				FROM {db_prefix}dp_dream_pages AS dp
				INNER JOIN {db_prefix}dp_dream_menu AS dm ON (dm.id_button = dp.id_button)
				WHERE LOWER(dp.page_name) = LOWER({string:dream_page_name}) AND dp.id_page = {int:dream_page_id}' . (!empty($id) ? ' AND LOWER(dp.page_name) != LOWER({string:curr_dream_page_name}) AND dp.id_page != {int:curr_dream_page_id}' : '') . '
				LIMIT 1',
				array(
					'dream_page_name' => $dream_page['name'],
					'dream_page_id' => $dream_page['id'],
					'curr_dream_page_name' => isset($_POST['dream_page_name']) ? $_POST['dream_page_name'] : '',
					'curr_dream_page_id' => isset($_POST['dream_page_id']) ? $_POST['dream_page_id'] : 0,
				)
			);

			if ($smcFunc['db_num_rows']($result) != 0)
			{
				list ($id_button, $title) = $smcFunc['db_fetch_row']($result);
				$post_errors['dream_page'] = sprintf($txt['dp_dream_menu_dream_page'], $scripturl . '?action=admin;area=dpmenu;sa=dpaddbutton;edit;bid=' . $id_button . ';' . $context['session_var'] . '=' . $context['session_id'], $title);
			}

			$smcFunc['db_free_result']($result);
		}

		if (empty($post_errors))
		{
			// Override the type if a Dream Page is found in the Forum Link type.
			if ($page != '')
				$type = 0;

			// I see you made it to the final stage, my young padawan.
			if (!empty($id))
			{
				// Ok, looks like we're modifying, so let's edit the existing Menu!
				$smcFunc['db_query']('','
					UPDATE {db_prefix}dp_dream_menu
					SET name = {string:name}, type = {string:type}, target = {string:target}, position = {string:position}, link = {string:link}, status = {int:status}, permissions = {string:permissions}, parent = {string:parent}, slug = {string:slug}, is_txt = {int:zero}
					WHERE id_button = {int:id}',
					array(
						'id' => (int) $id,
						'name' => htmlspecialchars($name),
						'type' => $type,
						'target' => $target,
						'position' => $position,
						'link' => $link,
						'status' => $status,
						'permissions' => $permissions,
						'parent' => $parent,
						'slug' => 'dpdm_' . $id,
						'zero' => 0,
					)
				);

				$curr_id = !empty($_POST['dream_page_id']) ? (int) $_POST['dream_page_id'] : 0;

				// Need to update dp_dream_pages table here as well.
				if ($curr_id != 0)
				{
					// It's associated with a Dream Page already, remove association if it's not the same.
					if ($curr_id != $dream_page['id'])
					{
						// Add 0 to the current page's id_button
						$smcFunc['db_query']('','
							UPDATE {db_prefix}dp_dream_pages
							SET id_button = {int:is_zero}
							WHERE id_page = {int:page_id} AND id_button = {int:id_button}',
							array(
								'is_zero' => 0,
								'id_button' => (int) $id,
								'page_id' => $curr_id,
							)
						);
					}
				}

				// Is it being set to another Dream Page?
				if (!empty($dream_page['id']))
				{
					$smcFunc['db_query']('','
						UPDATE {db_prefix}dp_dream_pages
						SET id_button = {int:id_button}
						WHERE id_button = {int:is_zero} AND id_page = {int:page_id}',
						array(
							'is_zero' => 0,
							'id_button' => $id,
							'page_id' => (int) $dream_page['id'],
						)
					);
				}
				redirectexit('action=admin;area=dpmenu;' . $context['session_var'] . '=' . $context['session_id']);
			}
			else
			{
				// Adding a brand new button?
				$smcFunc['db_insert']('insert',
					'{db_prefix}dp_dream_menu',
						array(
							'name' => 'string', 'type' => 'string', 'target' => 'string', 'position' => 'string', 'link' => 'string', 'status' => 'int', 'permissions' => 'string', 'parent' => 'string',
						),
						array(
							htmlspecialchars($name), $type, $target, $position, $link, $status, $permissions, $parent,
						),
						array('id_button')
					);

				// Grab the inserted Dream Menu id and add it to the slug!
				$iid = $smcFunc['db_insert_id']('{db_prefix}dp_dream_menu', 'id_button');
	
				$smcFunc['db_query']('','
					UPDATE {db_prefix}dp_dream_menu
					SET slug = {string:slug}
					WHERE id_button = {int:id}',
					array(
						'id' => (int)$iid,
						'slug' => 'dpdm_' . $iid,
					)
				);

				if ($page != '' && empty($dream_pages['id']))
				{
					$smcFunc['db_query']('','
						UPDATE {db_prefix}dp_dream_pages
						SET id_button = {int:id_button}
						WHERE id_button = {int:is_zero} AND ' . (is_numeric($page) ? 'id_page = {int:page}' : 'LOWER(page_name) = LOWER({string:page})') . '
						LIMIT 1',
						array(
							'id_button' => (int) $iid,
							'is_zero' => 0,
							'page' => $page,
						)
					);
				}
				
				
				redirectexit('action=admin;area=dpmenu;' . $context['session_var'] . '=' . $context['session_id']);
			}
		}
		else
		{
			$context['post_error'] = $post_errors;
			$context['error_title'] = sprintf($txt['dp_dream_menu_errors_title'], empty($id) ? $txt['dp_creating'] : $txt['dp_modifying']);

			// Needed for ListGroups()
			require_once($sourcedir . '/ManageDPLayouts.php');

			$context['button_data'] = array(
				'name' => htmlspecialchars($name),
				'type' => $type,
				'target' => $target,
				'position' => $position,
				'link' => $link,
				'parent' => $parent,
				'permissions' => ListGroups(!empty($_POST['permissions']) ? $_POST['permissions'] : array()),
				'status' => $status,
				'id' => $id,
				'slug' => !empty($_POST['slug']) ? $_POST['slug'] : '',
			);
			
			if (!empty($dream_page['id']) && !empty($dream_page['name']))
				$context['button_data']['dream_page'] = array(
					'curr_id' => !empty($_POST['dream_page_id']) ? (int) $_POST['dream_page_id'] : 0,
					'curr_name' => !empty($_POST['dream_page_name']) ? (string) $_POST['dream_page_name'] : '',
					'id' => !empty($dream_page['id']) ? (int) $dream_page['id'] : 0,
					'name' => !empty($dream_page['name']) ? (string) $dream_page['name'] : '',
				);

			if (isset($_REQUEST['dreampage_ids']))
			{
				$context['dpage_ids'] = explode(',', $_REQUEST['dreampage_ids']);

				// Only integer values should be in here!
				foreach(array_keys($context['dpage_ids']) as $value)
					$context['dpage_ids'][$value] = (int) $context['dpage_ids'][$value];

				$context['dream_pages'] = dp_listPages($context['dpage_ids']);
			}

			$context['page_title'] = !empty($id) ? $txt['dp_dream_menu_modify_title'] : $txt['dp_dream_menu_add_title'];
		}
	}
	else
	{
		// Needed for ListGroups()
		require_once($sourcedir . '/ManageDPLayouts.php');

		// Modifying...
		if (!empty($_GET['bid']))
		{
			// Seems like we grab too much tho, might be able to shorten this based on some sort of criteria, check it out later.
			$request = $smcFunc['db_query']('', '
				SELECT dm.id_button, dm.name, dm.target, dm.type, dm.position, dm.link, dm.status, dm.permissions, dm.parent, dm.slug, dm.is_txt,
					dp.id_page, dp.page_name, dp.title, dp.id_button AS dpage_id_button
				FROM {db_prefix}dp_dream_menu as dm
				LEFT JOIN {db_prefix}dp_dream_pages as dp ON (dp.id_button = {int:is_zero} || dp.id_button = {int:button_id})',
				array(
					'is_zero' => 0,
					'button_id' => (int) $_GET['bid'],
				)
			);

			$context['dpage_ids'] = array();
			$context['dream_pages'] = array();
			
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if ($row['id_button'] == (int) $_GET['bid'])
				{
					$context['button_data'] = array(
						'id' => $_GET['bid'],
						'name' => !empty($row['is_txt']) ? $txt[$row['name']] : $row['name'],
						'target' => $row['target'],
						'type' => !empty($row['type']) ? (int) $row['type'] : 0,
						'position' => $row['position'],
						'permissions' => ListGroups(explode(',', $row['permissions'])),
						'link' => $row['link'],
						'status' => !empty($row['status']) ? (int) $row['status'] : 0,
						'parent' => $row['parent'],
						'slug' => $row['slug'],
					);
					
					$page = check_page_link($row['link']);
					
					if ($page != '')
					{
						$page_array = menu_page_link($page);
						if ($page_array['id'] != 0 && $page_array['name'] != '')
							$context['button_data']['dream_page'] = array_merge($page_array, array('curr_id' => $page_array['id'], 'curr_name' => $page_array['name']));
						
						$context['button_data']['type'] = 0;
					}
					
					if (!is_null($row['id_page']))
					{
						$context['dpage_ids'][$row['id_page']] = $row['id_page'];

						if (!isset($context['dream_pages'][$row['id_page']]))
							$context['dream_pages'][$row['id_page']] = array(
								'id' => $row['id_page'],
								'value' => $row['page_name'] . '::' . $row['id_page'],
								'title' => $row['title'],
							);
					}
				}
				else
					if (!is_null($row['id_page']))
					{
						if ($row['dpage_id_button'] == 0)
						{
							$context['dpage_ids'][$row['id_page']] = $row['id_page'];

							if (!isset($context['dream_pages'][$row['id_page']]))
								$context['dream_pages'][$row['id_page']] = array(
									'id' => $row['id_page'],
									'value' => $row['page_name'] . '::' . $row['id_page'],
									'title' => $row['title'],
								);
						}
					}
			}
			$smcFunc['db_free_result']($request);

			$context['page_title'] = $txt['dp_dream_menu_modify_title'];
		}
		else
		{
			// Grab the list of available Dream Pages...
			$request = $smcFunc['db_query']('', '
				SELECT id_page, page_name, title
				FROM {db_prefix}dp_dream_pages
				WHERE id_button = {int:is_zero}
				ORDER BY NULL',
				array(
					'is_zero' => 0,
				)
			);
			$context['dpage_ids'] = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$context['dpage_ids'][$row['id_page']] = $row['id_page'];
				$context['dream_pages'][] = array(
					'id' => $row['id_page'],
					'value' => $row['page_name'] . '::' . $row['id_page'],
					'title' => $row['title'],
				);
				
			}

			$smcFunc['db_free_result']($request);
			
			if (!empty($context['dream_pages']))
				$dream_page = explode('::', $context['dream_pages'][0]['value']);
			

			$context['button_data'] = array(
				'name' => '',
				'link' => isset($dream_page) ? 'index.php?page=' . $dream_page[0] : '',
				'target' => '_self',
				'type' => !empty($context['dream_pages']) ? 0 : 1,
				'position' => 'before',
				'status' => 1,	// Status set to active by default.
				'permissions' => ListGroups(array('-3'), array(), array(), 0),
				'parent' => 'home',
				'id' => 0,
				'slug' => '',
			);

			$context['page_title'] = $txt['dp_dream_menu_add_title'];
		}

		if (isset($context['dream_pages']))
			uasort($context['dream_pages'], create_function('$a,$b','return strnatcmp($a[\'title\'], $b[\'title\']);'));
	}
}

function dp_listPages($dream_pages = array())
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_page, page_name, title
		FROM {db_prefix}dp_dream_pages
		' . (!empty($dream_pages) ? 'WHERE id_page IN ({array_int:curr_dreampage_ids})' : '') . '
		ORDER BY NULL',
		array(
			'curr_dreampage_ids' => $dream_pages,
		)
	);

	$return = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[] = array(
			'id' => $row['id_page'],
			'value' => $row['page_name'] . '::' . $row['id_page'],
			'title' => $row['title'],
		);
	}

	// Reorder it by Title, in Ascending order.
	if (!empty($return))
		uasort($return, create_function('$a,$b','return strnatcmp($a[\'title\'], $b[\'title\']);'));

	return $return;
}

function list_getMenu($start, $items_per_page, $sort)
{
	global $smcFunc, $txt, $scripturl, $context;

	$request = $smcFunc['db_query']('', '
		SELECT id_button, slug, name, target, type, position, link, status, permissions, parent, is_txt
		FROM {db_prefix}dp_dream_menu AS men
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$dream_menu = array();
	$temp = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$dream_menu[$row['id_button']] = array(
			'id_button' => $row['id_button'],
			'slug' => $row['slug'],
			'name' => !empty($row['is_txt']) ? $txt[$row['name']] : $row['name'],
			'target' => $row['target'],
			'type' => $row['type'],
			'position' => $row['position'],
			'link' => $row['link'],
			'status' => $row['status'],
			'permissions' => $row['permissions'],
			'parent' => $row['parent'],
		);
		$temp[$row['slug']] = !empty($row['is_txt']) ? $txt[$row['name']] : $row['name'];
	}

	// We just need all Non-DreamMenu buttons from the Menu!
	$context['dp_not_dream_menu'] = true;
	$menu_array = setupMenuContext();

	// Let's make it easier to read if possible!  Note:  array_keys is better for large arrays.
	foreach(array_keys($dream_menu) as $data)
	{
		if (isset($temp[$dream_menu[$data]['parent']]))
			$dream_menu[$data] += array('parent_name' => '<span class="smalltext" style="color: green;">' . $temp[$dream_menu[$data]['parent']] . '</span>');
		else
		{
			// We have an SMF Menu Button Parent, set the parent_name to the title text, this works for ALL SMF Languages installed automatically!
			if (isset($menu_array[$dream_menu[$data]['parent']]))
				$dream_menu[$data] += array('parent_name' => '<span class="smalltext" style="color: red;">' . $menu_array[$dream_menu[$data]['parent']] . '</span>');
		}
	}

	return $dream_menu;
}

function list_getNumButtons()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}dp_dream_menu',
		array(
		)
	);

	list ($numButtons) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numButtons;
}

function DreamMenuSettings($return_config = false)
{
	global $context, $txt, $scripturl, $modSettings;

	$config_vars = array(
			array('check', 'dp_menu_maintenance_mode', 'help' => 'dp_menu_maintenance_mode_help'),
		'',
			array('text', 'dp_home_menu_title', 'text_label' => sprintf($txt['dp_home_menu_title'], empty($modSettings['dp_disable_homepage']) ? $txt['dp_homepage'] : $txt['dp_boardindex']), 'help' => 'dp_home_menu_title_help'),
	);

	if ($return_config)
		return $config_vars;

	// Setup the default Homepage Menu Button title.
	if (empty($modSettings['dp_home_menu_title']))
		$modSettings['dp_home_menu_title'] = $txt['home'];

	// Adding in the Board Index forum Menu Button title for editing only if DP Homepage is enabled!
	if (empty($modSettings['dp_disable_homepage']))
	{
		$config_vars[] = array('text', 'dp_forum_menu_title', 'help' => 'dp_forum_menu_title_help');

		// Setup the default Forum Menu Button title.
		if (empty($modSettings['dp_forum_menu_title']))
			$modSettings['dp_forum_menu_title'] = $txt['forum'];
	}

	// Saving?
	if (isset($_GET['save']) && !empty($config_vars))
	{
		checkSession();

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=dpmenu;sa=dpmenusettings;' . $context['session_var'] . '=' . $context['session_id']);
	}

	$context['page_title'] = $txt['dp_admin_menusettings_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=dpmenu;save;sa=dpmenusettings';
	$context['settings_title'] = $txt['dp_dream_menu_settings_header'];

	prepareDBSettingContext($config_vars);
}

?>