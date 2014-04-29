NGApp.factory( 'DriverShiftsService', function( $rootScope, $resource ) {
	
	var service = {};

	// Create a private resource 'shifts'
	var shifts = $resource( App.service + 'drivershifts', {}, {}	);

	service.list = function( callback ){
		shifts.query( {}, function( data ){ callback( data ); } );
	}

	return service;
} );