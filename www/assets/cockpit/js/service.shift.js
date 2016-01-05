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

	var shift = ResourceFactory.createResource(App.service + 'shifts/:action', { action: '@action' }, {
		'weekStart' : { method: 'GET', params : { action: 'week-start' } },
		'loadShifts' : { method: 'POST', params : { action: 'load-shifts' } }
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

	return service;
});


