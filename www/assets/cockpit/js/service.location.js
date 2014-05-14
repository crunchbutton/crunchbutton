
// we have to be nice to the battery with both geolocation, and ajax requests

NGApp.factory('LocationService', function($http, $resource) {

	var service = {
		location: function() {
			return location;
		},
		updated: function() {
			return updated;
		}
	};
	
	var location = {
		lat: null,
		lon: null,
		accuracy: null,
		timestamp: null
	};
	
	var watcher, last, updated = null;

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
				setTimeout(trackStop, 5000);
			}, function() {
				console.debug('Could not locate driver');
			}, { enableHighAccuracy: true });
		}
	};
	
	var trackStop = function() {
		if (!watcher) {
			return;
		}
		navigator.geolocation.clearWatch(watcher);
		watcher = null;
		track();
		// reactivate tracking every 2 minites 
		setTimeout(watch, 60000 * 2);
	};
	
	var track = function() {
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