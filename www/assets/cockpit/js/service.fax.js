
NGApp.factory('FaxService', function(ResourceFactory) {

	var service = {};

	var fax = ResourceFactory.createResource( App.service + 'fax', {}, {
		'save' : {
			url: App.service + 'fax',
			method: 'POST',
			params : {}
		}
	});

	service.save = function(params, callback) {
		fax.save(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	};

	return service;
});
