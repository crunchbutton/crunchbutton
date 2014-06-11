NGApp.factory( 'SettlementService', function( $rootScope, $resource ) {

	var service = {};

	var settlement = $resource( App.service + 'settlement/:action/', { action: '@action' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
	}	);

	service.begin = function( params, callback ){
		settlement.begin( params, function( json ){
			callback( json );
		} );
	}

	service.range = function( callback ){
		settlement.range( function( json ){
			callback( json );
		} );
	}

	return service;
} );