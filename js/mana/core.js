/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function($) {
	// this variables are private to this code block
	var _translations = {};
	var _options = {};

	// Default usage of this function is to pass a string in original language and get translated string as a 
	// result. This same function is also used to register original and translated string pairs - in this case
	// plain object with mappings is passed as the only parameter. Anyway, we expect the only parameter to be 
	// passed
	$.__ = function(key) {
		if (typeof key === "string") { // do translation
			var args = arguments;
			args[0] = _translations[key] ? _translations[key] : key;
			return $.vsprintf(args);
		}
		else { // register translation pairs
			_translations = $.extend(_translations, key);
		}
	};
	// Default usage of this function is to pass a CSS selector and get plain object of associated options as 
	// a result. This same function is used to register selector-object pairs in this case plain object with 
	// with mappings is passed as the only parameter. Anyway, we expect the only parameter to be passed
	$.options = function (selector) {
		if (typeof selector === "string") { // return associated options
			return _options[selector];
		}
		else { // register selector-options pairs
			_options = $.extend(true, _options, selector);
		}
	};
	
	$.dynamicUpdate = function (update) {
		if (update) {
			$.each(update, function(index, update) {
				$(update.selector).html(update.html);
			});
		}
	}
	$.dynamicReplace = function (update, loud) {
		if (update) {
			$.each(update, function(selector, html) {
				var selected = $(selector);
				if (selected.length) {
					var first = $(selected[0]);
					if (selected.length > 1) {
						selected.slice(1).remove();
					}
					first.replaceWith(html);
				}
				else {
					if (loud) {
						throw 'There is no content to replace.';
					}
				}
				//console.log('Selector: ' + selector);
				//console.log('HTML: ' + html);
			});
		}
	}
	
	$.errorUpdate = function(selector, error) {
		if (!selector) {
			selector = '#messages';
		}
		var messages = $(selector);
		if (messages.length) {
			messages.html('<ul class="messages"><li class="error-msg"><ul><li>' + error + '</li></ul></li></ul>');
		}
		else {
			alert(error);
		}
	}
	
	// Array Remove - By John Resig (MIT Licensed)
	$.arrayRemove = function(array, from, to) {
	  var rest = array.slice((to || from) + 1 || array.length);
	  array.length = from < 0 ? array.length + from : from;
	  return array.push.apply(array, rest);
	};
	$.mViewport = function() {
		var m = document.compatMode == 'CSS1Compat';
		return {
			l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
			t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
			w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
			h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
		};
	}
	$.mStickTo = function(el, what) {
		var pos = $(el).offset();
		var viewport = $.mViewport();
		var top = pos.top + el.offsetHeight;
		var left = pos.left + (el.offsetWidth - what.outerWidth()) / 2;
		if (top + what.outerHeight() > viewport.t + viewport.h) {
			top = pos.top - what.outerHeight();
		}
		if (left + what.outerWidth() > viewport.l + viewport.w) {
			left = pos.left + el.offsetWidth - what.outerWidth();
		}
		what.css({left: left + 'px', top: top + 'px'});
	}
	$.fn.mMarkAttr = function (attr, condition) {
		if (condition) {
			this.attr(attr, attr);
		}
		else {
			this.removeAttr(attr);
		}
		return this;
	}; 
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		// fix for IE 7 and IE 8 where dom:loaded may fire too early
		try {
			mainNav("nav", {"show_delay":"100","hide_delay":"100"});
		}
		catch (e) {
			
		}
	});

    function _close() {
        $('.m-popup-overlay').fadeOut(500, function() {
            $('.m-popup-overlay').remove();
            $('#m-popup').fadeOut(1000);
        })
        return false;
    }

    $('.m-popup-overlay').live('click', _close);
    $(document).keydown(function (e) {
        if ($('.m-popup-overlay').length) {
            if (e.keyCode == 27) {
                return _close();
            }
        }
    });
})(jQuery);
