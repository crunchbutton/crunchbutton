NGApp.factory('MapService', function($rootScope, $resource, $routeParams, $templateRequest, $timeout, $compile, OrderService) {
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
		{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#FF6539"},{"lightness":-28}]},
		{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#FF6539"},{"visibility":"on"},{"lightness":-15}]},
		{"featureType":"road.local",
			"elementType":"geometry","stylers":[{"color":"#308a84"},{"lightness":-18}]},
			{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},
			{"elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},
		{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#38a9a1"},{"lightness":-34}]},
		{"featureType":"administrative","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#388a84"},{"weight":0.8}]},
		{"featureType":"poi.park","stylers":[{"color":"#1f4441"}]},
		{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#308a84"},{"weight":0.1},{"lightness":10}]}],
	};

	service.style = function(map) {
		map.setOptions({styles: service.styles.cockpit, scrollwheel: false});
	};

	var maps = {};

	service.reset = function(id) {
		maps[id] = null;
	};

	service.trackOrders = function(params) {

		var map = params.map;

		var closeInfoWindows = function() {
			for (var x in maps[params.id].infoWindows) {
				maps[params.id].infoWindows[x].close();
			}
		}

		if (!maps[params.id]) {
			maps[params.id] = {
				markers: [],
				infoWindows: []
			};
			params.scope.$on('$destroy', function() {
				service.reset(params.id);
			});

			google.maps.event.addListener(map, 'click', closeInfoWindows);
		}

		for (var x in maps[params.id].markers) {
			maps[params.id].markers[x].setMap(null);
		}
		maps[params.id].markers = [];

		for (var x in maps[params.id].infoWindows) {
			maps[params.id].infoWindows[x].close();
			maps[params.id].infoWindows[x] = null;
		}
		maps[params.id].infoWindows = [];

		var latlngbounds = new google.maps.LatLngBounds();
		var updateBounds = function(loc) {
			latlngbounds.extend(loc);
			map.setCenter(latlngbounds.getCenter());
			map.fitBounds(latlngbounds);
		};

		var geocoder = new google.maps.Geocoder();

		var getGeo = function(order, address, retries) {
			if (retries > 3) {
				return;
			}
			geocoder.geocode({address: address}, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					console.debug('Got geocoded result: ', results[0]);
					markOrder(order, results[0].geometry.location);

					// update the order with the lat and lon so we never have to geocode the address again
					OrderService.put({
						id_order: order,
						lat: results[0].geometry.location.lat(),
						lon: results[0].geometry.location.lng()
					});

				} else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
					setTimeout(function() {
						//getGeo(params.orders[x].address, retries+1);
					}, 1000);
					console.error('Could not geocode address: ', address, arguments);
				}
			});
		};

		var markOrder = function(order, loc) {
			updateBounds(loc);


			$templateRequest('/assets/view/mapview-order-info.html').then(function(d) {
				var orderScope = params.scope.$new(true);
				orderScope.order = order;

				var template = angular.element(d);
				var linkFn = $compile(d);
				var element = linkFn(orderScope);

				$timeout(function() {
					orderScope.$apply();
					var infowindow = new google.maps.InfoWindow({
						content: element[0].innerHTML
					});

					var marker = new google.maps.Marker({
						map: map,
						position: loc,
						zIndex: 99,
						animation: google.maps.Animation.DROP,
						icon: service.icon.customer
					});

					maps[params.id].markers.push(marker);
					maps[params.id].infoWindows.push(infowindow);

					google.maps.event.addListener(marker, 'click', function() {
						closeInfoWindows();
						infowindow.open(map, marker);
					});
				})



			});
		}

		var _already_shown = {};

		var trackOrder = function(order) {
			if (!order.address) {
				return;
			}
			if (order.lat && order.lon) {
				var key = order.lat + '_' + order.lon;
				if( !_already_shown[ key ] ){
					_already_shown[ key ] = true;
				} else {
					return;
				}
				markOrder(order, new google.maps.LatLng(parseFloat(order.lat), parseFloat(order.lon)));
			} else {
				getGeo(order.id_order, order.address, 0);
			}
		}

		for (var x in params.orders) {
			var order = params.orders[x];
			trackOrder( order );
		}

		// center on the US
		//map.setCenter(new google.maps.LatLng(parseFloat(39.0997), parseFloat(-94.5783)));

		return trackOrder;
	};

	service.trackCommunity = function(params) {
		var map = params.map;

		if (!maps[params.id]) {
			maps[params.id] = {markers: {}};
			params.scope.$on('$destroy', function() {
				service.reset(params.id);
			});
		}

		if (maps[params.id].markers.current) {
			maps[params.id].markers.current.setMap(null);
		}

		var myLatlng = new google.maps.LatLng(parseFloat(params.community.loc_lat), parseFloat(params.community.loc_lon));
		map.setCenter(myLatlng);

		maps[params.id].markers.current = new google.maps.Circle({
			strokeColor: '#ed3c06',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#fc7c08',
			fillOpacity: 0.35,
			map: map,
			center: myLatlng,
			radius: parseInt(params.community.range) * 1609.34
		});

		service.fixMapLoading( map, myLatlng );

	};

	service.fixMapLoading = function( map, myLatlng ){
		google.maps.event.addListenerOnce(map, 'idle', function(){
    	google.maps.event.trigger(map, 'resize');
		});
		google.maps.event.addListenerOnce(map, 'tilesloaded', function(){
			map.setCenter( myLatlng );
		});
	}

	service.trackRestaurant	 = function(params) {
		var map = params.map;

		if (!maps[params.id]) {
			maps[params.id] = {markers: {}};
			params.scope.$on('$destroy', function() {
				service.reset(params.id);
			});
		}

		if (maps[params.id].markers.current) {
			maps[params.id].markers.current.setMap(null);
		}

		var myLatlng = new google.maps.LatLng(parseFloat(params.restaurant.loc_lat), parseFloat(params.restaurant.loc_long));

		map.setCenter(myLatlng);

		maps[params.id].markers.current = new google.maps.Circle({
			strokeColor: '#ed3c06',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#fc7c08',
			fillOpacity: 0.35,
			map: map,
			center: myLatlng,
			radius: parseInt(params.restaurant.delivery_radius) * 1609.34
		});
	};

	service.trackStaff = function(params) {
		var map = params.map;

		if (!maps[params.id]) {
			maps[params.id] = {markers: {}};
			params.scope.$on('$destroy', function() {
				service.reset(params.id);
			});
		}

		var locs = {};

		locs[params.staff.location.lat] = params.staff.location.lon;

		for (var x in params.locations) {
			if (locs[params.locations[x].lat] == params.locations[x].lon) {
				continue;
			}
			new google.maps.Marker({
				map: map,
				position: new google.maps.LatLng(parseFloat(params.locations[x].lat), parseFloat(params.locations[x].lon)),
				zIndex: 99,
				icon: service.icon.dot
			});
			locs[params.locations[x].lat] = params.locations[x].lon;
		}

		if (maps[params.id].markers.current) {
			maps[params.id].markers.current.setMap(null);
		}

		var myLatlng = new google.maps.LatLng(parseFloat(params.staff.location.lat), parseFloat(params.staff.location.lon));

		map.setCenter(myLatlng);
		maps[params.id].markers.current = new google.maps.Marker({
			map: map,
			position: myLatlng,
			zIndex: 100,
			icon: params.staff.vehicle == 'car' ? service.icon.car : service.icon.bike
		});
	};

	service.trackOrder = function(params) {
		var map = params.map;
		if (params.driver && params.driver.location) {
			var driver = new google.maps.LatLng(parseFloat(params.driver.location.lat), parseFloat(params.driver.location.lon));
		}

		params.restaurant.loc_lat = ( params.restaurant.loc_lat ? params.restaurant.loc_lat : params.restaurant.location_lat );
		params.restaurant.loc_long = ( params.restaurant.loc_long ? params.restaurant.loc_long : params.restaurant.location_lon );

		var restaurant = new google.maps.LatLng(parseFloat(params.restaurant.loc_lat), parseFloat(params.restaurant.loc_long));

		if (!maps[params.id]) {
			maps[params.id] = {markers: {}};
			params.scope.$on('$destroy', function() {
				service.reset(params.id);
			});
		}

		var getDirections = function() {
			// directions render
			if (maps[params.id].markers.directions){
				maps[params.id].markers.directions.setMap(null);
			}

			var dest;
			if (params.order.status.status == 'accepted' || params.order.status.status == 'transfered') {
				//dest = restaurant;
				dest = maps[params.id].markers.customerLocation;
			} else if (params.order.status.status == 'pickedup') {
				dest = maps[params.id].markers.customerLocation;
			} else if (!maps[params.id].markers.directions) {
				map.setCenter(restaurant);
			} else {
				return;

			}
			var directionsService = new google.maps.DirectionsService();
			maps[params.id].markers.directions = new google.maps.DirectionsRenderer({suppressMarkers: true});
			maps[params.id].markers.directions.setMap(map);

			var routeParams = {
				origin: driver,
				destination: dest,
				travelMode: params.driver.vehicle == 'car' ? google.maps.TravelMode.DRIVING : google.maps.TravelMode.BICYCLING
			};

			if (params.order.status.status == 'accepted' || params.order.status.status == 'transfered') {
				routeParams.waypoints = [{location: restaurant,stopover: true}];
			}

			directionsService.route(routeParams, function(response, status) {
				console.debug('Got directions response: ', response);
				$rootScope.$broadcast('order-route', {
					order: params.order,
					restaurant: params.restaurant,
					legs: response.routes[0].legs
				});

				if (status === google.maps.DirectionsStatus.OK) {
					maps[params.id].markers.directions.setDirections(response);
				}
			});
		}

		// restaurant marker
		if (!maps[params.id].markers.restaurant) {
			maps[params.id].markers.restaurant = new google.maps.Marker({
				map: map,
				position: restaurant,
				zIndex: 98,
				icon: service.icon.restaurant
			});
		}

		// driver marker
		if (params.order.status.status != 'delivered' && params.order.status.status != 'new' && driver) {
			if (maps[params.id].markers.driver) {
				if (maps[params.id].markers.driverLat == params.driver.location.lat && maps[params.id].markers.driverLon == params.driver.location.lon) {
					// no updates
					console.debug('No updated driver position');
					return;
				}
				maps[params.id].markers.driver.setMap(null);
				getDirections();
			}
			maps[params.id].markers.driverLat = params.driver.location.lat;
			maps[params.id].markers.driverLon = params.driver.location.lon;

			maps[params.id].markers.driver = new google.maps.Marker({
				map: map,
				position: driver,
				zIndex: 100,
				icon: params.driver.vehicle == 'car' ? service.icon.car : service.icon.bike
			});
		}

		// customer marker
		if (!maps[params.id].markers.customer) {
			var geocoder = new google.maps.Geocoder();

			geocoder.geocode({address: params.order.address}, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					console.debug('Got geocoded result: ', results[0]);

					maps[params.id].markers.customerLocation = results[0].geometry.location;

					maps[params.id].markers.customer = new google.maps.Marker({
						map: map,
						position: results[0].geometry.location,
						zIndex: 99,
						icon: service.icon.customer
					});

					getDirections();
				} else {
					console.error('Could not geocode address: ', d.address);
				}
			});
		}
	};

	return service;
} );
