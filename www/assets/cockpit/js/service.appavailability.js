NGApp.factory( 'AppAvailabilityService', function() {

	var service = {};

	var schemes = { maps: {} };
	schemes.maps.waze = {
		scheme: function(){
						if( App.isCordova ){
							if( App.iOS() ){ return 'waze://'; }
							if( App.isAndroid() ){ return 'com.waze'; }
						}
						return false;
					},
		title: 'Waze',
		url: 'waze://'
	}

	schemes.maps.google = {
		scheme: function(){
						if( App.isCordova ){
							if( App.iOS() ){ return 'comgooglemaps://'; }
							if( App.isAndroid() ){ return true; }
						}
						return false;
					},
		title: 'Google Maps',
		url: 'comgooglemaps://'
	}

	schemes.maps.apple = {
		scheme: function(){
						if( App.isCordova ){
							if( App.iOS() ){ return true; }
							if( App.isAndroid() ){ return false; }
						}
						return false;
					},
		title: 'Apple Maps',
		url: 'maps://'
	}


	var checkAvailability = function( scheme, success, error ){
		if( parent.window && parent.window.appAvailability ){
			if( scheme === true ){ success(); }
			if( !scheme ){ error(); }
			parent.window.appAvailability.check(
				scheme,
				function() { if( success ){ success(); } },
				function() { if( error ){ error(); } } );
		}
		if( error ){ error(); }
	};

	var start = function(){
		// check all apps
		angular.forEach( schemes, function( kind, name ) {
			angular.forEach( kind, function( app, key ) {
				var key = key;
				var success = function(){
					if( !service[ name ] ){
						service[ name ] = {};
					}
					service[ name ][ key ] = { url: app.url, title: app.title };
				}
				var error = function(){};
				checkAvailability( app.scheme(), success, error );
			} );
		} );
	}

	setTimeout( function(){ start() }, 3000 );

	return service;
});