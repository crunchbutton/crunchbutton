NGApp.factory( 'DriverCommunityService', function( $resource, $routeParams ) {

	var service = {};

	var drivers = $resource( App.service + 'driver/community/:action/', { action: '@action' }, {
				'status' : { 'method': 'GET', params : { 'action' : 'status' }, isArray: true },
				'open' : { 'method': 'POST', params : { 'action' : 'open' }, isArray: true },
				'openItNow' : { 'method': 'POST', params : { 'action' : 'open-it-now' }, isArray: true },
				'close' : { 'method': 'POST', params : { 'action' : 'close' }, isArray: true },
				'textMessage' : { 'method': 'POST', params : { 'action' : 'text-message' }},
			}
		);

	service.status = function( callback ){
		drivers.status( function( data ){
			callback( data );
		} );
	}

	service.textMessage = function( params, callback ){
		drivers.textMessage( params, function( data ){
			callback( data );
		} );
	}

	service.open = function( params, callback ){
		drivers.open( params, function( data ){
			callback( data );
		} );
	}

	service.openItNow = function( params, callback ){
		drivers.openItNow( params, function( data ){
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

NGApp.factory( 'DriverRestaurantsService', function( $resource, $routeParams ) {

	var service = {};

	var resturants = $resource( App.service + 'driver/restaurants/:action/', { action: '@action' }, {
				'status' : { 'method': 'GET', params : { 'action' : 'status' }, isArray: true }
			}
		);

	service.status = function( callback ){
		resturants.status( function( data ){
			callback( data );
		} );
	}

	var restaurant = $resource( App.service + 'restaurant/edit/:action/:permalink', { action: '@action', permalink: '@permalink' }, {
				'post' : { 'method': 'POST' },
			}
		);

	service.closeRestaurantForToday = function( permalink, callback ){
		var success = function(){
				var data = {permalink:permalink};
				data.action = 'close-for-today';
				restaurant.post( data, function( data ){
				callback( data );
			} );
		}
		App.confirm('Confirm close this restaurant for today?', 'Confirm?', success, function(){}, 'Yes,No', true);
	}

	service.forceReopenReopenForToday = function( permalink, callback ){
		var success = function(){
				var data = {permalink:permalink};
				data.action = 'force-reopen-for-today';
				restaurant.post( data, function( data ){
				callback( data );
			} );
		}
		App.confirm('Confirm reopen this restaurant?', 'Confirm?', success, function(){}, 'Yes,No', true);
	}
	return service;
} );