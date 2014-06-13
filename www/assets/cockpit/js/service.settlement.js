NGApp.factory( 'SettlementService', function( $resource ) {

	var service = { restaurants : {}, drivers : {} };
	var settlement = { restaurants : {}, drivers : {} };

	service.pay_type_options = [ { 'name': 'All', 'value' : 'all' }, { 'name': 'Check', 'value' : 'check' }, { 'name': 'Deposit', 'value' : 'deposit' } ];
	service.sort_options = [ { 'name': 'Last Payment', 'value' : 'last_payment' }, { 'name': 'Alphabetical', 'value' : 'alphabetical' } ];

	settlement.restaurants = $resource( App.service + 'settlement/restaurants/:action/', { action: '@action' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
		'restaurant' : { 'method': 'POST', params : { action: 'restaurant' } },
		'pay_if_refunded' : { 'method': 'POST', params : { action: 'pay-if-refunded' } }
	}	);

	settlement.drivers = $resource( App.service + 'settlement/drivers/:action/', { action: '@action' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } }
	}	);

	service.restaurants.begin = function( params, callback ){
		settlement.restaurants.begin( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.pay_if_refunded = function( params, callback ){
		settlement.restaurants.pay_if_refunded( params, function( json ){
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

	service.drivers.range = function( callback ){
		settlement.drivers.range( function( json ){
			callback( json );
		} );
	}

	return service;
} );