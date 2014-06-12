NGApp.factory( 'SettlementService', function( $resource ) {

	var service = { restaurants : {}, drivers : {} };
	var settlement = { restaurants : {}, drivers : {} };

	settlement.restaurants = $resource( App.service + 'settlement/restaurants/:action/', { action: '@action' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
	}	);

	service.restaurants.begin = function( params, callback ){
		settlement.restaurants.begin( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.range = function( callback ){
		settlement.restaurants.range( function( json ){
			callback( json );
		} );
	}

	return service;
} );