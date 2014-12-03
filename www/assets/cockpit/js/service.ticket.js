
NGApp.factory('TicketService', function($rootScope, ResourceFactory, $routeParams) {

	var service = {};

	var tickets = ResourceFactory.createResource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'load' : {
			url: App.service + 'ticket/:id_support',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'ticket/:id_support',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});
	
	// @todo: there is a bug when calling the same service using resourcefactory. it cancels it out
	var ticketshort = ResourceFactory.createResource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'query' : {
			method: 'GET',
			params : {}
		}
	});
	
	service.list = function(params, callback) {
		tickets.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}
	
	service.shortlist = function(params, callback) {
		ticketshort.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_support, callback) {
		tickets.load({id_support: id_support}, function(data) {
			callback(data);
		});
	}
	
	$rootScope.$on('tickets', function(e, data) {
		$rootScope.supportMessages = {
			count: data,
			time: new Date
		};
	});

	return service;

});
