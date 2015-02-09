<?php
/**
 * This file handles Dream Portal's default module template.
 *
 * Module templates must meet the following criteria:
 *
 * - Only one function should exist
 *
 * @package moduletemplate
 * @copyright 2009-2012 Dream Portal
 * @since 1.1
 * @version 1.1
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Renders a module.
 *
 * @param array $module Array with all of the module information.  This array gets populated within the loadLayout function of Subs-DreamPortal.php.
 * @param int $style The type of style being used:
 * - 1 - Modular
 * - 0 - Block
 * @param int $location This is the module's index position. This variable is set to zero (0) if the module is either the first in a column or alone.
 */

function dp_template_module_default($module, $style, $location = 0)
{
	global $txt, $settings, $scripturl, $modSettings;

	// Which Layout Style to show?
	if (empty($style))
	{
		if (!empty($module['header_display']) || $module['header_display'] == 2)
			echo '
			<div id="dp_', $module['type'], 'module_', $module['id'], '" class="cat_bar', (!$module['is_collapsed'] || empty($modSettings['dp_collapse_modules']) ? ' block_header' : ''), '"', (!empty($location) ? ' style="margin-top: 7px;"' : ''), '>
				<h3 class="catbg">
					', !empty($modSettings['dp_collapse_modules']) && $module['header_display'] != 2 ? '<span class="floatright" onclick="' . $module['toggle'] . '"><img class="dp_curveblock hand" id="' . $module['type'] . 'collapse_' . $module['id'] . '" src="' . $settings['images_url'] . '/' . ($module['is_collapsed'] ? 'expand' : 'collapse') . '.gif" alt="" title="' . $txt['dp_core_modules'] . '" /></span>' : '', '
					' . (empty($module['icon']) ? '' : '
								<img src="' . $module['icon'] . '" alt="" title="' . $module['title'] . '" class="icon" style="margin-left: 0px;" />&nbsp;') . (empty($module['action']) && empty($module['url']) ? '' : (!empty($module['url']) ? $module['url'] : '<a href="' . $scripturl . '?' . $module['action'] . '" target="' . $module['target'] . '">')) . $module['title'] . (empty($module['action']) && empty($module['url']) ? '' : '</a>') . '
				</h3>
			</div>';
		else
			echo '
				<span class="upperframe"><span></span></span>';

		echo '
				<div id="', $module['type'], 'module_', $module['id'], '"', $module['is_collapsed'] && !empty($modSettings['dp_collapse_modules']) && !empty($module['header_display']) ? ' style="display: none;"' : '', '>
					<div class="roundframe blockframe"', (!empty($module['minheight']) ? ' style="' . $module['minheight'] . '"' : ''), '>
						', !empty($module['params']) ? $module['function']($module['params']) : $module['function'](), '
					</div>
				<span class="lowerframe"><span></span></span></div>';
	}
	else
	{
		if (!empty($module['header_display']))
			echo '
							<div class="cat_bar"', (!empty($location) ? ' style="margin-top: 7px;"' : ''), '>
								<h3 class="catbg">
									', !empty($modSettings['dp_collapse_modules']) && $module['header_display'] != 2 ? '<span class="floatright" onclick="' . $module['toggle'] . '"><img class="dp_curveblock hand" id="' . $module['type'] . 'collapse_' . $module['id'] . '" src="' . $settings['images_url'] . '/' . ($module['is_collapsed'] ? 'expand' : 'collapse') . '.gif" alt="" title="' . $txt['dp_core_modules'] . '" /></span>' : '', '
								' . (empty($module['icon']) ? '' : '
								<img src="' . $module['icon'] . '" alt="" title="' . $module['title'] . '" class="icon" style="margin-left: 0px;" />&nbsp;') . (empty($module['action']) && empty($module['url']) ? '' : (!empty($module['url']) ? $module['url'] : '<a href="' . $scripturl . '?' . $module['action'] . '">')) . $module['title'] . (empty($module['action']) && empty($module['url']) ? '' : '</a>') . '
								</h3>
							</div>';

		echo '
							<div id="', $module['type'], 'module_', $module['id'], '" style="padding: 0.5em 4px;', $module['is_collapsed'] && !empty($modSettings['dp_collapse_modules']) && !empty($module['header_display']) ? ' display: none;' : '', '">
								', !empty($module['params']) ? $module['function']($module['params']) : $module['function'](), '
							</div>';
	}
}

?>