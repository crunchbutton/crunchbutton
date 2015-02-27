/* global NGApp */
/* global App */
/* global _ */
NGApp.factory('MetricsService', function ($resource, $http, $q) {

	var service = {};
	var log = console.log;
	// pretty logging in console :)
	var log_debug = 'debug' in console ? console.debug : console.log;
	var log_info = 'info' in console ? console.info : console.log;
	var log_warn = 'warn' in console ? console.warn : console.log;
	var log_error = 'error' in console ? console.error : console.log;
	var relativeTimeRegex = /[1-9][0-9]*hmdMsw$/;
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
		if (specialCommunities.indexOf(communities) >= 0) {
			return true;
		}
		var communityArray = communities.split(/,/);
		if (communityArray.length === 0) {
			log_warn('no communities selected');
			return false;
		}
		for (var i = 0; i < communityArray.length; i++) {
			if (!parseInt(communityArray[i], 10)) {
				log_warn('non-integer community', communityArray[i]);
				return false;
			}
		}
		return true;
	}
	function convertURLData(watchData) {

	}
	var metrics = $resource(App.service + 'metrics/:id_metrics', { id_metrics: '@id_metrics'}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});

	function errorOnBadValue(name, value, allowed, nullAllowed) {
		if ((value === null || value === undefined) && nullAllowed) {
			return true;
		}
		for (var i = 0; i < allowed.length; i++) {
			if (value === allowed[i]) {
				return true;
			}
		}
		throw new Error('invalid value for ' + name + '. was: ' + value);
	}
	// properly iterate over an associative object
	function eachKV(data, func, includeUndefined) {
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				if (data[key] !== undefined || includeUndefined) {
					func(key, data[key]);
				}
			}
		}
	}
	function sum(arr, start) {
		var total = start || 0;
		for (var i = 0; i < arr.length; i++) {
			total = total + arr[i];
		}
		return total;
	}
	/**
	 * returns an ordering of chartData based on
	 * @param data - object that maps {groupKey => {chartType => {data: [], labels: []}}}
	 * @param on - chart type to order or
	 * @param method (optional, default 'max')- method for ordering (min, max, avg, nth), where nth means on the nth element
	 * @param direction (optional, default 'desc') - whether to ordering ascending (asc) or descending (desc)
	 * @param n (otpional) - the index into the array to sort on
	 *
	 **/
	service.orderChartData = function orderChartData(data, on, method, direction, n) {
		errorOnBadValue('direction', direction, ['asc', 'desc'], true);
		errorOnBadValue('method', method, ['min', 'max', 'avg', 'nth', 'last', 'first'], true);
		method = method || 'max';
		if (method === 'nth' && typeof n !== 'number') {
			throw new Error('n for ordering chart data must be a number. got: ' + n);
		}
		// default to descending
		var sign = direction === 'asc' ? 1 : -1;
		console.log('sign is ', sign, 'with direction: ', direction);
		var sortData = [];
		// need to convert to an array of data (vs. object) so we can sort it
		eachKV(data, function (k, v) { if(v[on] && v[on].data) { sortData.push({'key': k, 'data': v[on].data[0]}); } });
		var func;
		switch (method) {
		case 'min':
			func = function (d) { return Math.min.apply(null, d); };
			break;
		case 'max':
			func = function (d) { return Math.max.apply(null, d); };
			break;
		case 'avg':
			func = function (d) { return sum(d) / d.length; };
			break;
		case 'first':
			func = function (d) { return d[0]; };
			break;
		case 'last':
			func = function (d) { return d.slice(-1)[0]; };
			break;
		case 'nth':
			func = function (d) { return d[n]; };
			break;
		default:
			func = Math.max;
		}
		sortData.sort(function (a, b) { return sign * (func(a.data) - func(b.data)); });
		return sortData.map(function (d) { return d.key; });
	};
	/**
	 * calculates hard-coded scales for a particular chart type such that all graphs will have the same number of steps
	 * @param data - chartData
	 * @param type - string, name of chart type (e.g., 'orders')
	 * @param steps (optional, default 10) - integer, number of steps in scale
	 **/
	service.joinChartScales = function joinChartScales(data, type, steps) {
		var globalMin = 0;
		var globalMax = 0;
		var min, max;
		// TODO: do not hard code number of steps
		steps = steps || 4;
		eachKV(data, function (k, v) {
			if(!v[type] || !v[type].data || !v[type].data[0]) {
				// seeing communities with no data for this chart but that have data for other charts
				return;
			}
			min = Math.min.apply(null, v[type].data[0]);
			max = Math.max.apply(null, v[type].data[0]);
			if (min < globalMin) {
				globalMin = min;
			}
			if (max > globalMax) {
				globalMax = max;
			}
		});
		var scaleStepWidth = Math.max(Math.ceil((globalMax - globalMin) / steps), 1);
		if (isNaN(scaleStepWidth)) {
			log_error('could not set scale for globalMax', globalMax, 'globalMin', globalMin, 'steps', steps);
			return;
		} else {
			console.log('scale start', globalMin, 'scale end', globalMax, 'steps', steps, 'scale step width', scaleStepWidth);
		}
		eachKV(data, function (k, v) {
			if(!v[type]) {
				return;
			}
			if (!v[type].options) {
				v[type].options = {};
			}
			v[type].options.scaleOverride = true;
			v[type].options.scaleStartValue = globalMin;
			v[type].options.scaleStepWidth = scaleStepWidth;
			v[type].options.scaleSteps = steps;
		});
	};
	/**
	 * resets changes from joinChartScales
	 **/
	service.resetScales = function resetScales(data, type) {
		eachKV(data, function (k, v) {
			if (!v[type]) {
				return;
			}
			if (v[type].options) {
				delete(v[type].options.scaleOverride);
				delete(v[type].options.scaleStartValue);
				delete(v[type].options.scaleStepWidth);
				delete(v[type].options.scaleSteps);
			}
		});
	};
	function rstrip(text, ch) {
		var re = new RegExp('[' + ch + ']+$');
		return text.replace(re, '');
	}
	/**
	 * We want to be able to have both individual objects and readable URLs, so
	 * for now we convert an array of objects into the format
	 * 'k1:v1,k2:v2;k1:v1,k3:v3', with ':' indicating key-value pair, ','
	 * demarcating individual key/values and ';' marking end of a serialized
	 * object
	 **/
	function objsToString(objs, keyMap) {
		keyMap = keyMap || {};
		var str = '';
		var serializeKV = function (k, v) {
				var key = k in keyMap ? keyMap[k] : k;
				if (key && v !== undefined) {
					str = str + k + ':' + v + ',';
				}
			};
		for (var i = 0; i < objs.length; i++) {
			if (str) {
				str = str + ';';
			}
			eachKV(objs[i], serializeKV);
		}
		return str;
	}
	function objsFromString(str, keyMap) {
		keyMap = keyMap || {};
		var groups = rstrip(str, ';').split(/;/g);
		var out = [];
		var props, obj, pair, key;
		for (var i = 0; i < groups.length; i++) {
			props = rstrip(groups[i], ',').split(/,/g);
			obj = {};
			for (var j = 0; j < props.length; j++) {
				pair = rstrip(props[j], ':').split(/:/g);
				key = pair[0] in keyMap ? keyMap[pair[0]] : pair[0];
				if (key) {
					obj[key] = pair[1];
				} else {
					console.warn('not keeping key (keyMap was falsey) ', key);
				}
			}
			out.push(obj);
		}
		return out;
	}
	// want to use shorter names in serialization to keep URLs shorter
	var serializeKeyMap = {
		'type': 't',
		'orderMethod': 'om',
		'orderDirection': 'od',
		'uniformScale': 'us'
	};
	var deserializeKeyMap = {};
	eachKV(serializeKeyMap, function (k, v) { deserializeKeyMap[v] = k; });
	service.serializeChartOptions = function serializeChartOptions(chartOptions) {
		return objsToString(chartOptions, serializeKeyMap);
	};
	service.deserializeChartOptions = function deserializeChartOptions(optStr) {
		var deserialized = objsFromString(optStr, deserializeKeyMap);
		deserialized.forEach(function (d) {
			// force 'scale' back to bool
			if ('uniformScale' in d) {
				d.uniformScale = d.uniformScale === 'true';
			}
		});
		return deserialized;
	};
	/**
	 * grabs chart data from backend for a single chart type
	 * @param {string} chartType - the type of chart to request (e.g., 'users')
	 * @param {chartData} chartData - standard chartData-style object (note that
	 *   on reset, you should replace chartData so that this function's callback
	 *   doesn't overwrite new data with old data)
	 * @param {object} settings - the current chart settings in play
	 * @returns {promise} $q-style promise
	 **/
	service.getChartData = function (chartType, chartData, settings) {
		var deferred = $q.defer();
		console.log('chartType: ', chartType);
		var url = App.service + 'metrics/?type=' + chartType;
		['period', 'start', 'end'].forEach(function (k) { if (settings[k]) { url = url + '&' + k + '=' + settings[k]; } });
		$http.get(url).success(function (data) {
			console.log('successful getting data from url: ' + url);
			for (var key in data) {
				if (!chartData[key]) {
					chartData[key] = {};
				}
				chartData[key][chartType] = data[key];
			}
			deferred.resolve(data);
		}).error(function (err) {
			console.error('COULD NOT GET DATA from URL: ' + url, err);
			deferred.reject(err);
		});
		return deferred.promise;
	};
	return service;
});
