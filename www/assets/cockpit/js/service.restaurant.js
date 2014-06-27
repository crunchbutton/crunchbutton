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

	service.calc = function(){
		var elements = {};
		var total = this.subtotal();
		var totalWithoutMarkup = this.subtotalWithoutMarkup();
		var feeTotal = total;
		elements['subtotal'] = this.subtotal();
		elements['subtotalWithoutMarkup'] = this.subtotalWithoutMarkup();
		elements['delivery'] = this._breackDownDelivery();
		feeTotal += elements['delivery'];
		elements['fee'] = this._breackDownFee( feeTotal );
		feeTotal += elements['fee'];
		/* 	- taxes should be calculated using the price without markup
				- if restaurant uses 3rd party delivery service remove the delivery_fee
				- see #2236 and #2248 */

		// Check if the restaurant uses 3rd party delivery if it not, add the delivery fee
		if( parseInt( service.restaurant.delivery_service ) ==  0 ){
			totalWithoutMarkup += elements[ 'delivery' ];
		}
		// Caculate the tax using the total without the marked up prices
		elements['taxes'] = this._breackDownTaxes( totalWithoutMarkup );
		// The tip will use as base the total price (with the markup)
		elements['tip'] = this._breakdownTip(total);

		return elements;
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