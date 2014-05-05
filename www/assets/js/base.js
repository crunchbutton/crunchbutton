if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; },
		debug: function() { return null; },
		error: function() { return null; }
	};
}

if (typeof(Number.prototype.toRad) === 'undefined') {
	Number.prototype.toRad = function() {
		return this * Math.PI / 180;
	}
}

var _toRad = function() {
	var Value = arguments.shift();
	return Number.prototype.toRad.call(Value, args);
};

if (!typeof(App) == 'undefined') {
	App = {};
}

App.capitalize = function(word) {
	return word.charAt(0).toUpperCase() + word.slice(1);
};

App.request = function(url, complete, error) {
	if( angular && angular.element && angular.element( 'html' ) && angular.element( 'html' ).injector() && angular.element( 'html' ).injector().get( '$http' ) ){
		var http = angular.element( 'html' ).injector().get( '$http' );
		http( { url: url, method: 'GET' } ).success( function( data ) { 
			if( complete ){
				complete( data );	
			}
		}	).error(function( data ) {
			if( error ){
				error( data )
			}
		} );
	} else {
		$.getJSON(url).done(complete).fail(error);
	}
};

// @todo replace with lazyorm.js
App.cache = function(type, id) {

	var finalid, args = arguments, complete, partComplete, partError;

	complete = args[2] ? args[2] : function() {};
	error = args[3] ? args[3] : function() {};

	partComplete = function() {
		if (this.uuid) {
			App.cached[type][id.uuid] = this;
			App.cached[type][id] = this;
		}
		if (this.permalink) {
			App.cached[type][id.permalink] = this;
			App.cached[type][id] = this;
		}
		complete.call(this);
	}
	
	partError = function() {
		App.cached[type][id] = null;
		error.call(arguments);
	}

	if (typeof(id) == 'object') {
		eval('App.cached[type][id.id] = new '+type+'(id,partComplete, partError)');
		finalid = id.id;
	} else if (!App.cached[type][id]) {
		eval('App.cached[type][id] = new '+type+'(id,partComplete, partError)');
	} else {
		var element = App.cached[type][id];
		var removedFromCache = false;
		if( element.cachedAt ){
			var now = ( Math.floor( new Date().getTime() / 1000 ) );
			var age = Math.floor( now - element.cachedAt );
			if( age >= App.cachedObjectsExpiresIn ){
				removedFromCache = true;
				App.cached[type][id] = null;
				if (typeof(id) == 'object') {
					eval('App.cached[type][id.id] = new '+type+'(id,partComplete, partError)');
					finalid = id.id;
				} else {
					eval('App.cached[type][id] = new '+type+'(id,partComplete, partError)');
				}
			}
		}
		if( !removedFromCache ){
			complete.call( App.cached[type][id] );
		}
	}
	// @todo: remove this and things that use it
	return App.cached[type][finalid || id];

};

/**
 * Rounds a float number rounded up with 2 digits.
 */
App.ceil = function(num) {
	num = num*100;
	num = Math.ceil(num);
	return num / 100;
};

App.phone = {

	/**
	 * Add dashes to the phone number, unifying how phone number looks
	 */
	format: function(num) {
		if( num != null ){
			num = num.replace(/^0|^1/,'');
			num = num.replace(/[^\d]*/gi,'');
			num = num.substr(0,10);

			if (num.length >= 7) {
				num = num.replace(/(\d{3})(\d{3})(.*)/, "$1-$2-$3");
			} else if (num.length >= 4) {
				num = num.replace(/(\d{3})(.*)/, "$1-$2");
			}
		}
		return num;
	},
	validate: function(num) {

		if( !num ){
			return false;
		}

		num = num.replace(new RegExp( '-', 'g'), '');

		if (!num || num.length != 10) {
			return false;
		}

		var
			nums = num.split(''),
			prev;

		for (x in nums) {
			if (!prev) {
				prev = nums[x];
				continue;
			}

			if (nums[x] != prev) {
				return true;
			}
		}

		return false;
	}
};

