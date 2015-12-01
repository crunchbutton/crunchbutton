NGApp.factory( 'ChainService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var community = ResourceFactory.createResource(App.service + 'chains/:id_community/:action', { id_community: '@id_community', action: '@action' }, {
		'load' : {
			url: App.service + 'community/:id_community',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'community/:id_community/:action',
			method: 'POST',
			params : { 'action' : 'save' }
		}
	});

	service.list = function(params, callback) {
		community.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_community, callback) {
		community.load({id_community: id_community},  function(data) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		community.save(params,  function(data) {
			callback(data);
		});
	}

	return service;
} );