
NGApp.factory('HeartbeatService', function($rootScope, $resource, $heartbeat, LocationService) {

	var service = {
		date: null
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
		heartbeat.load(params, function(data){
			callback(data);
		});
	}
	
	$timeout(function() { badges() }, 30 * 1000 );
	
	// Event called when the app resumes
	$rootScope.$on('appResume', function(e, data) {
		heartbeat();
	});

	var heartbeat = function(){
		// Just run if the user is loggedin
		if ($rootScope.account.isLoggedIn()) {
			service.load(function(data) {
				service.date = new Date;
				$rootScope.$broadcast('totalOrders', data.orders['total']);
				$rootScope.$broadcast('newOrders', data.orders['new']);
				$rootScope.$broadcast('acceptedOrders', data.orders['accepted']);
				$rootScope.$broadcast('pickedupOrders', data.orders['pickedup']);
			});

			// run over and over again every 30 secs
			$timeout(function() {
				heartbeat()
			}, 30 * 1000);
		}

	}
	// Update the badges
	heartbeat();


	return service;

});