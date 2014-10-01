
NGApp.factory('HeartbeatService', function($rootScope, $resource, $interval, LocationService, AccountService) {

	var service = {
		date: null,
		repeat: null,
		every: 10 * 1000
	};

	var heartbeat = $resource(App.service + 'heartbeat', {}, {
		'load' : { 'method': 'GET', params : {} }
	});
	
	service.load = function(callback) {
		if (LocationService.location && LocationService.location.latitude && LocationService.location.longitude) {
			params = LocationService.location;
		} else {
			params = {};
		}
		heartbeat.load(params, function(data) {
			callback(data);
		});
	}

	service.check = function() {
		// Just run if the user is loggedin
		if (AccountService.isLoggedIn()) {
		
			// reboot the interval
			$interval.cancel(repeat);
			$interval(service.check, service.every);

			service.load(function(data) {
				service.date = new Date;
				
				if (App.isPhoneGap) {
					var complete = function(){};
					parent.plugins.pushNotification.setApplicationIconBadgeNumber(complete, complete, data.tickets + data.orders['new']);
				}
				
				$rootScope.$broadcast('tickets', data.tickets);
				$rootScope.$broadcast('totalOrders', data.orders['total']);
				$rootScope.$broadcast('newOrders', data.orders['new']);
				$rootScope.$broadcast('acceptedOrders', data.orders['accepted']);
				$rootScope.$broadcast('pickedupOrders', data.orders['pickedup']);
			});
		}
	}

	// wait for us to be logged in
	$rootScope.$on('userAuth', service.check);
	
	// check as soon as we come back
	$rootScope.$on('appResume', service.check);
	
	// check when told to
	$rootScope.$on('updateHeartbeat', service.check);

	// check every 30 seconds
	var repeat = $interval(service.check, service.every);

	return service;

});