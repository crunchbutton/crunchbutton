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
		if (App.isCordova && cordova) {
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

	/* the transitions type could be push, fade, pop or instant */
	service.link = function( path, transition , clearstack){


		// apply the css attribute first
		if (App.isNarrowScreen() || App.transitionForDesktop){
			if (transition == 'pop') {
				$('body').addClass('back');
				setTimeout(function(){
					$('body').removeClass('back');
					$rootScope.$safeApply();
				},400);
			} else if (transition == 'instant') {
				$('body').addClass('instant');
				setTimeout(function(){
					$('body').removeClass('instant');
					$rootScope.$safeApply();
				},400);
			}
			App.rootScope.$safeApply();
		}

		// wait for the digest cycle to be complete and transtion the page outside the normal digest
		setTimeout( function(){
			$location.search({});
			$location.path(path || '/');
			App.rootScope.$safeApply();
		}, 1);

		// close the side bar no matter what
		if( App.snap && App.snap.close ){
			App.snap.close();
		}
	}

	return service;

});
