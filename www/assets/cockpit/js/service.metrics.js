
NGApp.factory('MetricsService', function($resource) {

	var service = {};

	var metrics = $resource( App.service + 'metrics/:id_metrics', { id_metrics: '@id_metrics'}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});

	service.get = function(params, callback) {
		metrics.load(params, function(data) {
			callback(data);
		});
	}

	return service;
});
