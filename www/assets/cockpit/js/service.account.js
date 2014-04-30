NGApp.factory( 'AccountService', function($http, $rootScope, $resource) {

	// Private resource 'user'
	var user = $resource( App.service + '/:action', { action: '@action' }, {
				// actions
				'login' : { 'method': 'POST', params : { 'action' : 'login' } },
				'logout' : { 'method': 'GET', params : { 'action' : 'logout' } },
			}	
		);

	var service = {
		permissions: {},
		user: null
	};
	
	service.checkUser = function() {
		service.user = App.config.user;
		App.config.user = null;
	};
	
	service.login = function( username, password, callback ) {
		if( !username ){
			App.alert( 'Please type your username' );
			return;
		}
		if( !password ){
			App.alert( 'Please type your password' );
			return;
		}
		user.login( { 'username': username, 'password': password }, function( json ){
			if( json && json.id_admin ){
				service.user = json;
				$rootScope.$broadcast( 'userAuth', service.user );
				callback( true );
			} else {
				callback( false );
			}
		} );
	};
	
	service.logout = function() {
		user.logout( {}, function(){
			service.user = {};
			$rootScope.$broadcast('userAuth');
		} );
	};
	
	$rootScope.$on( 'userAuth', function( e, data ) {
		if ( service.user.id_admin ) {
			
		} else {
			
		}
	});

	return service;
});