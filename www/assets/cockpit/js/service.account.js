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
		user: null,
		// used to change how to display the menu
		isRestaurant: false,
		isDriver: false,
		isSupport: false,
		isAdmin: false,
		restaurants: []
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
			location.reload(true);
		} );
	};

	$rootScope.$on('userAuth', function(e, data) {
		console.debug('USERAUTH', data);

		service.user = data;

		service.isRestaurant = service.isDriver = service.isSupport = service.isAdmin = false;
		service.restaurants = [];

		if (service.user && service.user.permissions && service.user.permissions.GLOBAL) {
			service.isAdmin = true;
		}
		if (service.user && service.user.permissions && service.user.permissions.RESTAURANT) {
			service.isRestaurant = true;

			for (var x in service.user.permissions) {
				if (x.indexOf('RESTAURANT-') == 0) {
					service.restaurants.push(x.replace(/[^0-9]/g,''));
				}
			}

			// only one restaurant for now
			service.restaurant = service.restaurants[0];
		}

		if (service.user.groups) {
			for (var x in service.user.groups) {
				if (service.user.groups[x].indexOf('drivers-') == 0) {
					service.isDriver = true;
					break;
				}
			}
		}

		if (data && data.id_admin) {
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