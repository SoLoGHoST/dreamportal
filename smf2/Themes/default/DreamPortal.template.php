<?php

// Dream Portal (c) 2009-2012 Dream Portal Team
// DreamPortal.template.php; ver 1.1

// Template for displaying everything above the portal. In this case, the basic rendering of the layout is done here. Modules that go after SMF are held in a buffer and saved for later.
function template_portal_above()
{
	global $context, $modSettings;

	// Call the empty modules function in here before we even load up any other modules!
	if (!empty($context['empty_modules']))
	{
		foreach($context['empty_modules'] as $p => $mod)
		{
			if (!empty($mod['params']))
				$mod['function']($mod['params']);
			else
				$mod['function']();
		}
	}

	if (!empty($context['dream_columns']))
	{
		$dp_module_display_style = !empty($modSettings['dp_module_display_style']) ? $modSettings['dp_module_display_style'] : 0;

		echo '
		<table class="dp_main">';

		foreach ($context['dream_columns'] as $row_id => $row_data)
		{
			echo '
			<tr class="tablerow', $row_id, '" valign="top">';

			foreach ($row_data as $column_id => $column_data)
			{
			
				echo '
				<td class="tablecol_', $column_id, '"', $column_data['html'], '>';
				
				if (!empty($column_data['modules']))
					template_module_column($dp_module_display_style, $column_data['modules']);
					
				if ($column_data['is_smf'])
				{
					ob_start();
					$buffer = true;
				}
					echo '
				</td>';
			}
			echo '
			</tr>';
		}
		echo '
		</table>';
	}

	$context['dream_buffer'] = !empty($buffer) ? ob_get_clean() : '';
}

// This must be here to maintain balance!  DO NOT REMOVE!
function template_portal()
{
}

// Outputs everything in the buffer started in template_portal_above() and destroys it.
function template_portal_below()
{
	global $context;

	// Everything trapped by the buffer gets written here.
	echo $context['dream_buffer'];
}

// Sets up the column if the display style is set to Modular and calls the apropriate template for this module or cloned module (clone).
function template_module_column($style = 0, $column = array())
{
	// Modular Style
	if (!empty($style))
		echo '
					<span class="clear upperframe"><span></span></span>
					<div class="roundframe"><div class="innerframe">';

	$i = 0;
	foreach ($column as $m)
	{
		call_user_func_array($m['template']['function'], array($m, $style, $i));
		$i++;
	}

	// Modular Style
	if (!empty($style))
		echo '
					</div></div>
					<span class="lowerframe"><span></span></span>';
}

// Template used to render a Dream Page.
function template_dream_pages()
{
	global $context;

	echo '
					<div class="cat_bar">
						<h3 class="catbg">
							', $context['page_data']['title'], '
						</h3>
					</div>
					<span class="upperframe"><span></span></span>
						<div class="roundframe blockframe">
							', $context['page_data']['body'], '
						</div>
					<span class="lowerframe"><span></span></span>
				';
}

?>