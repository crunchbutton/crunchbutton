NGApp.factory( 'ReportService', function( $resource, $http, $routeParams ) {

	var service = { };

	var report = $resource( App.service + 'report/:action/', { action: '@action' }, {
		'first_time_user_gift_codes_used_per_school_per_day' : { 'method': 'POST', params : { action: 'first-time-user-gift-codes-used-per-school-per-day' } },
	}	);


	service.first_time_user_gift_codes_used_per_school_per_day = function( params, callback ){
		report.first_time_user_gift_codes_used_per_school_per_day( params, function( data ){
			console.log('data',data);
			callback( data );
		} );
	}

	return service;

} );