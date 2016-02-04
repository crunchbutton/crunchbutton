NGApp.factory('TicketService', function($rootScope, ResourceFactory, $routeParams) {

	var service = {};

	var tickets = ResourceFactory.createResource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'load' : {
			url: App.service + 'ticket/:id_support',
			method: 'GET',
			params : {}
		},
		'side_info' : {
			url: App.service + 'ticket/:id_support/side-info/:page',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'ticket/:id_support',
			method: 'POST',
			params : {}
		},
		'openClose' : {
			url: App.service + 'ticket/:id_support/open-close',
			method: 'POST',
			params : {}
		},
		'create' : {
			url: App.service + 'ticket/create',
			method: 'POST',
			params : {}
		},
		'message' : {
			url: App.service + 'ticket/:id_support/message',
			method: 'POST',
			params : {}
		},
		'tickets_query' : {
			method: 'GET',
			params : {}
		},
		'tickets_query_beta' : {
			url: App.service + 'tickets/beta/',
			method: 'GET',
			params : {}
		}

	});

	// @todo: there is a bug when calling the same service using resourcefactory. it cancels it out
	var ticketshort = ResourceFactory.createResource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'tickets_query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		tickets.tickets_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.list_beta = function(params, callback) {
		tickets.tickets_query_beta(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.side_info = function(params, callback) {
		tickets.side_info(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.shortlist = function(params, callback) {
		ticketshort.tickets_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.create = function(params, callback) {
		tickets.create( params, function(data) {
			callback(data);
		});
	}

	service.message = function(params, callback) {
		tickets.message( params, function(data) {
			callback(data);
		});
	}

	service.get = function(id_support, callback) {
		tickets.load({id_support: id_support}, function(data) {
			callback(data);
		});
	}

	service.openClose = function(id_support, callback) {
		tickets.openClose({id_support: id_support}, function(data) {
			callback(data);
			$rootScope.$broadcast( 'updateHeartbeat' );
		});
	}

	$rootScope.$on('tickets', function(e, data) {
		$rootScope.supportMessages = {
			count: data.tickets,
			timestamp: data.timestamp,
			time: new Date
		};
	});

	return service;

});
