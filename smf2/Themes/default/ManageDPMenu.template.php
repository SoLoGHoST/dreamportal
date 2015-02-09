<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDPMenu.template.php; ver 1.1

function template_main()
{
	global $context, $scripturl, $boardurl, $txt, $smcFunc, $settings;

	echo '
	<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[

		function doTypeChange(oVal)
		{
			var oLink = document.getElementById("menu_link");
			var pageSel = document.getElementById("dream_pages");
			oVal = parseInt(oVal);
			
			if (oVal != 0)
			{
				if (pageSel)
					pageSel.style.display = "none";

				oLink.disabled = false;
				oLink.value = oVal == 1 ? "index.php?" : "http://";
			}
			else
			{
				oLink.disabled = true;

				if (pageSel && pageSel.options)
				{
					pageSel.style.display = "";
					if (pageSel.selectedIndex)
						changePagelink(pageSel.options[pageSel.selectedIndex].value);
					else
						changePagelink(pageSel.options[0].value);
				}
			}
		}

		function changePagelink(str)
		{
			var pName = str.split("::", 2);
			document.getElementById("menu_link").value = "index.php?page=" + pName[0];
		}
		
		function submit_dreampage()
		{
			var dPage = document.getElementById("dream_pages");

			if (dPage && dPage.style.display != "none")
			{
				var menuForm = document.getElementById("menumodify");
				var hidden = document.createElement("input");
				hidden.name = "menu_mode";
				hidden.type = "hidden";
				hidden.value = "dream_page";
				menuForm.appendChild(hidden);
			}
		}

		$(document).ready(function() {			
			var allOptions = $("#dp_menu_opts option").clone();
			$("#dp_menu").change(function() {
				var fVal = $(this).val();
				var selChildof = $("#dp_menu_opts option:selected").hasClass("option-child_of");
				var selVal = $("#dp_menu_opts option:selected").val();
				if (!selChildof && fVal == "child_of")
				{
					var selIndex = $("#dp_menu_opts")[0].selectedIndex;
					// Loop through it and select the nearest parent of this child button.
					while (selIndex > 0 && !selChildof) {
						selIndex--;
						selChildof = $("#dp_menu_opts option").eq(selIndex).hasClass("option-child_of");
					}
					selVal = $("#dp_menu_opts option").eq(selIndex).val();
				}
				
				$("#dp_menu_opts").html(allOptions.filter(".option-" + fVal));
				$("#dp_menu_opts").val(selVal);
			});';
			
			if ($context['button_data']['position'] == 'child_of')
			echo '
			var fVal = $("#dp_menu").val();
			$("#dp_menu_opts").html(allOptions.filter(".option-" + fVal));';
			
			echo '
		});

	// ]]></script>
		<form action="', $scripturl, '?action=admin;area=dpmenu;sa=dpaddbutton" method="post" accept-charset="', $context['character_set'], '" name="menumodify" id="menumodify" onsubmit="javascript:submit_dreampage();" class="flow_hidden">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
				<div class="roundframe">';

	// If an error occurred, explain what happened.
	if (!empty($context['post_error']))
	{
		echo '
					<div class="errorbox" id="errors">
						<strong>', $context['error_title'], '</strong>
						<ul>';

		foreach ($context['post_error'] as $type => $error)
		{
			echo '
							<li>', ($type == 'dream_page' ? $error : $txt[$error]), '</li>';
		}

		echo '
						</ul>
					</div>';
	}

	echo '
					<dl id="post_header">
						<dt>
							<strong>', $txt['dptext_admin_title'], ':</strong>
						</dt>
						<dd>
							<input type="text" name="name" id="bnbox" value="', $context['button_data']['name'], '" tabindex="1" class="input_text" style="width: 100%;', (isset($context['post_error']['name']) ? ' border: 1px solid red;' : ''), '" />
							<input type="hidden" name="slug" value="', $context['button_data']['slug'], '" />
						</dd>
						<dt>
							<strong>', $txt['dp_dream_menu_button_position'], ':</strong>
						</dt>
						<dd>
							<select id="dp_menu" name="position" size="10" style="width: 20%;" onchange="this.form.position.disabled = this.options[this.selectedIndex].value == \'\';">
								<option value="after"', $context['button_data']['position'] == 'after' ? ' selected="selected"' : '', '>' . $txt['mboards_order_after'] . '...</option>
								<option value="child_of"', $context['button_data']['position'] == 'child_of' ? ' selected="selected"' : '', '>' . $txt['mboards_order_child_of'] . '...</option>
								<option value="before"', $context['button_data']['position'] == 'before' ? ' selected="selected"' : '', '>' . $txt['mboards_order_before'] . '...</option>
							</select>
							<select name="parent" size="10" style="width: 75%;', (isset($context['post_error']['parent']) ? ' border: 1px solid red;' : ''), '" id="dp_menu_opts">';

	foreach ($context['menu_buttons'] as $buttonIndex => $buttonData)
	{
		// If we are modifying a button do not add this button to the list of <option> tags...
		if (!empty($context['button_data']['id']) && $buttonIndex == $context['button_data']['slug'])
			continue;

		echo '
									<option value="', $buttonIndex, '"', $context['button_data']['parent'] == $buttonIndex ? ' selected="selected"' : '', ' class="option-after option-child_of option-before">', $buttonData['title'], '</option>';

		if (!empty($buttonData['sub_buttons']))
		{
			foreach ($buttonData['sub_buttons'] as $childButton => $childButtonData)
			{
				if (!empty($context['button_data']['id']) && $childButton == $context['button_data']['slug'])
					continue;

				echo '
									<option value="', $childButton, '"', $context['button_data']['parent'] == $childButton ? ' selected="selected"' : '', ' class="option-after option-child_of option-before">&nbsp;-&gt; ', $childButtonData['title'], '</option>';

				if (!empty($childButtonData['sub_buttons']))
					foreach ($childButtonData['sub_buttons'] as $grandChildButton => $grandChildButtonData)
					{
						if (!empty($context['button_data']['id']) && $grandChildButton == $context['button_data']['slug'])
							continue;

						echo '
										<option value="', $grandChildButton, '"', $context['button_data']['parent'] == $grandChildButton ? ' selected="selected"' : '', ' class="option-after option-before">&nbsp;&nbsp;&nbsp;&nbsp;-&gt; ', $grandChildButtonData['title'], '</option>';
					}
			}
		}
	}

	echo '
							</select>
						</dd>
						<dt>
							<strong>', $txt['dptext_admin_type'], ':</strong>
						</dt>
						<dd>
							<select name="type" id="type" onchange="javascript:doTypeChange(this.value);">', !empty($context['dream_pages']) ? '
								<option value="0"' . (empty($context['button_data']['type']) ? ' selected="selected"' : '') . '>' . $txt['dpdm_dreampage_link'] . '</option>' : '', '
								<option value="1"', $context['button_data']['type'] == 1 ? ' selected="selected"' : '', '>', $txt['dpdm_forum_link'], '</option>
								<option value="2"', $context['button_data']['type'] == 2 ? ' selected="selected"' : '', '>', $txt['dpdm_external_link'], '</option>
							</select>&nbsp;';
							
						if (!empty($context['dream_pages']))
						{
							echo '
								<select name="dream_pages" id="dream_pages"', (!empty($context['button_data']['type']) ? ' style="display:none;"' : ''), ' onchange="javascript:changePagelink(this.options[this.selectedIndex].value);">';

							foreach($context['dream_pages'] as $pages)
								echo '
									<option value="', $pages['value'], '"', isset($context['button_data']['dream_page']['id']) && $context['button_data']['dream_page']['id'] == $pages['id'] ? ' selected="selected"' : '', '>', $pages['title'], '</option>';

							echo '
								</select>';

							echo '
								<input type="hidden" name="dream_page_id" value="', isset($context['button_data']['dream_page']['curr_id']) ? $context['button_data']['dream_page']['curr_id'] : '0', '" />
								<input type="hidden" name="dream_page_name" value="', isset($context['button_data']['dream_page']['curr_name']) ? $context['button_data']['dream_page']['curr_name'] : '', '" />
								<input type="hidden" name="dreampage_ids" value="', implode(',', $context['dpage_ids']), '" />';	
						}
						echo '
						</dd>
						<dt>
							<strong>', $txt['dp_dream_menu_button_link'], ':</strong><br />
						</dt>
						<dd>
							<input type="text" id="menu_link" name="link" value="', !empty($context['button_data']['link']) ? $context['button_data']['link'] : ($context['button_data']['type'] == 1 ? 'index.php?' : ($context['button_data']['type'] == 2 ? 'http://' : '')) , '" tabindex="1"', (empty($context['button_data']['type']) && !empty($context['dream_pages']) ? ' disabled' : ''), ' class="input_text" style="width: 100%;', (isset($context['post_error']['link']) || isset($context['post_error']['dream_page']) ? ' border: 1px solid red;' : ''), '" />
							<span class="smalltext">', $txt['dp_dream_menu_button_link_desc'], '</span>
						</dd>
						<dt>
							<strong>', $txt['dp_dream_menu_link_type'], ':</strong>
						</dt>
						<dd>
							<label for="same_window"><input type="radio" id="same_window" class="input_check" name="target" value="_self"', $context['button_data']['target'] == '_self' ? ' checked="checked"' : '', '/>', $txt['dp_dream_menu_same_window'], '</label><br />
							<label for="new_tab"><input type="radio" id="new_tab" class="input_check" name="target" value="_blank"', $context['button_data']['target'] == '_blank' ? ' checked="checked"' : '', '/>', $txt['dp_dream_menu_new_tab'], '</label>
						</dd>
						<dt>
							<strong>', $txt['dp_admin_perms'], ':</strong>
						</dt>
						<dd>
							<fieldset id="group_perms">
								<legend><a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'none\';document.getElementById(\'group_perms_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>';

	$all_checked = true;

	// List all the groups to configure permissions for.
	foreach ($context['button_data']['permissions'] as $permission)
	{
		echo '
								<div id="permissions_', $permission['id'], '">
									<label for="check_group', $permission['id'], '">
										<input type="checkbox" class="input_check" name="permissions[]" value="', $permission['id'], '" id="check_group', $permission['id'], '"', $permission['checked'] ? ' checked="checked"' : '', ' />
										<span', ($permission['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : ''), '>', $permission['name'], '</span>
									</label>
								</div>';

		if (!$permission['checked'])
			$all_checked = false;
	}

	echo '
								<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'permissions[]\');" id="check_group_all"', $all_checked ? ' checked="checked"' : '', ' />
								<label for="check_group_all"><em>', $txt['check_all'], '</em></label><br />
							</fieldset>
							<a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'block\'; document.getElementById(\'group_perms_groups_link\').style.display = \'none\'; return false;" id="group_perms_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
							<script type="text/javascript"><!-- // --><![CDATA[
								document.getElementById("group_perms").style.display = "none";
								document.getElementById("group_perms_groups_link").style.display = "";
							// ]]></script>
						</dd>
						<dt>
							<strong>', $txt['dptext_admin_status'], ':</strong>
						</dt>
						<dd>
							<label for="active"><input type="radio" id="active" class="input_check" name="status" value="1"', !empty($context['button_data']['status']) ? ' checked="checked"' : '', ' />', $txt['dptext_admin_active'], '</label><br />
							<label for="inactive"><input type="radio" id="inactive" class="input_check" name="status" value="0"', empty($context['button_data']['status']) ? ' checked="checked"' : '', ' />', $txt['dptext_admin_nonactive'], '</label>
						</dd>
					</dl>
					<input name="bid" value="', $context['button_data']['id'], '" type="hidden" />
					<div class="righttext padding">
						<input name="submit" value="', $txt['dp_submit'], '" class="button_submit" type="submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
				</div>
			</form>
			<span class="lowerframe"><span></span></span>
			<br class="clear" />';
}

?>