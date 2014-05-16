NGApp.factory( 'PreOnboardingService', function( $resource ) {
	var service = {};
	var drivers = $resource( App.service + 'driver/:action/', { action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'onboarding' } },
			}	
		);
	service.save = function( driver, callback ){
		drivers.save( driver, function( json ){
			callback( json );
		} );
	}
	return service;
} );