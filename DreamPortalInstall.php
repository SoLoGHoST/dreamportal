<?php
/**
 * This script prepares the database for all the tables and other database changes Dream Portal requires.
 *
 * NOTE: This script is meant to run using the <samp><database></database></samp> elements of the package-info.xml file. This is so admins have the choice to uninstall any database data installed with the mod. Also, since using the <samp><database></samp> elements automatically calls on db_extend('packages'), we will only be calling that if we are running this script standalone.
 *
 * @package installer
 * @since 1.0
 */

/**
 * Before attempting to execute, this file attempts to load SSI.php to enable access to the database functions.
*/
// If SSI.php is in the same place as this file, and SMF isn't defined...
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

DatabasePopulation();
// change permission if file exists...
@chmod($boarddir . '/dp_ajax.php', 0644);

//!!! Installs Dream Portal Tables for SMF 2.0.x with default values!
function DatabasePopulation()
{
	global $smcFunc, $modSettings;

	$dp_tables = array();

	// dp_groups table
	$dp_tables[] = array(
		'name' => 'dp_groups',
		'columns' => array(
			0 => array(
				'name' => 'id_group',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'active',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 0,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_group')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_member')
			)
		),
		'default' => array(
			'columns' => array(
				'id_group' => 'int', 'id_member' => 'int', 'name' => 'string-255', 'active' => 'int'
			),
			'values' => array(
			   array(1, 0, 'Default', 1),
			),
			'keys' => array('id_group', 'id_member')
		)
	);

	// dp_layouts table
	$dp_tables[] = array(
		'name' => 'dp_layouts',
		'columns' => array(
			0 => array(
				'name' => 'id_layout',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_group',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'is_txt',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 0,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_layout')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_group')
			)
		),
		'default' => array(
			'columns' => array(
				'id_layout' => 'int',
				'id_group' => 'int',
				'name' => 'string-65',
				'is_txt' => 'int',
			),
			'values' => array(
				// Only 1 layout really necessary to start with.  Links to $txt['dp_homepage'] for language compatibility purposes!
				array(1, 1, 'dp_homepage', 1),
			),
			'keys' => array('id_layout', 'id_group')
		)
	);

	// dp_actions table
	$dp_tables[] = array(
		'name' => 'dp_actions',
		'columns' => array(
			0 => array(
				'name' => 'id_action',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_group',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'id_layout',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
			),
			3 => array(
				'name' => 'action',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_action')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_group')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_layout')
			)
		),
		'default' => array(
			'columns' => array(
				'id_action' => 'int',
				'id_group' => 'int',
				'id_layout' => 'int',
				'action' => 'string-255',
			),
			'values' => array(
				// Only 1 action really necessary to start with!
				array(1, 1, 1, '[home]'),
			),
			'keys' => array('id_action', 'id_group', 'id_layout')
		)
	);

	// dp_layout_positions table
	$dp_tables[] = array(
		'name' => 'dp_layout_positions',
		'columns' => array(
			0 => array(
				'name' => 'id_layout_position',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_layout',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'column',
				'type' => 'varchar',
				'size' => 16,
				'null' => false,
				'default' => '0:0',
			),
			3 => array(
				'name' => 'row',
				'type' => 'varchar',
				'size' => 16,
				'null' => false,
				'default' => '0:0',
			),
			4 => array(
				'name' => 'enabled',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 1,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_layout_position')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_layout')
			),
		),
		'default' => array(
			'columns' => array(
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'column' => 'string-16',
				'row' => 'string-16',
				'enabled' => 'int',
			),
			'values' => array(
				/*
				format = array(auto, layout for that action, position of the section, needs more work, is the section enabled?).
				*/
				// [home]
				array(1, 1, '0:3', '0:0', 0),
				array(2, 1, '0:0', '1:0', 0),
				array(3, 1, '1:0', '1:0', 1),
				array(4, 1, '2:0', '1:0', 0),
				array(5, 1, '0:3', '2:0', 0),
				array(6, 1, '0:0', '0:0', -1),
			),
			'keys' => array('id_layout_position', 'id_layout')
		)
	);

	// dp_module_positions table
	$dp_tables[] = array(
		'name' => 'dp_module_positions',
		'columns' => array(
			0 => array(
				'name' => 'id_position',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_layout_position',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'id_layout',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			3 => array(
				'name' => 'id_module',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			4 => array(
				'name' => 'id_clone',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			5 => array(
				'name' => 'position',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'default' => 0,
			),
			// For Empty Modules, value = 1!
			6 => array(
				'name' => 'empty',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 0,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_position')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_layout_position')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_layout')
			),
			3 => array(
				'type' => 'key',
				'columns' => array('id_module')
			),
			4 => array(
				'type' => 'key',
				'columns' => array('id_clone')
			)
		),
		'default' => array(
			'columns' => array(
				'id_position' => 'int',
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'id_module' => 'int',
				'position' => 'int',
			),
			'values' => array(
				/*

				NOTE:  SMF Module will have an id_module = 0 and id_clone = 0!  The SMF Module cannot be cloned!
				[home] action will NOT have an SMF Module, so for [home] id_module && id_clone will both never equal 0!!!

				action=[home] modules begin...
				--------------------------
				*/

				// Only the Custom Module showing within the Middle Columns here!
				array(1, 3, 1, 1, 0),
			),
			'keys' => array('id_position', 'id_layout_position', 'id_layout', 'id_module', 'id_clone')
		)
	);

	// dp_modules table
	$dp_tables[] = array(
		'name' => 'dp_modules',
		'columns' => array(
			0 => array(
				'name' => 'id_module',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			2 => array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'title_link',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			4 => array(
				'name' => 'target',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			5 => array(
				'name' => 'icon',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			6 => array(
				'name' => 'minheight',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			7 => array(
				'name' => 'minheight_type',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			8 => array(
				'name' => 'files',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			9 => array(
				'name' => 'header_files',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			10 => array(
				'name' => 'functions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			11 => array(
				'name' => 'header_display',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 1,
			),
			12 => array(
				'name' => 'id_template',
				'type' => 'mediumint',
				'size' => 8,
				'null' => false,
				'default' => 0,
			),
			13 => array(
				'name' => 'groups',
				'type' => 'varchar',
				'size' => 255,
				'default' => '-3',
			),
			14 => array(
				'name' => 'container',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 1,
			),
			15 => array(
				'name' => 'txt_var',
				'type' => 'tinyint',
				'size' => 1,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_module')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('name')
			)
		),
		'default' => array(
			'columns' => array(
				'id_module' => 'int',
				'name' => 'string-255',
				'title' => 'string-255',
				'title_link' => 'string-255',
				'target' => 'int',
				'icon' => 'string-255',
				'txt_var' => 'int',
			),
			'values' => array(
				// Default DP module
				array(1, 'custom', 'dp_custom_title_default', '', 1, '', 1),
			),
			'keys' => array('id_module', 'name')
		)
	);

	// dp_module_clones table
	$dp_tables[] = array(
		'name' => 'dp_module_clones',
		'columns' => array(
			0 => array(
				'name' => 'id_clone',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_module',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			3 => array(
				'name' => 'is_clone',
				'type' => 'tinyint',
				'size' => 1,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			4 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			5 => array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			6 => array(
				'name' => 'title_link',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'target',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			8 => array(
				'name' => 'icon',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			9 => array(
				'name' => 'minheight',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			10 => array(
				'name' => 'minheight_type',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			11 => array(
				'name' => 'files',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			12 => array(
				'name' => 'header_files',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			13 => array(
				'name' => 'functions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			14 => array(
				'name' => 'header_display',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 1,
			),
			15 => array(
				'name' => 'id_template',
				'type' => 'mediumint',
				'size' => 8,
				'null' => false,
				'default' => 0,
			),
			16 => array(
				'name' => 'groups',
				'type' => 'varchar',
				'size' => 255,
				'default' => '-3',
			),
			17 => array(
				'name' => 'container',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'unsigned' => true,
				'default' => 1,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_clone')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_module')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_member')
			)
		)
	);

	// dp_module_parameters table
	$dp_tables[] = array(
		'name' => 'dp_module_parameters',
		'columns' => array(
			0 => array(
				'name' => 'id_param',
				'type' => 'bigint',
				'size' => 20,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_clone',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'id_module',
				'type' => 'int',
				'size' => 4,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			3 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			4 => array(
				'name' => 'type',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			5 => array(
				'name' => 'value',
				'type' => 'text',
				'null' => false,
			),
			6 => array(
				'name' => 'fieldset',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'txt_var',
				'type' => 'tinyint',
				'size' => 1,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_param')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_clone')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_module')
			)
		),
		'default' => array(
			'columns' => array(
				'id_param' => 'int',
				'id_module' => 'int',
				'name' => 'string-255',
				'type' => 'string-255',
				'value' => 'string',
				'txt_var' => 'int',
			),
			'values' => array(
				// Custom PHP/BBC/HTML
				array(1, 1, 'code_type', 'select', '2:BBC;HTML;PHP', 0),
				array(2, 1, 'code', 'rich_edit', 'dp_custom_code_default', 1),
			),
			'keys' => array('id_param', 'id_clone', 'id_module')
		)
	);

	// dp_templates table
	$dp_tables[] = array(
		'name' => 'dp_templates',
		'columns' => array(
			0 => array(
				'name' => 'id_template',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			// 0 = Module Template; 1 = Dream Pages Template!
			2 => array(
				'name' => 'type',
				'type' => 'tinyint',
				'size' => 2,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			3 => array(
				'name' => 'file',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			4 => array(
				'name' => 'function',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_template')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('name')
			)
		)
	);

	// dp_languages table
	$dp_tables[] = array(
		'name' => 'dp_languages',
		'columns' => array(
			0 => array(
				'name' => 'id_language',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			2 => array(
				'name' => 'title',
				'type' => 'text',
				'null' => false,
			),
			3 => array(
				'name' => 'translator',
				'type' => 'text',
				'null' => false,
			),
			// 0 = Not UTF-8; 1 = UTF-8!
			4 => array(
				'name' => 'is_utf8',
				'type' => 'tinyint',
				'size' => 2,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			5 => array(
				'name' => 'version',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			6 => array(
				'name' => 'dp_version',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_language')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('name')
			)
		)
	);

	// dp_module_files table
	$dp_tables[] = array(
		'name' => 'dp_module_files',
		'columns' => array(
			0 => array(
				'name' => 'id_file',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_thumb',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			2 => array(
				'name' => 'id_param',
				'type' => 'bigint',
				'size' => 20,
				'unsigned' => true,
				'null' => false,
			),
			3 => array(
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			4 => array(
				'name' => 'file_type',
				'type' => 'int',
				'size' => 3,
				'unsigned' => true,
				'null' => false,
				'default' => 0,
			),
			5 => array(
				'name' => 'filename',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			6 => array(
				'name' => 'file_hash',
				'type' => 'varchar',
				'size' => 40,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'fileext',
				'type' => 'varchar',
				'size' => 8,
				'null' => false,
				'default' => '',
			),
			8 => array(
				'name' => 'size',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			9 => array(
				'name' => 'downloads',
				'type' => 'mediumint',
				'size' => 8,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			10 => array(
				'name' => 'width',
				'type' => 'mediumint',
				'size' => 8,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			11 => array(
				'name' => 'height',
				'type' => 'mediumint',
				'size' => 8,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			12 => array(
				'name' => 'mime_type',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_file')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('id_member', 'id_file')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_param')
			),
			3 => array(
				'type' => 'key',
				'columns' => array('file_type')
			)
		)
	);

	// dp_dream_pages table
	$dp_tables[] = array(
		'name' => 'dp_dream_pages',
		'columns' => array(
			0 => array(
				'name' => 'id_page',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'id_button',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			2 => array(
				'name' => 'page_name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'type',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			4 => array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			5 => array(
				'name' => 'body',
				'type' => 'longtext',
				'null' => false,
			),
			6 => array(
				'name' => 'page_views',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			7 => array(
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			8 => array(
				'name' => 'status',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_page')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('page_name')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_button')
			),
		)
	);

	// dp_dream_menu table
	$dp_tables[] = array(
		'name' => 'dp_dream_menu',
		'columns' => array(
			0 => array(
				'name' => 'id_button',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			2 => array(
				'name' => 'is_txt',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 0,
			),
			3 => array(
				'name' => 'slug',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			// 0 = Dream Page, 1 = Forum Link, 2 = External Link
			4 => array(
				'name' => 'type',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'unsigned' => true,
				'default' => 0,
			),
			5 => array(
				'name' => 'target',
				'type' => 'enum(\'_self\',\'_blank\')',
				'default' => '_self',
			),
			6 => array(
				'name' => 'position',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'link',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			// 0 = inactive; 1 = active
			8 => array(
				'name' => 'status',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			9 => array(
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			10 => array(
				'name' => 'parent',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_button')
			),
		),
		'default' => array(
			'columns' => array(
				'id_button' => 'int',
				'name' => 'string-65',
				'slug' => 'string-255',
				'is_txt' => 'int',
				'type' => 'int',
				'target' => 'string',
				'position' => 'string-65',
				'link' => 'string',
				'status' => 'int',
				'permissions' => 'string-255',
				'parent' => 'string-65',
			),
			'values' => array(
				// Inserting the Dream Portal Menu Button as a Sub Button under the SMF Admin Menu.
				array(1, 'dream_portal', 'dpdm_1', 1, 1, '_self', 'child_of', 'index.php?action=admin;area=dplayouts;sa=dpmanlayouts', 1, '1', 'admin'),
			),
			'keys' => array('id_button'),
		)
	);

	db_extend('packages');

	// Create all of the tables!
	foreach($dp_tables as $table)
	{
		$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes'], array(), 'update');
		// Insert all defaults if we have any...
		if (isset($table['default']))
			$smcFunc['db_insert']('replace', '{db_prefix}' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
	}

	$dp_permissions = array('dream_portal_view', 'dream_portal_menu_view', 'dream_portal_page_view');

	// Makes sense to let everyone view a portal and all of it's features, no? But don't modify the permissions if the admin has already set them.
	$request = $smcFunc['db_query']('', '
		SELECT id_group
		FROM {db_prefix}permissions
		WHERE permission IN ({array_string:dp_perms})',
		array(
			'dp_perms' => $dp_permissions,
		)
	);

	$num = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	if (empty($num))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_group
			FROM {db_prefix}membergroups
			WHERE id_group NOT IN ({array_int:exclude_groups})
			' . (empty($modSettings['permission_enable_postgroups']) ? '
				AND min_posts = {int:min_posts}' : ''),
			array(
				'exclude_groups' => array(1, 3),
				'min_posts' => -1,
			)
		);

		$groups = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			foreach($dp_permissions as $perm)
				$groups[] = array($row['id_group'], $perm, empty($modSettings['permission_enable_deny']) ? 1 : -1);
		
		foreach($dp_permissions as $perm)
		{
			$groups[] = array(-1, $perm, !empty($modSettings['permission_enable_deny']) ? 1 : -1);
			$groups[] = array(0, $perm, !empty($modSettings['permission_enable_deny']) ? 1 : -1);
		}

		$smcFunc['db_insert']('ignore',
			'{db_prefix}permissions',
			array('id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
			$groups,
			array('id_group', 'permission')
		);
	}

	// Finally insert the default settings into the SMF Settings table!
	updateSettings(array(
		'dp_pages_mode' => '1',
		'dp_menu_mode' => '1',
		'dp_collapse_modules' => '1',
		'dp_add_modules_limit' => '7',
		'dp_add_templates_limit' => '7',
		'dp_add_languages_limit' => '7',
		'dp_module_title_char_limit' => '25',
		'dp_column_enable_animations' => '1',
		'dp_column_animation_speed' => '2',
		'dp_module_enable_animations' => '1',
		'dp_module_animation_speed' => '2',
		'dp_enable_custommod_icons' => '1',
		'dp_icon_directory' => 'dreamportal/module_icons',
		'dp_image_directory' => 'dreamportal/module_images',
	));

	// Now presenting... *drumroll*
	add_integration_function('integrate_admin_include', '$sourcedir/dp_core.php');
	add_integration_function('integrate_core_features', 'add_dp_core_feature');

	if (!empty($modSettings['dp_portal_mode']))
	{
		$hooks = array(
			'integrate_pre_include' => '$sourcedir/Subs-DreamPortal.php',
			'integrate_actions' => 'add_dream_actions',
			'integrate_load_permissions' => 'add_dp_permissions',
			'integrate_menu_buttons' => 'add_dp_menu_buttons',
			'integrate_admin_areas' => 'add_dp_admin_areas',
			'integrate_whos_online' => 'dream_whos_online',
		);

		if (empty($modSettings['dp_disable_copyright']))
			$hooks += array('integrate_buffer' => 'dreamBuffer');

		if (empty($modSettings['dp_disable_homepage']))
			$hooks += array('integrate_redirect' => 'dreamRedirect');

		foreach($hooks as $type => $value)
			add_integration_function($type, $value);
	}
}

?>
