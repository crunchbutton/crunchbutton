
NGApp.factory('CustomerService', function($resource) {

	var service = {};

	var order = $resource(App.service + 'customers/:id_user', { id_user: '@id_user'}, {
		'load' : {
			url: App.service + 'customer/:id_user',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'customer/:id_user',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		order.query(params, function(data){
			callback(data);
		});
	}

	service.get = function(id_user, callback) {
		order.load({id_user: id_user}, function(data) {
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