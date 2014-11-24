
NGApp.factory('OrderService', function(ResourceFactory) {

	var service = {};

	var order = ResourceFactory.createResource(App.service + 'orders/:id_order', { id_order: '@id_order'}, {
		'load' : {
			url: App.service + 'order/:id_order',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'order/:id_order',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		order.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_order, callback) {
		order.load({id_order: id_order}, function(data) {
			callback(data);
		});
	}
	
	service.post = function(params, callback) {
		order.save(params, function(data) {
			callback(data);
		});
	}

	return service;

});