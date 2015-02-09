<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDP.english.php; @1.1

// FYI:  &#039; = ' (single quote character), could also just do this, instead:  \'

/********************************************************
	Manage General
*********************************************************/
// Page Titles, descriptions and section title.
$txt['dp_admin_general_info_title'] = 'Dream Portal - General Information';
$txt['dp_admin_general_info_desc'] = 'This section allows you to view announcements, live, from the <a href="http://dream-portal.net" target="_blank">DP.net</a> site, ensures you are up-to-date with the latest and greatest Dream Portal release, and, finally, displays the few and the proud who took part in helping to make this dream a reality.';
$txt['dp_admin_general_config_title'] = 'Dream Portal - General Configuration';
$txt['dp_admin_general_config_desc'] = 'Displays general configuration settings for Dream Portal.';
$txt['dp_admin_general_info_config'] = 'General Information &amp; Configuration';

// DP General Information Strings.
$txt['dp_admin_config_latest_news'] = 'Live from Dream Portal...';
$txt['dp_admin_config_unable_news'] = 'Unable to load the Dream Portal news file...';
$txt['dp_admin_config_support_info'] = 'Support Information';
$txt['dp_admin_config_version_info'] = 'Version Information';
$txt['dp_admin_config_installed_version'] = 'Installed Version';
$txt['dp_admin_config_latest_version'] = 'Latest Version';
$txt['dp_outdated'] = 'You are using an outdated version of Dream Portal. Please update to the latest version as soon as possible.';
$txt['dp_license'] = 'Dream Portal %1$s License';

// Credits...
$txt['dp_credits'] = 'Credits';
$txt['dp_credits_info'] = 'The Dream Portal Team would like to thank everyone who has made what Dream Portal is today. We also thank Simple Machines for making the software that this portal was built for as well as thanking YOU, the user, for using the portal.';
$txt['dp_credits_groups_founder_title'] = 'Founder';
$txt['dp_credits_groups_owner_title'] = 'Owner';
$txt['dp_credits_groups_pm_title'] = 'Project Managers';
$txt['dp_credits_groups_dev_title'] = 'Developers';
$txt['dp_credits_groups_support_title'] = 'Support Team';
$txt['dp_credits_special'] = 'Special Thanks';
$txt['dp_credits_fam_fam'] = 'Icons';
$txt['dp_credits_jscolor'] = 'JSColor';
$txt['dp_credits_jscolor_message'] = 'Jan Odv&aacute;rko for his <a href="http://jscolor.com/" target="_blank" onfocus="if(this.blur)this.blur();">JSColor project</a> released under the <a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" onfocus="if(this.blur)this.blur();">GNU Lesser General Public License</a>.';
$txt['dp_credits_fam_fam_message'] = 'Mark James for his <a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">Fam Fam Fam Silk Icons</a>.';
$txt['dp_credits_anyone'] = '<em>And for anyone we may have missed, thank you!</em>';
$txt['dp_credits_contribute'] = 'Do you like Dream Portal? Help us keep the Dream alive... <br /><table border="0" cellspacing="1" cellpadding="0" style="margin-top: 5px;"><tr><td valign="middle"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" style="border: none; background: transparent;" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" /></form></td><td valign="middle">and/or</td><td valign="middle">&nbsp;<a href="http://dream-portal.net/index.php?action=dp;area=join" target="_blank" onfocus="if(this.blur)this.blur();"><img src="http://dream-portal.net/images/btn_join_dream.png" border="0" alt="Join the Dream" style="border: none;" /></a></td></tr></table>';
$txt['dp_credits_group_names'] = array(
	'founder' => array('<span onclick="alert(\'THE FOUNDER\');">Chris &quot;ccbtimewiz&quot; Batista</span>'),
	'owner' => array('<span onclick="alert(\'THE DREAM MASTER\');">Solomon &quot;SoLoGHoST&quot; Closson</span>'),
	'pm' => array('<span onclick="alert(\'THE ONE AND ONLY\');">Draven &quot;Xarcell&quot; Vestatt</span>'),
	'dev' => array('<span onclick="alert(\'I shot THE SHERIFF, but I did not shoot THE DEPUTY!\');">Solomon &quot;SoLoGHoST&quot; Closson</span>', '<span onclick="alert(\'THE SHERIFF\');">John &quot;live627&quot; Rayes</span>', '<span onclick="alert(\'ITS WALUIGI TIME\');">Tyler &quot;tyty1234&quot; Asuncion</span>', '<span onclick="alert(\'DREAM MENU CREATOR!\');">Aldo &quot;hadesflames&quot; Barreras</span>', '<span onclick="alert(\'THE DEPUTY\');">Russell &quot;nend&quot; Najar</span>'),
	'support' => array('<span onclick="alert(\'THE MAN BEHIND THE CURTAIN\');">Willem &quot;willemjan&quot; Vries</span>', '<span onclick="alert(\'Russian Translator\');">Alexander &quot;Bugo&quot; Kordjukov</span>'),
	'special' => array('<span onclick="alert(\'THE ORIGINAL LD, NEVER to be FORGOTTEN!\');">Robert &quot;xero&quot; Stamm</span>', '<span onclick="alert(\'For his knowledge and assistance with setting up the original DP 1.1 Database structure!\');">necrit</span>', '<span onclick="alert(\'DP 1.0.x Language Coordinator\');">chilly</span>', '<span onclick="alert(\'THE MAGICIAN\');">DieWacht</span>', '<span onclick="alert(\'For his code and designs on the Layout Module Color changer.\');">Dave &quot;Shortie&quot; Malpas</span>', '<span onclick="alert(\'For his knowledge on a few ideas that were implemented in Dream Portal.\');">Steven &quot;Fustrate&quot; Hoffman</span>', '<span onclick="alert(\'THE OPINIONATOR\');">Arantor</span>', '<span onclick="alert(\'For his contributions in testing Dream Portal.\');">kcmartz</span>')
);

