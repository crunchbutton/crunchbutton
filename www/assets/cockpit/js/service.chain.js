NGApp.factory( 'ChainService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var chain = ResourceFactory.createResource(App.service + 'chains/:id_chain/:action', { id_chain: '@id_chain', action: '@action' }, {
		'load' : {
			url: App.service + 'chain/:id_chain',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'chain/:id_chain/:action',
			method: 'POST',
			params : { 'action' : 'save' }
		}
	});

	service.list = function(params, callback) {
		chain.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_chain, callback) {
		chain.load({id_chain: id_chain},  function(data) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		chain.save(params,  function(data) {
			callback(data);
		});
	}

	return service;
} );