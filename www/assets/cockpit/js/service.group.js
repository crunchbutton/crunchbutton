NGApp.factory( 'GroupService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var group = ResourceFactory.createResource(App.service + 'group/:id_group/:action', { id_group: '@id_group', action: '@action' }, {
		'load' : {
			url: App.service + 'group/load/:id_group',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'group/save/:id_group',
			method: 'POST',
			params : {}
		}
	});

	service.list = function(params, callback) {
		group.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		group.save( params,  function(data) {
			callback(data);
		});
	}

	service.get = function(id_group, callback) {
		group.load({id_group: id_group},  function(data) {
			callback(data);
		});
	}

	return service;

} );