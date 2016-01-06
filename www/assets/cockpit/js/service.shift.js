NGApp.factory('ShiftService', function(ResourceFactory, $routeParams, $resource) {

	var service = { checking: {} };

	var staff = ResourceFactory.createResource(App.service + 'shifts/checkin', {}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});

	service.checking.get = function( params , callback) {
		staff.load( params, function(data) {
			callback(data);
		});
	}

	return service;
});

NGApp.factory('ShiftScheduleService', function(ResourceFactory, $routeParams, $resource) {

	var service = { checking: {} };

	var shift = ResourceFactory.createResource(App.service + 'shifts/:action/:id_community_shift', { action: '@action', id_community_shift: '@id_community_shift' }, {
		'weekStart' : { method: 'GET', params : { action: 'week-start' } },
		'loadShift' : { method: 'GET', params : { action: 'load-shift' } },
		'loadShifts' : { method: 'POST', params : { action: 'load-shifts' } },
		'showHideShift' : { method: 'POST', params : { action: 'show-hide-shift' } },
		'addShift' : { method: 'POST', params : { action: 'add-shift' } },
	});

	service.weekStart = function( callback) {
		shift.weekStart( {}, function(data) {
			callback(data);
		});
	}

	service.loadShifts = function( params, callback) {
		shift.loadShifts( params, function( data ) {
			callback(data);
		});
	}

	service.loadShift = function( params, callback) {
		shift.loadShift( params, function( data ) {
			callback(data);
		});
	}

	service.showHideShift = function( params, callback) {
		shift.showHideShift( params, function( data ) {
			callback(data);
		});
	}

	service.addShift = function( params, callback) {
		shift.addShift( params, function( data ) {
			callback(data);
		});
	}

	return service;
});


