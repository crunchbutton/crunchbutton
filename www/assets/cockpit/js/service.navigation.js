// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, $rootScope, $route){

	// This variable will store the animation type.
	$rootScope.animationClass = '';

	var service = {
		page : '',
		buttons : {
			location: false,
			menu: true
		}
	};

	service.menu = App.toggleMenu;

	service.getFood = function( cartItems ){
		if( service.page == 'restaurant' &&
				cartItems &&
				service.restaurant && service.restaurant && service.restaurant._open &&
				!App.rootScope.notificationBarStatus ){
			angular.element( '.button-bottom-wrapper' ).addClass( 'button-bottom-show' );
		} else {
			angular.element( '.button-bottom-wrapper' ).removeClass( 'button-bottom-show' );
		}
	}

	service.home = function() {
		if (App.isPhoneGap && cordova) {
			$('html, body, .snap-content-inner').animate({scrollTop: 0}, 200, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
		} else if (App.isMobile()) {
			service.menu();
		} else {
			service.link();
		}
	}

	service.back = function(){
		history.go( -1 );
	}

	/* the transitions type could be push, fade, pop or none */
	service.link = function( path, transition ){
		if( App.isNarrowScreen() || App.transitionForDesktop ){
			App.rootScope.animationClass = transition ? 'animation-' + transition : '';
		}

		$location.search({});
		$location.path( path || '/' );
		if( App.snap && App.snap.close ){
			App.snap.close();
		}
	}

	return service;

});
