// Layout fixes
NGApp.factory( 'CordovaService', function( $route, $rootScope ){

	if( !App.isCordova ){
		return {};
	}

	service = {
		isCordova : App.isCordova,
		isAndroid : App.isAndroid()
	};

	$rootScope.$on('$routeChangeSuccess', function ( $currentRoute, $previousRoute ) {
		service.routeChanged( $route.current.action );
	} );

	service.routeChanged = function( route ){
		// hiden the keyboard accessory on the location page.
		if (App.isCordova && App.iOS() && navigator && navigator.keyboard) {
			if (route == 'location') {
				navigator.keyboard.hide();
			} else {
				navigator.keyboard.show();
			}
		}

		if( App.isCordova && !App.splashHidden && ( !route || route == 'home' ) ){
			//navigator.splashscreen.show();
		} else {
			setTimeout( function(){
				navigator.splashscreen.hide();
				App.splashHidden = true;
			}, 1000 );
		}


		switch( route ){
			case 'location':
				if( service.isAndroid ){
					// Fix the location background
					setTimeout( function(){
						$( '.home-top' ).css( 'background-position','top right' );
					}, 100 );

				}
				break;
		}
	}

	return service;
} );
