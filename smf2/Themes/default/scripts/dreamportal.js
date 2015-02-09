/**************************************************************************************
* dreamportal.js                                                                      *
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

var start = 0;
var colLStart = 0;
var colRStart = 0;
var rlit = 0;
var rrit = 0;
var mHeights = new Array();
var cWidthLeft = null;
var cWidthRight = null;
var guest = null;
var imageurl = null;
var sessid = null;

function loadInfo(user, imageDir, session)
{
	if (!guest)
		guest = user;
	if (!imageurl)
		imageurl = imageDir;
	if (!sessid)
		sessid = session;
}

function toggleModule(type, targetId)
{
	var test = null;
	var dpheader = null;
	var dpmodule = type + "module_" + targetId;
	var dpmoduleimage = type + "collapse_" + targetId;

	// Block Style
	if (document.getElementById("dp_" + type + "module_" + targetId) != test)
		dpheader = document.getElementById("dp_" + type + "module_" + targetId);

	mode = document.getElementById(dpmodule).style.display == "" ? 0 : 1;

	if (parseInt(guest) == 0)
		document.cookie = dpmodule + "=" + (mode ? 0 : 1);
	else
		smf_setThemeOption(dpmodule, mode ? 0 : 1, null, sessid);

	if (dpheader != test)
	{
		if (mode == 0)
			removeClassName(dpheader, "block_header");
		else
			addClassName(dpheader, "block_header");
	}

	document.getElementById(dpmoduleimage).src = imageurl + "/" + (mode ? "collapse.gif" : "expand.gif");
	document.getElementById(dpmodule).style.display = mode ? "" : "none";
}

function toggleModuleAnim(type, targetId, speed)
{
	if (start != 0)
		return;

	var dpmodule = type + "module_" + targetId;

	if (document.getElementById(dpmodule).style.display == "none")
		expandModuleAnim(type, parseInt(targetId), dpmodule, parseInt(speed), guest, imageurl, sessid);
	else
		collapseModuleAnim(type, parseInt(targetId), dpmodule, parseInt(speed), guest, imageurl, sessid);
}

function collapseModuleAnim(type, targetId, dpmodule, speed)
{
	var dpmoduleimage = type + "collapse_" + targetId;
	var dpheader = null;
	var test = null;

	// Block Style
	if (document.getElementById("dp_" + type + "module_" + targetId) != test)
		dpheader = document.getElementById("dp_" + type + "module_" + targetId);

	var module = document.getElementById(dpmodule);
	var modHeight = module.offsetHeight;
	module.style.overflowY = "hidden";

	if (!mHeights[targetId] && start == 0)
	{
		document.cookie = "dp_" + type + "module_height_" + targetId + "=" + modHeight;
		mHeights[targetId] = modHeight;
	}

	var minHeight = 0;
	var moveBy = Math.round(speed * 10);
	var intId = setInterval(function() {
		var curHeight = module.offsetHeight;
		var newHeight = curHeight - moveBy;
		if (newHeight > minHeight)
		{
			start = 1;
			module.style.height = newHeight + "px";
		}
		else {
			clearInterval(intId);
			module.style.height = "0px";
			if(dpheader != test)
				removeClassName(dpheader, "block_header");

			start = 0;
			module.style.display = "none";
			document.getElementById(dpmoduleimage).src = imageurl + '/expand.gif';
			if (parseInt(guest) == 0)
				document.cookie = dpmodule + "=1";
			else
				smf_setThemeOption(dpmodule, 1, null, sessid);

		}
	}, 30);
}

function expandModuleAnim(type, targetId, dpmodule, speed)
{
	var dpmoduleimage = type + "collapse_" + targetId;
	var dpheader = null;
	var test = null;

	// Block Style
	if (document.getElementById("dp_" + type + "module_" + targetId) != test)
		dpheader = document.getElementById("dp_" + type + "module_" + targetId);

	var module = document.getElementById(dpmodule);
	document.getElementById(dpmoduleimage).src = imageurl + '/collapse.gif';
	if(dpheader != test)
		addClassName(dpheader, "block_header");

	module.style.height = "0px";
	module.style.overflowY = "hidden";
	module.style.display = "";
	if (!mHeights[targetId])
		var match = getCookie("dp_" + type + "module_height_" + targetId);
	else
		var match = mHeights[targetId];

	var modHeight = parseInt(match);
	var moveBy = Math.round(speed * 10);
	var intId = setInterval(function() {
		var curHeight = module.offsetHeight;
		var newHeight = curHeight + moveBy;
		if (newHeight < modHeight)
		{
			module.style.height = newHeight + "px";
			start = 1;
		}
		else {
			clearInterval(intId);
			if(dpheader != test)
				module.style.overflowY = "hidden";
			else
				module.style.overflowY = "auto";

			module.style.height = "";
			start = 0;
			if(parseInt(guest) == 0)
				document.cookie = dpmodule + "=0";
			else
				smf_setThemeOption(dpmodule, 0, null, sessid);
		}
	}, 30);
}

function getCookie(c_name)
{
	if (document.cookie.length > 0)
	{
		var c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1)
		{
			c_start = c_start + c_name.length + 1;
			var c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1) c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		}
	}
	return "";
}

function addClassName(oElement, sClass)
{
	oElement.className += " " + sClass;
}

function removeClassName(oElement, sClass)
{
	oElement.className = oElement.className.replace(" " + sClass, "");
}

function replaceClassName(oElement, sClassFind, sClassReplace)
{
	oElement.className = oElement.className.replace(sClassFind, sClassReplace);
}