// DP General Configuration Strings.
$txt['dp_menu_mode'] = 'Enable Dream Menu';
$txt['dp_pages_mode'] = 'Enable Dream Pages';
$txt['dp_add_modules_limit'] = 'Modules listing Limit per page';
$txt['dp_add_templates_limit'] = 'Templates listing Limit per page';
$txt['dp_add_languages_limit'] = 'Languages listing Limit per page';
$txt['dp_disable_copyright'] = 'Disable Dream Portal copyright';

/**********************************************************
	Manage Dream Layouts
***********************************************************/
//	Just the basics.
$txt['dp_admin_manage_layouts_title'] = 'Dream Portal - Manage Layouts';
$txt['dp_homepage'] = 'Homepage';

//	Custom Module Strings
$txt['dpmodinfo_custom'] = 'Code that you can put into a module. There are three types to pick from: PHP, HTML, or BBC (Bulletin Board Code).';
$txt['dpmod_custom_code'] = 'Code';
$txt['dpmod_custom_code_type'] = 'Code Type';
$txt['dpmod_custom_code_type_PHP'] = 'PHP';
$txt['dpmod_custom_code_type_HTML'] = 'HTML';
$txt['dpmod_custom_code_type_BBC'] = 'BBC';

//	Some ALT attributes here!
$txt['dp_alt_module_colors'] = 'Module Colors';
$txt['dp_alt_white'] = 'White';
$txt['dp_alt_gray'] = 'Gray';
$txt['dp_alt_blue'] = 'Blue';
$txt['dp_alt_yellow'] = 'Yellow';
$txt['dp_alt_green'] = 'Green';
$txt['dp_alt_orange'] = 'Orange';
$txt['dp_alt_red'] = 'Red';
$txt['dp_alt_purple'] = 'Purple';
$txt['dp_alt_black'] = 'Black';

//	Manage Layouts
$txt['dp_admin_layouts_manmodules_desc'] = 'Manage your Dream Portal Layouts. You can drag modules into any column and/or disable them, from within that layout, by dragging them into the Disabled Modules section.  Modify the Parameters of these modules by clicking on the Modify link. Unchecking sections will disable the section within that specific layout.';
$txt['dp_admin_modules_manage_col_disabled'] = 'Disabled Modules';
$txt['dp_admin_modules_manage_col_section'] = 'Section';
$txt['dp_is_smf_section'] = 'SMF';
$txt['dp_admin_modules_manage_modify'] = 'Modify';
$txt['dp_admin_modules_manage_uninstall'] = '<span class="smalltext">Uninstall</span>';
$txt['dpmodule_uninstall_success'] = 'The module was successfully uninstalled!';
$txt['dpmodule_clone'] = 'Clone';
$txt['dpmodule_declone'] = 'Declone';
$txt['error_string'] = 'Error';
$txt['clone_made'] = 'The clone was made.';
$txt['clone_deleted'] = 'The clone was deleted.';
$txt['module_positions_saved'] = 'The module positions have been saved.';
$txt['click_to_close'] = 'Click to close this message.';
$txt['dp_module_colors'] = 'Modules background color selector.';
$txt['dp_admin_layout_disabled'] = '(Disabled)';

