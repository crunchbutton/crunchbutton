NGApp.factory( 'DriverService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'Driver'
	var drivers = $resource( App.service + 'driver/:action', { action: '@action' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'all' }, isArray: true },
				'paid' : { 'method': 'GET', params : { 'action' : 'paid' }, isArray: true },
				'summary' : { 'method': 'GET', params : { 'action' : 'summary' } }
			}
		);

	service.paid = function( callback ){
		drivers.paid( function( data ){
			callback( data );
		} );
	}

	service.summary = function( callback ){
		drivers.summary( function( data ){
			callback( data );
		} );
	}

	service.listSimple = function( callback ){
		drivers.listSimple( function( data ){
			callback( data );
		} );
	}

	return service;
} );