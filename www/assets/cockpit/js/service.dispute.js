NGApp.factory('DisputeService', function(ResourceFactory, $rootScope, $http) {

	var service = {};

	var dispute = ResourceFactory.createResource(App.service + '/stripe/dipute/:id_stripe_dispute', { id_stripe_dispute: '@id_stripe_dispute'}, {
		'load' : {
			url: App.service + 'stripe/dispute/:id_stripe_dispute',
			method: 'GET',
			params : {}
		},
		'dispute_query' : {
			url: App.service + 'stripe/disputes',
			method: 'GET',
			params : {}
		},
		'evidence' : {
			url: App.service + 'stripe/dispute/evidence/:id_stripe_dispute_evidence',
			method: 'GET',
			params : {}
		},
		'last_evidence' : {
			url: App.service + 'stripe/dispute/:id_stripe_dispute/last-evidence',
			method: 'GET',
			params : {}
		},
		'evidence_save' : {
			url: App.service + 'stripe/dispute/:id_stripe_dispute/last-evidence',
			method: 'POST',
			params : {}
		}
	});

	service.list = function(params, callback) {
		dispute.dispute_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function( id_stripe_dispute, callback) {
		dispute.load({id_stripe_dispute: id_stripe_dispute}, function(data) {
			callback(data);
		});
	}

	service.evidence = function( id_stripe_dispute_evidence, callback) {
		dispute.evidence({id_stripe_dispute_evidence: id_stripe_dispute_evidence}, function(data) {
			callback(data);
		});
	}

	service.last_evidence = function( id_stripe_dispute, callback) {
		dispute.last_evidence({id_stripe_dispute: id_stripe_dispute}, function(data) {
			callback(data);
		});
	}

	service.evidence_save = function( params, callback) {
		dispute.evidence_save( params, function(data) {
			callback(data);
		});
	}

	service.evidence_send_limit = 5;

	return service;

});
