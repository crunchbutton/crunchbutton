NGApp.factory( 'DriverService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'Driver'
	var drivers = $resource( App.service + 'driver/:action/:id_admin', { action: '@action', id_admin: '@id_admin' }, {
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

	service.summary = function( id_admin, callback ){
		drivers.summary( { id_admin: id_admin }, function( data ){
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