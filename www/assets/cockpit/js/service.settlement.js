NGApp.factory( 'SettlementService', function( $resource, $http, $routeParams ) {

	var service = { restaurants : {}, drivers : {} };
	var settlement = { restaurants : {}, drivers : {} };

	service.pay_type_options = [ { 'name': 'All', 'value' : 'all' }, { 'name': 'Check', 'value' : 'check' }, { 'name': 'Deposit', 'value' : 'deposit' } ];
	service.sort_options = [ { 'name': 'Last Payment', 'value' : 'last_payment' }, { 'name': 'Alphabetical', 'value' : 'alphabetical' } ];

	settlement.restaurants = $resource( App.service + 'settlement/restaurants/:action/:id_payment_schedule/:page/', { action: '@action', id_payment_schedule: '@id_payment_schedule' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
		'restaurant' : { 'method': 'POST', params : { action: 'restaurant' } },
		'pay_if_refunded' : { 'method': 'POST', params : { action: 'pay-if-refunded' } },
		'payment' : { 'method': 'POST', params : { action: 'payment' } },
		'do_payment' : { 'method': 'POST', params : { action: 'do-payment' } },
		'scheduled_payment' : { 'method': 'POST', params : { action: 'scheduled' } },
		'send_summary' : { 'method': 'POST', params : { action: 'send-summary' } },
		'payments' : { 'method': 'POST', params : { action: 'payments' } },
		'reimburse_cash_order' : { 'method': 'POST', params : { action: 'reimburse-cash-order' } },
		'do_not_pay_restaurant' : { 'method': 'POST', params : { action: 'do-not-pay-restaurant' } },
		'schedule' : { 'method': 'POST', params : { action: 'schedule' } },
		'scheduled' : { 'method': 'POST', params : { action: 'scheduled' } }
	}	);

	settlement.drivers = $resource( App.service + 'settlement/drivers/:action/', { action: '@action' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'do_not_pay_driver' : { 'method': 'POST', params : { action: 'do-not-pay-driver' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } }
	}	);

	service.restaurants.begin = function( params, callback ){
		settlement.restaurants.begin( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.paid_list = function( callback ){
		settlement.restaurants.paid_list( function( json ){
			callback( json );
		} );
	}

	service.restaurants.schedule = function( params, callback ){
		settlement.restaurants.schedule( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.scheduled = function( params, callback ){
		settlement.restaurants.scheduled( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.scheduled_payment = function( callback ){
		settlement.restaurants.scheduled_payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.payment = function( callback ){
		settlement.restaurants.payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.do_payment = function( id_payment_schedule, callback ){
		settlement.restaurants.do_payment( { 'id_payment_schedule' : id_payment_schedule  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.send_summary = function( callback ){
		settlement.restaurants.send_summary( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.view_summary = function( callback ){
		var url = App.service + 'settlement/restaurants/view-summary/' + $routeParams.id;
		$http( { method: 'POST', url: url } ).
    success( function( data, status, headers, config ) {
    	callback( data );
    }).
    error( function(data, status, headers, config ) {
    	callback( false );
    } );
	}

	service.restaurants.payments = function( params, callback ){
		settlement.restaurants.payments( { 'page' : params.page, 'id_restaurant' : params.id_restaurant }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.pay_if_refunded = function( params, callback ){
		settlement.restaurants.pay_if_refunded( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.reimburse_cash_order = function( params, callback ){
		settlement.restaurants.reimburse_cash_order( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.do_not_pay_restaurant = function( params, callback ){
		settlement.restaurants.do_not_pay_restaurant( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.range = function( callback ){
		settlement.restaurants.range( function( json ){
			callback( json );
		} );
	}

	service.drivers.begin = function( params, callback ){
		settlement.drivers.begin( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.do_not_pay_driver = function( params, callback ){
		settlement.drivers.do_not_pay_driver( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.range = function( callback ){
		settlement.drivers.range( function( json ){
			callback( json );
		} );
	}

	return service;
} );