/*------------
Modify Modules
--------------*/

//	General Strings
$txt['dp_modify_mod'] = 'Dream Portal - Modify Modules';
$txt['dp_module_not_installed'] = 'Sorry, unable to retrieve the modules id value, please make sure this module is installed.';
$txt['dp_modsettings'] = '&nbsp;Settings';
$txt['dream_module_title'] = 'Module&#039;s Title<br /><span class="smalltext">(Can not be empty)</span>';
$txt['dream_module_icon'] = 'Module&#039;s Icon';
$txt['dream_module_link'] = 'Module&#039;s Title Link';
$txt['dream_module_link_target'] = 'Target';
$txt['dream_module_link_blank'] = '_blank';
$txt['dream_module_link_self'] = '_self';
$txt['dream_module_link_parent'] = '_parent';
$txt['dream_module_link_top'] = '_top';
$txt['dream_module_minheight'] = 'Module&#039;s Minimum Height';
$txt['dream_module_minheight_type'] = 'Unit';
$txt['dream_module_minheight_type_px'] = 'pixels (px)';
$txt['dream_module_minheight_type_percentage'] = 'percent (%)';
$txt['dream_module_minheight_type_em'] = 'M (em)';
$txt['dream_module_minheight_type_rem'] = 'root M (rem)';
$txt['dream_module_minheight_type_ex'] = 'X (ex)';
$txt['dream_module_minheight_type_pt'] = 'point (pt)';

$txt['no_icon'] = '(no icon)';
$txt['dream_module_template'] = 'Module&#039;s Template';
$txt['dream_module_groups'] = 'Membergroups that can view this module';
$txt['dream_module_header_display'] = 'Module&#039;s Header';
$txt['dream_module_disable'] = 'Disable';
$txt['dream_module_enabled'] = 'Enabled';
$txt['dream_module_collapse'] = 'Title Only';

//	File input handling
$txt['more_files_error'] = 'Sorry, you aren&#039;t allowed to add any more files.';
$txt['more_files'] = 'more files';

//	File error handling.
$txt['module_file_timeout'] = 'Sorry, file(s) timed-out while uploading.  Please try again.';
$txt['module_wrong_mime_type'] = 'You are not allowed to upload this mime-type: %1$s';
$txt['module_not_image_type'] = 'Valid image types are as follows: gif, jpg, jpe, jpeg, png, bmp, and/or wbmp';
$txt['module_file_limit'] = 'Sorry, you have reached the limit for file upload of this module settings.  Please go back and remove one of your uploads before you will be able to upload any more files.';
$txt['module_files_no_write'] = 'Unable to write to the modules directory.  Please make sure this path is writable!';
$txt['files'] = '<strong>Current Files:</strong>';
$txt['uncheck_unwanted_files'] = 'Uncheck files you no longer want associated with this setting';
$txt['mod_folder_missing'] = 'Unable to get the Module&#039;s folderpath, which should be located, relative to your SMF Root:';
$txt['module_folderpath_error'] = 'Unable to store files within this modules folderpath.';
$txt['restricted_unexists'] = 'Sorry, seems that either the file is restricted, or does not exist.';
$txt['file_timeout'] = 'File timed-out while uploading, please try again!';
$txt['file_bad_extension'] = 'Unable to upload that type of file.  Please try a different filetype.';

// List groups handling...
$txt['checks_order_up'] = 'Up';
$txt['checks_order_down'] = 'Down';

/*----------------------
LAYOUTS (Adding/Editing)
------------------------*/

$txt['add_layout_title'] = 'Dream Portal - Add Layout';
$txt['add_layout'] = 'Add Layout';
$txt['dp_layout_name'] = 'Layout Name';

