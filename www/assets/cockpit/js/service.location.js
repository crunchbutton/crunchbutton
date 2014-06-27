
// we have to be nice to the battery with both geolocation, and ajax requests

NGApp.factory('LocationService', function($http, $resource, $rootScope) {

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
		lat: null,
		lon: null,
		accuracy: null,
		timestamp: null
	};

	var watcher, last, updated = null;

	var extendedTrack = false;

	var locationService = $resource( App.service + 'driver/:action', { action: '@action' }, {
			'track' : { 'method': 'POST', params : { 'action' : 'location' } }
		}
	);

	var watch = function() {
		if (watcher) {
			return;
		}
		if (navigator.geolocation) {
			watcher = navigator.geolocation.watchPosition(function(pos) {
				console.debug('Got drivers location: ', pos);
				location = {
					lat: pos.coords.latitude,
					lon: pos.coords.longitude,
					accuracy: pos.coords.accuracy,
					timestamp: pos.timestamp,
				};

				$rootScope.$broadcast('location', location);

				if (!extendedTrack) {
					setTimeout(trackStop, 5000);
				}

				track();

			}, function() {
				alert('Your location services are off, or you declined location permissions. Please enable this.');
			}, { enableHighAccuracy: true });
		}
	};

	var trackStop = function() {
		if (!watcher) {
			return;
		}
		navigator.geolocation.clearWatch(watcher);
		watcher = null;
		// reactivate tracking every 2 minites
		setTimeout(watch, 60000 * 2);
	};

	var track = function() {
		// I added this line - it was asking the user to login when he was filling the onboarding/setup form @pererinha
		if( !$rootScope || !$rootScope.account || !$rootScope.account.user || !$rootScope.account.user.id_admin ){
			return;
		}

		// if we dont have a location
		if (!location.lat) {
			return;
		}

		var d = new Date;
		d = d.getTime();

		// if it has been less than 1 minite
		if (updated && updated + 60000 > d) {
			return;
		}

		// if the location is the same and has been for under 5 min
		if (last && location.lat == last.lat && location.lon == last.lon && updated && updated + 300000 > d) {
			return;
		}

		// only send out a tracking post:
		// 	when the location is different and the time is over 1 minite
		//	when the location is the same and the time is over 5 minites

		locationService.track({
			lat: location.lat,
			lon: location.lon,
			accuracy: location.accuracy,
			timestamp: location.timestamp
		}, function(json) {
			updated = d;
			last = location;
			console.debug('Tracked drivers location: ', location, d);
		});
	};

	var interval = setInterval(track, 60000);

	watch();

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
			var circle = new google.maps.Circle( { center: latLong, radius: boundingRadius } );
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
		return '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' + to.lat + ',' + to.lon + '&zoom=' + zoom + '&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:R%7C' + from.lat + ',' + from.lon + '&markers=color:green%7Clabel:C%7C' + to.lat + ',' + to.lon + '&scale=2" class="map-image">'
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