NGApp.factory('DashboardService', function(ResourceFactory, $routeParams, $resource) {

	var service = {};

	var dashboard = ResourceFactory.createResource(App.service + 'dashboard/:id_page', { id_admin: '@id_page'}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});


	service.get = function(id_page, callback) {
		dashboard.load({}, function(data) {
			callback(data);
		});
	}

	return service;
});