$txt['dp_action_type'] = 'Actions';
$txt['select_smf_actions'] = 'Available SMF Actions';
$txt['select_user_defined_actions'] = 'User-defined';
$txt['select_user_defined_actions_desc'] = 'Encase any non-actions within brackets. For example: <i><strong>[board]</strong>, and <strong>[topic]</strong> will point to index.php?board and index.php?topic</i>';

$txt['dp_add_action'] = 'Add Action';
$txt['dp_remove_actions'] = 'Remove Action(s)';
$txt['layout_actions'] = 'Layout Actions';
$txt['layout_style'] = 'Layout Style';
$txt['layout_style_dream_portal'] = 'Default - Dream Portal';
$txt['layout_style_omega'] = 'Omega';
$txt['layout_sections'] = 'Layout Sections';

$txt['edit_layout'] = 'Edit Layout';
$txt['edit_layout_title'] = 'Dream Portal - Edit Layout';

$txt['delete_layout'] = 'Delete Layout';
$txt['confirm_delete_layout'] = 'Are you sure you want to delete the selected layout?';
$txt['no_layout_selected'] = 'Sorry, this layout doesn&#039;t exist.';
$txt['select_layout_to_delete'] = 'Select a layout to delete';

// Layout Errors
$txt['layout_error_header'] = 'The following error or errors occurred while adding your layout:';
$txt['edit_layout_error_header'] = 'The following error or errors occurred while editing your layout:';
$txt['dp_no_actions'] = 'No actions were defined within this layout.';
$txt['dp_no_sections'] = 'No sections were defined within this layout.';
$txt['dp_layout_exists'] = 'That layout name is already in use.';
$txt['dp_no_layout_name'] = 'Your layout must have a name.';
$txt['dp_layout_unknown'] = 'Unable to determine the layout type you selected.  Please go back and select your layout.';
$txt['cant_find_layout_id'] = 'Unable to edit this layout, either because there was no layout ID value supplied, or this was empty.';
$txt['dp_cant_delete_all'] = 'Sorry, you are unable to delete all columns and/or rows.  If this was your intention, than just Delete the Layout instead!';
$txt['dp_layout_invalid'] = 'Unable to edit this layout because of an inconsistent number of columns in each row.';

$txt['dp_smf_mod'] = 'SMF Content';
$txt['dp_edit'] = 'Edit';
$txt['dp_deleted'] = 'Deleted';
$txt['dp_restore'] = 'Restore';
$txt['dp_add_another'] = 'Add Another';
$txt['new_value'] = 'New Value';

// EDITING a LAYOUT...
$txt['dp_row'] = 'Row';
$txt['colspans'] = 'Colspans';
$txt['enabled'] = 'Enabled';
$txt['dp_columns_header'] = 'Columns';
$txt['dp_column'] = 'Column';
$txt['dp_add_column'] = 'Add a column at the end of';
$txt['dp_add_column_button'] = 'Add Column';
$txt['dp_add_row'] = 'Add Row';
$txt['confirm_remove_selected'] = 'Are you sure you want to remove the selected rows and/or columns?\n\nNote: The row that SMF is defined in can not be removed and there must always be atleast 1 other column besides SMF.  All empty rows will be removed also.';
$txt['dp_edit_remove_selected'] = 'Remove Selected';

/*-----------------
Layout Settings Tab
-------------------*/

// Description
$txt['dp_admin_config_layoutsettings_title'] = 'Dream Portal - Layout Settings';
$txt['dp_admin_config_layoutsettings_desc'] = 'Settings that apply, globally, to your Dream Layouts and/or Modules.';

// Module Settings titles
$txt['dp_disable_homepage'] = 'Disable Homepage Layout';
$txt['dp_module_header_height'] = 'Modules Expanded Header Height';
$txt['dp_module_header_height_legend'] = 'Theme-Based Heights';
$txt['dp_pixels'] = 'pixels';
$txt['dp_collapse_modules'] = 'Enable Collapsible Modules';
$txt['dp_module_display_style'] = 'Module Display Style';
$txt['dp_module_display_style_modular'] = 'Modular Style';
$txt['dp_module_display_style_blocks'] = 'Block Style';
$txt['dp_module_title_char_limit'] = 'Module Title Character Limit';
$txt['dp_module_enable_animations'] = 'Enable Module Animations';
$txt['dp_module_animation_speed'] = 'Module Animation Speed';
$txt['dp_animation_speed_veryslow'] = 'Very Slow';
$txt['dp_animation_speed_slow'] = 'Slow';
$txt['dp_animation_speed_normal'] = 'Normal';
$txt['dp_animation_speed_fast'] = 'Fast';
$txt['dp_animation_speed_veryfast'] = 'Very Fast';
$txt['dp_disable_custommod_icons'] = 'Do Not Install Custom Module Icons';
$txt['dp_enable_custommod_icons'] = 'Do Not Uninstall Custom Module Icons';
$txt['dp_icon_directory'] = 'Modules Icon directory';