App.pad = function(number, length) {
	var str = '' + number;
	while (str.length < length) {
		str = '0' + str;
	}
	return str;
};

App.formatTime = function(time) {
	if (!time) {
		return '';
	}

	var vals = time.split(':');

	var pm = false;

	vals[0] = new String(vals[0]);
	vals[1] = vals[1] ? new String(vals[1]) : 0;

	if (vals[0].match(/^[0-9]{3,4}$/i)) {
		vals[1] = vals[0].substr(-2,2);
		vals[0] = vals[0].substr(0,vals[0].length - 2);
	}

	if (vals[0] == '12') {
		pm =  true;
	}

	if (vals[0] == '24') {
		vals[0] = '00';
	}

	if (vals[0] > 12) {
		vals[0] -= 12;
		pm = true;
	} else{
		if (!pm && time.match(/p/i)) {
			pm = true;
		} 
		if (pm && time.match(/a/i)) {
			pm = false;
		} 	
	}
	
	if (!vals[1]) {
		vals[1] = '00';
	} 
	
	vals[1] = vals[1].replace(/[^0-9]+/,'');
	vals[0] = new String(vals[0]).replace(/[^0-9]+/,'');

	vals[0] = App.pad(vals[0],2);
	vals[1] = App.pad(vals[1],2);

	if (vals[0] == '00') {
		vals[0] = 12;
	}

	return vals.join(':') + (pm ? ' PM' : ' AM');
};

/**
 * Turns AM/PM format in 24h
 *
 * It was storing 12:00 AM as 24:00, or even worse, 12:30 AM as 24:30.
 * 12:00 AM should be 00:00 everywhere
 *
 * @return string
 */
App.unFormatTime = function(time) {
	var part   = time.split(' ');
	part[0]    = part[0].split(':');
	part[0][0] = parseInt(part[0][0], 10);

	if (part[1] == 'PM') {
		if (part[0][0] != 12) {
			part[0][0] = App.pad(part[0][0] + 12,2);
		}
	} else {
		if (part[0][0] == 12) {
			part[0][0] = '00';
		}
	}
	return part[0][0] + ':' + part[0][1];
};

App.cleanInput = function(text) {
	text = App.cleanBad(text);
	var type = arguments[1];
	switch (type) {
		case 'float':
			text = text.replace(/[^0-9\.]+/i,'');
			break;
		case 'text':
		default:
			text = text.replace(/[^a-z0-9\-_ \(\)\+\.\@\%\&\!\;\:\"\'\,\\\/]+/i,'');
			break;
	}

	return text
};

App.isVersionCompatible = function( required, installed ) {
	required = required.toString().split( '.' )
	installed = installed.toString().split( '.' );
	var length = Math.max( required.length, installed.length );
	var comparator = 0;
	for( var i = 0; i < length && !comparator; i++ ) {
		var part1 = parseInt( required[ i ], 10 ) || 0;
		var part2 = parseInt( installed[ i ], 10 ) || 0;
		if( part1 < part2 ){
			comparator = 1;
		}
		if(part1 > part2){
			comparator = -1;
		}
	}
	return ( comparator >= 0 );
}

App.cleanBad = function(s) {
	s = s.replace(/\u2018|\u2019|\u201A|\uFFFD/g, "'");
	s = s.replace(/\u201c|\u201d|\u201e/g, '"');
	s = s.replace(/\u02C6/g, '^');
	s = s.replace(/\u2039/g, '<');
	s = s.replace(/\u203A/g, '>');
	s = s.replace(/\u2013/g, '-');
	s = s.replace(/\u2014/g, '--');
	s = s.replace(/\u2026/g, '...');
	s = s.replace(/\u00A9/g, '(c)');
	s = s.replace(/\u00AE/g, '(r)');
	s = s.replace(/\u2122/g, 'TM');
	s = s.replace(/\u00BC/g, '1/4');
	s = s.replace(/\u00BD/g, '1/2');
	s = s.replace(/\u00BE/g, '3/4');
	s = s.replace(/[\u02DC|\u00A0]/g, " ");
	return s;
}