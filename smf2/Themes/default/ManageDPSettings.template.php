<?php
// Dream Portal (c) 2009-2012 Dream Portal Team
// ManageDPSettings.template.php; ver 1.0 RC

/**
 * This file handles showing Dream Portal's settings.
 *
 * @package template
 * @since 1.0
*/

/**
 * Renders the general imformation page.
 *
 * This function handles output of data populated by {@link DreamPortalInfo()}:
 * - upgraded DP version advisory
 * - latest news from dream-portal.net
 * - basic version check
 * - list of current forum admins
 * - credits
 *
 * @see DreamPortalInfo()
 * @since 1.0
*/
function template_portal_info()
{
	global $context, $txt, $portal_ver;
	
	echo '
	<div id="dp_update_section"></div>
	<div id="dp_admin_center">
		<div id="dp_admin_section">
			<div id="dp_live_news" class="floatleft">
				<div class="cat_bar" style="margin-bottom: 7px;">
					<h3 class="catbg">
						', $txt['dp_admin_config_latest_news'], '
					</h3>
				</div>
				<div class="windowbg" style="margin-top: -10px;">
					<div class="content">
						<div id="dpAnnouncements">', $txt['dp_admin_config_unable_news'], '</div>
					</div>
				<span class="botslice"><span></span></span>
				</div>
			</div>
			<div id="dpVersionTable" class="floatright">
				<div class="cat_bar" style="margin-bottom: 7px;">
					<h3 class="catbg">
						', $txt['dp_admin_config_support_info'], '
					</h3>
				</div>
				<div class="windowbg" style="margin-top: -10px;">
					<div class="content">
						<div id="dp_version_details">
							<strong>', $txt['dp_admin_config_version_info'], '</strong><br />
							', $txt['dp_admin_config_installed_version'], ':
							<em id="dp_installed_version" style="white-space: nowrap;">', $portal_ver, '</em><br />
							', $txt['dp_admin_config_latest_version'], ':
							<em id="dp_latest_version" style="white-space: nowrap;">??</em><br />
							<br />
							<strong>', $txt['administrators'], ':</strong>
							', implode(', ', $context['administrators']);
							
	// If we have lots of admins... don't show them all.
	if (!empty($context['more_admins_link']))
		echo '
							(', $context['more_admins_link'], ')';
	
	echo '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		</div>
		<div class="cat_bar" style="margin-bottom: 7px;">
			<h3 class="catbg"><span class="left"></span>
				', $txt['dp_credits'], '
			</h3>
		</div>
		<div class="windowbg2" style="margin-top: -10px;">
			<div class="content">';

	// Start the credits.
	foreach ($context['credits'] as $section)
	{
		// Show some "pre text".
		if (isset($section['pretext']))
			echo '
				<p>', $section['pretext'], '</p><br />';
					
		// Show the section title.
		if (isset($section['title']))
			echo '
				<p><strong>', $section['title'], '</strong></p>';
					
		// And now, list the members and groups.
		foreach ($section['groups'] as $group)
		{
			// Make sure there are members first.
			if (!empty($group['members']))
			{	
				echo '
				<p>';
				
				// Show the title.
				if (!empty($group['title']))
					echo '
					<strong>', $group['title'], '</strong>: ';
				
				echo '<span class="smalltext">' . implode(', ', $group['members']) . '</span>';
				
				echo '
				</p>';
			}
		}

		// And for some "post text".
		if (isset($section['posttext']))
			echo '
			<br />
				<p>', $section['posttext'], '</p>';
	}
	echo '
				<hr />
				', $txt['dp_credits_contribute'], '
			</div>
			<span class="botslice"><span></span></span>
		</div>		
	</div>
	<br class="clear" />
	<div class="cat_bar" style="margin-bottom: 7px;">
		<h3 class="catbg"><span class="left"></span>
			', $context['dp_license_header'], '
		</h3>
	</div>
	<div class="windowbg2" style="margin-top: -10px;">
		<div class="content dp_control_flow" style="padding-top: 0px;">
			<pre>', $context['dp_license'], '</pre>
		</div>
		<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
}

?>