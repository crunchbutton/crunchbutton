NGApp.factory( 'ConfigService', function( $resource ) {

	var service = {};

	var settings = $resource( App.service + 'config/:action', { action: '@action' }, {
				'processor' : { 'method': 'GET', params : { 'action' : 'processor' } },
			}
		);

	service.processor = function( callback ){
		settings.processor( function( data ){
			callback( data );
		} );
	}

	return service;
} );
