<?php
/**************************************************************************************
* ManageDPSettings.php                                                                *
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

function loadGeneralSettingParameters($subActions = array(), $defaultAction = '')
{
	global $context, $txt, $sourcedir;

	checkSession('request');

	loadLanguage('DreamHelp+ManageSettings');
	loadTemplate('ManageDPSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	$context['sub_template'] = 'show_settings';

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));
	$context['sub_action'] = $_REQUEST['sa'];
}

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function dpManageSettings()
{
	global $context, $txt, $modSettings, $settings;

	// Is DP enabled? Are you an admin?
	if (empty($modSettings['dp_portal_mode']) || !allowedTo('admin_forum'))
		redirectexit();

	$subActions = array(
		'dpinfo' => 'DreamPortalInfo',
		'dpconfig' => 'ModifyDPConfig',
	);

	loadGeneralSettingParameters($subActions, 'dpinfo');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['dp_admin_general_info_config'],
		'help' => 'dp_admin_general_help',
		'tabs' => array(
			'dpinfo' => array(
				'description' => $txt['dp_admin_general_info_desc'],
			),
			'dpconfig' => array(
				'description' => $txt['dp_admin_general_config_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

/**
 * Handles the Dream Portal information tab.
 *
 * @since 1.0
 */
function DreamPortalInfo()
{
	global $context, $txt, $scripturl, $sourcedir, $boarddir, $portal_ver, $forum_version;

	// Needed to get forum admins. (temporary placeholder)
	require_once($sourcedir . '/Subs-Membergroups.php');

	if (listMembergroupMembers_Href($context['administrators'], 1, 32) && allowedTo('manage_membergroups'))
	{
		// Add a 'more'-link if there are more than 32.
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=moderate;area=viewgroups;sa=members;group=1">' . $txt['more'] . '</a>';
	}
	
	// For displaying the Dream Portal License.
	$context['dp_license_header'] = sprintf($txt['dp_license'], $portal_ver);
	$context['dp_license'] = htmlentities(file_get_contents($boarddir . '/dp_license.txt'), ENT_QUOTES, $context['character_set']);

	// Some much needed scripting ;)
	$context['html_headers'] .=  '
	<script type="text/javascript"><!-- // --><![CDATA[

		function setDPNews()
		{
			if (!dpNews || dpNews.length <= 0)
				return;

			var str = "<dl>";

			for (var i = 0; i < dpNews.length; i++)
			{
				str += "\n	<dt><a href=\"" + dpNews[i].url + "\" target=\"_blank\">" + dpNews[i].subject + "<\/a> ' . $txt['on'] . ' " + dpNews[i].time + "<\/dt>";
				str += "\n	<dd>"
				str += "\n		" + dpNews[i].message;
				str += "\n	<\/dd>";
			}

			str += "<\/dl>";
			
			setInnerHTML(document.getElementById("dpAnnouncements"), str);
		}

		function setDPVersion()
		{
			var installed_version = "' . $portal_ver . '";

			if (typeof(window.dpCurrentVersion) === "undefined" || !window.dpCurrentVersion)
				return;

			if (installed_version != window.dpCurrentVersion)
			{
				setInnerHTML(document.getElementById("dp_installed_version"), \'<span class="alert">' . $portal_ver . '<\/span>\');
				setInnerHTML(document.getElementById("dp_update_section"), ' . JavaScriptEscape('
					<span class="upperframe"><span><!-- // --></span></span>
						<div class="roundframe smalltext">
						<span class="error">' . $txt['dp_outdated'] . '</span>
						</div>
					<span class="lowerframe"><span><!-- // --></span></span>
				') . ');
			}

			setInnerHTML(document.getElementById("dp_latest_version"), window.dpCurrentVersion);
		}
	// ]]></script>';

	// Our credits info. =D
	$context['credits'] = array(
		array(
			'pretext' => $txt['dp_credits_info'],
			'groups' => array(
				array(
					'title' => $txt['dp_credits_groups_founder_title'],
					'members' => $txt['dp_credits_group_names']['founder'],
				),
				array(
					'title' => $txt['dp_credits_groups_owner_title'],
					'members' => $txt['dp_credits_group_names']['owner'],
				),
				array(
					'title' => $txt['dp_credits_groups_pm_title'],
					'members' => $txt['dp_credits_group_names']['pm'],
				),
				array(
					'title' => $txt['dp_credits_groups_dev_title'],
					'members' => $txt['dp_credits_group_names']['dev'],
				),
				array(
					'title' => $txt['dp_credits_groups_support_title'],
					'members' => $txt['dp_credits_group_names']['support'],
				),
				array(
					'title' => $txt['dp_credits_special'],
					'members' => $txt['dp_credits_group_names']['special'],
				),
				array(
					'title' => $txt['dp_credits_fam_fam'],
					'members' => array(
						$txt['dp_credits_fam_fam_message'],
					),
				),
				array(
					'title' => $txt['dp_credits_jscolor'],
					'members' => array(
						$txt['dp_credits_jscolor_message'],
					),
				),
			),
			'posttext' => $txt['dp_credits_anyone'],
		)
	);

	$context['page_title'] = $txt['dp_admin_general_info_title'];
	$context['sub_template'] = 'portal_info';
	$context['insert_after_template'] .= '<script type="text/javascript" src="http://news.dream-portal.net/news.js?v=' . urlencode($portal_ver) . ';smf_version=' . urlencode($forum_version) . '"></script>';
}

/**
 * Loads the general settings for Dream Portal so the admin can change them. Uses the sub template show_settings in Admin.template.php to display them.
 *
 * @param bool $return_config Determines whether or not to return the config array.
 * @return void|array The $config_vars if $return_config is true.
 * @since 1.0
 */
function ModifyDPConfig($return_config = false)
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$config_vars = array(
		array('check', 'dp_menu_mode', 'help' => 'dp_menu_mode_help'),
		array('check', 'dp_pages_mode', 'help' => 'dp_pages_mode_help'),
		'',
		array('int', 'dp_add_modules_limit', 'help' => 'dp_add_modules_limit_help'),
		array('int', 'dp_add_templates_limit', 'help' => 'dp_add_templates_limit_help'),
		array('int', 'dp_add_languages_limit', 'help' => 'dp_add_languages_limit_help'),
		'',
		array('check', 'dp_disable_copyright', 'help' => 'dp_disable_copyright_help'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']) && !empty($config_vars))
	{
		checkSession();

		// Not set?  Set to 0 than.
		if (!isset($modSettings['dp_disable_copyright']))
			$modSettings['dp_disable_copyright'] = 0;

		// Not set?  Well, than it must be enabled, set to 0!
		if (!isset($_POST['dp_disable_copyright']))
			$_POST['dp_disable_copyright'] = 0;

		if ($modSettings['dp_disable_copyright'] != $_POST['dp_disable_copyright'])
		{
			if (!empty($_POST['dp_disable_copyright']))
				remove_integration_function('integrate_buffer', 'dreamBuffer');
			else
				add_integration_function('integrate_buffer', 'dreamBuffer');
		}

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=dpgeneral;sa=dpconfig;' . $context['session_var'] . '=' . $context['session_id']);
	}

	$context['page_title'] = $txt['dp_admin_general_config_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=dpgeneral;save;sa=dpconfig';
	$context['settings_title'] = $txt['dp_admin_general_config'];

	prepareDBSettingContext($config_vars);
}

?>