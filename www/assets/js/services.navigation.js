// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, $rootScope, $route, AccountModalService, RestaurantsService, LocationService, OrderViewService){

	// This variable will store the animation type.
	$rootScope.animationClass = '';

	var service = {
		page : '',
		buttons : {
			location: false,
			menu: true,
			back: false
		},
		navStack: []
	};

	service.menu = App.toggleMenu;
	service.modal = AccountModalService;

	service.getFood = function( cartItems ){
		if( service.page == 'restaurant' &&
				cartItems &&
				service.restaurant && service.restaurant && ( service.restaurant._open || service.restaurant.force_pre_order ) &&
				!App.rootScope.notificationBarStatus ){
			angular.element( '.button-bottom-wrapper' ).addClass( 'button-bottom-show' );
		} else {
			angular.element( '.button-bottom-wrapper' ).removeClass( 'button-bottom-show' );
		}
	}

	service.home = function() {
		if (App.isCordova && cordova) {
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

	/* the transitions type could be push, fade, pop or instant */
	service.link = function( path, transition , clearstack){

		// apply the css attribute first
		if (App.transitionAnimationEnabled && (App.isNarrowScreen() || App.transitionForDesktop)){
			App.rootScope.animationClass = transition ? 'animation-' + transition : '';
			App.rootScope.$safeApply();
		}

		// wait for the digest cycle to be complete and transtion the page outside the normal digest
		setTimeout( function(){

			if (path) {
				$location.path(path);
			} else {
				// to prevent the page to go to / and after that /food-delivery
				// it was reloading some stuff - and throwing facebook pixel error
				if (LocationService.position.pos().valid('restaurants')) {
					$location.path('/' + RestaurantsService.permalink);
				} else {
					$location.path( '/' );
				}
			}

			// manage back button stack
			if (clearstack) {
				service.navStack = [];
				service.control();
			}
			App.rootScope.$safeApply();

		}, 1);

		// close the side bar no matter what
		App.snap.close();
	}

	service.signin = function(){
		service.modal.signinOpen();
	}

	service.control = function() {
		service.buttons.location = service.page != 'location';
		if (service.page == 'location' || service.page == 'restaurants' || service.navStack.length < 2) {
			service.buttons.back = false;
			service.buttons.menu = true;
		} else {
			service.buttons.back = true;
			service.buttons.menu = false;
		}
	}

	return service;

});