/*******************************
	Dream Menu & Dream Pages
********************************/
$txt['dp_creating'] = 'creating';
$txt['dp_modifying'] = 'modifying';
$txt['dptext_admin_type'] = 'Type';
$txt['dptext_admin_title'] = 'Title';
$txt['dptext_admin_status'] = 'Status';
$txt['dp_admin_perms'] = 'Allowed Groups';
$txt['dptext_admin_active'] = 'Active';
$txt['dptext_admin_nonactive'] = 'Inactive';

// Submission Errors
$txt['dptext_admin_empty_title'] = 'The Title was left empty.';

/************************************
	Dream Menu
*************************************/

$txt['dp_admin_menu_manage_title'] = 'Dream Portal - Manage Dream Menu';
$txt['dp_dream_menu_add_title'] = 'Dream Portal - Add Dream Menu Button';
$txt['dp_dream_menu_modify_title'] = 'Dream Portal - Modify Dream Menu Button';
$txt['dp_admin_menusettings_title'] = 'Dream Portal - Dream Menu Settings';
$txt['dp_dream_menu_settings_header'] = 'Dream Menu Settings';
$txt['dp_admin_menu_desc'] = 'This section allows you to add, edit, or delete your, custom-made, menu buttons within the SMF Menu.';
$txt['dp_admin_manage_menu_desc'] = 'Manage your created Dream Menu buttons by modifying or deleting them.';
$txt['dp_admin_menu_settings_desc'] = 'Settings that apply to Dream Menu within Dream Portal.';

// Other Descriptions for Manage Dream Menu
$txt['dp_admin_menu_add_button_desc'] = 'Add new Dream Menu Buttons to the SMF Menu.  Place them anywhere you need them to be.  Only supports up to 2 Sub Levels for your buttons.';
$txt['dpdm_external_link'] = 'External Link';
$txt['dpdm_forum_link'] = 'Forum Link';
$txt['dpdm_dreampage_link'] = 'Dream Page';

$txt['dp_dream_menu_no_buttons'] = 'There are no Dream Buttons yet...';
$txt['dp_dream_menu_button_id'] = 'Button ID';
$txt['dp_dream_menu_button_position'] = 'Position';
$txt['dp_dream_menu_button_link'] = 'Link';
$txt['dp_dream_menu_actions'] = 'Actions';
$txt['dp_dream_menu_dream_page_assoc'] = 'Dream Page';
$txt['dp_dream_menu_page_name'] = 'Page Name';
$txt['dp_dream_menu_page_id'] = 'Page Id';
$txt['dp_dream_menu_not_dream_page'] = 'None';
$txt['modify'] = 'Modify';
$txt['before'] = 'Before';
$txt['after'] = 'After';
$txt['dp_dream_menu_remove_selected'] = 'Remove Selected Buttons';
$txt['dp_dream_menu_remove_all'] = 'Remove All Buttons';
$txt['dp_dream_menu_remove_confirm'] = 'Are you sure you want to remove the selected buttons?';
$txt['dp_dream_menu_remove_all_confirm'] = 'Are you sure you want to remove all of the buttons?';
$txt['dp_dream_menu_button_link_desc'] = 'For forum link start the url with "index.php?"';
$txt['dp_dream_menu_link_type'] = 'Link Target';
$txt['dp_dream_menu_same_window'] = 'Same Window';
$txt['dp_dream_menu_new_tab'] = 'New Tab';

// Settings
$txt['dp_boardindex'] = 'Board Index';
$txt['dp_menu_maintenance_mode'] = 'Enable Maintenance Mode';
$txt['dp_forum_menu_title'] = 'Board Index Menu Button Title';
$txt['dp_home_menu_title'] = '%1$s Menu Button Title';

