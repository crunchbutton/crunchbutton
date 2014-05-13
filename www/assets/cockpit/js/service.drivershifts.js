NGApp.factory( 'DriverShiftsService', function( $rootScope, $resource ) {
	
	var service = {};

	// Create a private resource 'shifts'
	var shifts = $resource( App.service + 'driver/shifts', {}, {}	);

	service.list = function( callback ){
		shifts.query( {}, function( data ){ callback( data ); } );
	}

	return service;
} );