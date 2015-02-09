<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDPLayouts.template.php; ver 1.0 RC

/**
 * This file handles showing Dream Portal's module management settings.
 *
 * @package template
 * @since 1.0
*/

/**
 * Template used to modify the options of modules/clones.
 *
 * @since 1.0
 */
function template_modify_modules()
{
	global $txt, $context, $scripturl, $settings, $dp_counter, $dp_script, $modSettings;

	echo '
	<div id="admincenter">
		<form name="dpModule" id="dpModule" ', isset($context['dp_file_input']) ? 'enctype="multipart/form-data" ' : '', 'action="', $scripturl, '?action=admin;area=dplayouts;sa=modifymod;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">';

	// Load the module title.
	if (isset($context['mod_info'][$context['dp_modid']]))
	{
		echo '
			<div class="title_bar">
				<h3 class="titlebg">
					', !empty($context['mod_info'][$context['dp_modid']]['help']) ? '<a href="' . $scripturl . '?action=helpadmin;help=' . $context['mod_info'][$context['dp_modid']]['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" /></a>' : '', $txt[$context['mod_info'][$context['dp_modid']]['titlebar']] . $txt['dp_modsettings'], '
				</h3>
			</div>';
	}
	// Brief description of the module!
	if (!empty($context['mod_info'][$context['dp_modid']]['info']))
		echo '
			<div class="information">', $context['mod_info'][$context['dp_modid']]['info'], '</div>';

	echo '
			<div class="windowbg2">
					<span class="topslice"><span></span></span>
					<div class="content">
						<dl class="settings">';

	// Get Module Icon, Title and URL.
	if (isset($context['mod_info'][$context['dp_modid']]))
	{
		// All Dream Module Icons.
		echo '
			<dt>
				<a id="setting_modicon" href="', $scripturl, '?action=helpadmin;help=dream_module_icon" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="mod_icon">', $txt['dream_module_icon'], '</label>
				</span>
			</dt>
			<dd>';

		echo '
			<div id="mod_icon">
				<span>
					<select name="cat" id="cat" size="10" onchange="changeSel(\'\');">';
		foreach ($context['icons'] as $icon)
			echo '
							<option value="', $icon['filename'] . ($icon['is_dir'] ? '/' : ''), '"', ($icon['checked'] ? ' selected="selected"' : ''), '>', $icon['name'], '</option>';
		echo '
					</select>
				</span>
				<span>
					<select name="file" id="file" size="10" style="display: none;" onchange="showDPIcon()"><option>&nbsp;</option></select>
				</span>

				<span id="dp_icon_holder">
					<img name="dp_icon" style="display: none;" id="dp_icon" src="" alt="', $txt['no_icon'], '" border="0" />
				</span>
				<script type="text/javascript"><!-- // --><![CDATA[
					var files = ["' . implode('", "', $context['dpicon_list']) . '"];
					var icon = document.getElementById("dp_icon");
					var cat = document.getElementById("cat");
					var selicon = "' . $context['icon_selected'] . '";
					var icondir = "' . $context['dpmod_icon_url'] . '";
					var size = dp_icon.alt.substr(3, 2) + " " + dp_icon.alt.substr(0, 2) + String.fromCharCode(117, 98, 116);
					var file = document.getElementById("file");

					if (dp_icon.name.indexOf("dp_icon") == 0)
						changeSel(selicon);

					function changeSel(selected)
					{
						if (cat.selectedIndex == -1)
							return;

						if (cat.options[cat.selectedIndex].value.length >= 1)
						{
							if (cat.options[cat.selectedIndex].value.indexOf("/") > 0)
							{
								var i;
								var count = 0;

								file.style.display = "inline";
								file.disabled = false;

								for (i = file.length; i >= 0; i = i - 1)
									file.options[i] = null;

								for (i = 0; i < files.length; i++)
									if (files[i].indexOf(cat.options[cat.selectedIndex].value) == 0)
									{
										var filename = files[i].substr(files[i].indexOf("/") + 1);
										var showFilename = filename.substr(0, filename.lastIndexOf("."));
										showFilename = showFilename.replace(/[_]/g, " ");

										file.options[count] = new Option(showFilename, files[i]);

										if (filename == selected)
										{
											if (file.options.defaultSelected)
												file.options[count].defaultSelected = true;
											else
												file.options[count].selected = true;
										}

										count++;
									}

								if (file.selectedIndex == -1 && file.options[0])
									file.options[0].selected = true;

								showDPIcon();
							}
							else
							{
								file.style.display = "none";
								file.disabled = true;
								dp_icon.name = "dpicon";
								dp_icon.style.display = "";
								dp_icon.src = icondir + cat.options[cat.selectedIndex].value;
								dp_icon.style.width = "";
								dp_icon.style.height = "";
							}
						}
						else
						{
							dp_icon.name = "dp_icon";
							dp_icon.style.display = "none";
							file.style.display = "none";
							file.disabled = true;
							dp_icon.src = icondir + cat.options[cat.selectedIndex].value;
							dp_icon.style.width = "";
							dp_icon.style.height = "";
						}
					}

					function showDPIcon()
					{

						if (file.selectedIndex == -1)
							return;

						dp_icon.style.display = "";
						dp_icon.src = icondir + file.options[file.selectedIndex].value;
						dp_icon.name = "dpicon";
						dp_icon.alt = file.options[file.selectedIndex].text;
						dp_icon.alt += file.options[file.selectedIndex].text == size ? "!" : "";
						dp_icon.style.width = "";
						dp_icon.style.height = "";
					}

				// ]]></script>
			</div>';

		echo '
			</dd>
			<dt>
				<a id="setting_modtemplate" href="', $scripturl, '?action=helpadmin;help=dream_module_template" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="mod_template">
						', $txt['dream_module_template'], '
					</label>
				</span>
			</dt>
			<dd>
				<select name="module_template" id="mod_template" class="smalltext">';

		foreach ($context['dpmod_templates'] as $key => $template)
			echo '
					<option value="' . $template['id'] . '"' . ($template['id'] == $context['mod_info'][$context['dp_modid']]['id_template'] ? ' selected="selected"' : '').'>' . $template['txt'] . '</option>';

		echo '
				</select>
			</dd>
			<dt>
				<a id="setting_modheader" href="', $scripturl, '?action=helpadmin;help=dream_module_header_display" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="mod_header">
						', $txt['dream_module_header_display'], '
					</label>
				</span>
			</dt>
			<dd>
				<select name="module_header" id="mod_header" class="smalltext">
					<option value="1"', (!empty($context['mod_info'][$context['dp_modid']]['header_display']) && $context['mod_info'][$context['dp_modid']]['header_display'] == 1 ? ' selected="selected"' : ''), '>'.$txt['dream_module_enabled'].'</option>
					<option value="0"', (empty($context['mod_info'][$context['dp_modid']]['header_display']) ? ' selected="selected"' : ''), '>'.$txt['dream_module_disable'].'</option>
					<option value="2"', (!empty($context['mod_info'][$context['dp_modid']]['header_display']) && $context['mod_info'][$context['dp_modid']]['header_display'] == 2 ? ' selected="selected"' : ''), '>'.$txt['dream_module_collapse'].'</option>
				</select>
			</dd>
			<dt>
				<a id="setting_modgroups" href="', $scripturl, '?action=helpadmin;help=dream_module_groups" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="group_perms">
						', $txt['dream_module_groups'], '
					</label>
				</span>
			</dt>
			<dd>
				<fieldset id="group_perms">
					<legend>
						<a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'none\';document.getElementById(\'group_perms_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a>
					</legend>';

		$all_checked = true;

		// List all the groups to configure permissions for.
		foreach ($context['mod_info'][$context['dp_modid']]['groups'] as $group)
		{
			echo '
						<div id="permissions_', $group['id'], '">
							<label for="check_group', $group['id'], '">
								<input type="checkbox" class="input_check" name="groups[]" value="', $group['id'], '" id="check_group', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' />
								<span', ($group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : ''), '>', $group['name'], '</span>
							</label>
						</div>';

			if (!$group['checked'])
				$all_checked = false;
		}

		echo '
					<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'groups[]\');" id="check_group_all"', $all_checked ? ' checked="checked"' : '', ' />
					<label for="check_group_all">
						<em>', $txt['check_all'], '</em>
					</label>
					<br />
				</fieldset>
				<a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'block\'; document.getElementById(\'group_perms_groups_link\').style.display = \'none\'; return false;" id="group_perms_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
				<script type="text/javascript"><!-- // --><![CDATA[
					document.getElementById("group_perms").style.display = "none";
					document.getElementById("group_perms_groups_link").style.display = "";
				// ]]></script>
			</dd>';

		echo '
			<dt>
				<a id="setting_modtitle" href="', $scripturl, '?action=helpadmin;help=dream_module_title" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="mod_title">', $txt['dream_module_title'], '</label>
				</span>
			</dt>
			<dd>
				<input type="text" name="module_title" id="mod_title" value="', $context['mod_info'][$context['dp_modid']]['title'], '" size="35" class="input_text" />
			</dd>
			<dt>
				<a id="setting_modlink" href="', $scripturl, '?action=helpadmin;help=dream_module_link" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
				<span>
					<label for="mod_link">', $txt['dream_module_link'], '</label>
				</span>
			</dt>
			<dd>';
		$target = $context['mod_info'][$context['dp_modid']]['target'];
		echo '
				<span>&nbsp;
					<select name="module_link_target" id="mod_link_target" class="smalltext">
						<optgroup label="', $txt['dream_module_link_target'], '">
							<option value="0"', empty($target) ? ' selected="selected"' : '', '>', $txt['dream_module_link_blank'], '</option>
							<option value="1"', $target == 1 ? ' selected="selected"' : '', '>', $txt['dream_module_link_self'], '</option>
							<option value="2"', $target == 2 ? ' selected="selected"' : '', '>', $txt['dream_module_link_parent'], '</option>
							<option value="3"', $target == 3 ? ' selected="selected"' : '', '>', $txt['dream_module_link_top'], '</option>
						</optgroup>
					</select>
				</span>';

		echo '
				<input type="text" name="module_link" id="mod_link" value="', $context['mod_info'][$context['dp_modid']]['title_link'], '" size="35" class="floatleft input_text" />
			</dd>';
			
		if (empty($modSettings['dp_module_display_style']))
			echo '
				<dt>
					<a id="setting_modminheight" href="', $scripturl, '?action=helpadmin;help=dream_module_minheight" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>
					<span>
						<label for="mod_minheight">', $txt['dream_module_minheight'], '</label>
					</span>
				</dt>
				<dd>
					<span>&nbsp;
						<select name="module_minheight_type" id="mod_minheight_type" class="smalltext">
							<optgroup label="', $txt['dream_module_minheight_type'], '">
								<option value="0"', empty($context['mod_info'][$context['dp_modid']]['minheight_type']) ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_px'], '</option>
								<option value="1"', $context['mod_info'][$context['dp_modid']]['minheight_type'] == 1 ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_percentage'], '</option>
								<option value="2"', $context['mod_info'][$context['dp_modid']]['minheight_type'] == 2 ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_em'], '</option>
								<option value="3"', $context['mod_info'][$context['dp_modid']]['minheight_type'] == 3 ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_rem'], '</option>
								<option value="4"', $context['mod_info'][$context['dp_modid']]['minheight_type'] == 4 ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_ex'], '</option>
								<option value="5"', $context['mod_info'][$context['dp_modid']]['minheight_type'] == 5 ? ' selected="selected"' : '', '>', $txt['dream_module_minheight_type_pt'], '</option>
							</optgroup>
						</select>
					</span>
					<input type="text" name="module_minheight" id="mod_minheight" value="', $context['mod_info'][$context['dp_modid']]['minheight'], '" size="5" class="floatleft input_text" />
				</dd>';
	}

	$dp_counter = 0;
	$dp_script['dpAdmin'] = false;
	$dp_script['jscolor'] = false;

	// Now looping through the parameters.
	foreach ($context['config_params'] as $config_id => $config_param)
	{
		if (empty($context['config_params'][$config_id]))
			continue;

		// Show a separator.
		if (empty($dp_counter))
			echo '
				</dl>
					<hr class="hrcolor" />
				<dl class="settings">';
			
		$help = '<a id="setting_' . $config_param['name'] . '"></a></dt><dt>';

		// Show the [?] button.
		if (!empty($config_param['help']))
			$help = '
				<dt>
					<a id="setting_' . $config_param['name'] . '" href="' . $scripturl . '?action=helpadmin;help=' . $config_param['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" class="icon" /></a>
					<span>';

		echo $help, '
						<label for="', $config_param['label_id'], '">', $txt[$config_param['label']], '</label>
					</span>
				</dt>
			<dd>';
	
		// Here we have an actual fieldset that needs to grab all of the parameters within it!
		if ($config_param['is_fieldset'] && !empty($context['dp_fieldset']))
		{
			// Fieldsets can have legends, so we apply it if it's defined.
			echo '
					<fieldset id="' . $config_param['name'] . '">', (isset($txt[$config_param['label'] . '_legend']) ? '<legend><a href="javascript:void(0);" onclick="document.getElementById(\'' . $config_param['name'] . '\').style.display = \'none\';document.getElementById(\'' . $config_param['name'] . '_link\').style.display = \'block\'; return false;">' . $txt[$config_param['label'] . '_legend'] . '</a></legend>' : '') . '
					<dl class="settings">';

			foreach($context['dp_fieldset'][$config_param['id']]['fieldset'] as $f => $f_param)
			{
				// Increment the counter
				$dp_counter++;

				echo '
					<dt>';

				// Adding in help icons if needed here!
				if (!empty($f_param['param']['help']))
					echo '
						<a id="setting_' . $f_param['param']['name'] . '" href="' . $scripturl . '?action=helpadmin;help=' . $f_param['param']['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" class="icon" /></a>';

				echo '
						<span><label for="', $f_param['param']['label_id'], '">', $txt[$f_param['param']['label']], '</label></span></dt><dd>';

				processParameter($f_param['id'], $f_param['param']);

				echo '
						</dd>';
			}

			echo '
					</dl>
					</fieldset>';

			if (isset($txt[$config_param['label'] . '_legend']))
				echo '
						<a href="javascript:void(0);" onclick="document.getElementById(\'' . $config_param['name'] . '\').style.display = \'block\'; document.getElementById(\'' . $config_param['name'] . '_link\').style.display = \'none\'; return false;" id="' . $config_param['name'] . '_link" style="display: none;">[ ', $txt[$config_param['label'] . '_legend'], ' ]</a>
						<script type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("' . $config_param['name'] . '").style.display = "none";
						document.getElementById("' . $config_param['name'] . '_link").style.display = "";
						// ]]></script>';

			echo '
				</dd>';
		}
		else	// We are loading up non-fieldset parameters here!
		{
			// Increment the counter
			$dp_counter++;

			processParameter($config_id, $config_param);

			echo '
				</dd>';
		}
	}

	echo '
		</dl>
			<hr class="hrcolor clear" />
		<div class="righttext">
		<input type="submit" name="save" value="', $txt['save'], '" class="button_submit" />
		</div>
		</div>
		<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="', ($context['is_clone'] ? 'module' : 'modid'), '" value="', $context['dp_modid'], '" />
			<input type="hidden" name="modname" value="', $context['mod_info'][$context['dp_modid']]['name'], '" />
			<input type="hidden" name="modparams_count" value="', $dp_counter, '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>
			</div>
			<br class="clear" />';
}

