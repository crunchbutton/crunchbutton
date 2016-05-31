NGApp.factory( 'DriverCommunityService', function( $resource, $routeParams ) {

	var service = {};

	var drivers = $resource( App.service + 'driver/community/:action/', { action: '@action' }, {
				'status' : { 'method': 'GET', params : { 'action' : 'status' }, isArray: true },
				'open' : { 'method': 'POST', params : { 'action' : 'open' }, isArray: true },
				'close' : { 'method': 'POST', params : { 'action' : 'close' }, isArray: true },
			}
		);

	service.status = function( callback ){
		drivers.status( function( data ){
			callback( data );
		} );
	}

	service.open = function( params, callback ){
		drivers.open( params, function( data ){
			callback( data );
		} );
	}

	service.close = function( params, callback ){
		drivers.close( params, function( data ){
			callback( data );
		} );
	}

	return service;
} );