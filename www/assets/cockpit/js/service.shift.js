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
		'loadShift' : { method: 'GET', params : { action: 'shift' } },
		'loadShiftLog' : { method: 'GET', params : { action: 'log' }, isArray: true },
		'loadShifts' : { method: 'POST', params : { action: 'shifts' } },
		'showHideShift' : { method: 'POST', params : { action: 'show-hide-shift' } },
		'assignDriver' : { method: 'POST', params : { action: 'assign-driver' } },
		'addShift' : { method: 'POST', params : { action: 'add-shift' } },
		'saveDriverNote' : { method: 'POST', params : { action: 'save-driver-note' } },
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

	service.loadShiftLog = function( params, callback) {
		shift.loadShiftLog( params, function( data ) {
			callback(data);
		});
	}

	service.saveDriverNote = function( params, callback) {
		shift.saveDriverNote( params, function( data ) {
			callback(data);
		});
	}

	service.showHideShift = function( params, callback) {
		shift.showHideShift( params, function( data ) {
			callback(data);
		});
	}

	service.assignDriver = function( params, callback) {
		shift.assignDriver( params, function( data ) {
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


