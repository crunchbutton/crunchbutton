
NGApp.factory('BlastService', function($rootScope, $resource, $routeParams) {

	var service = {};

	var blast = $resource( App.service + 'blasts/:id_blast', { id_blast: '@id_blast'}, {
		'load' : {
			url: App.service + 'blast/:id_blast',
			method: 'GET',
			params : {}
		},
		'cancel' : {
			url: App.service + 'blast/:id_blast',
			method: 'DELETE',
			params : {}
		},
		'save' : {
			url: App.service + 'blast/:id_blast',
			method: 'POST',
			params : {}
		},
		'sample' : {
			url: App.service + 'blast/sample',
			method: 'POST',
			params : {},
			isArray: true
		}
	});

	service.list = function(params, callback) {
		blast.query(params, function(data){
			callback(data);
		});
	}

	service.get = function(id_blast, callback) {
		blast.load({id_blast: id_blast}, function(data) {
			callback(data);
		});
	}
	
	service.post = function(params, callback) {
		blast.save(params, function(data) {
			callback(data);
		});
	}
	
	service.sample = function(data, callback) {
		blast.sample(data, function(data) {
			callback(data);
		});
	}

	service.cancel = function(id_blast, callback) {
		blast.cancel({id_blast: id_blast}, function(data) {
			if (typeof callback == 'function') {
				callback(data);
			}
		});
	}

	return service;

});