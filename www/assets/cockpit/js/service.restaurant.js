NGApp.factory('RestaurantService', function( $rootScope, $resource, $routeParams, ResourceFactory) {

	var service = {};

	// this is the stuff for the restaurant order placement
	// and some for restaurant settlement. not sure what is used
	var restaurants = $resource( App.service + 'restaurants/:action', { action: '@action' }, {
			'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
			'no_payment_method' : { 'method': 'GET', params : { 'action' : 'no-payment-method' }, isArray: true },
			'paid_list' : { 'method': 'GET', params : { 'action' : 'paid-list' }, isArray: true },
			'order_placement' : { 'method': 'GET', params : { 'action' : 'order-placement' } },
			'eta' : { 'method': 'GET', params : { 'action' : 'eta' }, isArray: true },
			'weight_adjustment' : { 'method': 'GET', params : { 'action' : 'weight-adjustment' }, isArray: true },
			'save_weight' : { 'method': 'POST', params : { 'action' : 'save-weight' } },
			'save_notes_to_driver' : { 'method': 'POST', params : { 'action' : 'save-notes-to-driver' } },
		}
	);

	var restaurant = ResourceFactory.createResource( App.service + 'restaurants/:id_restaurant', { id_restaurant: '@id_restaurant'}, {
		'load' : {
			url: App.service + 'restaurant/:id_restaurant',
			method: 'GET',
			params : {}
		},
		'restaurant_query' : {
			method: 'GET',
			params : {}
		},
	});

	var payinfo = $resource( App.service + 'restaurant/payinfo/:action/:id_restaurant', { action: '@action' }, {
			'payment_method' : { 'method': 'GET', params : { 'action' : 'payment-method' } },
			'payment_method_save' : { 'method': 'POST', params : { 'action' : 'payment-method' } },
			'balanced_to_sprite' : { 'method': 'POST', params : { 'action' : 'balanced-to-stripe' } },
			'balanced_to_sprite_account' : { 'method': 'POST', params : { 'action' : 'balanced-to-stripe' } },
			'stripe' : { 'method': 'POST', params : { 'action' : 'stripe' } },
		}
	);

	service.list = function(params, callback) {
		restaurant.restaurant_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.weight_adjustment = function( params, callback ){
		restaurants.weight_adjustment( params,  function( data ){
			callback( data );
		} );
	}

	service.save_weight = function( params, callback ){
		restaurants.save_weight( params,  function( data ){
			callback( data );
		} );
	}

	service.save_notes_to_driver = function( params, callback ){
		restaurants.save_notes_to_driver( params,  function( data ){
			callback( data );
		} );
	}


	service.get = function(id_restaurant, callback) {
		restaurant.load({id_restaurant: id_restaurant}, function(data) {
			callback(data);
		});
	}

	service.shortlist = function( callback ){
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

	service.payment_method = function( id_restaurant, callback ){
		payinfo.payment_method( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.payment_method_save = function( params, callback ){
		payinfo.payment_method_save( params,  function( data ){
			callback( data );
		} );
	}

	service.balanced_to_sprite = function( id_restaurant, callback ){
		payinfo.balanced_to_sprite( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.balanced_to_sprite_account = function( params, callback ){
		payinfo.balanced_to_sprite_account( params,  function( data ){
			callback( data );
		} );
	}


	service.stripe = function( params, callback ){
		payinfo.stripe( params,  function( data ){
			callback( data );
		} );
	}



	service.paid_list = function( callback ){
		restaurants.paid_list( function( data ){
			callback( data );
		} );
	}

	service.eta = function( callback ){
		restaurants.eta( function( data ){
			callback( data );
		} );
	}


	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	service.summaryMethod = function(){
		var methods = [];
		methods.push( { value: 'fax', label: 'Fax' } );
		methods.push( { value: 'email', label: 'Email' } );
		methods.push( { value: 'no summary', label: 'Does Not Need Summary' } );
		return methods;
	}

	service.paymentMethod = function(){
		var methods = [];
		methods.push( { value: 'check', label: 'Check' } );
		methods.push( { value: 'deposit', label: 'Deposit' } );
		methods.push( { value: 'no payment', label: 'Does Not Need Payment' } );
		return methods;
	}

	service.accountType = function(){
		var methods = [];
		methods.push( { value: 'individual', label: 'Individual' } );
		methods.push( { value: 'corporation', label: 'Corporation' } );
		return methods;
	}

	return service;
} );

NGApp.factory( 'RestaurantOrderPlacementService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var orders = $resource( App.service + 'order/:action/:id_restaurant', { action: '@action', id_restaurant: '@id_restaurant' }, {
				'process' : { 'method': 'POST' },
				'get' : { 'method': 'GET' },
				'list' : { 'method': 'GET', params: { 'action' : 'restaurant-list-last' } },
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
			if(restaurant.delivery_service){
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
		years.push( { value: '', label: 'Year' } );
		var date = new Date().getFullYear();
		for ( var x = date; x <= date + 20; x++ ) {
			years.push( { value: x.toString(), label: x.toString() } );
		}
		return years;
	}

	service.cardMonths = function(){
		var months = [];
		months.push( { value: '', label: 'Month' } );
		for ( var x = 1; x <= 12; x++ ) {
			months.push( { value: x.toString(), label: x.toString() } );
		}
		return months;
	}

	return service;
} );