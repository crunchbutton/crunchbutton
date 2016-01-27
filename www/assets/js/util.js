/**************************************************
* Functions to identify the user's browser/device *
**************************************************/

App.isMobile = function(){
	// $.browser.mobile doesn't detect android tablet
	return App.isAndroid() || $.browser.mobile;
}

App.hideKeyboard = function(){

	//this set timeout needed for case when hideKeyborad
	//is called inside of 'onfocus' event handler
	setTimeout(function() {

		//creating temp field
		var field = document.createElement('input');
		field.setAttribute('type', 'text');
		//hiding temp field from peoples eyes
		//-webkit-user-modify is nessesary for Android 4.x
		field.setAttribute('style', 'position:absolute; top: 0px; opacity: 0; -webkit-user-modify: read-write-plaintext-only; left:0px;');

		document.body.appendChild(field);

		//adding onfocus event handler for out temp field
		field.onfocus = function(){
			//this timeout of 200ms is nessasary for Android 2.3.x
			setTimeout(function() {
				field.setAttribute('style', 'display:none;');
				setTimeout(function() {
					document.body.removeChild( field );
					document.body.focus();
				}, 10);

			}, 10);
		};
		//focusing it
		field.focus();

	}, 10);
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


/* month methods */
Date.prototype.getMonthName = function(lang) {
	lang = lang && (lang in Date.locale) ? lang : 'en';
	return Date.locale[lang].month_names[this.getMonth()];
};

Date.prototype.getMonthNameShort = function(lang) {
	lang = lang && (lang in Date.locale) ? lang : 'en';
	return Date.locale[lang].month_names_short[this.getMonth()];
};

Date.locale = {
	en: {
		month_names: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		month_names_short: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']
	}
};

Date.prototype.formatted = function() {
	return ( this.getMonth() + 1 ) + '/' + this.getDate() + '/' + this.getFullYear();
};

Number.prototype.pad = String.prototype.pad = function(width, z) {
	var z = z || '0';
	var n = this + '';
	return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

// calc the diff between two timestamps in seconds
function timestampDiff( time1, time2 ){
	return Math.floor( ( time1 - time2 ) / 1000 );
}

function formatTime( seconds, message ){
	if( seconds <= 60 ){
		return 'in less than a minute';
	}
	var time = Math.floor( seconds / 60 );
	if( time && time > 0 ){
		var hours = Math.floor( time / 60 );
		var minutes = time - ( hours * 60 );
		if( hours > 0 ){
			// #2662
			if( hours <= 24 ){
				return 'in ' + ( hours + ( ( hours > 1 ) ? ' hours' : ' hour' ) );
			}
			if( hours > 24 ){
				if( message ){
					return ( message.tomorrow ? message.tomorrow : message.day ) + ' at ' + message.hour + ( parseInt( message.min ) > 0 ? ':' + message.min : '' ) + ' ' + message.ampm;
				} else {
					return 'in ' + ( hours + ( ( hours > 1 ) ? ' hours' : ' hour' ) );
				}
			}
		} else {
			return 'in ' + ( ( minutes > 1 ) ? minutes + ' minutes' : minutes + ' minute' );
		}
	}
	return '';
}


$.pluck = function(ar, len) {
	for (var i = ar.length - 1; i > 0; i--) {
		var j = Math.floor(Math.random() * (i + 1));
		var temp = ar[i];
		ar[i] = ar[j];
		ar[j] = temp;
	}
	return ar.slice(0, len || len.length);
};




function getCardType(number) {
	var re = new RegExp("^4");
	if (number.match(re) != null) {
		return "visa";
	}

	re = new RegExp("^(34|37)");
	if (number.match(re) != null) {
		return "amex";
	}

	re = new RegExp("^5[1-5]");
	if (number.match(re) != null) {
		return "mastercard";
	}

	re = new RegExp("^6011");
	if (number.match(re) != null) {
		return "discover";
	}
	return "";
}