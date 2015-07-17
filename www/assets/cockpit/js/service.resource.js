NGApp.factory( 'ResourceService', function( $rootScope, $resource, ResourceFactory ) {

	// Create a private resource 'drivers'
	var resource = $resource( App.service + 'resource/:action/:id_resource', { id_admin: '@id_admin', action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'get' : { 'method': 'GET', params : { } },
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
				'driver' : { 'method': 'GET', params : { 'action' : 'driver' }, isArray: true }
			}
		);


	var resources = ResourceFactory.createResource(App.service + 'resource/list', {}, {
		'query' : {
			method: 'GET',
			params : {}
		},
	});

	var service = {}

	service.get = function( id_resource, callback ){
		resource.get( { 'id_resource': id_resource }, function( json ){
			callback( json );
		} );
	}

	service.driver = function( callback ){
		resource.driver( { }, function( json ){
			callback( json );
		} );
	}

	service.save = function( _resource, callback ){
		resource.save( _resource, function( json ){
			callback( json );
		} );
	}

	service.list = function(params, callback) {
		resources.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	return service;
} );