// Submission errors
$txt['dp_dream_menu_not_found'] = 'The button you tried to edit does not exist!';

// %1 = $txt['dp_creating'] or $txt['dp_modifying'] depending on which they are doing.
$txt['dp_dream_menu_errors_title'] = 'The following error(s) occurred while %1$s your Dream Menu Button:';
$txt['dp_dream_menu_numeric_desc'] = 'The button title you chose is all numeric. You must use a title that contains atleast one none numeric character.<br />
1e5 is considered numeric (scientific notation) 1.5 is considered numeric (decimal number)';

$txt['dp_dream_menu_empty_parent'] = 'No Parent selected, be sure to select a parent for this button from the list of parent menus first.';
$txt['dream_menu_parent_unavailable'] = 'Parent Unavailable<br /><strong>Must reassign!</strong>';
$txt['dp_dream_menu_page_already_defined'] = 'This dream page has already been assigned to a Dream Menu Button.  You must delete the Dream Menu Button that this page is assigned to before you can assign it again.';
$txt['dp_dream_menu_empty_link'] = 'The Link was left empty.';
$txt['dp_dream_menu_dream_page'] = 'The Dream Page is currently assigned to the <strong>%2$s</strong> Dream Menu Button. You can&#039;t have more than 1 Dream Menu button assigned to the same Dream Page. Click on the following link to change the Dream page associated with the %2$s Dream Menu button:  <a href="%1$s">Modify %2$s</a>';

/*******************************
	Dream Pages
********************************/

// Other Descriptions for Manage Dream Pages
$txt['dp_admin_pages_manage_title'] = 'Dream Portal - Manage Dream Pages';
$txt['dp_admin_pagesettings_title'] = 'Dream Portal - Dream Page Settings';
$txt['dp_dream_pages_add_title'] = 'Dream Portal - Add Dream Page';
$txt['dp_dream_pages_edit_title'] = 'Dream Portal - Edit Dream Page';
$txt['dp_dream_page_settings_header'] = 'Dream Page Settings';
$txt['dp_admin_pages_manpages_desc'] = 'Allows you to manage your, custom-made, Dream Pages.';
$txt['dp_admin_pages_addpage_desc'] = 'This section allows you to add, edit, or delete your, custom-made, Dream Pages within Dream Portal.';
$txt['dp_admin_pages_settings_desc'] = 'Settings that apply to all of your Dream Pages within Dream Portal.';

$txt['dp_dream_pages_page_name'] = 'Name';
$txt['dp_dream_pages_domain_url'] = '{domain URL}/index.php?page= ';
$txt['dp_dream_pages_page_php'] = 'PHP';
$txt['dp_dream_pages_page_bbc'] = 'BBC';
$txt['dp_dream_pages_page_html'] = 'HTML';
$txt['dp_dream_pages_page_views'] = 'Views';
$txt['dp_dream_pages_page_body'] = 'Body';

// Settings
$txt['dp_pages_maintenance_mode'] = 'Enable Maintenance Mode';

// Submission errors
$txt['dp_dream_pages_not_found'] = 'The page you tried to edit does not exist!';

// %1 = $txt['dp_creating'] or $txt['dp_modifying'] depending on which they are doing.
$txt['dp_dream_pages_errors_title'] = 'The following error or errors occurred while %1$s your Dream Page:';
$txt['dp_dream_pages_empty_page_name'] = 'The page name was left empty.';
$txt['dp_dream_pages_empty_body'] = 'The body was left empty.';
$txt['dp_dream_pages_mysql'] = 'The page name you chose is already in use!';
$txt['dp_dream_pages_numeric'] = 'The page name you chose is all numeric. You must use a name that contains atleast one none numeric character.<br />
1e5 is considered numeric (scientific notation) 1.5 is considered numeric (decimal number)';
$txt['dp_dream_pages_invalid_name'] = 'The page name is invalid.  Make sure it is not empty and try again.';

$txt['dp_dream_pages_no_page'] = 'No pages yet...';
$txt['dp_dream_pages_page_id'] = 'Page ID';
$txt['dp_dream_pages_actions'] = 'Actions';

$txt['dp_dream_pages_remove_selected'] = 'Remove Selected Pages';
$txt['dp_dream_pages_remove_confirm'] = 'Are you sure you want to remove the selected pages?';
$txt['dp_dream_pages_remove_all'] = 'Remove All Pages';
$txt['dp_dream_pages_remove_all_confirm'] = 'Are you sure you want to remove all of the pages?';

