NGApp.factory( 'DeliverySignUpService', function( $resource ) {
	var service = {};
	var delivery = $resource( App.service + 'delivery/signup/:action/', { action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'onboarding' } },
			}
		);
	service.save = function( driver, callback ){
		delivery.save( driver, function( json ){
			callback( json );
		} );
	}
	return service;
} );