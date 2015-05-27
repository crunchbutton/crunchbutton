NGApp.factory( 'CreditService', function( $rootScope, $resource, ResourceFactory ) {

	var credit = $resource( App.service + 'credit/:action/', { action: '@action' }, {
				'add' : { 'method': 'POST', params : { action: 'add' } }
			}
		);

	var service = {};

	service.add = function( params, callback ){
		credit.add( params, function( json ){
			callback( json );
		} );
	}

	return service;

} );