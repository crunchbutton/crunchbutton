NGApp.factory( 'AccountService', function($http, $rootScope, $resource) {

	// Private resource 'user'
	var user = $resource( App.service + ':action', { action: '@action' }, {
			// actions
			'login' : { 'method': 'POST', params : { 'action' : 'login' } },
			'logout' : { 'method': 'GET', params : { 'action' : 'logout' } },
		}	
	);

	var service = {
		permissions: {},
		user: null
	};
	
	service.isLoggedIn = function(){
		return ( service.user && service.user.id_admin ) ? true : false;
	}

	service.checkUser = function() {
		$rootScope.$broadcast('userAuth', App.config.user);
		App.config.user = null;
	};

	service.login = function( username, password, callback ) {
		user.login( { 'username': username, 'password': password }, function( json ){
			if( json && json.id_admin ){
				$rootScope.$broadcast( 'userAuth', json );
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
	
	$rootScope.$on('userAuth', function(e, data) {
		service.user = data;

		if (service.user && service.user.id_admin) {
			App.snap.enable();
			var name = service.user.name.split(' ');
			service.user.initials = '';
			for (var x in name) {
				service.user.initials += name[x].charAt(0);
			}

		} else {
			App.snap.disable();
		}

		App.snap.close();
		$rootScope.reload();
	});

	return service;
});