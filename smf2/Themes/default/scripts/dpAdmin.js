/**************************************************************************************
* dpAdmin.js	                                                                      *
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

// Checks for number, else sets it to 0.
Number.prototype.NaN0 = function()
{
	return isNaN(this) ? 0 : this;
}

// Simulates Php's trim() function.
String.prototype.trim = function ()
{
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

function swap_action(type)
{
	switch(type.id)
	{
		case "action_choice_smf_actions":
			document.getElementById("action_smf_actions").style.display = "";
			document.getElementById("action_user_defined").style.display = "none";
			document.getElementById("action_user_defined2").style.display = "none";
			break;
		case "action_choice_user_defined":
			document.getElementById("action_smf_actions").style.display = "none";
			document.getElementById("action_user_defined").style.display = "";
			document.getElementById("action_user_defined2").style.display = "";
			break;
	}
}

function addAction()
{
	var user_defined = false;
	var actions = document.getElementById("actions");
	if (document.getElementById("action_smf_actions").style.display == "none")
	{
		var user_defined = true;
		var udefined = document.getElementById("udefine").value;
		udefined = udefined.php_strtolower();
		udefined = udefined.trim();
		if (udefined == "") return;
		var p = exceptions.length;
		while(p--)
			if (udefined == exceptions[p]) return;

		document.getElementById("udefine").value = "";
	}
	var layouts = document.getElementById("lay_right");
	var action_list = document.getElementById("actions_list");
	var opt = document.createElement("option");
	var nextIn = action_list.options.length;
	var action_val = user_defined == false ? actions.options[actions.selectedIndex].text : udefined;
	var i = action_list.options.length;
	while(i--)
		if (action_list.options[i].text == action_val)
			return;

	action_list.options.add(opt);
	opt.text = action_val;
	var hidden = document.createElement("input");
	hidden.name = 'layout_actions[]';
	hidden.id = "dream_action" + nextIn;
	hidden.type = 'hidden';
	hidden.value = action_val;
	layouts.appendChild(hidden);
}

function removeActions()
{
	var action_list = document.getElementById("actions_list");
	var parent = document.getElementById("lay_right");

	// Remember selected items.
	// Opera deselects all selected options when removing any of them, so this fixes that.
	var is_selected = [];
	for (var i = 0; i < action_list.options.length; ++i)
	{
		is_selected[i] = action_list.options[i].selected;
	}

	// Remove selected items.
	var x = action_list.options.length;
	while(x--)
	{
		if (is_selected[x])
		{
			action_list.remove(x);
			parent.removeChild(document.getElementById("dream_action" + x));
		}
	}

	var s = 0;
	// Reorder them
	for(var p=0; p < parent.childNodes.length; p++)
	{
		if (parent.childNodes[p].nodeName == '#text') continue;
		var action = parent.childNodes[p].id;

		if (action.indexOf('dream_action') == 0)
		{
			parent.childNodes[p].id = 'dream_action' + s;
			s++;
		}
	}

	return true;
}

function orderChecks(objId, order)
{
	var element = document.getElementById(objId);
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	var daOrder = '';
	for(x=0;x<elements.length;x++)
	{
		var daComma = x == elements.length - 1 ? "" : ",";
		var child = elements[x].firstChild.firstChild;
		var daValue = child.getAttribute('value');
		daOrder += daValue + daComma;
	}
	if(daOrder != '')
		document.getElementById(order).value = daOrder;
}

function moveDown(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	for(i=0;i<elements.length;i++)
	{
		if(elements[i]==element)
		{
			var x = (i+1) % (elements.length);
			element.parentNode.insertBefore(element.cloneNode(true), (x>0?elements[x].nextSibling:elements[x]));
			element.parentNode.removeChild(element);
		}
	}
}

function moveUp(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	for(i=0;i<elements.length;i++)
	{
		if(elements[i]==element)
		{
			element.parentNode.insertBefore(element.cloneNode(true), (i-1>=0?elements[i-1]:elements[elements.length-1].nextSibling));
			element.parentNode.removeChild(element);
		}
	}
}

function toggleBBCDisabled(section, disable)
{
	var i = document.forms.bbcForm.length;
	while(i--)
	{
		if (typeof(document.forms.bbcForm[i].name) == "undefined" || (document.forms.bbcForm[i].name.substr(0, 11) != "enabledTags") || (document.forms.bbcForm[i].name.indexOf(section) != 11))
			continue;

		document.forms.bbcForm[i].disabled = disable;
	}
	document.getElementById("bbc_" + section + "_select_all").disabled = disable;
}

// Deleting/Editing a Layout thats currently selected!
function submitLayout(confirmText, url, sessVar, sessId)
{
	if (confirmText != "editlayout")
	{
		var delLayout = confirm(confirmText);
		if (!delLayout)
			return;
	}

	var layoutForm = document.forms.urLayouts;
	layoutForm.action = url + sessVar + "=" + sessId;
	layoutForm.submit();
}

function loadModuleColors(value, sessId)
{
	var alldivs = document.getElementsByTagName("div");
	var i = alldivs.length;
	while(i--)
	{
		var divClass = alldivs[i].className;

		if (!divClass)
		  continue;

		if (divClass.indexOf("DragBox") !== 0)
		  continue;

		if (divClass.indexOf("clonebox") >= 0)
			alldivs[i].className = "DragBox clonebox" + value + " draggable_module centertext";
		else
			alldivs[i].className = "DragBox modbox" + value + " draggable_module centertext";
	}
	smf_setThemeOption("dp_mod_color", parseInt(value), null, sessId);
}

function sortOptions(optionList)
{
	var arrToSort = [];
	for (var i = 0; i < optionList.length; i++)
	{
		arrToSort[i] = [];
		arrToSort[i][0] = optionList.options[i].text;
		arrToSort[i][1] = optionList.options[i];
	}

	arrToSort.sort();
	optionList.length = 0;
	// var s = arrToSort.length;
	for (var s = 0; s < arrToSort.length; s++)
		optionList.options.add(arrToSort[s][1]);
}

function addRow()
{
	var oTable = document.getElementById("edit_layout");
	var oTr = oTable.insertRow(-1);
	oTr.className = "titlebg2";
	oTr.id = "row_" + totalRows;
	var rowText = rowString + ' ' + (totalRows + 1);
	var cellSpans = smfLayout ? "6" : "5";

	var oCell = oTr.insertCell(-1);
	oCell.setAttribute("align", "center");
	oCell.setAttribute("colspan", cellSpans);
	oCell.innerHTML = '<label for="inputrow_' + totalRows + '">' + rowText + '</label> <input id="inputrow_' + totalRows + '" type="checkbox" class="' + checkClass + '" onclick="invertChecks(this, this.form, \'check_' + totalRows + '_\');" />';

	var selEle = document.getElementById("selAddColumn");
	var newOpt = document.createElement("option");
	newOpt.value = totalRows;
	newOpt.text = rowText;
	selEle.options.add(newOpt);
	totalRows++;
}

function deleteSelected(oConfirm)
{
	var delSel = confirm(oConfirm);
		if (!delSel)
			return;

	var currIndex = 0;
	var smfRow = -1;
	var smfColumn = -1;
	var row = -1;
	var hasColumn = false;
	var sel = document.getElementById('selAddColumn');
	var oTable = document.getElementById("edit_layout");
	var currIndex = totalColumns + totalRows + 1;
	var sCurrIndex = [];

	var i = oTable.childNodes.length;
	while(i--)
	{
		var nodename = oTable.childNodes[i].nodeName;
		if (nodename == '#text') continue;
		nodename = nodename.toLowerCase();
		if (nodename != 'tbody')
			continue;

		if (oTable.childNodes[i].hasChildNodes)
		{
			var p = oTable.childNodes[i].childNodes.length;
			while (p--)
			{
				var pNodeName = oTable.childNodes[i].childNodes[p].nodeName;
				if (pNodeName == '#text') continue;
				pNodeName = pNodeName.toLowerCase();
				var oId = oTable.childNodes[i].childNodes[p].id;

				if (!oId) continue;

				if (oId.indexOf("tr_") != 0 && oId.indexOf("row_") != 0)
					continue;

				currIndex--;

				// Columns...
				if (oId.indexOf("tr_") == 0)
				{
					var iColumn = oId.split("_");
					var colCheck = document.getElementById("check_" + iColumn[1] + "_" + iColumn[2] + "_" + iColumn[3]);

					if (!colCheck || (parseInt(iColumn[1]) == rowPos && parseInt(iColumn[2]) == colPos && parseInt(iColumn[3]) == layoutPos))
					{
						smfRow = parseInt(iColumn[1]);
						smfColumn = parseInt(iColumn[2]);
						continue;
					}

					if (currIndex == 2 && !hasColumn)
						continue;

					if (colCheck.checked)
					{
						if (parseInt(iColumn[1]) == 0 && rowPos == 0 && colPos == 0 && currIndex == 3 && !hasColumn)
							continue;

						if (rowPos == 0 && colPos == 0)
						{
							if (parseInt(iColumn[1]) == 1)
							{
								if (!hasColumn)
								{
									sCurrIndex[sCurrIndex.length] = "column_" + currIndex + "_" + iColumn[3];
									continue;
								}
							}
							else if (parseInt(iColumn[1]) == 0)
							{
								if (!hasColumn)
								{
									sCurrIndex[sCurrIndex.length] = "column_" + currIndex + "_" + iColumn[3];
									continue;
								}
							}
						}

						document.getElementById("remove_positions").value += "_" + parseInt(iColumn[3]);
						oTable.deleteRow(currIndex);
						totalColumns--;

						if (parseInt(iColumn[3]) == 0)
							newColumns--;
					}
					else
					{
						row = parseInt(iColumn[1]);
						hasColumn = true;
					}
				}
				else
				{
					// Rows...
					var iRow = oId.split("_");
					var rowCheck = document.getElementById("inputrow_" + iRow[1]);

					if ((rowCheck.checked && smfRow != parseInt(iRow[1]) && row != parseInt(iRow[1])) || ((row != parseInt(iRow[1]) || row == -1) && smfRow != parseInt(iRow[1])))
					{
						if (parseInt(iRow[1]) == 0 && !hasColumn)
							continue;
						else
						{
							if (parseInt(iRow[1]) == 1 && rowPos == 0 && colPos == 0 && !hasColumn)
							{
								sCurrIndex[sCurrIndex.length] = "row_" + currIndex + "_" + iRow[1];
								continue;
							}
							else
							{
								sel.remove(parseInt(iRow[1]));
								oTable.deleteRow(currIndex);
								totalRows--;
							}
						}
					}
				}
			}
		}
	}

	// Another check...
	if (sCurrIndex.length > 0)
	{
		var it = 0;
		var isFound = false;
		var arLen = sCurrIndex.length;
		for (var a = 0; a < arLen; a++)
		{
			var sIs = sCurrIndex[a].split("_");
			var xRowCol = sIs[0];

			if (hasColumn)
			{
				if (xRowCol == "column")
				{
					if (parseInt(sIs[2]) == 0)
						newColumns--;

					totalColumns--;
				}
				else
				{
					sel.remove(parseInt(sIs[2]));
					totalRows--;
				}
				document.getElementById("remove_positions").value += "_" + parseInt(sIs[2]);
				oTable.deleteRow(parseInt(sIs[1]));
				continue;
			}
			else
			{
				var oLast = sCurrIndex[arLen - 1];
				var oLastVal = oLast.split("_");

				// Skip it
				if (oLastVal[0] == "row" && sIs[0] != "row" && it < 1)
				{
					isFound = true;
					it++;
					continue;
				}
				else
				{
					if (isFound && sIs[0] == "row")
						continue;

					if (xRowCol == "column")
					{
						if (parseInt(sIs[2]) == 0)
							newColumns--;

						totalColumns--;
					}
					else
					{
						sel.remove(parseInt(sIs[2]));
						totalRows--;
					}
					document.getElementById("remove_positions").value += "_" + parseInt(sIs[2]);
					oTable.deleteRow(parseInt(sIs[1]));
				}
			}
		}
	}

	// Finally, make it look purdy...
	var s = oTable.childNodes.length;
	var currRow = -1;
	var firstRow = 0;
	var currCol = 0;

	while(s--)
	{
		var nodename = oTable.childNodes[s].nodeName;
		if (nodename == '#text') continue;
		nodename = nodename.toLowerCase();
		if (nodename != 'tbody')
			continue;

		if (oTable.childNodes[s].hasChildNodes)
		{
			var trChilds = oTable.childNodes[s].childNodes.length;
			for (sc = 0; sc < trChilds; sc++)
			{
				var psNodeName = oTable.childNodes[s].childNodes[sc].nodeName;
				if (psNodeName == '#text') continue;
				psNodeName = psNodeName.toLowerCase();
				var oTrId = oTable.childNodes[s].childNodes[sc].id;

				if (!oTrId) continue;

				if (oTrId.indexOf("tr_") != 0 && oTrId.indexOf("row_") != 0)
					continue;

				// Left over Columns
				if (oTrId.indexOf("tr_") == 0)
				{
					var osTrId = oTrId.split("_");
					var currSuffix = oTrId.substring(3);
					var currLen = currSuffix.length;

					oTable.childNodes[s].childNodes[sc].id = "tr_" + currRow + "_" + currCol + "_" + osTrId[3];

					if (oTable.childNodes[s].childNodes[sc].hasChildNodes)
					{
						var scTd = oTable.childNodes[s].childNodes[sc].childNodes.length;
						while(scTd--)
						{
							if(oTable.childNodes[s].childNodes[sc].childNodes[scTd].nodeName=='#text') continue;
							var scTdId = oTable.childNodes[s].childNodes[sc].childNodes[scTd].id;
							if (!scTdId) continue;

							var elPreLen = scTdId.length - currLen;
							var elPrefix = scTdId.substr(0, elPreLen);

							oTable.childNodes[s].childNodes[sc].childNodes[scTd].id = elPrefix + currRow + "_" + currCol + "_" + osTrId[3];

							if (oTable.childNodes[s].childNodes[sc].childNodes[scTd].hasChildNodes)
							{
								var scEle = oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes.length;

								while (scEle--)
								{
									if (oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].nodeName=='#text') continue;
									var scEleId = oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].id;

									if (!scEleId) continue;

									var scElPreLen = scEleId.length - currLen;
									var scElPrefix = scEleId.substr(0, scElPreLen);

									oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].id = scElPrefix + currRow + "_" + currCol + "_" + osTrId[3];

									if (scEleId.indexOf("column_") == 0)
										oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].innerHTML = columnString + " " + (currCol + 1);

									if (scEleId.indexOf("radio_") == 0)
										oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].setAttribute("onclick", "javascript:smfRadio('" + currRow + "', '" + currCol + "', '" + osTrId[3] + "');");
								}
							}
						}
					}

					if (rowPos == parseInt(osTrId[1]) && colPos == parseInt(osTrId[2]) && layoutPos == parseInt(osTrId[3]))
					{
						rowPos = currRow;
						colPos = currCol;
					}

					currCol++;
				}
				else if (oTrId.indexOf("row_") == 0)
				{
					// Left over Rows...
					var cRow = currRow + 1;

					oTable.childNodes[s].childNodes[sc].id = "row_" + cRow;

					if (oTable.childNodes[s].childNodes[sc].hasChildNodes)
					{
						var rTd = oTable.childNodes[s].childNodes[sc].childNodes.length;
						while(rTd--)
						{
							if (oTable.childNodes[s].childNodes[sc].childNodes[rTd].nodeName=='#text') continue;

							if (oTable.childNodes[s].childNodes[sc].childNodes[rTd].hasChildNodes)
							{
								var rTdEl = oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes.length;
								while(rTdEl--)
								{
									rNodeName = oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].nodeName;

									if (rNodeName=='#text') continue;

									rNodeName = rNodeName.toLowerCase();

									if (rNodeName == 'label')
									{
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].setAttribute("for", "inputrow_" + cRow);
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].innerHTML = rowString + " " + (cRow + 1);
									}
									else if (rNodeName == 'input')
									{
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].id = "inputrow_" + cRow;
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].setAttribute("onclick", "invertChecks(this, this.form, 'check_" + cRow + "_');");
									}
								}
							}
						}
					}

					// Update the select box too....
					sel.options[cRow].value = cRow;
					sel.options[cRow].text = rowString + " " + (cRow + 1);

					currRow++;
					currCol = 0;
				}
			}
		}
	}
}

function addColumn()
{
	var columns = 0;
	var sel = document.getElementById("selAddColumn");
	var selVal = parseInt(sel.options[sel.selectedIndex].value);
	var oTable = document.getElementById("edit_layout_tbody");

	var i = oTable.childNodes.length;
	while(i--)
	{
		var rId = oTable.childNodes[i].id;

		if (!rId) continue;

		if (rId.indexOf("tr_" + selVal) == 0)
			columns++;
	}

	var columnIdVal = selVal + "_" + columns;

	getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=dream;sa=insertcolumn;xml;insert=' + columnIdVal.php_urlencode() + ";layout=" + document.getElementById("layout_picker").value, getIdLayoutPosition__callback);
}

function getIdLayoutPosition__callback(XMLDoc)
{
	var columnIdVal = XMLDoc.getElementsByTagName("item")[0].firstChild.nodeValue;
	var idLayoutPosition = columnIdVal.split("_")[2];

	var columns = 0;
	var rows = 0;
	var lastCol = 0;
	var lastColIndex = 0;
	var iP = 0;
	var columnsCount = 0;
	var startRow = 0;
	var nextRow = 0;
	var trClassName = "";
	var sel = document.getElementById("selAddColumn");
	var lTable = document.getElementById("edit_layout");
	var selVal = parseInt(sel.options[sel.selectedIndex].value);

	for (var i = 0; i < lTable.childNodes.length; i++)
	{
		if(lTable.childNodes[i].nodeName=='#text') continue;

		if (lTable.childNodes[i].hasChildNodes)
		{
			var ltChild = lTable.childNodes[i].childNodes.length;

			for (var p = 0; p < ltChild; p++)
			{
				var nodename = lTable.childNodes[i].childNodes[p].nodeName;

				if (nodename == '#text') continue;

				nodename = nodename.toLowerCase();

				if (nodename != 'tr')
					continue;
				else
					iP++;

				var lId = lTable.childNodes[i].childNodes[p].id;

				if (!lId) continue;

				if (lId.indexOf("tr_") == 0)
					columnsCount++;

				if (lId.indexOf("row_" + selVal) == 0)
					startRow = 1;
				else if (lId.indexOf("tr_" + selVal) == 0)
				{
					columns++;

					if (startRow == 1)
						trClassName = lTable.childNodes[i].childNodes[p].className;

					var trId = lId.split("_");
					lastCol = parseInt(trId[2]);
				}
				else if (lId.indexOf("row_") == 0)
				{
					rows++;
					if (startRow == 1)
						startRow = 2;

					if (lId.indexOf("row_" + (selVal + 1)) == 0)
					{
						nextRow = 1;
						lastColIndex = iP - 1;

						trClassName = trClassName == "windowbg2" ? "windowbg" : "windowbg2";
					}
				}
				else
					continue;
			}
		}
	}

	if (nextRow == 0 && startRow != 2)
		trClassName = trClassName == "windowbg2" ? "windowbg" : "windowbg2";

	columnNum = columns + 1;

	if (lastColIndex == 0)
		lastColIndex = -1;

	if (columns != 0)
		lastCol = lastCol + 1;

	trClassName = trClassName.trim();

	if (trClassName == "")
		trClassName = "windowbg2";

	var trEle = lTable.insertRow(lastColIndex);
	trEle.className = trClassName;
	trEle.id = "tr_" + columnIdVal;

	var cellColumns = trEle.insertCell(0);
	cellColumns.id = "tdcolumn_" + columnIdVal;
	cellColumns.innerHTML = '<div class="floatleft"><a href="javascript:void(0);" onclick="javascript:columnUp(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();"><img src="' + dp_upImg + '" style="width: 12px; height: 11px;" border="0" /></a> <a href="javascript:void(0);" onclick="javascript:columnDown(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();"><img src="' + dp_downImg + '" style="width: 12px; height: 11px;" border="0" /></a></div><span class="dp_edit_column" id="column_' + columnIdVal + '">' + columnString + ' ' + columnNum + '</span>';

	var cellColspans = trEle.insertCell(1);
	cellColspans.id = "tdcspans_" + columnIdVal;
	cellColspans.setAttribute("style", "text-align: center;");
	cellColspans.innerHTML = '<input type="text" id="cspans_'+ columnIdVal + '" name="colspans[' + idLayoutPosition + ']" size="5" value="0" class="' + textClass + '" />';

	var cellEnabled = trEle.insertCell(2);
	cellEnabled.id = "tdenabled_" + columnIdVal;
	cellEnabled.setAttribute("style", "text-align: center;");
	cellEnabled.innerHTML = '<input type="checkbox" id="enabled_' + columnIdVal + '" name="enabled[' + idLayoutPosition + ']" class="' + checkClass + '" />';

	if (smfLayout)
	{
		var cellSMF = trEle.insertCell(3);
		cellSMF.id = "tdradio_" + columnIdVal;
		cellSMF.setAttribute("style", "text-align: center;");
		cellSMF.innerHTML = '<input type="radio" id="radio_' + columnIdVal + '" name="smf_radio" onfocus="if(this.blur)this.blur();" onclick="javascript:smfRadio(\'' + selVal + '\', \'' + columns + '\', \'' + idLayoutPosition + '\');" value="0" class="' + radioClass + '" />';
	}

	var cellSelected = trEle.insertCell(-1);
	cellSelected.id = "tdcheck_" + columnIdVal;
	cellSelected.setAttribute("style", "text-align: center;");
	cellSelected.innerHTML = '<input type="checkbox" id="check_' + columnIdVal + '" name="section[]" class="' + checkClass + '" />';

	totalColumns++;
	newColumns++;
}

function columnDown(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	var i = elements.length;
	var isSMFset = 0;
	while(i--)
	{
		if(elements[i]==element)
		{
			var eleId = element.id;
			var row = eleId.split("_");

			var x = (i+1) % (elements.length);
			var pDown = elements[x];
			var rowId = pDown.id;
			var rowIdInfo = row[1] + "_" + row[2] + "_" + row[3];
			var rowLen = rowIdInfo.length;

			if (rowId.indexOf("row_") == 0)
			{
				// New Row...
				var xRow = rowId.split("_");
				var iP = 0;
				for(var p = x+1; p<elements.length; p++)
				{
					if(elements[p].nodeName=='#text') continue;
					var pId = elements[p].id;
					if (!pId) continue;

					if (pId.indexOf("row_") == 0)
						break;

					if (pId.indexOf("tr_") != 0)
						continue;

					var currRow = pId.split("_");
					var currSuffix = pId.substring(3);
					var currLen = currSuffix.length;

					iP = iP == 0 ? 1 : parseInt(iP) + 1;

					if (rowPos == parseInt(currRow[1]) && colPos == parseInt(currRow[2]) && layoutPos == parseInt(currRow[3]) && isSMFset == 0)
					{
						isSMFset = 1;
						colPos = iP;
					}

					elements[p].id = currRow[0] + "_" + currRow[1] + "_" + iP + "_" + currRow[3];

					if (elements[p].hasChildNodes)
					{
						var s = elements[p].childNodes.length;
						while(s--)
						{
							if(elements[p].childNodes[s].nodeName=='#text') continue;
							var tdId = elements[p].childNodes[s].id;
							if (!tdId) continue;

							var currPreLen = tdId.length - currLen;

							var currPrefix = tdId.substr(0, currPreLen);

							elements[p].childNodes[s].id = currPrefix + currRow[1] + "_" + iP + "_" + currRow[3];

							if (elements[p].childNodes[s].hasChildNodes)
							{
								var t = elements[p].childNodes[s].childNodes.length;
								while(t--)
								{
									if(elements[p].childNodes[s].childNodes[t].nodeName=='#text') continue;
									var elId = elements[p].childNodes[s].childNodes[t].id;
									if (!elId) continue;

									var elPreLen = elId.length - currLen;
									var elPrefix = elId.substr(0, elPreLen);

									elements[p].childNodes[s].childNodes[t].id = elPrefix + currRow[1] + "_" + iP + "_" + currRow[3];

									if (elId.indexOf("column_") == 0)
										elements[p].childNodes[s].childNodes[t].innerHTML = columnString + " " + (iP + 1);

									if (elId.indexOf("radio_") == 0)
									{
										elements[p].childNodes[s].childNodes[t].setAttribute("onclick", "javascript:smfRadio('" + currRow[1] + "', '" + iP + "', '" + currRow[3] + "');");
									if (row[3] == layoutPos)
										elements[p].childNodes[s].childNodes[t].checked = false;
									}
								}
							}
						}
					}
				}

				if (rowPos == parseInt(row[1]) && colPos == parseInt(row[2]) && layoutPos == parseInt(row[3]))
				{
					rowPos = parseInt(xRow[1]);
					colPos = 0;
				}

				element.id = "tr_" + xRow[1] + "_0_" + row[3];
				var r = element.childNodes.length;
				while(r--)
				{
					if(element.childNodes[r].nodeName=='#text') continue;
					var rTdId = element.childNodes[r].id;
					if (!rTdId) continue;

					var rPreLen = rTdId.length - rowLen;

					var rPrefix = rTdId.substr(0, rPreLen);

					element.childNodes[r].id = rPrefix + xRow[1] + "_0_" + row[3];

					if (element.childNodes[r].hasChildNodes)
					{
						var e = element.childNodes[r].childNodes.length;
						while(e--)
						{
							if(element.childNodes[r].childNodes[e].nodeName=='#text') continue;
							var erTdId = element.childNodes[r].childNodes[e].id;
							if (!erTdId) continue;

							var erPreLen = erTdId.length - rowLen;
							var erPrefix = erTdId.substr(0, erPreLen);

							element.childNodes[r].childNodes[e].id = erPrefix + xRow[1] + "_0_" + row[3];

							if (erTdId.indexOf("column_") == 0)
								element.childNodes[r].childNodes[e].innerHTML = columnString + " 1";

							if (erTdId.indexOf("radio_") == 0)
							{
								element.childNodes[r].childNodes[e].setAttribute("onclick", "javascript:smfRadio('" + xRow[1] + "', '0', '" + row[3] + "');");
								if (row[3] == layoutPos)
								{
									element.childNodes[r].childNodes[e].checked = true;
									layoutPos = parseInt(row[3]);
								}
							}
						}
					}
				}
			}
			else
			{
				// Same Row...
				var xRow = rowId.split("_");
				var suffix = rowId.substring(3);
				var xLen = suffix.length;

				elements[x].id = "tr_" + xRow[1] + "_" + row[2] + "_" + xRow[3];

				var p = elements[x].childNodes.length;
				while(p--)
				{
					if(elements[x].childNodes[p].nodeName=='#text') continue;
					var pId = elements[x].childNodes[p].id;

					if (!pId) continue;

					var preLen = pId.length - xLen;
					var prefix = pId.substr(0, preLen);

					elements[x].childNodes[p].id = prefix + xRow[1] + "_" + row[2] + "_" + xRow[3];

					if (elements[x].childNodes[p].hasChildNodes)
					{
						var b = elements[x].childNodes[p].childNodes.length;
						while(b--)
						{
							if (elements[x].childNodes[p].childNodes[b].nodeName=='#text') continue;
							var cPId = elements[x].childNodes[p].childNodes[b].id;

							if (!cPId) continue;

							var cpreLen = cPId.length - xLen;
							var cprefix = cPId.substr(0, cpreLen);

							elements[x].childNodes[p].childNodes[b].id = cprefix + xRow[1] + "_" + row[2] + "_" + xRow[3];

							if (cPId.indexOf("column_") == 0)
								elements[x].childNodes[p].childNodes[b].innerHTML = columnString + " " + (parseInt(row[2]) + 1);

							if (cPId.indexOf("radio_") == 0)
							{
								elements[x].childNodes[p].childNodes[b].setAttribute("onclick", "javascript:smfRadio('" + xRow[1] + "', '" + row[2] + "', '" + xRow[3] + "');");
								if (row[3] == layoutPos)
									elements[x].childNodes[p].childNodes[b].checked = false;
							}
						}
					}
				}

				element.id = "tr_" + row[1] + "_" + xRow[2] + "_" + row[3];

				var t = element.childNodes.length;
				while(t--)
				{
					if(element.childNodes[t].nodeName=='#text') continue;
					var tId = element.childNodes[t].id;

					if (!tId) continue;

					var tpreLen = tId.length - rowLen;
					var tprefix = tId.substr(0, tpreLen);

					element.childNodes[t].id = tprefix + row[1] + "_" + xRow[2] + "_" + row[3];

					if (element.childNodes[t].hasChildNodes)
					{
						var a = element.childNodes[t].childNodes.length;
						while(a--)
						{
							if (element.childNodes[t].childNodes[a].nodeName=='#text') continue;
							var aPId = element.childNodes[t].childNodes[a].id;

							if (!aPId) continue;

							var apreLen = aPId.length - rowLen;
							var aprefix = aPId.substr(0, apreLen);

							element.childNodes[t].childNodes[a].id = aprefix + row[1] + "_" + xRow[2] + "_" + row[3];

							if (aPId.indexOf("column_") == 0)
								element.childNodes[t].childNodes[a].innerHTML = columnString + " " + (parseInt(xRow[2]) + 1);

							if (aPId.indexOf("radio_") == 0)
							{
								element.childNodes[t].childNodes[a].setAttribute("onclick", "javascript:smfRadio('" + row[1] + "', '" + xRow[2] + "', '" + row[3] + "');");
								if (row[3] == layoutPos)
								{
									element.childNodes[t].childNodes[a].checked = true;
									layoutPos = parseInt(row[3]);
								}
							}
						}
					}
				}

				if (rowPos == parseInt(row[1]) && colPos == parseInt(row[2]) && layoutPos == parseInt(row[3]))
					colPos = parseInt(xRow[2]);
				else if(rowPos == parseInt(xRow[1]) && colPos == parseInt(xRow[2]) && layoutPos == parseInt(xRow[3]))
					colPos = parseInt(row[2]);
			}

			if (x == 0)
				x = 1;

			element.parentNode.insertBefore(element.cloneNode(true), (x > 1 ? elements[x].nextSibling : elements[x]));
			element.parentNode.removeChild(element);
		}
	}

	// IE Fix
	checkSMFRadio();
}

function columnUp(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	var i = elements.length;
	while(i--)
	{
		if(elements[i]==element)
		{
			var eleId = element.id;
			var row = eleId.split("_");
			var pUp = i-1 >= 0 ? elements[i-1] : elements[elements.length-1].nextSibling;
			var rowId = pUp.id;
			var rowIdInfo = row[1] + "_" + row[2] + "_" + row[3];
			var rowLen = rowIdInfo.length;

			// Different Row...
			if (rowId.indexOf("row_" + row[1]) == 0)
			{
				pUp = i-1 > 0 ? elements[i-2] : elements[elements.length-1];
				var pUpSecs = rowId.split("_");

				var pUpSecsRow = parseInt(pUpSecs[1]).NaN0() == 0 ? 0 : parseInt(pUpSecs[1]);
				var pValUpSecsCol = parseInt(pUpSecs[2]) + 1;
				var pUpSecsCol = pValUpSecsCol.NaN0();

				// 1st Row?
				if (parseInt(row[1]) == 0)
					var prevRow = totalRows - 1;
				else
					var prevRow = parseInt(row[1]) - 1;

				var tempTr = document.getElementById("row_" + prevRow);
				var totalSibs = 0;

				while(tempTr)
				{
					var tempId = tempTr.id;

					if (!tempId || tempTr.nodeType != 1)
					{
						tempTr = tempTr.nextSibling;
						continue;
					}

					if (tempId.indexOf("row_" + (prevRow+1)) == 0)
						break;

					if (tempId.indexOf("tr_"  + prevRow) != 0)
					{
						tempTr = tempTr.nextSibling;
						continue;
					}

					totalSibs++;
					tempTr = tempTr.nextSibling;
				}

				pUpSecsRow = prevRow;
				pUpSecsCol = totalSibs;

				var newId = "tr_" + pUpSecsRow + "_" + pUpSecsCol + "_" + row[3];
				element.id = newId;

				var p = element.childNodes.length;
				while(p--)
				{
					if(element.childNodes[p].nodeName=='#text') continue;

					var pId = element.childNodes[p].id;

					if (!pId) continue;

					var preLen = pId.length - rowLen;
					var prefix = pId.substr(0, preLen);

					element.childNodes[p].id = prefix + pUpSecsRow + "_" + pUpSecsCol + "_" + row[3];

					if (element.childNodes[p].hasChildNodes)
					{
						var b = element.childNodes[p].childNodes.length;
						while(b--)
						{
							if (element.childNodes[p].childNodes[b].nodeName=='#text') continue;
							var cId = element.childNodes[p].childNodes[b].id;

							if (!cId) continue;

							var cPreLen = cId.length - rowLen;
							var cPrefix = cId.substr(0, cPreLen);

							element.childNodes[p].childNodes[b].id = cPrefix + pUpSecsRow + "_" + pUpSecsCol + "_" + row[3];

							if (cId.indexOf("column_") == 0)
								element.childNodes[p].childNodes[b].innerHTML = columnString + " " + (pUpSecsCol + 1);

							if (cId.indexOf("radio_") == 0)
							{
								element.childNodes[p].childNodes[b].setAttribute("onclick", "javascript:smfRadio('" + pUpSecsRow + "', '" + pUpSecsCol + "', '" + row[3] + "');");
								if (row[3] == layoutPos)
								{
									element.childNodes[p].childNodes[b].checked = true;
									layoutPos = row[3];
								}
							}
						}
					}
				}

				if (rowPos == parseInt(row[1]) && colPos == parseInt(row[2]) && layoutPos == parseInt(row[3]))
				{
					rowPos = pUpSecsRow;
					colPos = pUpSecsCol;
				}

				var fromRow = document.getElementById(rowId).nextSibling;
				var frRowArray = rowId.split("_");

				while(fromRow)
				{
					var fRowId = fromRow.id;

					if (!fRowId || fromRow.nodeType != 1 || fRowId.indexOf("tr_" + frRowArray[1]) != 0)
					{
						fromRow = fromRow.nextSibling;
						continue;
					}

					if (fRowId.indexOf("row_") == 0)
						break;

					var fRow = fRowId.split("_");
					fRowIdInfo = fRow[1] + "_" + fRow[2] + "_" + fRow[3];
					fRowLen = fRowIdInfo.length;
					var fNewCol = parseInt(fRow[2]) - 1;
					var fRowCol = fNewCol.NaN0() == 0 ? 0 : fNewCol;

					fromRow.id = "tr_" + fRow[1] + "_" + fRowCol + "_" + fRow[3];

					var f = fromRow.childNodes.length;
					while(f--)
					{

						if (fromRow.childNodes[f].nodeName=='#text') continue;
						var fTdId = fromRow.childNodes[f].id;

						if (!fTdId) continue;

						var fPreLen = fTdId.length - fRowLen;
						var fPrefix = fTdId.substr(0, fPreLen);

						fromRow.childNodes[f].id = fPrefix + fRow[1] + "_" + fRowCol + "_" + fRow[3];

						if (fromRow.childNodes[f].hasChildNodes)
						{
							var fc = fromRow.childNodes[f].childNodes.length;
							while(fc--)
							{
								if (fromRow.childNodes[f].childNodes[fc].nodeName == '#text') continue;
								var fcId = fromRow.childNodes[f].childNodes[fc].id;

								if (!fcId) continue;

								var fcPreLen = fcId.length - fRowLen;
								var fcPrefix = fcId.substr(0, fcPreLen);

								fromRow.childNodes[f].childNodes[fc].id = fcPrefix + fRow[1] + "_" + fRowCol + "_" + fRow[3];

								if (fcId.indexOf("column_") == 0)
									fromRow.childNodes[f].childNodes[fc].innerHTML = columnString + " " + (fRowCol + 1);

								if (fcId.indexOf("radio_") == 0)
								{
									fromRow.childNodes[f].childNodes[fc].setAttribute("onclick", "javascript:smfRadio('" + fRow[1] + "', '" + fRowCol + "', '" + fRow[3] + "');");
									if (row[3] == layoutPos)
										fromRow.childNodes[f].childNodes[fc].checked = false;
								}
							}
						}
					}

					if (rowPos == parseInt(fRow[1]) && colPos == parseInt(fRow[2]) && layoutPos == parseInt(fRow[3]))
						colPos = fRowCol;

					fromRow = fromRow.nextSibling;
				}
			}
			else
			{
				// Same Row...
				var pUpSecs = rowId.split("_");
				var suffix = rowId.substring(3);
				var pUpLen = suffix.length;

				element.id = "tr_" + pUpSecs[1] + "_" + pUpSecs[2] + "_" + row[3];

				var a = element.childNodes.length;
				while(a--)
				{
					if(element.childNodes[a].nodeName=='#text') continue;
					var pId = element.childNodes[a].id;

					if (!pId) continue;

					var preLen = pId.length - rowLen;

					var prefix = pId.substr(0, preLen);

					element.childNodes[a].id = prefix + pUpSecs[1] + "_" + pUpSecs[2] + "_" + row[3];

					if (element.childNodes[a].hasChildNodes)
					{
						var b = element.childNodes[a].childNodes.length;
						while(b--)
						{
							if (element.childNodes[a].childNodes[b].nodeName=='#text') continue;
							var cPId = element.childNodes[a].childNodes[b].id;

							if (!cPId) continue;

							var cPreLen = cPId.length - rowLen;
							var cPrefix = cPId.substr(0, cPreLen);

							element.childNodes[a].childNodes[b].id = cPrefix + pUpSecs[1] + "_" + pUpSecs[2] + "_" + row[3];

							if (cPId.indexOf("column_") == 0)
								element.childNodes[a].childNodes[b].innerHTML = columnString + " " + (parseInt(pUpSecs[2]) + 1);

							if (cPId.indexOf("radio_") == 0)
							{
								element.childNodes[a].childNodes[b].setAttribute("onclick", "javascript:smfRadio('" + pUpSecs[1] + "', '" + pUpSecs[2] + "', '" + row[3] + "');");
								if (row[3] == layoutPos)
								{
									element.childNodes[a].childNodes[b].checked = true;
									layoutPos = row[3];
								}
							}
						}
					}
				}

				var p = pUp.childNodes.length;
				while(p--)
				{
					if(pUp.childNodes[p].nodeName=='#text') continue;

					var pPId = pUp.childNodes[p].id;

					if (!pPId) continue;

					var pPreLen = pPId.length - pUpLen;
					var pPrefix = pPId.substr(0, pPreLen);

					pUp.childNodes[p].id = pPrefix + row[1] + "_" + row[2] + "_" + pUpSecs[3];

					if (pUp.childNodes[p].hasChildNodes)
					{
						var x = pUp.childNodes[p].childNodes.length;
						while(x--)
						{
							if (pUp.childNodes[p].childNodes[x].nodeName=='#text') continue;
							var pcPId = pUp.childNodes[p].childNodes[x].id;

							if (!pcPId) continue;

							var pcPreLen = pcPId.length - pUpLen;
							var pcPrefix = pcPId.substr(0, pcPreLen);

							pUp.childNodes[p].childNodes[x].id = pcPrefix + row[1] + "_" + row[2] + "_" + pUpSecs[3];

							if (pcPId.indexOf("column_") == 0)
								pUp.childNodes[p].childNodes[x].innerHTML = columnString + " " + (parseInt(row[2]) + 1);

							if (pcPId.indexOf("radio_") == 0)
							{
								pUp.childNodes[p].childNodes[x].setAttribute("onclick", "javascript:smfRadio('" + row[1] + "', '" + row[2] + "', '" + pUpSecs[3] + "');");
								if (row[3] == layoutPos)
									pUp.childNodes[p].childNodes[x].checked = false;
							}
						}
					}
				}

				pUp.id = "tr_" + row[1] + "_" + row[2] + "_" + pUpSecs[3];

				if (rowPos == parseInt(row[1]) && colPos == parseInt(row[2]) && layoutPos == parseInt(row[3]))
				{
					rowPos = parseInt(pUpSecs[1]);
					colPos = parseInt(pUpSecs[2]);
				}
				else if(rowPos == parseInt(pUpSecs[1]) && colPos == parseInt(pUpSecs[2]) && layoutPos == parseInt(pUpSecs[3]))
				{
					rowPos = parseInt(pUpSecs[1]);
					colPos = parseInt(row[2]);
				}
			}

			if (i == 1)
				i = 0;

			var newEle = element.parentNode.insertBefore(element.cloneNode(true), (i-1 >= 0 ? elements[i-1] : elements[elements.length-1].nextSibling));
			element.parentNode.removeChild(element);
		}
	}

	// IE Fix
	checkSMFRadio();
}

function checkSMFRadio()
{
	var oForm = document.dpFlayouts;

	var i = oForm.elements.length;
	while(i--)
	{
		if (oForm.elements[i].type != "radio")
			continue;

		var rId = oForm.elements[i].id;

		if (!rId) continue;

		if (rId.indexOf("radio_") != 0)
			continue;

		if (rId == "radio_" + rowPos + "_" + colPos + "_" + layoutPos)
			oForm.elements[i].checked = true;
		else
			oForm.elements[i].checked = false;
	}
}

function smfRadio(oRow, oCol, oLayoutPos)
{
	var rowCheck = document.getElementById("inputrow_" + rowPos).checked;
	var allCheck = document.getElementById("all_checks").checked;

	document.getElementById("tdenabled_" + rowPos + "_" + colPos + "_" + layoutPos).innerHTML = '<input type="checkbox" id="enabled_' + rowPos + '_' + colPos + "_" + layoutPos + '" name="enabled[]"' + ' checked="checked" class="' + checkClass + '" />';
	document.getElementById("tdcheck_" + rowPos + "_" + colPos + "_" + layoutPos).innerHTML = '<input type="checkbox" id="check_' + rowPos + '_' + colPos + "_" + layoutPos + '" name="section[]"' + (rowCheck || allCheck ? ' checked="checked"' : '') + ' class="' + checkClass + '" />';

	document.getElementById("tdenabled_" + oRow + "_" + oCol + "_" + oLayoutPos).innerHTML = "";
	document.getElementById("tdcheck_" + oRow + "_" + oCol + "_" + oLayoutPos).innerHTML = "";

	rowPos = parseInt(oRow);
	colPos = parseInt(oCol);
	layoutPos = parseInt(oLayoutPos);

	document.getElementById("smf_section").value = oLayoutPos;
}

function invertChecks(oCheckbox, oForm, idStr)
{
	var i = oForm.length;
	while(i--)
	{
		if (oForm[i].id.indexOf(idStr) != 0)
			continue;

		oForm[i].checked = oCheckbox.checked;
	}
}

// Simple function to add a hidden element for form submission
function addHiddenElement(formName, sValue, sName)
{
	var parent = document.forms[formName];
	var oHidden = document.createElement("input");
	oHidden.type = "hidden";
	oHidden.value = sValue;
	oHidden.name = sName;

	parent.appendChild(oHidden);

	return oHidden;
}

// Simple function to remove all hidden elements from an element
function removeHiddenElements(formName)
{
	var parent = document.forms[formName];
	element = parent.getElementsByTagName("input");
	var i = element.length;
	while (i--)
		if (element[i].type.indexOf("hidden") == 0)
			parent.removeChild(element[i]);
}

// Adds several hidden inputs to the edit layouts form
function beforeLayoutEditSubmit()
{
	addHiddenElement("dpFlayouts", rowPos, "rowPos");
	addHiddenElement("dpFlayouts", colPos, "colPos");
	addHiddenElement("dpFlayouts", layoutPos, "layoutPos");
	addHiddenElement("dpFlayouts", newColumns, "newColumns");
	addHiddenElement("dpFlayouts", totalColumns, "totalColumns");
	addHiddenElement("dpFlayouts", totalRows, "totalRows");

	var oTable = document.getElementById("edit_layout_tbody");
	var cId = "";

	var i = oTable.childNodes.length;
	while(i--)
	{
		var rId = oTable.childNodes[i].id;

		if (!rId) continue;

		if (rId.indexOf("row_") > -1)
		{
			addHiddenElement("dpFlayouts", rId.replace("row_", ""), "rId[]");
			cId = rId;
		}

		if (rId.indexOf("tr_") > -1)
			addHiddenElement("dpFlayouts", rId.replace("tr_", ""), "cId[]");
	}
}