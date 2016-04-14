NGApp.factory('MarketingMaterialsService', function($rootScope, $resource, $routeParams) {

	var service = {};

	var marketing = $resource( App.service + 'marketing/materials/refil', {}, {
		'load' : {
			url: App.service + 'marketing/materials/refil',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'marketing/materials/refil/save',
			method: 'POST',
			params : {}
		}
	});

	service.load = function(callback) {
		marketing.load({}, function(data) {
			callback(data);
		});
	}

	service.save = function(callback) {
		marketing.save({}, function(data) {
			callback(data);
		});
	}

	return service;

});