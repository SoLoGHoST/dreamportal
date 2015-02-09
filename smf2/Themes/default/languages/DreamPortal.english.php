<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// DreamPortal.english.php; @1.1

global $scripturl, $context;

// General Strings
$txt['forum'] = 'Forum';
$txt['dream_portal'] = 'Dream Portal';
$txt['dp_core_modules'] = 'Collapse or Expand this Module';
$txt['dp_who_forum'] = 'Viewing the forum index of <a href="' . $scripturl . '?action=forum">' . $context['forum_name'] . '</a>.';
$txt['dp_who_portal'] = 'Viewing the portal index of <a href="' . $scripturl . '">' . $context['forum_name'] . '</a>.';
$txt['dp_who_page'] = 'Viewing the page &quot;<a href="' . $scripturl . '?page=%1$s">%2$s</a>&quot;.';
$txt['dp_submit'] = 'Submit';
$txt['dp_cancel'] = 'Cancel';
$txt['dp_no_permission'] = 'You do not have permission to access this section of Dream Portal.';

// Custom Dream Portal Module Title!
$txt['dpmod_custom'] = 'Custom';

// Default Dream Portal Template Title!
$txt['dptemp_default'] = 'Default';

// Admin Panel Strings
$txt['dp_admin_general'] = 'General';
$txt['dp_admin_general_info'] = 'Information';
$txt['dp_admin_general_config'] = 'Configuration';
$txt['dp_admin_layout_settings'] = 'Layout Settings';
$txt['dp_admin_dream_layouts'] = 'Dream Layouts';
$txt['dp_admin_extend'] = 'Extend Dream Portal';
$txt['dp_admin_add_modules'] = 'Add Modules';
$txt['dp_admin_add_templates'] = 'Add Templates';
$txt['dp_admin_add_languages'] = 'Add Languages';
$txt['dp_admin_manage_layouts'] = 'Manage Layouts';
$txt['dp_admin_dream_pages'] = 'Dream Pages';
$txt['dp_admin_add_dream_page'] = 'Add Page';
$txt['dp_admin_dream_page_settings'] = 'Page Settings';
$txt['dp_admin_dream_menu_settings'] = 'Menu Settings';
$txt['dp_admin_manage_dream_pages'] = 'Manage Pages';
$txt['dp_admin_dream_menu'] = 'Dream Menu';
$txt['dp_admin_add_dream_button'] = 'Add Button';
$txt['dp_admin_manage_dream_menu'] = 'Manage Menu';

// Core Features in the AdminCP
$txt['core_settings_item_dp_desc'] = 'Dream Portal allows you to add the portal of your dreams to your forum.';

/*
	Module error handling that can be used in YOUR modules (ANY MODULES WHATSOEVER)!
	Gets called from Subs-DreamPortal.php within the module_error() function.

	Example of how to use in your modules:
	--------------------------------------
	if (empty($user_info['id']))
	{
		module_error('not_allowed');
		return;
	}

*/
$txt['dp_module_mod_not_installed'] = 'Sorry, this mod hasn&#039;t been installed yet!';
$txt['dp_module_not_allowed'] = 'Access Denied!';
$txt['dp_module_no_language'] = 'Unable to load the Language file for this mod!';
$txt['dp_module_query_error'] = 'Error trying to obtain information for this mod.<br />Mod must be reinstalled!';
$txt['dp_module_empty'] = 'No content to display for this module!';
$txt['dp_module_error'] = 'There is an error with this module!';

// Dream Pages Errors
$txt['dp_pages_no_access_error'] = 'Error';
$txt['dp_pages_no_access'] = 'You are not allowed to view this page.';
$txt['dp_pages_not_exist'] = 'That page does not exist!';
$txt['dp_pages_ajax_available'] = ' is <span style="color: #009933">available!</span>';
$txt['dp_pages_ajax_navailable'] = ' is <span style="color: #CC0000">not available!</span>';
$txt['dp_pages_php_error'] = 'There is an error within the PHP Syntax on this page.';
$txt['dp_pages_name_exists'] = '<strong><em>%1$s</em></strong> already exists';
$txt['dp_pages_name_change'] = '<strong><em>%1$s</em></strong> will be renamed to <strong>%2$s</strong>';

// File Input Error
$txt['file_not_found'] = 'File Not Found.';

// Module's file or function can't be found.
// %1$s = the name of the module, %2$s = the location of the module.
$txt['dp_modfile_not_exist'] = 'The module %1$s is missing it&#039;s file which should be located at: %2$s';
$txt['dp_module_function_error'] = 'Unable to load up the function for this module.';

// Additional Errors
$txt['dp_error_occured'] = 'An Error Occured!';
$txt['dp_no_httpr'] = 'Your Browser does not support HTTP Requests!';
$txt['dp_unable_to_view_file'] = 'Sorry, but either Dream Portal is disabled, or you don\'t have permission to view/download this file.';

// PHP Syntax Errors
$txt['dp_parse_error'] = 'Parse Error';
$txt['dp_error_line'] = 'on line';

// Custom Module default values!
$txt['dp_custom_title_default'] = 'Dream Portal Installed!';
$txt['dp_custom_code_default'] = 'global $user_info, $portal_ver, $txt;
echo \'
	&lt;table&gt;
		&lt;tr&gt;
			&lt;td&gt;
				&lt;h1 class=&quot;largetext&quot; style=&quot;color:green;&quot;&gt;DREAM PORTAL HAS JUST BEEN INSTALLED&lt;/h1&gt;
			&lt;br /&gt;\';
if ($user_info[\'is_admin\'])
	echo \'
				&lt;p&gt;Congratulations on installing \', $txt[\'dream_portal\'], \' \', $portal_ver, \'. We hope you enjoy using Dream Portal as much as we did creating it.  Quickly visit our website to be able to extend Dream Portal even further, with custom-made, unique, modules, module templates that change the way modules appear, and language packs for your preferred language. There is so much that you can do now with just a few clicks. Find out more at &lt;a href=&quot;http://dream-portal.net&quot; target=&quot;_blank&quot; onfocus=&quot;if(this.blur)this.blur();&quot;&gt;Dream Portal dot net&lt;/a&gt;&lt;/p&gt;\';
else
	echo \'
				&lt;p&gt;We are in the process of updating our modules to fit this sites needs.  Please bear with us as we make a few changes to our homepage and throughout the forum.&lt;br /&gt;&lt;br /&gt;Thank You!&lt;/p&gt;\';

echo \'
			&lt;/td&gt;
			&lt;td valign=&quot;middle&quot; width=&quot;400&quot; align=&quot;center&quot;&gt;
				&lt;a href=&quot;http://dream-portal.net&quot;&gt;&lt;img src=&quot;http://dream-portal.net/images/dplogo.png&quot; border=&quot;0&quot; alt=&quot;\', $txt[\'dream_portal\'], \'&quot; title=&quot;\', $txt[\'dream_portal\'], \' - What Dreams are Made Of!&quot; /&gt;&lt;/a&gt;
			&lt;/td&gt;
		&lt;/tr&gt;
	&lt;/table&gt;\';';

?>