<?php
/**************************************************************************************
* DreamModules.php                                                                    *
***************************************************************************************
* Dream Portal                                                                        *
* Forum Portal Modification Project founded by ccbtimewiz (ccbtimewiz@ccbtimewiz.com) *
* =================================================================================== *
* Software by:                  Dream Portal Team (http://dream-portal.net)			  *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2009-2012 by:       Dream Portal Team									  *
* License:						http://dream-portal.net/index.php?page=license		  *
* Support, News, Updates at:    http://dream-portal.net                               *
**************************************************************************************/

if (!defined('SMF') || !defined('DP'))
	die('Hacking attempt...');

/*	
	This file contains the code for the Custom Module that is the Default Module that comes installed with Dream Portal.
*/

function module_custom($params)
{
	global $context;

	if (is_array($params))
	{
		$content = (!empty($params['code']) ? $params['code'] : '');

		if (empty($content))
		{
			module_error('empty');
			return;
		}
		
		dreamportal_code_content($content, $params['code_type']);
	}
	else
		module_error();
}

?>