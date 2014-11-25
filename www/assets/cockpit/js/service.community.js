NGApp.factory( 'CommunityService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	// Create a private resource 'community'
	var communities = $resource( App.service + 'community/:action', { action: '@action' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
			}	
		);

	var community = ResourceFactory.createResource(App.service + 'communities/:id_community', { id_community: '@id_community'}, {
		'load' : {
			url: App.service + 'community/:id_community',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
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

	service.listSimple = function( callback ){
		communities.listSimple( function( data ){ 
			callback( data ); 
		} );
	}

	return service;
} );