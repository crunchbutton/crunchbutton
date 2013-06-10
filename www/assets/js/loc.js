
App.loc = {
	bounding: null,
	range: App.defaultRange,
	loaded: false,
	locationNotServed: false,
	locs: [],


	/**
	 * calculate the distance between two points
	 */
	distance: function(params) {
		try {
			var R = 6371; // Radius of the earth in km
			var dLat = _toRad(params.to.lat - params.from.lat);
			var dLon = _toRad(params.to.lon - params.from.lon);
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
				Math.cos(_toRad(params.from.lat)) * Math.cos(_toRad(params.to.lat)) *
				Math.sin(dLon/2) * Math.sin(dLon/2);
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
			var d = R * c; // Distance in km
			return d;

		} catch(e) {
			App.track('Location Error', {
				lat: App.loc.lat,
				lon: App.loc.lon,
				address: App.loc.pos().address()
			});
		}
	},


	/**
	 * get the closest
	 */
	getClosest: function(results, latLong) {

		var from = {
			lat: latLong.lat(),
			lon: latLong.lng()
		};

		var distances = [];
		var closest = -1;

		for (i = 0; i < results.length; i++) {
			var d = App.loc.distance({
				to: {lat: results[i].geometry.location.lat(), lon: results[i].geometry.location.lng()},
				from: from
			});

			distances[i] = d;
			if (closest == -1 || d < distances[closest]) {
				closest = i;
			}
		}
		return results[closest];
	},


	/**
	 * get the most recent position
	 */
	pos: function() {
		return App.loc.locs[0] || new Location;
	},


	/**
	 * get location from the browsers geolocation
	 */
	getLocationByBrowser: function(success, error) {
		var success = success || function(){};
		var error = error || function(){};

		if (navigator.geolocation) {
			App.loc.timerId = setTimeout(function() {
				error();
			}, 5000);

			navigator.geolocation.getCurrentPosition(function(position){
				clearTimeout(App.loc.timerId);

				App.loc.locs.push(new Location({
					lat: position.coords.latitude,
					lon: position.coords.longitude,
					type: 'geolocation'
				}));

				App.track('Locations Shared', {
					lat: position.coords.latitude,
					lon: position.coords.longitude
				});
				// get the city from shared location
				App.loc.reverseGeocode(position.coords.latitude, position.coords.longitude, success, error);

			}, function() {
				clearTimeout(App.loc.timerId);
				error();
			}, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});

		} else {
			error();
		}
	},


	/**
	 * initilize location functions
	 */
	init: function() {
		// 1) set bounding to maxmind results if we have them
		if (App.config.loc.lat && App.config.loc.lat) {
			App.loc.bounding = App.config.loc;
			App.loc.bounding.type = 'geoip';
		}

		// 2) retrieve and set location date from cookies
		var cookieLocs = $.cookie('locsv2') ? JSON.parse($.cookie('locsv2')) : null;
		var cookieBounding = $.cookie('boundingv2') ? JSON.parse($.cookie('boundingv2')) : null;
		if (cookieLocs) {
			console.log(cookieLocs)
			var locs = [];
			for (var x in cookieLocs) {
				locs.push(new Location(cookieLocs[x]._properties));
			}
			App.loc.locs = locs;
			console.log(locs)
		}
		if (cookieBounding) {
			App.loc.bounding = cookieBounding;
			App.loc.bounding.type = 'cookie';
		}
		
		// 3) set location info by stored user
		if (App.config && App.config.user && App.config.user.location_lat) {
			App.loc.bounding = {
				lat: App.config.user.location_lat,
				lon: App.config.user.location_lon,
				type: 'user'
			};
		}

		// 4) get a more specific bounding location result from google
		if (google && google.load) {
			google.load('maps', '3', {callback: App.loc.googleCallback, other_params: 'sensor=false'});
		}
	},
	
	// callback for google location api
	googleCallback: function() {
		console.debug('PROCESSING LOCATION DATA FROM GOOGLE API');
		
		// if we dont have the proper location data, just populate from bounding
		var error = function() {
			if (App.loc.bounding.lat && App.loc.bounding.lon && !App.loc.bounding.city) {
				App.loc.reverseGeocode(App.loc.bounding.lat, App.loc.bounding.lon, function(loc) {
					App.loc.bounding.city = loc.city();
					App.loc.bounding.region = '';
				}, function(){});
			}
		};

		App.loc.loaded = true;

		if (google.loader.ClientLocation) {
			// we got a location back from google. use it
			if (google.loader.ClientLocation.latitude && google.loader.ClientLocation.longitude) {
				App.loc.bounding = {
					lat: google.loader.ClientLocation.latitude,
					lon: google.loader.ClientLocation.longitude,
					city: google.loader.ClientLocation.address.city,
					region: google.loader.ClientLocation.address.country_code == 'US' && google.loader.ClientLocation.address.region ? google.loader.ClientLocation.address.region.toUpperCase() : google.loader.ClientLocation.address.country_code,
					type: 'googleip'
				};
			}
		}
		
		// 5) if there is no previously used locations of any kind
		if (!App.loc.locs.length) {
			App.loc.getLocationByBrowser(function(){}, error);
		} else {
			error();
		}
	},
	

	/**
	 * geocode an address and perform callbacks
	 */
	geocode: function(address, success, error) {
		App.loc.doGeocode(address, function(results) {

			if (results.alias) {
				var loc = new Location({
					address: results.alias.address,
					entered: address,
					type: 'alias',
					lat: results.alias.lat,
					lon: results.alias.lon,
					city: results.alias.city,
					prep: results.alias.prep
				});
				
			} else {

				// if we have a bounding result, bind to it
				if (App.loc.bounding) {
					var latLong = new google.maps.LatLng(App.loc.bounding.lat, App.loc.bounding.lon);
					var closest = App.loc.getClosest(results, latLong);

				} else {
					var closest = results[0];
				}
				
				var loc = new Location({
					results: results,
					entered: address,
					type: 'user',
					lat: closest.geometry.location.lat(),
					lon: closest.geometry.location.lng()
				});
			}

			success(loc);

		}, function() {
			error();
		});
	},
	

	/**
	 * process the geocode result
	 */
	doGeocode: function(address, success, error) {
		address = $.trim(address);

		App.track('Location Entered', {
			address: address
		});

		var rsuccess = function(results) {
			success(results);
		};
		
		// there was no alias, do a real geocode
		var rerror = function() {
			var params = {
				address: address
			};
			// if we have a bounding result, process based on that
			if (App.loc.bounding) {
				var latLong = new google.maps.LatLng(App.loc.bounding.lat, App.loc.bounding.lon);
				
				// Create a cicle bounding box
				var circle = new google.maps.Circle({center: latLong, radius: App.boundingBoxMeters});
				var bounds = circle.getBounds();
				
				params.bounds = bounds;
			}

			// Send the request out to google
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode(params, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					success(results, status);
				} else {
					error(results, status);
				}
			});
		};

		// Check if the typed address has an alias
		App.routeAlias(address, rsuccess, rerror);
	},


	/**
	 * perform a reverse geocode from lat/lon
	 */
	reverseGeocode: function(lat, lon, success, error) {
		App.track('Location Reverse Geocode', {
			lat: lat,
			lon: lon
		});

		var geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(lat, lon);

		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[1]) {
					success(new Location({
						results: results
					}));
				} else {
					error();
				}
			} else {
				error();
			}
		});
	},


	// This method validate the acceptables types of address/location
	validateAddressType : function( addressLocation ){

		// Check if the address is rooftop or range_interpolated
		if( addressLocation && addressLocation.geometry && addressLocation.geometry.location_type && 
			( addressLocation.geometry.location_type == google.maps.GeocoderLocationType.ROOFTOP || 
				addressLocation.geometry.location_type == google.maps.GeocoderLocationType.RANGE_INTERPOLATED ) ){
			return true;
		}

		// If the address is not rooftop neither range_interpolated it could be approximate
		if( addressLocation && addressLocation.geometry && addressLocation.geometry.location_type && 
			( addressLocation.geometry.location_type == google.maps.GeocoderLocationType.APPROXIMATE ) ){
			// The address type could be premise, subpremise, intersection or establishment
			for ( var x in addressLocation.types ) {
				var addressType = addressLocation.types[ x ];
				if( addressType == 'premise' || addressType == 'subpremise' || addressType == 'intersection' || addressType == 'establishment' ){
					return true;
				}
			}
		}
		// It is not valid
		return false;
	},


	/**
	 * convert killometers to miles
	 */
	km2Miles : function(km) {
		return km * 0.621371;
	},


	/**
	 * convert miles to killometers
	 */
	Miles2Km : function(miles){
		return miles * 1.60934;
	},


	/**
	 * verify a location, and add to the location stack if nessicary
	 */
	addVerify: function() {
		if (arguments[0] && typeof arguments[1] != 'function') {
			// its lat/lon
			var success = arguments[2];
			var error = arguments[3];
		} else {
			// its text based
			var address = arguments[0];
			var success = arguments[1];
			var error = arguments[2];

			for (var x in App.loc.locs) {
				if (App.loc.locs[x].entered == address && App.loc.locs[x].verified) {
					success(App.loc.locs[x]);
					return;
				}
			}

			App.loc.geocode(address, function(loc) {
				App.loc.locs.push(loc);
				success();
				$.cookie('locsv2', JSON.stringify(App.loc.locs), { expires: new Date(3000,01,01), path: '/'});
				$.cookie('boundingv2', JSON.stringify(App.loc.bounding), { expires: new Date(3000,01,01), path: '/'});
			}, error);
		}
		//App.loc.geocode(address, success, error);

		/*
		App.log.location( { 'address' : App.loc.address(), 'lat' : App.loc.pos().lat, 'lon' : App.loc.pos().lon  } , 'address not served' );

		App.track('Location Error', {
			lat: App.loc.pos().lat,
			lon: App.loc.pos().lon,
			address: App.loc.address()
		});
		*/

	}
}