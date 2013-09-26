/* Table Search - Jquery Plugin
 * Query results in html table
 * https://github.com/Yarkhan/jquery-tablesearch/
 *
*/
(function($){
	$.extend($.expr[":"], {
		"icontains": function(elem, i, match, array) {
			return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
		}
	});
	$.fn.tableSearch = function(val,options){
		opts = $.extend({},$.fn.tableSearch.defaults,options);
		match = $('tbody > tr:icontains("'+val+'")',this);
		nomatch = $('tbody > tr:not(:icontains("'+val+'"))',this);
		opts.withMatch(match);
		opts.withNoMatch(nomatch);
	}

	$.fn.tableSearch.defaults = {
		withMatch:function(matches){
			matches.fadeIn();
		},
		withNoMatch:function(nomatches){
			nomatches.fadeOut();
		}
	}
}(jQuery));