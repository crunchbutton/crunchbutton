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

NGApp.factory( 'RestaurantOrderService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var orders = $resource( App.service + 'order/:action', { action: '@action' }, {
				'process' : { 'method': 'POST', params : { 'action' : '' } } }
		);

	service.calcTotal = function( order, restaurant ){

			var fee = function( total ){
				if ( restaurant.fee_customer ) {
					return App.ceil( total * ( parseFloat( restaurant.fee_customer ) / 100 ) );
				}
				return 0;
			}

			var tax = function( total ){
				return ( total * ( restaurant.tax / 100 ) );
			}

			var markup = function( total ){
				if( restaurant.delivery_service_markup ){
					return App.ceil( ( total * ( restaurant.delivery_service_markup / 100 ) ) );
				}
				return 0;
			}

			var delivery = function(){
				return App.ceil( parseFloat( restaurant.delivery_fee ) );
			}

			var tip = function( total ){
				// calc tip % of total or real value
				return order.tip;
			}

			var breakdown = {};
			var total = order.subtotal + markup();
			var totalWithoutMarkup = order.subtotal;
			var feeTotal = total;
			breakdown['subtotal'] = order.subtotal;
			breakdown['subtotalWithoutMarkup'] = totalWithoutMarkup;
			breakdown['delivery'] = delivery();
			feeTotal += breakdown['delivery'];
			breakdown['fee'] = fee( feeTotal );
			feeTotal += breakdown['fee'];

			if( parseInt( restaurant.delivery_service ) ==  0 ){
				totalWithoutMarkup += breakdown[ 'delivery' ];
			}

			breakdown['taxes'] = tax( totalWithoutMarkup );

			breakdown['tip'] = tip( total );

			total = breakdown.subtotal;
			feeTotal = total;
			feeTotal += breakdown.delivery;
			feeTotal += breakdown.fee;
			finalAmount = feeTotal + breakdown.taxes;
			finalAmount += tip( total );

			return App.ceil( finalAmount ).toFixed( 2 );
	}


	service.process = function( order, card, callback ){

		App.tokenizeCard( { name: order.name, number: card.number, expiration_month: card.month, expiration_year: card.year, security_code: null },
											function( status ) {
												if ( !status.status ) {
													callback( { error: status.error } );
													return;
												}
												order.card = status;
												orders.process( order, function( data ){
													callback( data );
												} );
											} );

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