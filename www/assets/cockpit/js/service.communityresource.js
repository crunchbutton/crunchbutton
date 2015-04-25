NGApp.factory( 'CommunityResourceService', function( $rootScope, $resource, $routeParams ) {

	// Create a private resource 'drivers'
	var resource = $resource( App.service + 'community/resource/:action/:id_resource', { id_admin: '@id_admin', action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'get' : { 'method': 'GET', params : { } },
			}
		);

	var service = {}

	service.get = function( id_community_resource, callback ){
		resource.get( { 'id_resource': id_community_resource }, function( json ){
			callback( json );
		} );
	}

	service.save = function( _resource, callback ){
		resource.save( _resource, function( json ){
			callback( json );
		} );
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	return service;
} );