NGApp.factory( 'DriverService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'Driver'
	var drivers = $resource( App.service + 'driver/:action', { action: '@action' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'all' }, isArray: true },
			}
		);

	service.listSimple = function( callback ){
		drivers.listSimple( function( data ){
			callback( data );
		} );
	}

	return service;
} );