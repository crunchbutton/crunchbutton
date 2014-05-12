NGApp.factory( 'CommunityService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'community'
	var communities = $resource( App.service + 'community/:action', { action: '@action' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
			}	
		);

	service.listSimple = function( callback ){
		communities.listSimple( function( data ){ 
			callback( data ); 
		} );
	}

	return service;
} );