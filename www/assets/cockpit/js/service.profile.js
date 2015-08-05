NGApp.factory( 'ProfileService', function( $resource ) {
	var service = {};
	var profile = $resource( App.service + 'profile/:action/', { action: '@action' }, {
				'change_password' : { 'method': 'POST', params : { action: 'change-password' } },
			} );

	service.change_password = function( password, callback ){
		profile.change_password( password, function( json ){
			callback( json );
		} );
	}
	return service;
} );