NGApp.factory('BlockedService', function(ResourceFactory) {

	var service = {};

	var block = ResourceFactory.createResource(App.service + 'blockeds/', {}, {
		'block' : {
			url: App.service + 'blocked',
			method: 'POST',
			params : {}
		},
		'config' : {
			url: App.service + 'blocked/config',
			method: 'GET',
			params : {}
		},
		'save_config' : {
			url: App.service + 'blocked/config',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.config = {
		load: function( callback ) {
			block.config( {}, function(data) {
				callback(data);
			});
		},
		save: function( params, callback) {
				block.save_config( params, function(data) {
					callback(data);
				});
			}
	}

	service.list = function(params, callback) {
		block.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_user, callback) {
		block.load({id_user: id_user}, function(data) {
			callback(data);
		});
	}

	service.user = function( id_user, callback) {
		block.block( { id_user: id_user } , function(data) {
			callback(data);
		});
	}

	service.phone = function( id_phone, callback) {
		block.block( { id_phone: id_phone } , function(data) {
			callback(data);
		});
	}

	service.phone_number = function( phone, callback) {
		block.block( { phone: phone } , function(data) {
			callback(data);
		});
	}

	return service;

});
