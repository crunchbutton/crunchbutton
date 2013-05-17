
App.loc = {
	aproxLoc: null,
	realLoc: null,
	range: App.defaultRange,
	loaded: false,
	changeLocationAddressHasChanged: false,
	locationNotServed: false,
	// calculate the distance between two points
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
				address: App.loc.address()
			});
		}
	},

	// get the best posible location
	pos: function() {
		if (!App.loc.realLoc) {
			return App.loc.aproxLoc;
		} else {
			return App.loc.realLoc;		
		}
	},
	
	// bind for a single event only triggered once
	bind: function(event, fn) {
		$(document).unbind(event)
		$(document).one(event, fn);
	},

	// get the best posible address
	address: function() {
		return App.loc.pos() ? (App.loc.pos().addressEntered || App.loc.pos().addressReverse || App.loc.pos().addressAlias) : '';
	},

	// get the location city
	city: function() {
		return (App.loc.pos() && App.loc.pos().city) ? App.loc.pos().city : '';
	},

	// get the preposition for the location
	prep: function() {
		return (App.loc.pos() && App.loc.pos().prep) ? App.loc.pos().prep : 'in';
	},

	// get the location from the browsers geolocation
	getLocationByBrowser: function(success, error) {

		if (navigator.geolocation) {
			App.loc.timerId = setTimeout(function() {
				error();
			}, 5000);

			navigator.geolocation.getCurrentPosition(function(position){
				
				// console.log('position',position);

				clearTimeout(App.loc.timerId);

				App.loc.realLoc = {
					lat: position.coords.latitude,
					lon: position.coords.longitude
				};
				App.track('Locations Shared', {
					lat: position.coords.latitude,
					lon: position.coords.longitude
				});
				App.loc.reverseGeocode(position.coords.latitude, position.coords.longitude, function() {
					success();
				}, error);

			}, function() {
				clearTimeout(App.loc.timerId);
				error();
			}, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});

		} else {
			error();
		}
	},
	
	// parse the city name from the result set
	getCityFromResult: function(results) {
		if (!results) {
			return;
		}
		switch (results[0].types[0]) {
			default:
			case 'administrative_area_level_1':
				App.loc.realLoc.city = results[0].address_components[0].long_name;
				break;
			case 'locality':
				App.loc.realLoc.city = results[0].address_components[0].long_name;
				App.loc.realLoc.region = results[0].address_components[2].short_name;
				break;
			case 'street_address':
				App.loc.realLoc.city = results[0].address_components[2].long_name;
				App.loc.realLoc.region = results[0].address_components[4].short_name;
				break;
			case 'postal_code':
			case 'route':
				App.loc.realLoc.city = results[0].address_components[1].long_name;
				App.loc.realLoc.region = results[0].address_components[3].short_name;
				break;
		}

		// @todo: do we need this?
		for (var i = 0; i < results[0].address_components.length; i++) {
			for (var j = 0; j < results[0].address_components[i].types.length; j++) {
				if (results[0].address_components[i].types[j] == 'locality') {
					App.loc.realLoc.city = results[0].address_components[i].long_name;
				}
			}
		}
	},
	
	// get the guessed address
	getAddressFromResult: function(results) {
		if (!results) {
			return;
		}
		App.loc.realLoc.addressReverse = results[0].formatted_address;
	},
	
	// set the location from the result set
	setFormattedLocFromResult: function(result) {
		App.loc.getCityFromResult(result);
		App.loc.getAddressFromResult(result);
		$.cookie('location', JSON.stringify(App.loc.realLoc), { expires: App.cookieExpire, path: '/'});
		$('.loc-your-area').html(App.loc.city() || 'your area');
	},
	
	// set the location by name
	setFormattedLoc: function(loc) {
		$('.loc-your-area').html(loc);
	},

	// run in the begining
	init: function() {
		// retrieve the real loc from cookie. ignore googles location
		var lcookie = $.cookie('location') ? JSON.parse($.cookie('location')) : null;
		if (lcookie) {
			App.loc.realLoc = lcookie;
			
		} else if (App.config.user.location_lat) {
			// set the realloc to the users position
			App.loc.realLoc = {
				lat: App.config.user.location_lat,
				lon: App.config.user.location_lon,
				addressEntered: App.config.user.address
			};
		}
		google.load('maps', '3',  {callback: App.loc.preProcess, other_params: 'sensor=false'});
	},
	
	// callback for google location api
	preProcess: function() {
		console.log('preprocessing')
	
		var success = function() {
			// browser detection success
			$(document).trigger('location-detected');
		};
		
		var error = function() {
			// Last try, reverseGeocode with aproxLoc
			if( App.loc.aproxLoc ){
				App.loc.realLoc = App.loc.aproxLoc;
				App.loc.reverseGeocode( App.loc.aproxLoc.lat, App.loc.aproxLoc.lon, function() {
					success();
				}, function(){ /* do nothing - detection error */ });	
			}
		};

		var complete = function(lat, lon, city, region) {
			if (lat) {
				// we have a location! but its just a guess
				App.loc.aproxLoc = {
					lat: lat,
					lon: lon,
					city: city,
					region: region
				};
			} else {
				// if we dont have a location, then lets ask for an address
				 App.loc.aproxLoc = null;
			}

			App.loc.loaded = true;
			$(document).trigger('location-loaded');
			
			// if we dont have any real location, then wait for secondary location detection
			if (!App.loc.realLoc) {
				App.loc.getLocationByBrowser(success, error);
			}
		}

		if (google.loader.ClientLocation) {
			// we got a location back from google. use it
			complete(
				google.loader.ClientLocation.latitude,
				google.loader.ClientLocation.longitude,
				google.loader.ClientLocation.address.city,
				google.loader.ClientLocation.address.country_code == 'US' && google.loader.ClientLocation.address.region ? google.loader.ClientLocation.address.region.toUpperCase() : google.loader.ClientLocation.address.country_code
			);

		} else if (App.config.loc.lat) {
			// we didnt get a location from google. use maxmind instead
			complete(App.config.loc.lat, App.config.loc.lon, App.config.loc.city, App.config.loc.region);
		} else {
			// we have no location
			complete();
		}
	},
	
	// send out a geocode request
	doGeocode: function(address, success, error) {
		address = $.trim(address);

		// track the entered address to mixpanel
		App.track('Location Entered', {
			address: address
		});

		// there was an alias. just set it to that
		var rsuccess = function(results) {
			success(results);
		};
		
		// there was no alias, do a real geocode
		var rerror = function() {
			// send the request out to google
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({address: address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					success(results, status);
				} else {
					error(results, status);
				}
			});
		};

		// Check if the typed address has an alias
		App.routeAlias(address, rsuccess, rerror);

		// set the bounds of the address to our guessed location
	},


	doGeocodeLocationPage: function(address, success, error) {
		address = $.trim(address);

		App.track('Location Entered', {
			address: address
		});

		var rsuccess = function(results) {
			success(results);
		};
		
		// there was no alias, do a real geocode
		var rerror = function() {

			var latLong = new google.maps.LatLng( App.loc.aproxLoc.lat, App.loc.aproxLoc.lon );	

			// Create a cicle bounding box
			var circle = new google.maps.Circle( { center: latLong, radius: App.boundingBoxMeters } ); 
			var bounds = circle.getBounds();

			// Send the request out to google
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({address: address, bounds : bounds }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					success(results, status);
				} else {
					error(results, status);
				}
			});
		};

		// Check if the typed address has an alias
		App.routeAlias(address, rsuccess, rerror);

		// set the bounds of the address to our guessed location
	},

	// perform a geocode and store the results
	geocode: function(address, success, error) {
		App.loc.doGeocode(address, function(results) {
			if (results.alias) {
				App.loc.realLoc = {
					addressAlias: results.alias.address,
					lat: results.alias.lat,
					lon: results.alias.lon,
					prep: results.alias.prep,
					city: results.alias.city
				};
				App.loc.setFormattedLocFromResult();
			} else {
				App.loc.realLoc = {
					addressEntered: address,
					lat: results[0].geometry.location.lat(),
					lon: results[0].geometry.location.lng()
				};
				App.loc.setFormattedLocFromResult(results);
			}
			success();

		}, function() {
			App.loc.realLoc = null;
			error();
		});
	},

	geocodeLocationPage: function(address, success, error) {
		App.loc.doGeocodeLocationPage(address, function(results) {
			if (results.alias) {
				App.loc.realLoc = {
					addressAlias: results.alias.address,
					lat: results.alias.lat,
					lon: results.alias.lon,
					prep: results.alias.prep,
					city: results.alias.city
				};
				App.loc.setFormattedLocFromResult();
			} else {

				if( App.loc.aproxLoc ){
					var latLong = new google.maps.LatLng( App.loc.aproxLoc.lat, App.loc.aproxLoc.lon );	
					// Get the closest address from that lat/lng
					var theClosestAddress = App.loc.theClosestAddress( results, latLong );
					results[0] = theClosestAddress;
				} else {
					var theClosestAddress = results[0];
				}
				
				App.loc.realLoc = {
					addressEntered: App.loc.formatedAddress( theClosestAddress ),
					lat: theClosestAddress.geometry.location.lat(),
					lon: theClosestAddress.geometry.location.lng()
				};
				App.loc.changeLocationAddressHasChanged = true;
				App.loc.setFormattedLocFromResult( results );
			}
			success();

		}, function() {
			App.loc.realLoc = null;
			error();
		});
	},

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
					App.loc.setFormattedLocFromResult(results);
					success();
				} else {
					$('.location-address').val('Where are you?!');
				}
			} else {
				error();
			}
		});
	},

	doGeocodeWithBound: function(address, latLong, success, error) {

		address = $.trim(address);

		// track the entered address to mixpanel
		App.track('Location Entered', {
			address: address
		});

		// Create a cicle bounding box
		var circle = new google.maps.Circle( { center: latLong, radius: App.boundingBoxMeters } ); 
		var bounds = circle.getBounds();

		// Send the request out to google
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({address: address, bounds : bounds }, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				success(results, status);
			} else {
				error(results, status);
			}
		});
	},

	theClosestAddress : function( results, latLong ) {
		var lat = latLong.lat();
		var lng = latLong.lng();
		var R = 6371;
		var distances = [];
		var closest = -1;
		for( i=0;i<results.length; i++ ) {
				var alat = results[i].geometry.location.lat();
				var alng = results[i].geometry.location.lng();
				var dLat  = _toRad( alat - lat );
				var dLong = _toRad( alng - lng );
				var a = Math.sin( dLat / 2 ) * Math.sin( dLat / 2 ) +
						Math.cos( _toRad( lat ) ) * Math.cos( _toRad( lat ) ) * Math.sin( dLong/2 ) * Math.sin( dLong/2 );
				var c = 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
				var d = R * c;
				distances[ i ] = d;
				if ( closest == -1 || d < distances[ closest ] ) {
						closest = i;
				}
		}
		return results[ closest ];
	},

	formatedAddress : function( location ){
		// Remove the country name, it is useless here
		return location.formatted_address.replace( ', USA', '' );
	},

	// Return the zip code of a location
	zipCode : function( location ){
		var address_components = location.address_components;
		var zipCode = null;
		$.each( address_components, function(){
			if( this.types[0] == 'postal_code' ){
				zipCode = this.short_name;
			}
		} );
		return zipCode;
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

	km2Miles : function( km ){
		return km * 0.621371;
	},
	Miles2Km : function( miles ){
		return miles * 1.60934;
	},
	log: function(){
		$.ajax({
		type: 'POST',
		dataType: 'json',
		data: App.loc.pos(),
		url:  App.service + 'loc_log/new',
		success: function( json ) {}
		});	
	}
}