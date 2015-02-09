<?php
/**************************************************************************************
* dp_ajax.php                                                                         *
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
require_once(dirname(__FILE__) . '/SSI.php');

// Make sure we have required the SSI.php, if not, hacking attempt.
if (!defined('SMF'))
	die('Hacking attempt...');

if (isset($_GET['check']))
{
	checkSession('get');

	if (!allowedTo(array('admin_forum', 'admin_dppages')))
		return;

	if (isset($_GET['pn']))
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-DreamPortal.php');

		$name = parseString(htmlspecialchars_decode($_GET['pn'], ENT_QUOTES), 'function_name');

		if (trim($_GET['pn']) == '' || trim($name) == '')
		{
			echo '';
			return;
		}

		// Let's make sure you're not trying to make a page name that's already taken.
		// If we are editing the current page, we don't want to include this page name ofcourse!
		$query = $smcFunc['db_query']('', '
			SELECT id_page
			FROM {db_prefix}dp_dream_pages
			WHERE page_name = {string:name} && id_page != {int:id_page}',
			array(
				'name' => $name,
				'id_page' => (int) $_GET['id'],
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$row = $smcFunc['db_fetch_assoc']($query);
		if ($check != 0 && empty($_GET['id']))
			$ret = $txt['dp_pages_ajax_navailable'] . '<br class="clear" /><div class="floatleft information">' . sprintf($txt['dp_pages_name_exists'], $name) . '</div>';
		elseif (!empty($_GET['id']) && !empty($row))
			$ret = $txt['dp_pages_ajax_navailable'] . '<br class="clear" /><div class="floatleft information">' . sprintf($txt['dp_pages_name_exists'], $name) . '</div>';
		else
			$ret = $txt['dp_pages_ajax_available'] . (trim($_GET['pn']) != $name ? '<br class="clear" /><div class="floatleft information">' . sprintf($txt['dp_pages_name_change'], $_GET['pn'], $name) . '</div>' : '');

		echo $ret;
	}
}
else
	redirectexit();

?>