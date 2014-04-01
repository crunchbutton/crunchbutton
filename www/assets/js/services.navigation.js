// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, $rootScope, $route, AccountModalService, RestaurantsService, OrderViewService){ 
	
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
	service.modal = AccountModalService;

	service.home = function() {
		if (App.isPhoneGap && cordova) {
			$('html, body, .snap-content-inner').animate({scrollTop: 0}, 200, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
		} else if (App.isMobile()) {
			service.menu();
		} else {
			service.link();
		}
	}

	service.goToRestaurants = function(){
		return service.link( RestaurantsService.permalink );
	}

	/* the transitions type could be push, fade, pop or none */
	service.link = function( path, transition ){
		if( App.isNarrowScreen() || App.transitionForDesktop ){
			App.rootScope.animationClass = transition ? 'animation-' + transition : '';
		}
		$location.path( path || '/' );
		App.snap.close();
	}

	service.signin = function(){
		service.modal.signinOpen();
	}

	service.control = function() {
		switch (service.page) {
			case 'restaurant':
			case 'order':
				service.buttons.location = true;
				service.buttons.back = true;
				service.buttons.menu = false;
				if( OrderViewService.newOrder ){
					service.buttons.back = false;
					service.buttons.menu = true;
				}

				break;
			default:
				service.buttons.back = false;
				service.buttons.location = ( service.page != 'location' );
				service.buttons.menu = true;


				break;
		}
	}

	return service;

});
