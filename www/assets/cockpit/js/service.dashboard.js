NGApp.factory('DashboardService', function(ResourceFactory, $routeParams, $resource) {

	var service = {};

	var dashboard = ResourceFactory.createResource(App.service + 'dashboard/:id_page', { id_admin: '@id_page'}, {
		'communities_with_shift' : {
			url: App.service + 'dashboard/beta/communities-with-shift',
			method: 'GET',
			params : {},
			isArray: true
		},
		'communities' : {
			url: App.service + 'dashboard/beta/communities',
			method: 'POST',
			params : {},
			isArray: true
		},

		'load' : {
			method: 'GET',
			params : {}
		}
	});

	service.communities_with_shift = function(callback) {
		dashboard.communities_with_shift({}, function(data) {
			callback(data);
		});
	}

	service.communities = function(communities, callback){
		dashboard.communities({communities:communities}, function(data) {
			callback(data);
		});
	}


	service.get = function(id_page, callback) {
		dashboard.load({}, function(data) {
			callback(data);
		});
	}

	return service;
});
