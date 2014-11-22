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
	
	service.styles = {
		cockpit: [
		{"featureType":"water","elementType":"geometry","stylers":[{"color":"#333739"}]},
		{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#265754"}]},
		{"featureType":"poi","stylers":[{"color":"#31736e"},{"lightness":-7}]},
		{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#3baaa3"},{"lightness":-28}]},
		{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#3baaa3"},{"visibility":"on"},{"lightness":-15}]},
		{"featureType":"road.local",
			"elementType":"geometry","stylers":[{"color":"#308a84"},{"lightness":-18}]},
			{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},
			{"elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},
		{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#38a9a1"},{"lightness":-34}]},
		{"featureType":"administrative","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#388a84"},{"weight":0.8}]},
		{"featureType":"poi.park","stylers":[{"color":"#225652"}]},
		{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#308a84"},{"weight":0.1},{"lightness":10}]}],
	};
	
	service.style = function(map) {
		map.setOptions({styles: service.styles.cockpit});
	};

	return service;
} );