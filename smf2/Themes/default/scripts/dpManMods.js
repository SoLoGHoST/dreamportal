/**************************************************************************************
* dpManMods.js                                                                       *
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
var $j = jQuery.noConflict();

$j(document).ready(function() {
	// Make all module containers sortable and connect them, too, so they might recieve each other's items
	$j(".module_container").dragsort({itemSelector: "div", dragSelector: "div", dragBetween: true, dragEnd: function() {}, placeHolderTemplate: "<div class='placeholder'></div>" });

	$j("#save").click(function() {
		var submit_data = "";
		$j(".DragBox").each(function() {
			submit_data += $j(this).parent().attr("id") + "[]=" + $j(this).attr("id").replace("dreammod_", "") + "&";
		});
		$j(".check_enabled").each(function() {
			submit_data += $j(this).attr("id") + "=" + ($j(this).is(":checked") ? 1 : 0) + "&";
		});
		$j.ajax({
			type: "POST",
			url: smf_prepareScriptUrl(smf_scripturl) + "action=admin;area=dplayouts;sa=dpsavelayout;xml;js_save;" + sessVar + "=" + sessId,
			data: submit_data,
			success: function(data) {
				$j("#messages").html("<div id=\"profile_success\"></div>");
				$j("#profile_success").html("<strong>" + modulePositionsSaved + "</strong>")
				.append("<br />" + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$j("#messages").html("<div id=\"profile_error\"></div>");
				$j("#profile_error").html("<strong>" + errorString + "</strong>" + textStatus)
				.append("<br />" + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			}
		});
	});
	$j(".clonelink").click(cloneLinkEvent);

	function cloneLinkEvent(event) {
		event.preventDefault();
		$j.ajax({
			url: $j(this).attr("href"),
			success: function(data) {
				if (data.indexOf("deleted") != -1)
				{
					var removedID = data.replace("deleted", "");
					$j("#dreammod_" + removedID).fadeOut().remove();

					$j("#messages").html("<div id=\"profile_success\"></div>");
					$j("#profile_success").html("<strong>" + cloneDeleted + "</strong>")
					.append("<br />" + clickToClose)
					.hide()
					.click(function() {
						$j(this).fadeOut();
					})
					.fadeIn();
				}
				else
				{
					$j(".clonelink").unbind("click", cloneLinkEvent);
					$j("#messages").html("<div id=\"profile_success\"></div>");
					$j("#profile_success").html("<strong>" + cloneMade + "</strong>")
					.append("<br />" + clickToClose)
					.hide()
					.click(function() {
						$j(this).fadeOut();
					})
					.fadeIn();

					// Insert the new cloned module
					$j(".disabled .dummy").before(data);

					// We need to rebind the event to the new clone link.
					$j(".clonelink").bind("click", cloneLinkEvent);
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$j("#messages").html("<div id=\"profile_error\"></div>");
				$j("#profile_error").html("<strong>" + errorString + "</strong>" + textStatus)
				.append("<br />" + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			}
		});
	}
});

/*
	jQuery List DragSort v0.3.10
	Website: http://dragsort.codeplex.com/
	License: http://dragsort.codeplex.com/license
*/

