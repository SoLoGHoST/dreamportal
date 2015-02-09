<?php
/**************************************************************************************
* Subs-DreamModules.php                                                               *
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

if (!defined('SMF'))
	die('Hacking attempt...');
/*	
	This file is intended to be used as helper functions for any Modules
	in case they need them to be able to output information easier!

	It provides the following functions:

	string dreamportal_code_content(string content = '', string type = 'BBC', boolean echo = true, array $code_empty = array('function' => 'module_error', 'params' => array('empty')), array $code_error = array('permissions' => false, 'function' => 'module_error', 'params' => array()))
		- This helper function used to output either BBC, HTML, or PHP content within a module anywhere the customizer see's fit!!
		- content holds the actual string that will be parsed into either BBC, HTML, or PHP.
		- type parameter sets the type for this content to be parsed as:  either BBC, HTML, or PHP.
		- if echo is true, will automatically echo the results into the module, otherwise, returns the parsed content for you to use within your module, if so desired.
		- $code_empty = array('function' => 'module_error', 'params' => array('empty')) determines the function with array([parameter1], [parameter2], etc....) to be called if the $content is an empty string.
		- $code_error = array('permissions' => boolean, 'function' => 'module_error', 'params' => array()) determines the function with optional parameters array([parameter1], [parameter2], etc....) to be called if their are PHP Syntax Errors found within $content (if $type == 'PHP').

	mixed (boolean/array) php_syntax_error(string $code)
		- Returns false if PHP Syntax is fine, otherwise returns an array of the error message and line number!
		- This function will not check anything except PHP Syntax errors!  It does not check whether functions exist or not, and many other instances that may cause errors!

*/

function dreamportal_code_content($content = '', $type = 'BBC', $echo = true, $code_empty = array('function' => 'module_error', 'params' => array('empty')), $code_error = array('function' => 'module_error', 'params' => array()))
{
	global $context, $txt;

	// If permissions aren't set, default to Manage & Administrate Layouts, since this would mean it's a module.
	if (!isset($code_error['permissions']))
		$code_error['permissions'] = allowedTo(array('manage_dplayouts', 'admin_dplayouts'));

	if (trim($content) != '')
	{
		if (empty($type))
			$type = 'BBC';

		// BBC
		if (strtoupper($type) == 'BBC')
		{
			$content = parse_bbc(strip_tags($content));
			$code = trim($content);
		}
		// HTML
		elseif (strtoupper($type) == 'HTML')
		{
			$content = html_entity_decode($content, ENT_QUOTES, $context['character_set']);
			$code = trim($content);
		}
		// PHP
		elseif (strtoupper($type) == 'PHP')
		{
			$content = trim(html_entity_decode($content, ENT_QUOTES, $context['character_set']));
			$has_error = php_syntax_error($content);

			// PHP Syntax errors?
			if ($has_error)
			{
				// Only show the Error and Line number for those who have permission to manage it!
				// Cause otherwise, it just don't make sense to show it to everyone!
				if (!empty($code_error['permissions']) && is_array($has_error))
				{
					$code = '
						<strong>' . $txt['dp_parse_error'] . '</strong>: ';

					foreach($has_error as $error_val)
					{
						if (is_int($error_val))
							$code .= ' ' . $txt['dp_error_line'] . ' <strong>' . $error_val . '</strong>';
						else
							$code .= $error_val;
					}
					if ($echo)
						echo $code;
					else
						return $code;
				}
				else
					call_user_func_array($code_error['function'], $code_error['params']);

				return;
			}
			else
			{
				ob_start();
				eval($content);
				$code = ob_get_contents();
				ob_end_clean();
			}
		}

		if ($echo)
			echo $code;
		else
			return $code;
	}
	else
		call_user_func_array($code_empty['function'], $code_empty['params']);
}

function php_syntax_error($code)
{
	$braces = 0;
	$inString = 0;

	// First of all, we need to know if braces are correctly balanced.
	// This is not trivial due to variable interpolation which
	// occurs in heredoc, backticked and double quoted strings
	foreach (token_get_all('<?php ' . $code) as $token)
	{
		if (is_array($token))
		{
			switch ($token[0])
			{
				case T_CURLY_OPEN:
				case T_DOLLAR_OPEN_CURLY_BRACES:
				case T_START_HEREDOC: ++$inString; break;
				case T_END_HEREDOC:   --$inString; break;
			}
		}
		else if ($inString & 1)
		{
			switch ($token)
			{
				case '`':
				case '"': --$inString; break;
			}
		}
		else
		{
			switch ($token)
			{
				case '`':
				case '"': ++$inString; break;

				case '{': ++$braces; break;
				case '}':
					if ($inString) --$inString;
					else
					{
						--$braces;
						if ($braces < 0) break 2;
					}

					break;
			}
		}
	}

	// Display parse error messages and use output buffering to catch them
	$inString = @ini_set('log_errors', false);
	$token = @ini_set('display_errors', true);
	ob_start();

	// If $braces is not zero, then we are sure that $code is broken.
	// We run it anyway in order to catch the error message and line number.

	// Else, if $braces are correctly balanced, then we can safely put
	// $code in a dead code sandbox to prevent its execution.
	// Note that without this sandbox, a function or class declaration inside
	// $code could throw a "Cannot redeclare" fatal error.

	$braces || $code = "if(0){{$code}\n}";

	if (false === eval($code))
	{
		if ($braces) $braces = PHP_INT_MAX;
		else
		{
			// Get the maximum number of lines in $code to fix a border case
			false !== strpos($code, "\r") && $code = strtr(str_replace("\r\n", "\n", $code), "\r", "\n");
			$braces = substr_count($code, "\n");
		}

		$code = ob_get_clean();
		$code = strip_tags($code);

		 // Get the error message and line number
		if (preg_match("'syntax error, (.+) in .+ on line (\d+)$'s", $code, $code))
		{
			$code[2] = (int) $code[2];
			$code = $code[2] <= $braces
				? array($code[1], $code[2])
				: array('unexpected $end' . substr($code[1], 14), $braces);
		}
		else $code = array('syntax error', 0);
	}
	else
	{
		ob_end_clean();
		$code = false;
	}

	@ini_set('display_errors', $token);
	@ini_set('log_errors', $inString);

	return $code;
}

?>