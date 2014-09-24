
// we have to be nice to the battery with both geolocation, and ajax requests

NGApp.factory('LocationService', function($http, $resource, $rootScope) {


	if (App.isPhoneGap) {

		var bgGeo = parent.window.plugins.backgroundGeoLocation;

		var callbackFn = function(location) {
			console.debug('BackgroundGeoLocation callback:  ' + location.latitude + ',' + location.longitude);
			track();
        	bgGeo.finish();
		}
		var failureFn = function(error) {
			console.log('BackgroundGeoLocation error');
		}
		
		bgGeo.configure(callbackFn, failureFn, {
			url: App.service + 'driver/location',
			params: {
            	auth_token: 'user_secret_auth_token',    //  <-- Android ONLY:  HTTP POST params sent to your server when persisting locations.
				foo: 'bar'                              //  <-- Android ONLY:  HTTP POST params sent to your server when persisting locations.
			},
			headers: {                                   // <-- Android ONLY:  Optional HTTP headers sent to your configured #url when persisting locations
				"X-Foo": "BAR"
			},
			desiredAccuracy: 10,
			stationaryRadius: 20,
			distanceFilter: 30, 
			notificationTitle: 'Background tracking', // <-- android only, customize the title of the notification
			notificationText: 'ENABLED', // <-- android only, customize the text of the notification
			activityType: 'AutomotiveNavigation',
			debug: true // <-- enable this hear sounds for background-geolocation life-cycle.
		});
		
	}

	var locationService = $resource( App.service + 'driver/:action', { action: '@action' }, {
			'track' : { 'method': 'POST', params : { 'action' : 'location' } }
		}
	);

	var track = function(loc) {
		// I added this line - it was asking the user to login when he was filling the onboarding/setup form @pererinha
		if( !$rootScope || !$rootScope.account || !$rootScope.account.user || !$rootScope.account.user.id_admin ){
			return;
		}

		// if it is a restaurant account, dont track
		if( $rootScope.account.restaurant ){
			return;
		}

		// if we dont have a location
		if (!loc.latitude) {
			return;
		}

		var d = new Date;
		d = d.getTime();

		// if it has been less than 1 minite
		if (updated && updated + 60000 > d) {
			return;
		}

		// if the location is the same and has been for under 3 min
		if (last && loc.latitude == last.latitude && loc.longitude == last.longitude && updated && updated + 180000 > d) {
			//return;
		}

		// only send out a tracking post:
		// 	when the location is different and the time is over 1 minite
		//	when the location is the same and the time is over 3 minites

		locationService.track(loc, function(json) {
			updated = d;
			last = location;
			console.debug('Tracked drivers location: ', location, d);
		});
	};

	var service = {
		location: function() {
			return location;
		},
		updated: function() {
			return updated;
		},
		track: function() {
			// only set extended tracking for a max of 15 minites
			if (!extendedTrack) {
				setTimeout(function() {
					service.untrack();
				}, 15000);

				extendedTrack = true;
			}
		},
		untrack: function() {
			extendedTrack = false;
		}
	};

	var location = {
		latitude: null,
		longitude: null,
		accuracy: null,
		timestamp: null
	};

	var watcher, last, updated = null;

	var extendedTrack = false;
	
	var startWatch = function() {
		if (App.isPhoneGap) {
			parent.window.navigator.geolocation.getCurrentPosition(function(location) {
				console.log('Location from Phonegap');
			});
			bgGeo.start();
		}
		watch();
		
	}

	var watch = function() {
		if (watcher) {
			return;
		}
		if (navigator.geolocation) {
			watcher = parent.window.navigator.geolocation.watchPosition(function(pos) {
				console.debug('Got drivers location: ', pos);
				location = pos.coords;

				$rootScope.$broadcast('location', location);

				if (!extendedTrack) {
					setTimeout(trackStop, 5000);
				}

				track(location);

			}, function() {
				//alert('Your location services are off, or you declined location permissions. Please enable this.');
			}, { enableHighAccuracy: true });
		}
	};

	var trackStop = function() {
		if (!watcher) {
			return;
		}
		parent.window.navigator.geolocation.clearWatch(watcher);
		watcher = null;
		// reactivate tracking every 2 minites
		setTimeout(watch, 60000 * 2);
	};

	var interval = setInterval(track, 60000);
	
	var stopWatch = function() {
		if (App.isPhoneGap) {
			bgGeo.stop();
		}
	}


	$rootScope.$on('userAuth', function(e, data) {
		// start watching if there is a user and their docs are filled out
		console.log('authing',data.id_admin);
		if (data.id_admin) {
			startWatch();
		} else {
			stopWatch();
		}
	});

	return service;
});

NGApp.factory( 'PositionService', function( $rootScope, $resource, $routeParams ) {

	var boundingRadius = 8000;
	var bounding = { lat: 0, lon: 0,  };
	var service = {};

	// set bouding
	service.bounding = function( lat, lon ){
		bounding.lat = lat;
		bounding.lon = lon;
	}

	service.getPosition = function( results ){
		for (i = 0; i < results.length; i++) {
			if( results[i] && results[i].geometry && results[i].geometry.location && results[i].geometry.location.lat() && results[i].geometry.location.lng() ){
				return { lat: results[i].geometry.location.lat(), lon: results[i].geometry.location.lng() };
			}
		}
		return false;
	}

	service.checkDistance = function( lat, lon ){
		if( lat && lon && bounding.lat && bounding.lon ) {
			return distance( { from: { lat: bounding.lat, lon: bounding.lon }, to: { lat: lat, lon: lon } } );
		}
		return false;
	}

	service.find = function( address, success, error ){

		var params = { address: address };

		if ( bounding.lat && bounding.lon && google && google.maps && google.maps.LatLng ) {
			var latLong = new google.maps.LatLng( bounding.lat, bounding.lon );
			var circle = new google.maps.Circle( { center: latLong } );
			var bounds = circle.getBounds();
			params.bounds = bounds;
		}

		// Send the request out to google
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode( params, function ( results, status ) {
			if ( status == google.maps.GeocoderStatus.OK ) {
				success( results, status );
			} else {
				error( results, status );
			}
		} );
	}

	service.getMapImageSource = function( from, to, zoom ){
		return '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' + to.lat + ',' + to.lon + '&zoom=' + zoom + '&size=200x100&maptype=roadmap&markers=color:blue%7Clabel:R%7C' + from.lat + ',' + from.lon + '&markers=color:green%7Clabel:C%7C' + to.lat + ',' + to.lon + '&scale=2" class="map-image">';
	}

	service.getDirectionsLink = function( from, to ){
		return 'https://www.google.com/maps/dir/' + from + '/' + to;
	}

	var km2Miles = function (km) {
		return km * 0.621371;
	}

	var Miles2Km = function (miles) {
		return miles * 1.60934;
	}

	var toRad = function( number ){
		return number * Math.PI / 180;
	}

	// return the distance of two points in miles
	var distance = function ( params ) {
		try {
			var R = 6371; // Radius of the earth in km
			var dLat = toRad( params.to.lat - params.from.lat );
			var dLon = toRad( params.to.lon - params.from.lon );
			var a = Math.sin( dLat / 2 ) * Math.sin( dLat / 2 ) +
				Math.cos( toRad( params.from.lat ) ) * Math.cos( toRad( params.to.lat) ) * Math.sin( dLon / 2 ) * Math.sin( dLon / 2 );
			var c = 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
			var d = R * c;
			return km2Miles( d ).toFixed(2);
		} catch (e) {
			console.log( 'distance::error', e );
		}
	}

	return service;

} );