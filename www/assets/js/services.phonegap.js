// Layout fixes
NGApp.factory( 'PhoneGapService', function( $route, $rootScope ){

	if( !App.isPhoneGap ){
		return {};
	}

	service = {
		isPhoneGap : App.isPhoneGap,
		isAndroid : App.isAndroid()
	};
	
	$rootScope.$on('$routeChangeSuccess', function ( $currentRoute, $previousRoute ) {
		service.routeChanged( $route.current.action );
	} );

	service.routeChanged = function( route ){

		if( App.isPhoneGap && !App.splashHidden && ( !route || route == 'home' ) ){
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
