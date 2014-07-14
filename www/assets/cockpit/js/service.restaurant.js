NGApp.factory( 'RestaurantService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'restaurant'
	var restaurants = $resource( App.service + 'restaurant/:action', { action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
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

	service.paid_list = function( callback ){
		restaurants.paid_list( function( data ){
			callback( data );
		} );
	}

	return service;
} );

NGApp.factory( 'RestaurantOrderPlacementService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var orders = $resource( App.service + 'order/:action/:id_restaurant', { action: '@action', id_restaurant: '@id_restaurant' }, {
				'process' : { 'method': 'POST' },
				'get' : { 'method': 'GET' },
				'list' : { 'method': 'GET', params: { 'action' : 'restaurant-list-last' }, isArray: true },
			}
		);

	var restaurant = $resource( App.service + 'restaurant/orderplacement/:action/:id_restaurant', { action: '@action', id_restaurant: '@id_restaurant' }, {
				'get' : { 'method': 'GET' },
				'status' : { 'method': 'GET', params: { 'action': 'status' } },
				'all' : { 'method': 'GET', params: { 'action' : 'all' }, isArray: true },
			}
		);

	service.get = function( callback ){
		orders.get( { 'action': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.list = function( id_restaurant, callback ){
		orders.list( { id_restaurant: id_restaurant }, function( data ){
			callback( data );
		} );
	}

	service.restaurant = {
		get : function( id_restaurant, callback ){
			restaurant.get( { id_restaurant: id_restaurant }, function( data ){
				callback( data );
			} );
		},
		status : function( id_restaurant, callback ){
			restaurant.status( { id_restaurant: id_restaurant }, function( data ){
				callback( data );
			} );
		},
		all : function ( callback ){
			restaurant.all( function( data ){
				callback( data );
			} );
		}
	}

	service.calcTotal = function( order, restaurant ){

			var _fee = function( total ){
				if ( restaurant.fee_customer ) {
					return App.ceil( total * ( parseFloat( restaurant.fee_customer ) / 100 ) );
				}
				return 0;
			}

			var _tax = function( total ){
				return ( total * ( restaurant.tax / 100 ) );
			}

			var _markup = function( total ){
				if( restaurant.delivery_service_markup ){
					return App.ceil( ( total * ( restaurant.delivery_service_markup / 100 ) ) );
				}
				return 0;
			}

			var _delivery = function(){
				return App.ceil( parseFloat( restaurant.delivery_fee ) );
			}

			var _tip = function( total ){
				if( order.tip_type == 'percent' ){
					if( order.tip ){
						return App.ceil( ( total * ( order.tip / 100 ) ) );
					} else {
						return 0;
					}

				}
				return order.tip;
			}

			var total = order.subtotal + _markup( order.subtotal );
			var totalWithoutMarkup = order.subtotal;
			var delivery = _delivery();
			total += delivery;
			var fee = _fee( total );
			total += fee;
			if( parseInt( restaurant.delivery_service ) ==  0 ){
				totalWithoutMarkup += delivery;
			}
			total += _tax( totalWithoutMarkup );
			total += _tip( total );
			return App.ceil( total ).toFixed( 2 );
	}


	service.process = function( order, card, callback ){

		var process = function(){
			orders.process( order, function( data ){ callback( data ); } );
		}

		if( order.pay_type == 'card' ){
			App.tokenizeCard( { name: order.name, number: card.number, expiration_month: card.month, expiration_year: card.year, security_code: null },
												function( status ) {
													if ( !status.status ) {
														callback( { error: status.error } );
														return;
													}
													order.card = status;
													process();
												} );

		} else {
			process();
		}
	}

	service.tipPercents = function(){
		var tips = [];
		tips.push( { value: 0, label: '0%' } );
		tips.push( { value: 10, label: '10%' } );
		tips.push( { value: 15, label: '15%' } );
		tips.push( { value: 18, label: '18%' } );
		tips.push( { value: 20, label: '20%' } );
		tips.push( { value: 25, label: '25%' } );
		tips.push( { value: 30, label: '30%' } );
		return tips;
	}

	service.cardYears = function(){
		var years = [];
		years.push( { value: 0, label: 'Year' } );
		var date = new Date().getFullYear();
		for ( var x = date; x <= date + 20; x++ ) {
			years.push( { value: x.toString(), label: x.toString() } );
		}
		return years;
	}

	service.cardMonths = function(){
		var months = [];
		months.push( { value: 0, label: 'Month' } );
		for ( var x = 1; x <= 12; x++ ) {
			months.push( { value: x.toString(), label: x.toString() } );
		}
		return months;
	}

	return service;
} );