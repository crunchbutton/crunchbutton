/**************************************************
* Functions to identify the user's browser/device *
**************************************************/

App.isMobile = function(){
	// $.browser.mobile doesn't detect android tablet
	return App.isAndroid() || $.browser.mobile;
}

App.isNarrowScreen = function(){
	return $( window ).width() <= 769;
}

App.iOS = function(){
	return /ipad|iphone|ipod/i.test( navigator.userAgent.toLowerCase() );
}

App.iOS7 = function(){
	return /iphone os 7_/i.test( navigator.userAgent.toLowerCase() );
}

App.isAndroid = function(){
	return /android/i.test( navigator.userAgent.toLowerCase() );
}

App.isChrome = function(){
	// As the user agent can be changed, let make sure if the browser is chrome or not.
	return /chrom(e|ium)/.test( navigator.userAgent.toLowerCase() ) || /crios/.test( navigator.userAgent.toLowerCase() ) || ( typeof window.chrome === 'object' );
}

var sort_by;

(function() {
	// utility functions
	var default_cmp = function(a, b) {
		if (a == b) return 0;
		return a < b ? -1 : 1;
	},
		getCmpFunc = function(primer, reverse) {
			var cmp = default_cmp;
			if (primer) {
				cmp = function(a, b) {
					return default_cmp(primer(a), primer(b));
				};
			}
			if (reverse) {
				return function(a, b) {
					return -1 * cmp(a, b);
				};
			}
			return cmp;
		};

	// actual implementation
	sort_by = function() {
		var fields = [],
			n_fields = arguments.length,
			field, name, reverse, cmp;

		// preprocess sorting options
		for (var i = 0; i < n_fields; i++) {
			field = arguments[i];
			if (typeof field === 'string') {
				name = field;
				cmp = default_cmp;
			}
			else {
				name = field.name;
				cmp = getCmpFunc(field.primer, field.reverse);
			}
			fields.push({
				name: name,
				cmp: cmp
			});
		}

		return function(A, B) {
			var a, b, name, cmp, result;
			for (var i = 0, l = n_fields; i < l; i++) {
				result = 0;
				field = fields[i];
				name = field.name;
				cmp = field.cmp;

				result = cmp(A[name], B[name]);
				if (result !== 0) break;
			}
			return result;
		}
	}
}());

var startCoords = {}, endCoords = {}, cordsThresh = 3;

if (window.jQuery) {
	(function($){
		$.fn.checkToggle = function(params) {
			var checks = $(this).filter('input[type="checkbox"]');
			$(this).filter('input[type="checkbox"]').each(function() {		
				$(this).prop('checked', !$(this).is(':checked'));
			});
			return this;
		};
	})(jQuery);
}