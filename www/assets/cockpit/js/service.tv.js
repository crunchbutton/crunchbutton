
NGApp.factory('TvService', function($resource) {

	var service = {};

	var tv = $resource( App.service + 'tv', {}, {
		'load' : {
			url: App.service + 'tv',
			method: 'GET',
			params : {}
		},
	});

	
	service.get = function(callback) {
		tv.load({}, function(data) {
			callback(data);
		});
	}
	return service;
});
