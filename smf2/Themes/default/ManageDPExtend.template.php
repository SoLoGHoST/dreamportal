<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDPExtend.template.php; ver 1.1

// 1 Template function to RULE them ALL!
function template_main()
{
	global $txt, $context, $scripturl, $modSettings;

	echo '
			<div id="admincenter">';

	echo '
				<div class="cat_bar">
					<h3 class="catbg">
						', $context['page_title'], '
					</h3>
				</div>';

	if (empty($context['extension_info']))
		echo '
				<div class="information">', $context['dp_extension_vars']['none_exist'], '</div>';

	if (!empty($context['extension_info']))
	{
		$num_extensions = count($context['extension_info']);

		if (!empty($modSettings[$context['dp_extension_vars']['modsettings_var']]))
			echo '
			<div class="pagesection">
				<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) && $num_extensions > 1 ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#lastExtension"><strong>' . $txt['go_down'] . '</strong></a>' : '', '</div>
			</div>';
		else
			echo '
				<br />';

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
				  <tr class="catbg">
					<th scope="col" class="first_th">', $context['dp_extension_vars']['name_col'], '</th>
					<th scope="col">', $txt['dp_extend_version'], '</th>
					<th scope="col" class="last_th">', ($context['dp_extension_vars']['sa'] == 'dpaddlanguages' ? $txt['dp_lang_translator'] : $txt['dp_extend_description']), '</th>
				  </tr>
				</thead>
				<tbody>';

		$style = '';
		$i = 0;

		// Output the available extensions
		foreach ($context['extension_info'] as $name => $extension)
		{
			// No need for this if only 1 Extension found...
			

			$i++;
			$style = $i%2 ? '2' : '';

			// Need to add in the extra column <td> element for adding of languages in here!
			echo '
					<tr class="windowbg', $style, '">
						<td class="windowbg', $style, '" align="left">';

			if (!empty($modSettings['topbottomEnable']))
			{
				if ($i == 1)
					echo '
						<a name="firstExtension"></a>';
				elseif ($num_extensions == $i)
					echo '
						<a name="lastExtension"></a>';
			}

			echo '
				<strong>', $extension['title'], '</strong><br style="margin-top: 5px;" />', 
				(isset($extension['install_href']) ? '<a href="' . $extension['install_href'] . '">' . $txt['dp_extend_install'] . '</a> | ' : ''),
				(isset($extension['uninstall_href']) ? '<a href="' . $extension['uninstall_href'] . '">' . $txt['dp_extend_uninstall'] . '</a> | ' : ''),
				(isset($extension['settings_href']) ? '<a href="' . $extension['settings_href'] . '">' . $txt['dp_extend_settings'] . '</a> | ' : ''), '<a href="' . $extension['delete_href'] . '">' . $txt['dp_extend_delete'] . '</a>',
						'</td>
						<td class="windowbg', $style, '" align="center">', $extension['version'], '</td>
						<td class="windowbg', $style, '" align="left">', ($context['dp_extension_vars']['sa'] == 'dpaddlanguages' ? $extension['translator'] : $extension['description']), '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';

		if (!empty($modSettings[$context['dp_extension_vars']['modsettings_var']]))
			echo '
			<div class="pagesection">
				<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#firstExtension"><strong>' . $txt['go_up'] . '</strong></a>' : '', '</div>
			</div>';
		else
			echo '
			<br />';
	}
	echo '
				<div class="cat_bar">
					<h3 class="catbg">
						', $context['dp_extension_vars']['upload_txt'], '
					</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
						<form action="', $scripturl, '?action=admin;area=dpextend;sa=', $context['dp_extension_vars']['sa'], '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
							<dl class="settings">
								<dt>
									<strong>', $context['dp_extension_vars']['extension_to_upload'], '</strong>
								</dt>
								<dd>
									<input name="', $context['dp_extension_vars']['input_name'], '" type="file" class="input_file" size="38" />
								</dd>
							</dl>
							<div class="righttext">
								<input name="upload" type="submit" value="' . $txt['dp_extend_upload'] . '" class="button_submit" />
								<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							</div>
						</form>
					</div>
					<span class="botslice"><span></span></span>
				</div></div>
			<br class="clear" />';
}
?>