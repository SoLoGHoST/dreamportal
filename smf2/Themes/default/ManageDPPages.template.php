<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDPPages.template.php; ver 1.1

/**
	This file handles the visuals of Dream Page management.
*/

/**
 * Main template for ading a page.
 *
 * @since 1.0
 */

function template_main()
{
	global $context, $scripturl, $boardurl, $txt, $smcFunc, $settings;

	// Let's begin our AJAX code, followed by all the content.
	echo '
	<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[

		$(document).ready(function() {
			$("#pnbox").keyup(function() {
				var page_name = $(this).val();
				$.ajax({
					type: "GET",
					url: "', $boardurl, '/dp_ajax.php?check=true;pn=" + page_name.php_to8bit().php_urlencode() + ";id=', $context['page_data']['id'], ';', $context['session_var'], '=', $context['session_id'], '",
					success: function(data) {
						$("#pn").html(data);
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						$("#pn").html(textStatus);
					}
				});
			});
		});

		function submit_bbc(rObj)
		{
			if (getCheckedValue(rObj) == "2")
			{
				var obj_wysiwyg = document.getElementById("html_' . $context['page_content'] . '");
				if (obj_wysiwyg.style.display != "none")
				{
					var sText = oEditorHandle_' . $context['page_content'] . '.oFrameDocument.' . $context['page_content'] . '.innerHTML;
					
					if (sText == "<br>")
						sText = "";

					document.getElementById("' . $context['page_content'] . '").value = sText;
					
					var pageForm = document.getElementById("pagemodify");
					var hidden = document.createElement("input");
					hidden.name = "body_mode";
					hidden.type = "hidden";
					hidden.value = "1";
					pageForm.appendChild(hidden);
				}
			}
		}
		
		function getCheckedValue(radioObj)
		{
			if(!radioObj)
				return "";
			var radioLength = radioObj.length;
			if(radioLength == undefined)
				if(radioObj.checked)
					return radioObj.value;
				else
					return "";
			for(var i = 0; i < radioLength; i++) {
				if(radioObj[i].checked) {
					return radioObj[i].value;
				}
			}
			return "";
		}

		function check_bbc(val)
		{
			if (val != 2)
			{
				// Disable WYSIWYG if enabled!
				if (oEditorHandle_' . $context['page_content'] . '.bRichTextEnabled === true)
					oEditorHandle_' . $context['page_content'] . '.toggleView(0);

				document.getElementById("bbcBox_message").style.display = "none";
				document.getElementById("smileyBox_message").style.display = "none";
			}
			else
			{
				document.getElementById("bbcBox_message").style.display = "";
				document.getElementById("smileyBox_message").style.display = "";
			}
		}

	// ]]></script>
		<form action="', $scripturl, '?action=admin;area=dppages;sa=dpaddpage" method="post" accept-charset="', $context['character_set'], '" name="pagemodify" id="pagemodify" class="flow_hidden" onsubmit="javascript:submit_bbc(document.forms[\'pagemodify\'].elements[\'type\']);">
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

		foreach ($context['post_error'] as $error)
			echo '
							<li>', $txt[$error], '</li>';

		echo '
						</ul>
					</div>';
	}

	echo '
					<dl id="post_header">
						<dt>
							<strong>', $txt['dp_dream_pages_page_name'], ':</strong>
						</dt>
						<dd>
							<span>', $txt['dp_dream_pages_domain_url'], '</span><input type="text" name="page_name" id="pnbox" value="', $context['page_data']['page_name'], '" tabindex="1" class="input_text" size="30" /> <span id="pn"></span>
						</dd>
						<dt>
							<strong>', $txt['dptext_admin_type'], ':</strong>
						</dt>
						<dd>
							<label for="dreampage_php"><input type="radio" id="dreampage_php" onchange="javascript:check_bbc(this.value);" class="input_check" name="type" value="0"', $context['page_data']['type'] == 0 ? ' checked="checked"' : '', '/>', $txt['dp_dream_pages_page_php'], '</label><br />
							<label for="dreampage_html"><input type="radio" id="dreampage_html" onchange="javascript:check_bbc(this.value);" class="input_check" name="type" value="1"', $context['page_data']['type'] == 1 ? ' checked="checked"' : '', '/>', $txt['dp_dream_pages_page_html'], '</label><br />
							<label for="dreampage_bbc"><input type="radio" id="dreampage_bbc" onchange="javascript:check_bbc(this.value);" class="input_check" name="type" value="2"', $context['page_data']['type'] == 2 ? ' checked="checked"' : '', '/>', $txt['dp_dream_pages_page_bbc'], '</label>
						</dd>
						<dt>
							<strong>', $txt['dptext_admin_title'], ':</strong>
						</dt>
						<dd>
							<input type="text" name="title" value="', $context['page_data']['title'], '" tabindex="1" class="input_text" style="width: 100%;" />
						</dd>
						<dt>
							<strong>', $txt['dp_dream_pages_page_body'], ':</strong>
						</dt>
						<dd>
								<div id="bbcBox_message"', ($context['page_data']['type'] != 2 ? ' style="display: none;"' : ''), '></div>
								<div id="smileyBox_message"', ($context['page_data']['type'] != 2 ? ' style="display: none;"' : ''), '></div>
							', template_control_richedit($context['page_content'], 'smileyBox_message', 'bbcBox_message'), '
						</dd>
						<dt>
							<strong>', $txt['dp_admin_perms'], ':</strong>
						</dt>
						<dd>
							<fieldset id="group_perms">
								<legend><a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'none\';document.getElementById(\'group_perms_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>';

	$all_checked = true;

	// List all the groups to configure permissions for.
	foreach ($context['page_data']['permissions'] as $permission)
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
							<label for="dreampage_active"><input type="radio" id="dreampage_active" class="input_check" name="status" value="1"', $context['page_data']['status'] == 1 ? ' checked="checked"' : '', ' />', $txt['dptext_admin_active'], '</label><br />
							<label for="dreampage_inactive"><input type="radio" id="dreampage_inactive" class="input_check" name="status" value="0"', $context['page_data']['status'] == 0 ? ' checked="checked"' : '', ' />', $txt['dptext_admin_nonactive'], '</label>
						</dd>
					</dl>
					<input name="pid" value="', $context['page_data']['id'], '" type="hidden" />
					<input type="hidden" name="real_page_name" value="', $context['page_data']['real_page_name'], '" />
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