/*****************************
	Extend Dream Portal
******************************/

// Main Strings
$txt['dp_admin_title_add_modules'] = 'Dream Portal - Add Modules';
$txt['dp_admin_title_add_templates'] = 'Dream Portal - Add Templates';
$txt['dp_admin_title_add_languages'] = 'Dream Portal - Add Languages';
$txt['dp_admin_extend_addmodules_desc'] = 'Manage your uploaded modules and/or upload more modules for use within your Dream Layouts.';
$txt['dp_admin_extend_addtemplates_desc'] = 'Manage your uploaded templates and/or upload more, custom-made, templates for your Modules within your Dream Layouts.';
$txt['dp_admin_extend_addlanguages_desc'] = 'Manage your uploaded languages and allows you to upload more languages for Dream Portal.  The language that gets used depends on what language you are using within SMF.  If you are using a language within SMF that is not listed on this page, than the English language files will be used for Dream Portal by default.';

// These strings get used in sprintf, to switch to the correct text output and format!
$txt['dptext_module'] = 'Module';
$txt['dptext_template'] = 'Template';
$txt['dptext_language'] = 'Language';
$txt['dptext_module_lower'] = 'module';
$txt['dptext_template_lower'] = 'template';
$txt['dptext_language_lower'] = 'language';
$txt['dptext_template_plural_lower'] = 'templates';
$txt['dptext_module_plural_lower'] = 'modules';
$txt['dptext_language_plural_lower'] = 'languages';
$txt['dptext_modules'] = 'Modules';
$txt['dptext_templates'] = 'Templates';
$txt['dptext_languages'] = 'Languages';

// Basic sprintf strings!
$txt['no_extensions_exist'] = 'No %2$s exist.  You&#039;ll have to upload a %1$s if you want to install any.';
$txt['dp_upload_extension'] = 'Upload a %1$s';
$txt['dp_extension_name'] = '%1$s name';
$txt['extension_to_upload'] = '%1$s to Upload:';

// Basic txt strings used for all extensions.
$txt['dp_extend_upload'] = 'Upload';
$txt['dp_extend_version'] = 'Version';
$txt['dp_extend_description'] = 'Description';
$txt['dp_extend_install'] = 'Install';
$txt['dp_extend_settings'] = 'Settings';
$txt['dp_extend_uninstall'] = 'Uninstall';
$txt['dp_extend_delete'] = 'Delete';

