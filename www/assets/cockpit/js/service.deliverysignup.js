NGApp.factory( 'DeliverySignUpService', function( $resource ) {
	var service = {};

	var delivery = $resource( App.service + 'delivery/signup/:action/', { action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'change_status' : { 'method': 'POST', params : { action: 'change-status' } },
				'delivey_query' : { 'method': 'GET', params : { action: 'list' } },
			}
		);

	service.save = function( params, callback ){
		delivery.save( params, function( json ){
			callback( json );
		} );
	}

	service.change_status = function( params, callback ){
		delivery.change_status( params, function( json ){
			callback( json );
		} );
	}




	service.list = function(params, callback) {
		delivery.delivey_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	return service;
} );