NGApp.factory('AccountService', function($http, $rootScope) {
	var service = {};
	
	service.checkUser = function() {
		service.user = App.config.user;
		App.config.user = null;
	};
	
	service.login = function(user, pass, callback) {
		$http({
			method: 'POST',
			url: App.service + 'login',
			data: $.param({'username': user, 'password': pass}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data) {
			if (data && data.id_admin) {
				service.user = data;
				$rootScope.$broadcast('userAuth', service.user);
				callback(true);
			} else {
				callback(false);
			}
		});
	};
	
	service.logout = function() {
		$http.get(App.service + 'logout').success(function() {
			service.user = {};
			$rootScope.$broadcast('userAuth');
		})
	};

	return service;
});