function processParameter($config_id = 0, $config_param = array())
{
	global $txt, $context, $scripturl, $settings, $dp_counter, $dp_script, $boardurl;
	
	if ($config_param['type'] == 'check')
		echo '
			<input type="checkbox" name="', $config_param['name'], '" id="', $config_param['label_id'], '"', (!empty($config_param['value']) ? ' checked="checked"' : ''), ' value="1" class="input_check" />';

	elseif ($config_param['type'] == 'db_select' && $config_param['db_select_custom'])
	{
		if (!$dp_script['dpAdmin'])
		{
			$dp_script['dpAdmin'] = true;
			echo '
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>';
		}

		echo '
			<div id="db_select_option_list_', $config_id, '"">';

		foreach ($config_param['db_select_options'] as $key => $select_value)
			echo '
					<div id="db_select_container_', $config_param['label_id'], '_', $key, '"><input type="radio" name="', $config_param['name'], '" id="', $config_param['label_id'], '_', $key, '" value="', $key, '"', ($key == $config_param['db_selected'] ? ' checked="checked"' : ''), ' class="input_check" /> <label for="', $config_param['label_id'], '_', $key, '" id="label_', $config_param['label_id'], '_', $key, '">', $select_value ,'</label> <span id="db_select_edit_', $config_param['label_id'], '_', $key, '" class="smalltext">(<a href="#" onclick="dpEditDbSelect(', $config_id, ', \'', $config_param['label_id'], '_', $key, '\'); return false;" id="', $config_param['label_id'], '_', $key, '_db_custom_more">', $txt['dp_edit'], '</a>', $key != 1 ? ' - <a href="#" onclick="dpDeleteDbSelect(' . $config_id . ', \'' . $config_param['label_id'] . '_' . $key . '\'); return false;" id="' . $config_param['label_id'] . '_' . $key . '_db_custom_delete">' . $txt['delete'] . '</a>' : '', ')</span></div>';

		echo '
			</div>
				<input type="hidden" name="param_opts', $config_id, '" value="', $config_param['options'], '" />
				<script type="text/javascript"><!-- // --><![CDATA[
					function dpEditDbSelect(config_id, key)
					{
						var parent = document.getElementById(\'db_select_edit_\' + key);
						var child = document.getElementById(key + \'_db_custom_more\');
						var newElement = document.createElement("input");
						newElement.type = "text";
						newElement.value = document.getElementById(\'label_\' + key).innerHTML;
						newElement.name = "edit_" + key;
						newElement.id = "edit_" + key;
						newElement.className = "input_text";
						newElement.setAttribute("size", 30);

						parent.insertBefore(newElement, child);
						newElement.focus();
						newElement.select();

						document.getElementById(\'label_\' + key).style.display = \'none\';
						child.style.display = \'none\';

						newElement = document.createElement("span");
						newElement.innerHTML = " <a href=\"#\" onclick=\"dpSubmitEditDbSelect(" + config_id + ", \'" + key + "\'); return false;\">', $txt['dp_submit'], '</a> - <a href=\"#\" onclick=\"dpCancelEditDbSelect(" + config_id + ", \'" + key + "\'); return false;\">', $txt['dp_cancel'], '</a> - ";
						newElement.id = "db_select_edit_buttons_" + key;

						document.getElementById(\'db_select_edit_\' + key).insertBefore(newElement, document.getElementById(key + \'_db_custom_delete\'));

						return true;
					}

					function dpSubmitEditDbSelect(config_id, key)
					{
						var send_data = "data=" + escape(document.getElementById("edit_" + key).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B") + "&config_id=" + config_id + "&key=" + key;
						var url = smf_prepareScriptUrl(smf_scripturl) + "action=dream;sa=dbSelect;xml";

						sendXMLDocument(url, send_data);

						var parent = document.getElementById(\'db_select_edit_\' + key);

						document.getElementById(key + \'_db_custom_more\').style.display = \'\';
						document.getElementById(\'label_\' + key).innerHTML = document.getElementById("edit_" + key).value;
						document.getElementById(\'label_\' + key).style.display = \'\';
						parent.removeChild(document.getElementById(\'db_select_edit_buttons_\' + key));
						parent.removeChild(document.getElementById(\'edit_\' + key));

						return true;
					}

					function dpCancelEditDbSelect(config_id, key)
					{
						var parent = document.getElementById(\'db_select_edit_\' + key);

						parent.removeChild(document.getElementById(\'db_select_edit_buttons_\' + key));
						parent.removeChild(document.getElementById(\'edit_\' + key));
						document.getElementById(key + \'_db_custom_more\').style.display = \'\';
						document.getElementById(\'label_\' + key).style.display = \'\';

						return true;
					}

					function dpDeleteDbSelect(config_id, key)
					{
						var parent = document.getElementById(\'db_select_container_\' + key);

						newElement = document.createElement("span");
						newElement.innerHTML = document.getElementById(\'label_\' + key).innerHTML + " <span class=\"smalltext\">(', $txt['dp_deleted'], ' - <a href=\"#\" onclick=\"dpRestoreDbSelect(" + config_id + ", \'" + key + "\'); return false;\">', $txt['dp_restore'], '</a>)</span>";
						newElement.id = "db_select_deleted_" + key;

						parent.appendChild(newElement);
						oHidden = addHiddenElement("dpModule", document.getElementById(\'label_\' + key).innerHTML, "dpDeletedDbSelects_" + config_id);
						oHidden.id = "dpDeletedDbSelects_" + key;
						oHidden.name = "dpDeletedDbSelects_" + config_id + "[]";

						document.getElementById(key).style.display = \'none\';
						document.getElementById(\'label_\' + key).style.display = \'none\';
						document.getElementById(\'db_select_edit_\' + key).style.display = \'none\';

						return true;
					}

					function dpRestoreDbSelect(config_id, key)
					{
						var parent = document.getElementById(\'db_select_container_\' + key);
						var child = document.getElementById(\'db_select_deleted_\' + key);

						parent.removeChild(child);
						document.forms["dpModule"].removeChild(document.getElementById("dpDeletedDbSelects_" + key));

						document.getElementById(key).style.display = \'\';
						document.getElementById(\'label_\' + key).style.display = \'\';
						document.getElementById(\'db_select_edit_\' + key).style.display = \'\';

						return true;
					}

					function dpInsertBefore(oParent, oChild, sType)
					{
						var parent = document.getElementById(oParent);
						var child = document.getElementById(oChild);
						var newElement = document.createElement("input");
						newElement.type = sType;
						newElement.value = "";
						newElement.name = "', $config_param['name'], '_db_custom[]";
						newElement.className = "input_text";
						newElement.setAttribute("size", "' . $config_param['size'] . '");
						newElement.setAttribute("style", "display: block");

						parent.insertBefore(newElement, child);

						return true;
					}
				// ]]></script>
			<div id="', $config_param['name'], '_db_custom_container" class="smalltext">
					<a href="#" onclick="dpInsertBefore(\'', $config_param['name'], '_db_custom_container\', \'', $config_param['name'], '_db_custom_more\', \'text\'); return false;" id="', $config_param['name'], '_db_custom_more">(', $txt['dp_add_another'], ')</a>
			</div>';
	}
	elseif ($config_param['type'] == 'int')
		echo '
			<input type="text" name="', $config_param['name'], '" id="', $config_param['label_id'], '" value="', (!empty($config_param['value']) ? $config_param['value'] : 0), '"', ($config_param['size'] ? ' size="' . $config_param['size'] . '" ' : ' '), 'class="input_text" />';
	elseif ($config_param['type'] == 'large_text' || $config_param['type'] == 'html')
		echo '
			<textarea rows="', (!empty($config_param['size']) ? $config_param['size'] : 4), '" cols="60" name="', $config_param['name'], '" id="', $config_param['label_id'], '">', $config_param['value'], '</textarea>';
	elseif ($config_param['type'] == 'color')
	{
		if (!$dp_script['jscolor'])
		{
			$dp_script['jscolor'] = true;
			echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					var dreamportal_url = "' . $boardurl . '/dreamportal";
				// ]]></script>
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/jscolor.js"></script>';
		}

		echo '
			<input class="color' . (!empty($config_param['color_vars']) ? ' ' . $config_param['color_vars'] : '') . '" name="', $config_param['name'], '" id="', $config_param['label_id'], '" value="', $config_param['value'], '" size="', $config_param['size'], '" />';
	}
	elseif ($config_param['type'] == 'select' || $config_param['type'] == 'list_boards' || ($config_param['type'] == 'db_select' && !$config_param['db_select_custom']))
	{
		echo '
			<select name="', $config_param['name'], '" id="', $config_param['label_id'], '">';

			// Show all boards within a Category for each Category.
			if ($config_param['type'] == 'list_boards')
			{
				foreach ($config_param['select_options'] as $key => $option)
				{
					echo '
								<optgroup label="', $option['category'], '">';

					foreach ($option['board'] as $boardid => $board)
						echo '
										<option value="', $boardid, '"', ((strval($boardid) == $config_param['select_value'] || (trim($config_param['select_value']) == '' && empty($boardid))) ? ' selected="selected"' : ''), '>', $board, '</option>';

					echo '
								</optgroup>';
				}
			}
			elseif ($config_param['type'] == 'db_select' && !$config_param['db_select_custom'])
			{
				if(!$dp_script['dpAdmin'])
				{
					$dp_script['dpAdmin'] = true;
					echo '
								<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>';
				}

				foreach ($config_param['db_select_options'] as $key => $select_value)
					echo '
									<option value="', $key, '"', ($key == $config_param['db_selected'] ? ' selected="selected"' : ''), '>', $select_value, '</option>';
			}
			else
			{
				foreach ($config_param['select_options'] as $key => $option)
				{
					if ($config_param['type'] == 'select')
						$option = isset($txt['dpmod_' . $config_param['name'] . '_' . $option]) ? $txt['dpmod_' . $config_param['name'] . '_' . $option] : $option;

					echo '
								<option value="', $key, '"', ($key == $config_param['select_value'] || (trim($config_param['select_value']) == '' && empty($key)) ? ' selected="selected"' : ''), '>', $option, '</option>';
				}
			}

			echo '
							</select>';

		if ($config_param['type'] == 'select' || ($config_param['type'] == 'db_select' && !$config_param['db_select_custom']))
			echo '
							<input type="hidden" name="param_opts', $config_id, '" value="', $config_param['options'], '" />';
	}

	// Rich Edit text area.
	elseif ($config_param['type'] == 'rich_edit')
		template_control_richedit($config_param['post_box_name']);

	// BBC list...
	elseif ($config_param['type'] == 'list_bbc')
	{
		if(!$dp_script['dpAdmin'])
		{
			$dp_script['dpAdmin'] = true;
			echo '
			<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>';
		}

		echo '
				<fieldset id="', $config_param['name'], '">
					<legend>', $txt['bbcTagsToUse_select'], '</legend>
						<ul class="reset">';

		foreach ($config_param['bbc_columns'] as $bbcColumn)
		{
			foreach ($bbcColumn as $bbcTag)
				echo '
							<li class="list_bbc floatleft">
								<input type="checkbox" name="', $config_param['name'], '_enabledTags[]" id="tag_', $config_param['name'], '_', $bbcTag['tag'], '" value="', $bbcTag['tag'], '"', isset($config_param['bbc_sections'][$bbcTag['tag']]['disabled']) && !in_array($bbcTag['tag'], $config_param['bbc_sections'][$bbcTag['tag']]['disabled']) ? ' checked="checked"' : '', ' class="input_check" /> <label for="tag_', $config_param['name'], '_', $bbcTag['tag'], '">', $bbcTag['tag'], '</label>', $bbcTag['show_help'] ? ' (<a href="' . $scripturl . '?action=helpadmin;help=tag_' . $bbcTag['tag'] . '" onclick="return reqWin(this.href);">?</a>)' : '', '
							</li>';
		}
		echo '			</ul>
		<input type="checkbox" id="select_all', $config_id, '" onclick="invertAll(this, this.form, \'', $config_param['name'], '_enabledTags\');"', $config_param['bbc_all_selected'] ? ' checked="checked"' : '', ' class="input_check" /> <label for="select_all', $config_id, '"><em>', $txt['bbcTagsToUse_select_all'], '</em></label>
				</fieldset>';
	}

	// List Groups or Checklist.
	elseif ($config_param['type'] == 'list_groups' || $config_param['type'] == 'checklist')
	{
		$checkCount = 0;

		$checkid = $config_param['type'] == 'list_groups' ? 'grp' : 'chk';
		$checkname = $config_param['type'] == 'list_groups' ? 'group' : 'check';

		if($config_param['check_order'] && !$dp_script['dpAdmin'])
		{
			$dp_script['dpAdmin'] = true;
			echo '
			<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>';
		}

		foreach($config_param['check_options'] as $check)
		{
			$checkCount++;
			echo '
				<div id="', $checkid, '_', $dp_counter . '_' . $checkCount, '"><label for="', $checkname . 's_' . $dp_counter . $check['id'], '"><input type="checkbox" name="', $checkname . 's' . $dp_counter, '[]" value="', $check['id'], '" id="', $checkname . 's_' . $dp_counter . $check['id'], '" ', ($check['checked'] ? 'checked="checked" ' : ''), 'class="input_check" /><span', ($config_param['type'] == 'list_groups' ? ($check['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : '') : ''), '>', $check['name'], '</span></label>', $config_param['check_order'] ? '<span style="padding-left: 10px;"><a href="javascript:void(0);" onClick="moveUp(this.parentNode.parentNode); orderChecks(\'' . $checkid . '_' . $dp_counter . '_' . $checkCount . '\', \'order' . $checkid . '_' . $dp_counter . '\');">' . $txt['checks_order_up'] . '</a> | <a href="javascript:void(0);" onClick="moveDown(this.parentNode.parentNode); orderChecks(\'' . $checkid . '_' . $dp_counter . '_' . $checkCount . '\', \'order' . $checkid . '_' . $dp_counter . '\');">' . $txt['checks_order_down'] . '</a></span>' : '', '</div>';
		}
		echo '
				<em>', $txt['check_all'], '</em> <input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'', $checkname . 's' . $dp_counter, '[]\');" /><br />
				<br />
				<input type="hidden" name="conval' . $checkid . '_' . $dp_counter . '" value="' . $config_param['check_value'] . '" />', ($config_param['check_order'] ? '
				<input type="hidden" id="order' . $checkid . '_' . $dp_counter . '" name="order' . $checkid . '_' . $dp_counter . '" value="' . $context[$checkname . '_order' . $config_param['id']] . '" />' : '');
	}

	// File Input
	elseif ($config_param['type'] == 'file_input')
	{
		$file_count = !empty($context['current_files'][$config_param['id']]) ? ($config_param['file_count'] - count($context['current_files'][$config_param['id']])) : $config_param['file_count'];

		// Has any files been uploaded for this parameter already?
		if (!empty($context['current_files'][$config_param['id']]))
		{
				echo '
				<div>
					<div>
					', $txt['files'], '</div>
					<div class="smalltext">
						<input type="hidden" name="file_del', $dp_counter, '[]" value="0" />
						(', $txt['uncheck_unwanted_files'], ')
					</div>';
				foreach ($context['current_files'][$config_param['id']] as $key => $file)
					echo '
						<div class="smalltext">
							<label for="file_', $file['id'], '"><input type="checkbox" id="file_', $file['id'], '" name="file_del', $dp_counter, '[]" value="', $file['id'], '" checked="checked" class="input_check" /> ', $file['name'], '</label>
						</div>';

				echo '
					</div>';
		}

		// Show more file inputs only if they aren't approaching their limit.
		if ($file_count >= 1 || empty($config_param['file_count']))
			echo '
						<input type="file" name="', $config_param['name'], '[]" size="38" class="input_file" />';

		if ($file_count > 1 || empty($config_param['file_count']))
		{
			echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[

				var allowed_files', $dp_counter, ' = ', $file_count, ' - 1;
				var exception', $dp_counter, ' = ', empty($config_param['file_count']) ? 'true' : 'false', ';

				function addFile' . $dp_counter . '()
				{
					if (allowed_files', $dp_counter, ' <= 0 && !exception', $dp_counter, ')
						return alert("', $txt['more_files_error'], '");

					if (allowed_files', $dp_counter, ' >= 1 || exception', $dp_counter, ')
						setOuterHTML(document.getElementById("moreFiles', $dp_counter, '"), \'<div><input type="file" size="38" name="', $config_param['name'], '[]" class="input_file" /><\' + \'/div><div id="moreFiles', $dp_counter, '"><a href="#" onclick="addFile', $dp_counter, '(); return false;">(', $txt['more_files'], ')<\' + \'/a><\' + \'/div>\');

					if (allowed_files', $dp_counter, ' == 1 && !exception', $dp_counter, ')
						document.getElementById("moreFiles', $dp_counter, '").style.display = "none";

					if (!exception', $dp_counter, ')
						allowed_files', $dp_counter, ' = allowed_files', $dp_counter, ' - 1;

					return true;
				}

			// ]]></script>';


			echo '
						<div id="moreFiles', $dp_counter, '"><a href="#" onclick="addFile', $dp_counter, '(); return false;">(', $txt['more_files'], ')</a></div>';
		}

		echo '
			<input type="hidden" name="file_mimes', $dp_counter, '" value="', $config_param['file_mimes'], '" />
			<input type="hidden" name="file_count', $dp_counter, '" value="', $config_param['file_count'], '" />
			<input type="hidden" name="file_dimensions', $dp_counter, '" value="', $config_param['file_dimensions'], '" />';
	}
	// Just show a regular textbox.
	else
	{
		echo '
					<input type="text" name="', $config_param['name'], '" id="', $config_param['label_id'], '" value="', $config_param['value'], '"', ($config_param['size'] ? ' size="' . $config_param['size'] . '"' : ''), ' class="input_text" />';
	}

	// Holds all parameters param_name1, param_name2, param_name3, and so on.
	echo '
			<input type="hidden" name="param_txt_value', $dp_counter, '" value="', $config_param['txt_value'], '" />
			<input type="hidden" name="param_name', $dp_counter, '" value="', $config_param['name'], '" />
			<input type="hidden" name="param_id', $dp_counter, '" value="', $config_id, '" />
			<input type="hidden" name="param_type', $dp_counter, '" value="', $config_param['type'], '" />';
}

/**
 * Template used to manage the position of modules/clones.
 *
 * @since 1.0
 */
function template_manage_layouts()
{
	global $txt, $context, $scripturl, $options, $modSettings;

	// Build the normal button array.
	$dream_buttons = array(
		'add' => array('text' => 'add_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=admin;area=dplayouts;sa=dpaddlayout;' . $context['session_var'] . '=' . $context['session_id']),
	);

	if ((empty($modSettings['dp_disable_homepage']) && $_SESSION['selected_layout']['name'] == $txt['dp_homepage']) || $_SESSION['selected_layout']['name'] != $txt['dp_homepage'])
		$dream_buttons += array(
			'edit' => array('text' => 'edit_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => 'javascript:void(0);', 'custom' => 'onclick="javascript:submitLayout(\'editlayout\', \'' . $scripturl . '?action=admin;area=dplayouts;sa=dpeditlayout;\', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\');"'),
		);

	if ($_SESSION['selected_layout']['name'] != $txt['dp_homepage'] && count($_SESSION['layouts']) > 1)
		$dream_buttons += array(
			'del' => array('text' => 'delete_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => 'javascript:void(0);', 'custom' => 'onclick="javascript:submitLayout(\'' . $txt['confirm_delete_layout'] . '\', \'' . $scripturl . '?action=admin;area=dplayouts;sa=dpdellayout;\', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\');"'),
		);

	echo '
	<div class="floatleft" style="width: 100%;">
		<div class="floatright">
			<form name="urLayouts" id="dpmod_change_layout" action="', $scripturl, '?action=admin;area=dplayouts;sa=dpmanlayouts;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
				<select onchange="document.forms[\'dpmod_change_layout\'].submit();" name="layout_picker" style="width: 100%;">';
				
		foreach ($_SESSION['layouts'] as $id_layout => $layout_name)
			echo '
					<option value="', $id_layout, '"', ($_SESSION['selected_layout']['id_layout'] == $id_layout ? ' selected="selected"' : ''), '>', $layout_name, '', (!empty($modSettings['dp_disable_homepage']) && $layout_name == $txt['dp_homepage'] ? ' ' . $txt['dp_admin_layout_disabled'] : ''), '</option>';

	echo '
				</select>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>';

	template_button_strip($dream_buttons, 'right');

	// Input the back colors.
	echo '
				<div class="dp_colour_menu" style="margin-right: -12px;">
					<ul class="dp_select">
						<li class="dp_line"><a href="javascript:void(0);"><img src="', $context['dpadmin_image_url'], '/dp_colors.png" alt="', $txt['dp_alt_module_colors'], '" title="', $txt['dp_module_colors'], '" width="25" height="25" border="0" /></a>
							<ul class="dp_sub">
								<li><a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'1\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/white.png" alt="', $txt['dp_alt_white'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'2\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/gray.png" alt="', $txt['dp_alt_gray'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'3\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/blue.png" alt="', $txt['dp_alt_blue'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'4\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/yellow.png" alt="', $txt['dp_alt_yellow'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'5\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/green.png" alt="', $txt['dp_alt_green'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'6\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/orange.png" alt="', $txt['dp_alt_orange'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'7\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/red.png" alt="', $txt['dp_alt_red'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'8\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/purple.png" alt="', $txt['dp_alt_purple'], '" width="25" height="25" border="0" /></a></li>
								<li>
									<a href="javascript:void(0);" onclick="javascript:loadModuleColors(\'9\', \'' . $context['session_id'] . '\');" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/black.png" alt="', $txt['dp_alt_black'], '" width="25" height="25" border="0" /></a></li>
							</ul>
						</li>
					</ul>
				</div></div>
				<div id="messages"></div></div>
				<div class="module_page floatright">';

	if (isset($context['dp_columns']['disabled']))
	{
		echo '
					<div id="module_container_', $context['dp_columns']['disabled']['id_layout_position'], '" class="disabled module_holder">
						<div class="cat_bar block_header">
							<h3 class="catbg centertext">
								', $txt['dp_admin_modules_manage_col_disabled'], '
							</h3>
						</div>
						<div class="roundframe blockframe module_container" id="dpcol_', $context['dp_columns']['disabled']['id_layout_position'], '">';

		if (!empty($context['dp_columns']['disabled']['modules']))
			foreach($context['dp_columns']['disabled']['modules'] as $module => $id)
				echo '
							<div class="DragBox ', ($id['is_clone'] ? 'clonebox' : 'modbox'), '', (!empty($options['dp_mod_color']) ? $options['dp_mod_color'] : '1'), ' draggable_module centertext" id="dreammod_' . $id['id'] . '">
								<p>
									', $id['title'], '
								</p>
								<p class="dp_inner">
									', $id['modify'], ' | ', $id['clone'], '
								</p>
							</div>';

		echo '
							<div class="draggable_module dummy">&nbsp;</div>
						</div>
						<span class="lowerframe"><span></span></span>
					</div>
					<div class="clear"></div>
				</div>
				<div class="module_page floatleft">';

		unset($context['dp_columns']['disabled']);
	}

	echo '
					<table width="100%" cellspacing="11" style="table-layout: fixed;">';

	foreach ($context['dp_columns'] as $row_id => $row_data)
	{
		echo '
						<tr class="tablerow', $row_id, '" valign="top">';

		foreach ($row_data as $column_id => $column_data)
		{
			if (isset($column_data['disabled_module_container']) && $column_data['disabled_module_container'] === false)
			{
				echo '
							<td class="tablecol_', $column_id, '"', $context['span']['rows'][$column_data['id_layout_position']], $context['span']['columns'][$column_data['id_layout_position']], '>

								<div id="module_container_', $column_data['id_layout_position'], '" class="enabled" style="width: 100%;">
									<div class="cat_bar block_header">
										<h3 class="catbg centertext">
											', (!$column_data['is_smf'] ? '<input type="checkbox" ' . (!empty($column_data['enabled']) ? 'checked="checked" ' : '') . 'id="column_' . $column_data['id_layout_position'] . '" class="check_enabled input_check" /><label for="column_' . $column_data['id_layout_position'] . '">' . $txt['dp_admin_modules_manage_col_section'] . '</label>' : $txt['dp_is_smf_section']), '
										</h3>
									</div>
									<div class="roundframe blockframe ', (!$column_data['is_smf'] ? 'module' : 'smf'), '_container" id="dp', (!$column_data['is_smf'] ? 'col_' . $column_data['id_layout_position'] : 'smf'), '">';

					if (!empty($column_data['modules']))
					{
						foreach($column_data['modules'] as $module => $id)
						{
							if ($id['is_smf'])
							{
								echo '
											<div class="smf_content" id="smfmod_', $id['id'], '"><strong>', $txt['dp_smf_mod'], '</strong></div>
											<script type="text/javascript"><!-- // --><![CDATA[
												var smf_container = document.getElementById("smfmod_', $id['id'], '").parentNode;
												smf_container.className = "roundframe blockframe";
											// ]]></script>';
								continue;
							}
							echo '
											<div class="DragBox ', ($id['is_clone'] ? 'clonebox' : 'modbox'), '', (!empty($options['dp_mod_color']) ? $options['dp_mod_color'] : '1'), ' draggable_module centertext" id="dreammod_' . $id['id'] . '">
												<p>
													', $id['title'], '
												</p>
												<p class="dp_inner">
													', $id['modify'], ' | ', $id['clone'], '
												</p>
											</div>';
						}
					}
					echo '
											<div class="draggable_module dummy">&nbsp;</div>';
					echo '
										</div>
										<span class="lowerframe"><span></span></span>
									</div>
								</td>';
			}
		}

				echo '
							</tr>';
	}
	echo '
						</table>
						<span class="botslice"><span></span></span>
					</div>
					<br class="clear" />
						<div class="padding righttext">
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
						</div>';
}

/**
 * Template used to add a new layout.
 *
 * @since 1.0
 */
function template_add_layout()
{
	global $txt, $context, $scripturl, $settings;

		echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				var nonallowed_actions = \''. implode('|', $context['unallowed_actions']) . '\';
				var exceptions = nonallowed_actions.split("|");
			// ]]></script>
			<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['add_layout'], '
				</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
			<form name="dpFlayouts" id="dpLayouts" ', isset($context['dp_file_input']) ? 'enctype="multipart/form-data" ' : '', 'action="', $scripturl, '?action=admin;area=dplayouts;sa=dpaddlayout2;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
					<div class="content">';

						// If there were errors when adding the Layout, show them.
						if (!empty($context['layout_error']['messages']))
						{
							echo '
									<div class="errorbox">
										<strong>', $txt['layout_error_header'], '</strong>
										<ul>';

							foreach ($context['layout_error']['messages'] as $error)
								echo '
											<li class="error">', $error, '</li>';

							echo '
										</ul>
									</div>';
						}

					echo '
						<dl class="settings">
							<dt>
								<a id="setting_layoutname" href="', $scripturl, '?action=helpadmin;help=dp_layout_name" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a><span', (isset($context['layout_error']['no_layout_name']) || isset($context['layout_error']['layout_exists']) ? ' class="error"' : ''), '>', $txt['dp_layout_name'], ':</span>
							</dt>
							<dd>
									<input type="text" name="layout_name" ', (!empty($context['layout_name']) ? 'value="' . $context['layout_name'] . '" ' : ''), 'class="input_text" style="width: 295px;" />
							<dd>
							<dt>
							<a id="setting_actions" href="', $scripturl, '?action=helpadmin;help=dp_layout_actions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a><span><strong>', $txt['dp_action_type'], '</strong><br />
								<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_smf_actions" value="smf_actions" checked="checked" class="input_radio" /><label for="action_choice_smf_actions">' . $txt['select_smf_actions'] . '</label><br />', '
								<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_user_defined" value="user_defined" class="input_radio" /><label for="action_choice_user_defined">' . $txt['select_user_defined_actions'] . '</label></span>
							</dt>
							<dd>
							<div class="floatleft" id="action_smf_actions">
									<select id="actions" name="dpLayout_smf_actions" style="max-width: 300px;" onfocus="selectRadioByName(document.forms.dpFlayouts.action_choice, \'smf_actions\');">';
									foreach($context['available_actions'] as $action)
										echo '
											<option value="', $action, '">', $action, '</option>';
									echo '
									</select>
							</div>
							<div id="action_user_defined2" class="smalltext">', $txt['select_user_defined_actions_desc'], '</div>
							<div class="floatleft" id="action_user_defined">
								<input id="udefine" type="text" name="dpLayout_user_defined" size="34" value="" onfocus="selectRadioByName(document.forms.dpFlayouts.action_choice, \'user_defined\');" class="input_text" />
							</div>
							<div style="float: left; margin-left: 5px;"><input type="button" value="', $txt['dp_add_action'], '" onclick="javascript:addAction();" class="button_submit smalltext"></div>';
			echo '
								<script type="text/javascript"><!-- // --><![CDATA[
								// This is shown by default.
								document.getElementById("action_smf_actions").style.display = "";
								document.getElementById("action_user_defined").style.display = "none";
								document.getElementById("action_user_defined2").style.display = "none";
								document.getElementById("action_choice_smf_actions").checked = true;
								// ]]></script>
							</dd>
							<dt><span><a id="setting_curr_actions" href="', $scripturl, '?action=helpadmin;help=dp_layout_curr_actions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_actions'], '
							</span></dt>
							<dd>
									<select id="actions_list" name="layouts" multiple style="height: 128px; width: 300px;', (isset($context['layout_error']['no_actions']) ? ' border: 1px solid red;' : ''), '">';
							foreach($context['current_actions'] as $cur_action)
								echo '
									<option value="', $cur_action, '">', $cur_action, '</option>';

		echo '
									</select><br /><input type="button" value="', $txt['dp_remove_actions'], '" onclick="javascript:removeActions();" class="button_submit smalltext">
							</dd>
							<dt><span><a id="setting_layout_style" href="', $scripturl, '?action=helpadmin;help=dp_layout_style" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_style'], '</span></dt>
							<dd>
									<select name="layout_style" style="width: 300px;">';

		foreach ($context['layout_styles'] as $num => $layout_style)
			echo '
										<option value="', $num, '"', ($context['selected_layout'] == $num ? ' selected="selected"' : ''), '>', $txt['layout_style_' . $layout_style], '</option>';

		echo '
									</select>
							</dd>
						</dl>
						<hr class="hrcolor">
						<div id="lay_right" class="righttext">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

		foreach($context['current_actions'] as $k => $cur_action)
			echo
									'<input id="dream_action', $k, '" name="layout_actions[]" type="hidden" value="', $cur_action, '" />';

		echo '
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
						</div>
					</div>
					</form>
				<span class="botslice"><span></span></span>
			</div>';
}

/**
 * Template used to edit an existing layout.
 *
 * @since 1.0
 */
function template_edit_layout()
{
	global $txt, $context, $scripturl, $settings;

	echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				var nonallowed_actions = \''. implode('|', $context['unallowed_actions']) . '\';
				var exceptions = nonallowed_actions.split("|");
			// ]]></script>
			<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dpAdmin.js"></script>
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['edit_layout'], '
				</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
			<form name="dpFlayouts" id="dpLayouts" ', isset($context['dp_file_input']) ? 'enctype="multipart/form-data" ' : '', 'action="', $scripturl, '?action=admin;area=dplayouts;sa=dpeditlayout2;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="beforeLayoutEditSubmit()">
					<div class="content">';

	// If there were errors when editing the Layout, show them.
	if (!empty($context['layout_error']['messages']))
	{
		echo '
									<div class="errorbox">
										<strong>', $txt['edit_layout_error_header'], '</strong>
										<ul>';

		foreach ($context['layout_error']['messages'] as $error)
			echo '
											<li class="error">', $error, '</li>';

		echo '
										</ul>
									</div>';
	}

		echo '
						<dl class="settings">';

	if ($context['show_smf'])
	{
		echo '
							<dt>
								<a id="setting_layoutname" href="', $scripturl, '?action=helpadmin;help=dp_layout_name" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a><span', (isset($context['layout_error']['no_layout_name']) || isset($context['layout_error']['layout_exists']) ? ' class="error"' : ''), '>', $txt['dp_layout_name'], ':</span>
							</dt>
							<dd>
									<input type="text" name="layout_name" value="' . $context['layout_name'], '" class="input_text" style="width: 295px;', (isset($context['layout_error']['no_layout_name']) ? ' border: 1px solid red;' : ''), '" />
							<dd>
							<dt>
							<a id="setting_actions" href="', $scripturl, '?action=helpadmin;help=dp_layout_actions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a><span><strong>', $txt['dp_action_type'], '</strong><br />
							<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_smf_actions" value="smf_actions" checked="checked" class="input_radio" /><label for="action_choice_smf_actions">' . $txt['select_smf_actions'] . '</label><br />', '
								<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_user_defined" value="user_defined" class="input_radio" /><label for="action_choice_user_defined">' . $txt['select_user_defined_actions'] . '</label></span>
							</dt>
							<dd>
							<div class="floatleft" id="action_smf_actions">
									<select id="actions" name="dpLayout_smf_actions" style="max-width: 300px;" onfocus="selectRadioByName(document.forms.dpFlayouts.action_choice, \'smf_actions\');">';

		foreach ($context['available_actions'] as $action)
			echo '
											<option value="', $action, '">', $action, '</option>';

		echo '
									</select>
							</div>
							<div id="action_user_defined2" class="smalltext">', $txt['select_user_defined_actions_desc'], '</div>
							<div class="floatleft" id="action_user_defined">
								<input id="udefine" type="text" name="dpLayout_user_defined" size="34" value="" onfocus="selectRadioByName(document.forms.dpFlayouts.action_choice, \'user_defined\');" class="input_text" />
							</div>
							<div style="float: left; margin-left: 5px;"><input type="button" value="', $txt['dp_add_action'], '" onclick="javascript:addAction();" class="button_submit smalltext"></div>';

		echo '
								<script type="text/javascript"><!-- // --><![CDATA[
									// This is shown by default.
									document.getElementById("action_smf_actions").style.display = "";
									document.getElementById("action_user_defined").style.display = "none";
									document.getElementById("action_user_defined2").style.display = "none";
									document.getElementById("action_choice_smf_actions").checked = true;
								// ]]></script>
							</dd>
							<dt><span', (isset($context['layout_error']['no_actions']) ? ' class="error"' : ''), '><a id="setting_curr_actions" href="', $scripturl, '?action=helpadmin;help=dp_layout_curr_actions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_actions'], '
							</span></dt>
							<dd>
									<select id="actions_list" name="layouts" multiple style="height: 128px; width: 300px;', (isset($context['layout_error']['no_actions']) ? ' border: 1px solid red;' : ''), '">';

		foreach($context['current_actions'] as $cur_action)
			echo '
									<option value="', $cur_action, '">', $cur_action, '</option>';

		echo '
									</select><br /><input type="button" value="', $txt['dp_remove_actions'], '" onclick="javascript:removeActions();" class="button_submit smalltext">
							</dd>';
	}

	echo '
							<dt><span', (isset($context['layout_error']['no_sections']) ? ' class="error"' : ''), '><a id="setting_curr_actions" href="', $scripturl, '?action=helpadmin;help=dp_layout_curr_sections" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_sections'], '
							</span></dt>
							<dd></dd>
							<table class="table_grid" width="100%" cellspacing="0" id="edit_layout">
								<thead>
								<tr class="catbg">
									<th class="first_th" scope="col">', $txt['dp_columns_header'],'</th>
									<th scope="col">', $txt['colspans'],'</th>
									<th scope="col">', $txt['enabled'], '</th>';

	if ($context['show_smf'])
		echo '
									<th scope="col">', $txt['dp_is_smf_section'], '</th>';

								echo '<th class="last_th" scope="col"><input id="all_checks" type="checkbox" class="input_check" onclick="invertChecks(this, this.form, \'check_\');" /></th>
								</tr></thead>';

		// Some js variables to make this easier.
		echo '<script type="text/javascript"><!-- // --><![CDATA[
					var checkClass = "input_check";
					var textClass = "input_text";
					var radioClass = "input_radio";
					var columnString = \'', $txt['dp_column'], '\';
					var rowString = \'', $txt['dp_row'], '\';
					var newColumns = 0;
					var totalColumns = ', $context['total_columns'], ';
					var totalRows = ', $context['total_rows'], ';
					// Some error variables here.
					var delAllRowsError = \'', $txt['dp_cant_delete_all'], '\';
					// ]]></script>';

	$rows = array();
	$xRow = 0;
	$i = 0;

	echo '<tbody id="edit_layout_tbody">';

	foreach($context['current_sections'] as $column)
	{
		$rows[] = $xRow + 1;
		$windowbg = '';
		$pCol = 0;

		echo '
								<tr class="titlebg2" id="row_', $xRow, '"><td align="center" colspan="', ($context['show_smf'] ? '6' : '5'), '"><label for="inputrow_', $xRow, '">', $txt['dp_row'], ' ', ($xRow + 1), '</label> <input id="inputrow_', $xRow, '" type="checkbox" class="input_check" onclick="invertChecks(this, this.form, \'check_', $xRow, '_\');" /></td></tr>';

		foreach($column as $section)
		{
			$i++;

			if ($section['is_smf'] && $context['show_smf'])
			{
				$smfRow = $xRow;
				$smfCol = $pCol;
				$smfSection = $section['id_layout_position'];
			}

	echo '
							<tr class="windowbg', $windowbg, '" id="tr_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '">
								<td id="tdcolumn_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '"><div class="floatleft"><a href="javascript:void(0);" onclick="javascript:columnUp(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();"><img src="' . $context['dpadmin_image_url'] . '/dp_up.gif" style="width: 12px; height: 11px;" border="0" /></a> <a href="javascript:void(0);" onclick="javascript:columnDown(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();"><img src="', $context['dpadmin_image_url'], '/dp_down.gif" style="width: 12px; height: 11px;" border="0" /></a></div><span class="dp_edit_column" id="column_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '">', $txt['dp_column'], ' ', $pCol + 1, '</span></td>
								<td id="tdcspans_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '" style="text-align: center;"><input type="text" id="cspans_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '" name="colspans[', $section['id_layout_position'], ']" size="5" value="', (isset($_POST['colspans'][$section['id_layout_position']]) ? $_POST['colspans'][$section['id_layout_position']] : $section['colspans']), '"', (in_array($section['id_layout_position'], $context['colspans_error_ids']) ? ' style="border: 1px solid red;"' : ''), ' class="input_text" /></td>
								<td style="text-align: center;" id="tdenabled_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '">', (!$section['is_smf'] ? '<input type="checkbox" id="enabled_' . $xRow . '_' . $pCol . '_' . $section['id_layout_position'] . '" name="enabled[' . $section['id_layout_position'] . ']"' . ($section['enabled'] ? ' checked="checked"' : '') . ' class="input_check" />' : ''), '</td>';

if ($context['show_smf'])
	echo '
								<td style="text-align: center;" id="tdradio_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '"><input type="radio" id="radio_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '" name="smf_radio"', ' onfocus="if(this.blur)this.blur();" onclick="javascript:smfRadio(\'', $xRow, '\', \'', $pCol, '\', \'', $section['id_layout_position'], '\');" value="' . $section['id_layout_position'], '" class="input_radio" /></td>';

	echo '
								<td style="text-align: center;" id="tdcheck_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '">', (!$section['is_smf'] ? '<input type="checkbox" id="check_' . $xRow . '_' . $pCol . '_' . $section['id_layout_position'] . '" name="section[]" class="input_check" />' : ''), '</td>
							<input type="hidden" name="layout_position[]" value="', $section['id_layout_position'], '" />';

	if ($context['show_smf'] && $section['is_smf'])
	{
		$smf_section = $section['id_layout_position'];

		echo '
							<input type="hidden" name="old_smf_pos" value="', $section['id_layout_position'], '" />';
	}

		echo '
			</tr>';

			$windowbg = $windowbg == '2' ? '' : '2';

			$pCol++;
		}

		$xRow++;
	}

		echo '
	</tbody></table>';

		echo '
			<script type="text/javascript"><!-- // --><![CDATA[

				var dp_downImg = "', $context['dpadmin_image_url'], '/dp_down.gif";
				var dp_upImg = "', $context['dpadmin_image_url'], '/dp_up.gif";';

			if ($context['show_smf'])
				echo '
					var smfLayout = true;
					var rowPos = ', $smfRow, ';
					var colPos = ', $smfCol, ';
					var layoutPos = ', $smfSection, ';
					createEventListener(window);
					window.addEventListener("load", checkSMFRadio, false);';
			else
				echo '
						var smfLayout = false;
						var rowPos = -1;
						var colPos = -1;
						var layoutPos = -1;';

		echo '
			// ]]></script>';

			echo '
			</dl>
			<div class="floatright">
			<p style="text-align: right;"><label for="add_column">', $txt['dp_add_column'], '</label> <select id="selAddColumn">';

					foreach($rows as $key => $value)
						echo '
							<option value="', $key, '">', $txt['dp_row'], ' ', $value, '</option>';

		echo '
			</select> <input type="button" class="button_submit" value="', $txt['dp_add_column_button'], '" onclick="javascript:addColumn();" />
			</p>
			<p style="text-align: right;">
			<input type="button" class="button_submit" value="', $txt['dp_add_row'], '" onclick="javascript:addRow();" /> <input type="button" class="button_submit" value="', $txt['dp_edit_remove_selected'], '" onclick="javascript:deleteSelected(\'', $txt['confirm_remove_selected'], '\');" />
			</p></div>

					<div style="clear: right;">
						<hr class="hrcolor">
						<div id="lay_right" class="righttext">', ($context['show_smf'] ? '
							<input type="hidden" id="smf_section" name="smf_id_layout_position" value="' . $smf_section . '" />' : ''), '
							<input type="hidden" name="disabled_section" value="', $context['disabled_section'], '" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" id="layout_picker" name="layout_picker" value="', $_POST['layout_picker'], '" />
							<input type="hidden" id="remove_positions" name="remove_positions" value="" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

	foreach($context['current_actions'] as $k => $cur_action)
		echo '
							<input id="dream_action', $k, '" name="layout_actions[]" type="hidden" value="', $cur_action, '" />';

	echo '
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
						</div>
					</div>
				</div>
			</form>
				<span class="botslice"><span></span></span>
			</div>';
}

function template_callback_dpmodule_header_heights()
{
	global $txt, $context, $settings, $scripturl;
	
		echo '
			<dt>
				<a id="setting_dpmodule_header_heights" href="', $scripturl, '?action=helpadmin;help=dpmodule_header_heights_help" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['dp_module_header_height'], '
			</dt>
			<dd>
				<fieldset id="mod_theme_heights">
					<legend><a href="javascript:void(0);" onclick="document.getElementById(\'mod_theme_heights\').style.display = \'none\';document.getElementById(\'mod_theme_heights_link\').style.display = \'block\'; return false;">', $txt['dp_module_header_height_legend'], '</a></legend>
					<dl class="settings">';
			
			foreach ($context['module_themes'] as $theme)
			{
				echo '
					<dt>', $theme['theme_name'], '</dt>
					<dd>
						<input type="text" name="dp_mod_header', $theme['name'], '" id="', $theme['name'], '" value="', $theme['value'], '" size="3" class="input_text" /> ', $txt['dp_pixels'], '
					</dd>';
			}

		echo '
			</dl>
			</fieldset>
			<a href="javascript:void(0);" onclick="document.getElementById(\'mod_theme_heights\').style.display = \'block\'; document.getElementById(\'mod_theme_heights_link\').style.display = \'none\'; return false;" id="mod_theme_heights_link" style="display: none;">[ ', $txt['dp_module_header_height_legend'], ' ]</a>
			<script type="text/javascript"><!-- // --><![CDATA[
				document.getElementById("mod_theme_heights").style.display = "none";
				document.getElementById("mod_theme_heights_link").style.display = "";
			// ]]></script>
			</dd>';
}

?>