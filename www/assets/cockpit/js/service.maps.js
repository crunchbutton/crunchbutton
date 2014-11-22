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
			customer: {
				url: '/assets/cockpit/images/map-icon-customer.png',
				size: new google.maps.Size(26,35),
				scaledSize: new google.maps.Size(26,35),
				origin: new google.maps.Point(0, 0)
			},
			restaurant: {
				url: '/assets/cockpit/images/map-icon-restaurant.png',
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
	
	service.trackOrder = function(params) {
		var map = params.map;
		var driver = new google.maps.LatLng(parseFloat(params.driver.location.lat), parseFloat(params.driver.location.lon));
		var restaurant = new google.maps.LatLng(parseFloat(params.restaurant.loc_lat), parseFloat(params.restaurant.loc_long));
		var customer;
		
		// restaurant marker
		new google.maps.Marker({
			map: map,
			position: restaurant,
			icon: service.icon.restaurant
		});

		// driver marker
		new google.maps.Marker({
			map: map,
			position: driver,
			icon: params.driver.vehicle == 'car' ? service.icon.car : service.icon.bike
		});

		// customer marker
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({address: params.order.address}, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				console.debug('Got geocoded result: ', results[0]);
				customer = results[0].geometry.location;

				new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					icon: service.icon.customer
				});
				
				getDirections();
				
			} else {
				console.error('Could not geocode address: ', d.address);
			}
		});
		
		// directions render
//		if (directionsRenderer){
//			directionsRenderer.setMap(null);
//		}
		var getDirections = function() {
			var dest;
			if (params.order.status.status == 'accepted' || params.order.status.status == 'transfered') {
				dest = restaurant;
			} else if (params.order.status.status == 'pickedup') {
				dest = customer;
			} else {
				return;
			}
			
			var directionsService = new google.maps.DirectionsService();
			var directionsRenderer = new google.maps.DirectionsRenderer({suppressMarkers: true});
	
			directionsRenderer.setMap(map);
			directionsService.route({
				origin: driver,
				destination: dest,
				travelMode: params.driver.vehicle == 'car' ? google.maps.TravelMode.DRIVING : google.maps.TravelMode.BICYCLING

			}, function(response, status) {
				console.log(response);
				if (status === google.maps.DirectionsStatus.OK) {
					directionsRenderer.setDirections(response);
				}
			});
		}
	};

	return service;
} );