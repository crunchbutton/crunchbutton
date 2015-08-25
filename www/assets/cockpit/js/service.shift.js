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