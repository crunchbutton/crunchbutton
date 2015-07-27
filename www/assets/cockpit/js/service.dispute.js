NGApp.factory('DisputeService', function(ResourceFactory, $rootScope, $http) {

	var service = {};

	var dispute = ResourceFactory.createResource(App.service + '/stripe/dipute/:id_stripe_dispute', { id_stripe_dispute: '@id_stripe_dispute'}, {
		'load' : {
			url: App.service + 'stripe/dispute/:id_stripe_dispute',
			method: 'GET',
			params : {}
		}
	});

	service.get = function( id_stripe_dispute, callback) {
		dispute.load({id_stripe_dispute: id_stripe_dispute}, function(data) {
			callback(data);
		});
	}

	return service;

});
