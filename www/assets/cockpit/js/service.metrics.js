
NGApp.factory('MetricsService', function($resource) {

	var service = {};
	var log = console.log;
	// pretty logging in console :)
	var log_debug = 'debug' in console ? console.debug : console.log;
	var log_info = 'info' in console ? console.info : console.log;
	var log_warn = 'warn' in console ? console.warn : console.log;
	var log_error = 'error' in console ? console.error : console.log;
	var relativeTimeRegex = /[1-9][0-9]*hmdMsw$/
	// validateTime checks that time matches expectations and can be sent to backend
	// Formats:
	//  Relative time: -7d (7 days in the past from today), -5M (5 months)
	//    period: 'd' (day), 'h' (hour), 'M' (month), 'm' (minute), 'w' (week),
	//            's' (second)
	//  Unix timestamp: 1423778006.28 (February 12, 2015 at 1:56 PM -0700)
	//  Human readable: 'now' (current second)
	function validateTime(timeString) {
		var maybeFloat = parseFloat(timeString);
		if (!isNaN(maybeFloat)) {
			return maybeFloat > 0;
		}
		timeString = timeString.replace(/ /g, '');
		if (timeString === 'now') {
			return true;
		}
		return relativeTimeRegex.test(timeString);
	}

	// check whether given string period matches a valid period (see validPeriods for more)
	var validPeriods = ['hour', 'day', 'week', 'month'];
	function validatePeriod(period) {
		// TODO: Check if we support IE8 and below (this will fail on those browsers);
		return validPeriods.indexOf(period.replace(/ /g, '')) >= 0;
	}

	var specialCommunities = ['all', 'active', 'inactive'];

	function validateCommunities(communities) {
		communities = communities.replace(/ /g, '');
		if(specialCommunities.indexOf(communities) >= 0) {
			return true;
		}
		communityArray = communities.split(/,/);
		if(communityArray.length === 0) {
			log_warn('no communities selected');
			return false;
		}
		for(var i=0; i < communityArray.length; i++) {
			if(!parseInt(communityArray[i])) {
				log_warn('non-integer community', communityArray[i]);
				return false;
			}
		}
		return true;
	}
	function convertURLData(watchData) {

	}
	var metrics = $resource( App.service + 'metrics/:id_metrics', { id_metrics: '@id_metrics'}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});

	service.get = function(params, callback) {
		metrics.load(params, function(data) {
			callback(data);
		});
	}

	return service;
});
