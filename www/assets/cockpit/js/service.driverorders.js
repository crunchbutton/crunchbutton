NGApp.factory('DriverOrdersService', function($http, $rootScope) {
	var service = {

	};
	
	service.loadOrders = function() {
		$http.get(App.service + 'driverorders').success(function(orders) {
			$rootScope.driverorders = orders;
			var newDriverOrders = 0;
			for (var x in orders) {
				if (orders[x].lastStatus.status = 'new') {
					newDriverOrders++;
				}
			}
			console.log(newDriverOrders);
			$rootScope.newDriverOrders = newDriverOrders;
		});
	};
	
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
	
	$rootScope.$on('userAuth', function(e, data) {
		if (service.user.id_admin) {
			
		} else {
			
		}
	});

	return service;
});