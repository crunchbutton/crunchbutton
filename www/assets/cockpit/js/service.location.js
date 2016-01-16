
// we have to be nice to the battery with both geolocation, and ajax requests

NGApp.factory('LocationService', function($http, $resource, $rootScope, AccountService) {

	if (App.isCordova && parent.window.plugins && parent.window.plugins.backgroundGeoLocation) {

		var bgGeo = parent.window.plugins.backgroundGeoLocation;

		var callbackFn = function(location) {
			console.debug('BackgroundGeoLocation callback:  ' + location.latitude + ',' + location.longitude);
			track(location, true);
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
				'X-Foo': 'BAR'
			},
			desiredAccuracy: 10,
			stationaryRadius: 20,
			distanceFilter: 30,
			notificationTitle: 'Background tracking',
			notificationText: 'ENABLED',
			activityType: 'AutomotiveNavigation',
			debug: false
		});

	}

	var locationService = $resource( App.service + 'driver/:action', { action: '@action' }, {
			'track' : { 'method': 'POST', params : { 'action' : 'location' } },
			'requested' : { 'method': 'POST', params : { 'action' : 'requested' } }
		}
	);

	var track = function(trackedPos, report) {
		// I added this line - it was asking the user to login when he was filling the onboarding/setup form @pererinha
		if (!$rootScope || !$rootScope.account || !$rootScope.account.user || !$rootScope.account.user.id_admin) {
			return;
		}

		// just track drivers
		if (!$rootScope.account.isDriver) {
			return;
		}

		// if we dont have a location
		if (!trackedPos || !trackedPos.latitude) {
			return;
		}

		var d = new Date;
		d = d.getTime();

		// if it has been less than 1 minite
//		if (updated && updated + 60000 > d) {
			//return;
//		}

		service.location = trackedPos;
		service.updated = d;

		if (report) {
			locationService.track(trackedPos, function(json) {
				console.debug('Tracked drivers location: ', trackedPos, d);
			});
		}
	};

	var service = {
		location: null,
		updated: null
	};

	var watcher = null;

	service.testLocation = function(){
		parent.window.navigator.geolocation.getCurrentPosition(
		function(p){console.log( 'ok', p );service.locationPermitted()},
		function(p){console.log( 'np', p );service.locationDenied()},
		{
					enableHighAccuracy: false,
					timeout: 5000,
					maximumAge: 3600000
				}
		 )
	}

	service.locationPermitted = function(){
		locationService.requested( { 'permitted': true }, function(){} );
	}

	service.locationDenied = function(){
		locationService.requested( { 'permitted': false }, function(){} );
	}

	service.register = function(complete) {
		parent.window.navigator.geolocation.getCurrentPosition(function(pos) {
			complete();
			service.locationPermitted();
		}, function() {
			App.alert('Please enable location services for Cockpit in <b>Settings &gt; Privacy &gt; Location Services &gt; Cockpit</b>. Your location will only be tracked while you are on shift.')
			complete();
			service.locationDenied();
		}, {
			enableHighAccuracy: false,
			timeout: 5000,
			maximumAge: 3600000
		});
	};

	var startWatch = function() {
		if (watcher) {
			return;
		}

		var webLocationTrack = function(pos) {
			var trackedPos = pos.coords;
			trackedPos.timestamp = pos.timestamp;
			console.debug('Got foreground drivers location: ', trackedPos, Math.random());
			$rootScope.$broadcast('location', trackedPos);
			track(trackedPos, false);
			service.locationPermitted();
		};

		if (!bgGeo && parent.window.navigator.geolocation) {
			watcher = parent.window.navigator.geolocation.watchPosition( webLocationTrack, function() {
				//alert('Your location services are off, or you declined location permissions. Please enable this.');
				service.locationDenied()
			}, { enableHighAccuracy: true });
		}
		if (App.isCordova && bgGeo) {
			watcher = true;
			parent.window.navigator.geolocation.getCurrentPosition(function(pos) {
				webLocationTrack(pos);
				bgGeo.start();
				service.locationPermitted();
			}, function() {
				App.alert('Please enable location services for Cockpit in <b>Settings &gt; Privacy &gt; Location Services</b>. Your location will only be tracked while you are on shift.')
				service.locationDenied();
			});

		}
	};

	// check to make sure we are sending the location
	var checkWatch = function() {
		var d = new Date;
		d = d.getTime();

		if (service.updated && service.updated + 300000 > d) {
			stopWatch();
			startWatch();
		}
	};

	setInterval(checkWatch, 100000);

	var stopWatch = function() {
		parent.window.navigator.geolocation.clearWatch(watcher);
		watcher = null;

		if (App.isCordova && bgGeo) {
			bgGeo.stop();
		}
	}
/*
	$rootScope.$on('userAuth', function(e, data) {
		// start watching if there is a user and their docs are filled out
		if (data && data.id_admin) {
			startWatch();
		} else {
			stopWatch();
		}
	});
	*/

	$rootScope.$watch('account.user.working', function(value) {
		console.debug('Got a change in user working:', arguments);

		if (value && AccountService.isDriver && App.isMobile()) {
			console.debug('Starting tracking because user is working and a driver.');
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