NGApp.factory('CustomerService', function(ResourceFactory) {

	var service = {};

	var customer = ResourceFactory.createResource(App.service + 'customers/:id_user', { id_user: '@id_user'}, {
		'load': {
			url: App.service + 'customer/:id_user',
			method: 'GET',
			params: {}
		},
		'save': {
			url: App.service + 'customer/:id_user',
			method: 'POST',
			params: {}
		},
		'query': {
			method: 'GET',
			params: {}
		},
		'active_orders': {
			url: App.service + 'customer/:id_user/active-orders',
			method: 'GET',
			params: {},
			isArray: true
		}
	});

	service.list = function(params, callback) {
		customer.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.active_orders = function(id_user, callback) {
		customer.active_orders({id_user: id_user}, function(data) {
			callback(data);
		});
	}

	service.get = function(id_user, callback) {
		customer.load({id_user: id_user}, function(data) {
			callback(data);
		});
	}

	service.post = function(params, callback) {
		customer.save(params, function(data) {
			callback(data);
		});
	}

	return service;

});