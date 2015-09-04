NGApp.factory( 'DeliverySignUpService', function( $resource ) {
	var service = {};

	var delivery = $resource( App.service + 'delivery/signup/:action/', { action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'restaurants' : { 'method': 'GET', params : { action: 'restaurants' } },
			}
		);

	service.save = function( params, callback ){
		delivery.save( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants = function(callback) {
		delivery.restaurants({}).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	return service;
} );