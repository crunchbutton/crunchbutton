NGApp.factory( 'PexCardService', function( $resource, $http, $routeParams ) {

	var service = {};

	var pexcard = $resource( App.service + 'pexcard/:action/', { action: '@action' }, {
		'pex_id' : { 'method': 'POST', params : { action: 'pex-id' } }
	}	);

	service.pex_id = function( id, callback ){
		pexcard.pex_id( { 'id': id }, function( data ){
			callback( data );
		} );
	}
	return service;

} );