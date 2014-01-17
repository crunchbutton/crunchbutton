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

		switch( route ){
			case 'location':
				alert( route );
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
