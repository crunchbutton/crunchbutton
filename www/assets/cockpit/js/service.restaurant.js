NGApp.factory( 'RestaurantService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'restaurant'
	var restaurants = $resource( App.service + 'restaurant/:action', { action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
				'paid_list' : { 'method': 'GET', params : { 'action' : 'paid-list' }, isArray: true },
			}
		);

	service.list = function( callback ){
		restaurants.list( function( data ){
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