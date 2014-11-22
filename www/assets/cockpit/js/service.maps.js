NGApp.factory('MapService', function($rootScope, $resource, $routeParams) {
	var service = {
		icon: {
			car: {
				url: '/assets/cockpit/images/map-icon-driver-car.png',
				size: new google.maps.Size(26,35),
				scaledSize: new google.maps.Size(26,35),
				origin: new google.maps.Point(0, 0)
			},
			bike: {
				url: '/assets/cockpit/images/map-icon-driver-bike.png',
				size: new google.maps.Size(26,35),
				scaledSize: new google.maps.Size(26,35),
				origin: new google.maps.Point(0, 0)
			},
			dot: {
				url: '/assets/cockpit/images/map-icon-dot.png',
				size: new google.maps.Size(8,8),
				scaledSize: new google.maps.Size(8,8),
				origin: new google.maps.Point(0, 0)
			}
		}
	};

	return service;
} );