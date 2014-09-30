
NGApp.factory('TicketService', function($rootScope, $resource, $routeParams) {

	var service = {};

	var tickets = $resource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'load' : { 'method': 'GET', params : {} },
		'get' : { 'method': 'GET', params : {} }
	});
	
	service.list = function(params, callback) {
		tickets.query(params, function(data){
			callback(data);
		});
	}

	service.get = function(id_support, callback) {
		tickets.load({id_support: id_support}, function(data) {
			callback(data);
		});
	}
	
	$rootScope.$watch('support-messages', function(newValue, oldValue, scope) {
		$rootScope.supportMessages = newValue;
	});

	return service;

});