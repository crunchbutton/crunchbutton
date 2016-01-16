
NGApp.factory('HeartbeatService', function($rootScope, $resource, $interval, LocationService, AccountService, PushService, favicoService) {

	var service = {
		date: null,
		repeat: null,
		every: 20 * 1000
	};

	var heartbeat = $resource(App.service + 'heartbeat', {}, {
		'load' : { 'method': 'GET', params : {} }
	});

	service.load = function(callback) {
		if (LocationService.location && ( LocationService.location.latitude && LocationService.location.longitude ) ) {
			params = { 	latitude: LocationService.location.latitude,
									longitude: LocationService.location.longitude,
									accuracy: LocationService.location.accuracy,
									timestamp: LocationService.location.timestamp };
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
			// Register push service -Android
			if( App.isCordova && AccountService && AccountService.user ){
				if ( AccountService.user.android_push && window.parent && window.parent.device && window.parent.device.platform && ( window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' || window.parent.device.platform == 'amazon-fireos')) {
					if( !PushService.registered ){
						PushService.register( function(){ console.log("push registered"); });
						service.registered = true;
					}
				}
				if ( AccountService.user.ios_push && window.parent && window.parent.device.platform && window.parent.device.model && window.parent.device.platform == 'iOS') {
					if( !PushService.registered ){
						PushService.register( function(){ console.log("push registered"); });
						service.registered = true;
					}
				}
			}

			// reboot the interval
			$interval.cancel(service.repeat);
			service.repeat = $interval(service.check, service.every);

			service.load(function(data) {
				service.date = new Date;

				if (App.isCordova && parent.plugins && parent.plugins.pushNotification) {
					var complete = function(){};
					PushService.badges = parseInt(data.tickets) + parseInt(data.orders['new']);
					parent.plugins.pushNotification.setApplicationIconBadgeNumber(complete, complete, PushService.badges);
				}

				$rootScope.$broadcast('tickets', { 'tickets': data.tickets, 'timestamp': data.timestamp } );
				if( data.orders ){
					$rootScope.$broadcast('totalOrders', data.orders['total']);
					$rootScope.$broadcast('newOrders', data.orders['new']);
					$rootScope.$broadcast('acceptedOrders', data.orders['accepted']);
					$rootScope.$broadcast('pickedupOrders', data.orders['pickedup']);
				}
				$rootScope.$broadcast('adminWorking', data.working);

				favicoService.badge((parseInt(data.tickets) + parseInt(data.orders['new'])) || 0);
			});
		}
	}

	// wait for us to be logged in
	$rootScope.$on('userAuth', service.check);

	// check as soon as we come back
	$rootScope.$on('appResume', service.check);

	$rootScope.$on('appPause', function() {
		if (App.isCordova && parent.plugins && parent.plugins.pushNotification) {
			var complete = function(){};
			parent.plugins.pushNotification.setApplicationIconBadgeNumber(complete, complete, PushService.badges);
		}
	});

	// check when told to
	$rootScope.$on('updateHeartbeat', service.check);

	// check every 20 seconds
	service.repeat = $interval(service.check, service.every);

	return service;

});
