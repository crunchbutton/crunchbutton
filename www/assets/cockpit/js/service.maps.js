NGApp.factory( 'RestaurantService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'restaurant'
	var restaurants = $resource( App.service + 'restaurant/:action', { action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
				'no_payment_method' : { 'method': 'GET', params : { 'action' : 'no-payment-method' }, isArray: true },
				'paid_list' : { 'method': 'GET', params : { 'action' : 'paid-list' }, isArray: true },
				'order_placement' : { 'method': 'GET', params : { 'action' : 'order-placement' } },
			}
		);

	service.list = function( callback ){
		restaurants.list( function( data ){
			callback( data );
		} );
	}

	service.order_placement = function( callback ){
		restaurants.order_placement( function( data ){
			callback( data );
		} );
	}

	service.no_payment_method = function( callback ){
		restaurants.no_payment_method( function( data ){
			callback( data );
		} );
	}

	service.paid_list = function( callback ){
		restaurants.paid_list( function( data ){
			callback( data );
		} );
	}

	return service;
} );