(function($j) {

	$j.fn.dragsort = function(options) {
		var opts = $j.extend({}, $j.fn.dragsort.defaults, options);
		var lists = new Array();
		var list = null, lastPos = null;
		if (this.selector)
			$j("head").append("<style type='text/css'>" + (this.selector.split(",").join(" " + opts.dragSelector + ",") + " " + opts.dragSelector) + " { cursor: pointer; }</style>");

		this.each(function(i, cont) {

			if ($j(cont).is("table") && $j(cont).children().size() == 1 && $j(cont).children().is("tbody"))
				cont = $j(cont).children().get(0);

			var newList = {
				draggedItem: null,
				placeHolderItem: null,
				pos: null,
				offset: null,
				offsetLimit: null,
				container: cont,

				init: function() {
					$j(this.container).attr("listIdx", i).mousedown(this.grabItem).find(opts.dragSelector).css("cursor", "pointer");
				},

				grabItem: function(e) {
					if (e.button == 2 || $j(e.target).is(opts.dragSelectorExclude))
						return;

					var elm = e.target;
					while (!$j(elm).is("[listIdx=" + $j(this).attr("listIdx") + "] " + opts.dragSelector)) {
						if (elm == this) return;
						elm = elm.parentNode;
					}

					if (list != null && list.draggedItem != null)
						list.dropItem();

					$j(e.target).css("cursor", "move");

					list = lists[$j(this).attr("listIdx")];
					list.draggedItem = $j(elm).closest(opts.itemSelector);
					var mt = parseInt(list.draggedItem.css("marginTop"));
					var ml = parseInt(list.draggedItem.css("marginLeft"));
					var w = parseInt(list.draggedItem.width());
					list.offset = list.draggedItem.offset();
					list.offset.top = e.pageY - list.offset.top + (isNaN(mt) ? 0 : mt) - 1;
					list.offset.left = e.pageX - list.offset.left + (isNaN(ml) ? 0 : ml) - 1;

					if (!opts.dragBetween) {
						var containerHeight = $j(list.container).outerHeight() == 0 ? Math.max(1, Math.round(0.5 + $j(list.container).children(opts.itemSelector).size() * list.draggedItem.outerWidth() / $j(list.container).outerWidth())) * list.draggedItem.outerHeight() : $j(list.container).outerHeight();
						list.offsetLimit = $j(list.container).offset();
						list.offsetLimit.right = list.offsetLimit.left + $j(list.container).outerWidth() - list.draggedItem.outerWidth();
						list.offsetLimit.bottom = list.offsetLimit.top + containerHeight - list.draggedItem.outerHeight();
					}

					if ($j(elm).hasClass("disabled"))
						list.draggedItem.css({ position: "absolute", opacity: 0.8, "z-index": 999 }).after(opts.placeHolderTemplate);
					else
						list.draggedItem.css({ position: "absolute", opacity: 0.8, "z-index": 999, "width": $j(list.container).children(opts.itemSelector).width() }).after(opts.placeHolderTemplate);
					list.placeHolderItem = list.draggedItem.next().css({"height": $j(list.container).children(opts.itemSelector).height(), "width": $j(list.container).children(opts.itemSelector).width()}).attr("placeHolder", true);

					$j(lists).each(function(i, l) { l.ensureNotEmpty(); l.buildPositionTable(); });

					list.setPos(e.pageX, e.pageY);
					$j(document).bind("selectstart", list.stopBubble); //stop ie text selection
					$j(document).bind("mousemove", list.swapItems);
					$j(document).bind("mouseup", list.dropItem);
					return false; //stop moz text selection
				},

				setPos: function(x, y) {
					var top = y - this.offset.top;
					var left = x - this.offset.left;

					if (!opts.dragBetween) {
						top = Math.min(this.offsetLimit.bottom, Math.max(top, this.offsetLimit.top));
						left = Math.min(this.offsetLimit.right, Math.max(left, this.offsetLimit.left));
					}

					this.draggedItem.parents().each(function() {
						if ($j(this).css("position") != "static" && $j(this).css("display") != "table") {
							var offset = $j(this).offset();
							top -= offset.top;
							left -= offset.left;
							return false;
						}
					});

					this.draggedItem.css({ top: top, left: left });
				},

				buildPositionTable: function() {
					var item = this.draggedItem == null ? null : this.draggedItem.get(0);
					var pos = new Array();
					$j(this.container).children(opts.itemSelector).each(function(i, elm) {
						if (elm != item) {
							var loc = $j(elm).offset();
							loc.right = loc.left + $j(elm).width();
							loc.bottom = loc.top + $j(elm).height();
							loc.elm = elm;
							pos.push(loc);
						}
					});
					this.pos = pos;
				},

				dropItem: function(e) {
					if (list.draggedItem == null)
						return;

					$j(list.container).find(opts.dragSelector).css("cursor", "pointer");
					list.placeHolderItem.before(list.draggedItem);

					if (list.draggedItem.parents().hasClass("disabled"))
						list.draggedItem.css({ position: "", top: "", left: "", opacity: "", "z-index": "" });
					else
						list.draggedItem.css({ position: "", top: "", left: "", opacity: "", "z-index": "", width: "100%" });
					list.placeHolderItem.remove();

					$j("*[emptyPlaceHolder]").remove();

					opts.dragEnd.apply(list.draggedItem);
					list.draggedItem = null;
					$j(document).unbind("selectstart", list.stopBubble);
					$j(document).unbind("mousemove", list.swapItems);
					$j(document).unbind("mouseup", list.dropItem);
					return false;
				},

				stopBubble: function() { return false; },

				swapItems: function(e) {
					if (list.draggedItem == null)
						return false;

					list.setPos(e.pageX, e.pageY);

					var ei = list.findPos(e.pageX, e.pageY);
					var nlist = list;
					for (var i = 0; ei == -1 && opts.dragBetween && i < lists.length; i++) {
						ei = lists[i].findPos(e.pageX, e.pageY);
						nlist = lists[i];
					}

					if (ei == -1 || $j(nlist.pos[ei].elm).attr("placeHolder"))
						return false;

					if (lastPos == null || lastPos.top > list.draggedItem.offset().top || lastPos.left > list.draggedItem.offset().left)
						$j(nlist.pos[ei].elm).before(list.placeHolderItem);
					else
						$j(nlist.pos[ei].elm).after(list.placeHolderItem);

					$j(lists).each(function(i, l) { l.ensureNotEmpty(); l.buildPositionTable(); });
					lastPos = list.draggedItem.offset();
					return false;
				},

				findPos: function(x, y) {
					for (var i = 0; i < this.pos.length; i++) {
						if (this.pos[i].left < x && this.pos[i].right > x && this.pos[i].top < y && this.pos[i].bottom > y)
							return i;
					}
					return -1;
				},

				ensureNotEmpty: function() {
					if (!opts.dragBetween)
						return;

					var item = this.draggedItem == null ? null : this.draggedItem.get(0);
					var emptyPH = null, empty = true;

					$j(this.container).children(opts.itemSelector).each(function(i, elm) {
						if ($j(elm).attr("emptyPlaceHolder"))
							emptyPH = elm;
						else if (elm != item)
							empty = false;
					});

					if (empty && emptyPH == null)
						$j(this.container).append(opts.placeHolderTemplate).children(":last").attr("emptyPlaceHolder", true);
					else if (!empty && emptyPH != null)
						$j(emptyPH).remove();
				}
			};

			newList.init();
			lists.push(newList);
		});

		return this;
	};

	$j.fn.dragsort.defaults = {
		itemSelector: "li",
		dragSelector: "li",
		dragSelectorExclude: "input, a[href]",
		dragEnd: function() { },
		dragBetween: false,
		placeHolderTemplate: "<li>&nbsp;</li>"
	};

})(jQuery);