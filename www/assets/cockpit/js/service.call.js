
NGApp.factory('CallService', function($rootScope, $resource, $routeParams) {

	var service = {};

	var call = $resource( App.service + 'calls/:id_call', { id_support: '@id_call'}, {
		'load' : { 'method': 'GET', params : {} }
	});
	
	service.list = function(params, callback) {
		call.query(params, function(data){
			callback(data);
		});
	}

	service.get = function(id_call, callback) {
		call.load({id_call: id_call}, function(data) {
			callback(data);
		});
	}

	$rootScope.$on('calls', function(e, data) {
		$rootScope.calls = {
			count: data,
			time: new Date
		};
	});

	return service;

});

