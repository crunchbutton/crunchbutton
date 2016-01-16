NGApp.factory( 'AccountService', function($http, $rootScope, $resource, MainNavigationService) {

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
		isMarketingRep: false,
		isCampusManager: false,
		restaurants: [],
		init: false
	};

	service.permcheck = function(perms) {
		if (typeof perms != 'object') {
			perms = [perms];
		}

		var pass = false;

		for (var x in perms) {
			if( service.user && service.user.permissions ){
				if (service.user.permissions[perms[x].toUpperCase()] === true) {
					pass = true;
				} else if (service.user.permissions[perms[x].toUpperCase()] === false) {
					return false;
				}
			}
		}
		return pass;
	};

	service.isLoggedIn = function(){
		return ( service.user && service.user.id_admin ) ? true : false;
	}

	service.checkUser = function() {
		$rootScope.$broadcast('userAuth', App.config.user);
		App.config.user = null;
	};

	service.login = function( username, password, callback ) {
		// App.isCordova
		user.login( { 'username': username, 'password': password, 'native': true }, function( json ){
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
			MainNavigationService.link('/login');
		});
	};

	$rootScope.$on('adminWorking', function(e, data) {
		service.user.working = data;
	});

	$rootScope.$on('userAuth', function(e, data) {
		service.init = true;

		service.user = data;

		service.isRestaurant = service.isDriver = service.isSupport = service.isAdmin = false;
		service.restaurants = [];

		if (service.user) {
			if (service.user.permissions && service.user.permissions.GLOBAL) {
				service.isAdmin = true;
				service.isSupport = true;
			}
			if (service.user.permissions && service.user.permissions.RESTAURANT) {
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

			service.isMarketingRep = service.user.isMarketingRep;
			service.isCampusManager = service.user.isCampusManager;

			if (data && data.id_admin) {
				var name = service.user.name.split(' ');
				service.user.firstName = name[0];
				service.user.initials = '';
				for (var x in name) {
					service.user.initials += name[x].charAt(0);
				}

				$.totalStorage('hasLoggedIn',true);
			}
		}

		App.snap.close();

		// hopefully this doesnt break anything
		//$rootScope.reload();

	} );

	return service;
});