/*-----------------------------------------------------------------------------------------------------
	The following strings handle all extension upload errors for modules, templates, and languages.
-------------------------------------------------------------------------------------------------------*/
$txt['dp_extend_upload_error_type'] = 'Sorry, only zip and tar.gz archives are supported.';
$txt['invalid_language_filepath'] = 'This extension could not be installed because one or more language filepaths are currently invalid.';
$txt['dpamerr_unknown'] = 'Sorry, but an error occurred while attempting to upload your extension to Dream Portal.  Please try again.';
$txt['dpamerr_UPLOAD_ERR_INI_SIZE'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
$txt['dpamerr_UPLOAD_ERR_FORM_SIZE'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
$txt['dpamerr_UPLOAD_ERR_PARTIAL'] = 'The uploaded file was only partially uploaded.  Please try again.';
$txt['dpamerr_UPLOAD_ERR_NO_FILE'] = 'No file was uploaded.  Please make sure the file exists.';
$txt['dpamerr_UPLOAD_ERR_NO_TMP_DIR'] = 'Unable to upload this file due to a missing temporary folder.';
$txt['dpamerr_UPLOAD_ERR_CANT_WRITE'] = 'Failed to create this file on your server.  Possible cause can be a directory not having write access available.';
$txt['dpamerr_UPLOAD_ERR_EXTENSION'] = 'A PHP extension prevented this file from being uploaded onto your server, you can examine your list of loaded extensions using phpinfo(), which may help you to ascertain which extension could be causing this.';

/*-------------------------------------------------------------------------------------------------------------------------------
	%1$s = $txt['dptext_module_lower'] or $txt['dptext_template_lower'] or $txt['dptext_language_lower']
	%2$s = $txt['dptext_module_plural_lower'] or $txt['dptext_template_plural_lower'] or $txt['dptext_language_plural_lower']
---------------------------------------------------------------------------------------------------------------------------------*/
$txt['dp_extend_infoxml_missing'] = 'This %1$s is missing the info.xml file that must be within the root of the package.  Unable to upload this %1$s!';
$txt['dp_extend_package_corrupt'] = 'Sorry, this %1$s is corrupt and can not be installed.';
$txt['dp_extend_has_no_name'] = 'Sorry, it&#039;s required that all %2$s have a name, you can not install a %1$s without a name';
$txt['dp_extend_has_no_version'] = 'Sorry, it&#039;s required that all %2$s have a version associated with it.  This %1$s doesn&#039;t seem to have a version defined for it.';
$txt['dp_extend_restricted_name'] = 'Sorry, unable to add this %1$s due to, either, an invalid name, no name given, or a name that already exists.';
$txt['dp_extend_function_duplicates'] = 'There is a %1$s already installed that is using a function name that is specified within this %1$s. You will have to uninstall the other %1$s that is using this function name before you can install this %1$s.';
$txt['dp_extend_invalid_filename'] = 'Sorry, this %1$s has an invalid filename or filepath associated with it and could not be uploaded.';
$txt['dp_extend_function_already_exists'] = 'This %1$s is attempting to overwrite a function that you already have defined in SMF and can not be installed.';
$txt['dp_extend_invalid_function_name'] = 'This %1$s contains a function with an invalid name and can not be installed.';
$txt['dp_extend_no_title_desc'] = 'This %1$s does not have a title and description defined to it and therefore can not be uploaded until it does.  Please be sure that the title and description text strings are defined within the language file(s) for this %1$s.';
$txt['dp_extend_no_title'] = 'This %1$s does not have a title defined to it.  A %1$s can not be uploaded without a title defined within the language file(s).';
$txt['dp_extend_no_desc'] = 'This %1$s does not have a description defined within a language file.  All %2$s must have a description defined within the language file(s).';
$txt['dp_extend_uninstall_error'] = 'Unable to uninstall this %1$s.';

/*---------------------------
Module-Specific Upload Errors
-----------------------------*/
$txt['invalid_database_filepath'] = 'This modules database file location is not correct. Unable to proceed!';
$txt['database_files_no_exist'] = 'Can not locate the database install and/or uninstall files for this module. Unable to proceed!';
$txt['database_uninstall_missing'] = 'There is no uninstall file associated with the database for this module.  This module can not be uploaded without an uninstall, database, file.';
$txt['module_has_no_files'] = 'This module doesn&#039;t have any script files associated with it and can not be installed without one.';
$txt['module_has_no_main_function'] = 'This module is either missing the main function for output or has more than 1 main function defined, in either case, it can not be installed.';
$txt['module_has_no_functions'] = 'This module doesn&#039;t have any functions associated with it and can not be installed.';
$txt['module_missing_files'] = 'Sorry, this module could not be installed because it is missing files associated with it.';
$txt['module_invalid_image_filepath'] = 'Sorry, this module has an invalid image filepath associated with it.';
$txt['invalid_function_name'] = 'This modules main function contains invalid characters and can not be installed.';
$txt['module_has_file_defined_already'] = 'This module is attempting to define 2 files with the same exact filepath and can not be added.';

/*-----------------------------
Template-Specific Upload Errors
-------------------------------*/
$txt['dp_templates_invalid_filename'] = 'Unable to upload this template because it does not have a valid <strong>php</strong> file extension for the file defined within this template package.';

/*-----------------------
Language Specific Strings
-------------------------*/
$txt['dp_lang_translator'] = 'Translator(s)';
$txt['dp_lang_unknown'] = 'Unknown';
$txt['dp_lang_no_filename'] = 'Sorry, unable to add this Language Pack.';
$txt['dp_lang_pack_error'] = 'Unable to upload this language pack because of mandatory tags that are missing from the info.xml file.';
$txt['dp_lang_pack_already_installed'] = 'This language pack is already installed.';
$txt['dp_lang_dir_invalid'] = 'The directory for the Language files within this language pack is invalid and can not be installed.';
$txt['dp_lang_update_needed'] = 'Language Pack needs to be updated for this version of Dream Portal.';
$txt['dp_exclamation'] = '(!)';

?>