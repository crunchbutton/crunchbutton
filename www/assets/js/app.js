/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */

var REDIRECT = false;
var COMMUNITY_DIMENSION = 'dimension1';

if (top.frames.length != 0 || window != top || top.location != location) {
	top.location.href = location.href;
	top.location = self.document.location;
	REDIRECT = true;
}

var App = {
	version: 'web',
	tagline: '',
	service: '/api/',
	logService: 'https://log.crunchbutton.com/api/',
	server: '/',
	imgServer: '/',
	cached: {},
	community: null,
	config: null,
	_init: false,
	_pageInit: false,
	_identified: false,
	touchX: null,
	touchY: null,
	touchOffset: null,
	localStorage: false,
	isPhoneGap: (document.location.protocol == 'file:' || document.location.host == 'localhost:12344'),
	useNativeAlert: false,
	useNativeConfirm: true,
	ajaxTimeout: 5000,
	splashHidden: false,
	parallax: {
		bg: null,
		x: 0,
		y: 0,
		enabled: false
	},
	restaurantsPaging: {
		enabled: true,
		desktop: 20,
		mobile: 6
	},
	transitionAnimationEnabled : true,
	transitionForDesktop : false,
	cachedObjectsExpiresIn : 86400, // 86400 seconds is 24 hours
	enableSplash: true,
	useTransform: true,
	minimalMode: false,
	hasFacebook: false,
	hasApplePay: false
};

App.useTransform = true;

// enable localstorage on phonegap
App.localStorage = App.isPhoneGap;

App.setLoggedIn = function(loggedIn) {
	if ($('.is-ui2').get(0) && !loggedIn && App.isPhoneGap && App.enableSplash) {
		var goToSplash = true;
		if( localStorage && localStorage.locsv3 ){
			var locsv3 = JSON.parse( localStorage.locsv3 );
			if( locsv3 && locsv3.length > 0 ){
				goToSplash = false;
			}
		}
		if( goToSplash ){
			setTimeout( function(){ App.go( '/splash' ); }, 10 );
		}
	}
};

App.NGinit = function() {
	$('body').attr('ng-controller', 'AppController');
	angular.bootstrap(document,['NGApp']);
	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
};

var modules = [ 'ngRoute', 'ngResource', 'ngMask' ];
if (!App.minimalMode) {
	modules.push('ngAnimate');
}

var NGApp = angular.module('NGApp', modules);

NGApp.config(function($compileProvider){
	$compileProvider.aHrefSanitizationWhitelist(/.*/);
});

NGApp.factory('errorInterceptor', function($q) {
	var errorFromResponse = function(response) {
		var headers;
		if (response.headers) {
			headers = response.headers();
		}
		if (headers && headers['php-fatal-error']) {
			console.error(headers['php-fatal-error']);
			App.alert('There was an error connecting to the server. Please try again, or contact support if it continues to be a problem.');
			return false;
		}
		return true;
	};
	var errorInterceptor = {
		responseError: function(response) {
			errorFromResponse(response);
			return $q.reject(response);
		},
		response: function(response) {
			if (!errorFromResponse(response)) {
				return $q.reject(response);
			} else {
/*
				if (response.headers) {
					var headers = response.headers();
					if (headers && headers['App-Token']) {
						$.totalStorage('token', headers['App-Token']);
					}
					console.debug('RESPONSE',headers);
				} else {
					console.debug('No headers');
				}
				*/

				return response;
			}
		},
		request: function(config) {
			config.data = config.data || {};

			config.params = config.params || {};
			config.headers['App-Version'] = App.version;

			if (App.version != 'web' && $.totalStorage('token')) {
				config.headers['App-Token'] = $.totalStorage('token');
			}

			return config || $q.when(config);
		}
	};
	return errorInterceptor;
});
NGApp.config(['$httpProvider', function($httpProvider) {
	$httpProvider.defaults.headers.common['Http-Error'] = 1;
	$httpProvider.interceptors.push('errorInterceptor');
	//$httpProvider.defaults.withCredentials = true;
}]);

NGApp.run(function() {
	FastClick.attach(document.body);
} );

// This config will intercept all the ajax requests and take care of the errors
NGApp.config( function( $provide, $httpProvider ) {
	$provide.factory( 'httpInterceptor', function( $q ) {
		var onError = function( rejection ){
			var status = rejection.status;
			// Is offline or the server wasn't found
			if( !window.navigator.onLine || status == 0 ){
				var showError = false;
				var url = rejection.config.url;

				if ( url ) {
					console.log('AJAX ERROR: ', rejection );
					// Check if the url was an api url
					if( url.indexOf( App.service ) >= 0 ){
						var api = url.split( App.service );
						if( api.length > 0 ){
							// Get the api endpoint
							api = api[1];
							var showErrorFor = [ 'order', 'restaurant', 'user' ];
							for( var i = 0; i < showErrorFor.length; i++ ){
								if( api.indexOf( showErrorFor[i] ) >= 0 ){
									showError = true;
									break;
								}
							}
						}
					}
				} else {
					showError = true;
				}
				if( showError && !$( '.connection-error' ).is( ':visible' ) ){
					App.connectionError();
					if ( App.busy.isBusy() ) {
						App.busy.unBusy();
					}
				}
			}
		}

		return {
			// request ok
			request: function( config ) {
				return config || $q.when( config );
			},
			// response ok
 			response: function( response ) {

 				if( typeof response.data == 'object' ){
 					var headers = response.headers();
 				}

				return response || $q.when( response );
			},
			// request no ok
			requestError: function( rejection ) {
				onError( rejection );
				return $q.reject( rejection );
			},
			// response no ok
			responseError: function ( rejection ) {
				onError( rejection );
				return $q.reject( rejection );
			}
		};
	});
	$httpProvider.interceptors.push( 'httpInterceptor' );
} );


NGApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider, RestaurantsService) {
	$routeProvider
		.when('/location', {
			action: 'location',
			controller: 'LocationCtrl',
			templateUrl: 'assets/view/location.html'
		})
		.when('/delivery-signup', {
			action: 'delivery-signup',
			controller: 'DeliverySignUpCtrl',
			templateUrl: 'assets/view/delivery.signup.html'
		})
		.when('/location/unavailable', {
			action: 'location',
			controller: 'LocationUnavailableCtrl',
			templateUrl: 'assets/view/location.unavailable.html'
		})
		.when('/splash', {
			action: 'splash',
			controller: 'SplashCtrl',
			templateUrl: 'assets/view/splash.html'
		})
		.when('/download', {
			action: 'download',
			controller: 'DownloadCtrl',
			templateUrl: 'assets/view/download.html'
		})
		.when('/food-delivery', {
			action: 'restaurants',
			controller: 'RestaurantsCtrl',
			templateUrl: 'assets/view/restaurants.html'
		})
		.when('/food-delivery/:id', {
			action: 'restaurant',
			controller: 'RestaurantCtrl',
			templateUrl: 'assets/view/restaurant.html'
		})
		.when('/legal', {
			action: 'legal',
			controller: 'LegalCtrl',
			templateUrl: 'assets/view/legal.html'
		})
		.when('/help', {
			action: 'help',
			controller: 'HelpCtrl',
			templateUrl: 'assets/view/help.html'
		})
		.when('/free-food', {
			action: 'free-food',
			controller: 'FreeFoodCtrl',
			templateUrl: 'assets/view/free-food.html'
		})
		.when('/about', {
			action: 'about',
			controller: 'AboutCtrl',
			templateUrl: 'assets/view/about.html'
		})
		.when('/work', {
			action: 'work',
			controller: 'WorkCtrl',
			templateUrl: 'assets/view/work.html'
		})
		.when('/drivers/apply', {
			action: 'apply',
			controller: 'ApplyCtrl',
			templateUrl: 'assets/view/drivers.apply.html'
		})
 		.when('/reps/apply', {
			action: 'reps-apply',
			controller: 'RepsApplyCtrl',
			templateUrl: 'assets/view/reps.apply.html'
		})
		.when('/reps/apply/:id', {
			action: 'reps-apply',
			controller: 'RepsApplyShareCtrl',
			templateUrl: 'assets/view/reps.apply.share.html'
		})
		.when('/thankyou', {
			action: 'thankyou',
			controller: 'ThankyouCtrl',
			templateUrl: 'assets/view/thankyou.html'
		})
		.when('/owners', {
			action: 'owners',
			controller: 'OwnersCtrl',
			templateUrl: 'assets/view/owners.html'
		})
		.when('/profile', {
			action: 'profile',
			controller: 'ProfileCtrl',
			templateUrl: 'assets/view/profile.html'
		})
		.when('/orders', {
			action: 'orders',
			controller: 'OrdersCtrl',
			templateUrl: 'assets/view/orders.html'
		})
		.when('/order/:id', {
			action: 'order',
			controller: 'OrderCtrl',
			templateUrl: 'assets/view/order.html'
		})
		.when('/order/:id/:action', {
			action: 'order',
			controller: 'OrderCtrl',
			templateUrl: 'assets/view/order.confirm.html'
		})
		.when('/cities', {
			action: 'cities',
			controller: 'CitiesCtrl',
			templateUrl: 'assets/view/cities.html'
		})
		.when('/giftcard', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/giftcard/:id', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/gift', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/gift/:id', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/invite', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/invite/:id', {
			action: 'giftcard',
			controller: 'GiftcardCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/reset', {
			action: 'reset',
			controller: 'AccountResetCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/reset/:id', {
			action: 'reset',
			controller: 'AccountResetCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/', {
			action: 'home',
			controller: 'HomeCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/cafe', {
			action: 'cafe',
			controller: 'CafeCtrl',
			templateUrl: 'assets/view/cafe.html'
		})
		.otherwise({
			action: 'home',
			controller: 'DefaultCtrl',
			templateUrl: 'assets/view/home.html'
		})
	;
	// only use html5 enabled location stuff if its not in a phonegap container
	$locationProvider.html5Mode({
		enabled: !App.isPhoneGap,
		requireBase: false
	});
}]);

// global route change items
NGApp.controller('AppController', function ($scope, $route, $http, $routeParams, $rootScope, $location, $window, AccountService, MainNavigationService, AccountSignOut, CartService, ReferralService, LocationService, PhoneGapService, PushService, RestaurantService, RestaurantsService) {
	// define external pointers
	App.rootScope = $rootScope;
	App.location = $location;
	App.http = $http;
	App.push = PushService;
	$rootScope.hasFacebook = App.hasFacebook;
	$rootScope.topCommunities = App.topCommunities;
	var hello = 'moc.nottubhcnurc@olleh'.split('').reverse().join('');
	$scope.hello = hello;
	var textUs = '4441387646'.split('').reverse().join('');
	$scope.textUs = textUs;
	var textUsFormatted = '4441-387 )646('.split('').reverse().join('');
	$scope.textUsFormatted = textUsFormatted;
	$rootScope.config = App.config.site;

	var trackWithCommunity = function(action){
		if(action=='restaurants' && RestaurantsService && RestaurantsService.community && RestaurantsService.community.id_community ){
			App.track('page', action, undefined, RestaurantsService.community.id_community);
		} else if (action=='restaurant' && RestaurantService && RestaurantService.id_community){
			App.track('page', action, undefined, RestaurantService.id_community);
		} else if (action=='order' && RestaurantService && RestaurantService.id_community){
			App.track('page', action, undefined, RestaurantService.id_community);
		}
		else {
			App.track('page', action);
		}
	}

	// hack to fix the phonegap bug at android with soft keyboard #2908
	$rootScope.softKeyboard = function( e ){
		if( App.isPhoneGap && App.isAndroid() ){
			var el = $( e.currentTarget );
			var walkTo = ( $('.snap-content-inner').scrollTop() + el.offset().top - ( $( window ).height() / 2 ) + ( el.height() + 55 ) );
			$( 'html, body, .snap-content-inner' ).animate( { scrollTop: walkTo }, '500');
		}
	}

	$rootScope.softKeyboardExit = function() {
		$('.nav-top').removeClass('at-top');
	};

	// hack to fix the phonegap bug at android with soft keyboard #2908 - input at modals
	$rootScope.softKeyboardModal = function( e ){
		if( App.isPhoneGap && App.isAndroid() ){
			var el = $( '.mfp-content' );
			if( el.css( 'marginTop' ) == '0px' ){
				var walkTo = el.offset().top + 20;
				el.animate( { marginTop: -walkTo }, '500');
			}
		}
	}

	// define global services
	$rootScope.account = AccountService;
	$rootScope.navigation = MainNavigationService;
	$rootScope.signout = AccountSignOut;
	$rootScope.isPhoneGap = App.isPhoneGap;
	$rootScope.server = App.server;

	$rootScope.animationClass = '';

	$rootScope.debug = function() {
		return ( App.config && App.config.user && App.config.user.debug == '1' );
	};

	$rootScope.test = App.test;

	$rootScope.cartScroll = function(permalink) {
		//$('.snap-content-inner').scrollTop() + $('.cart-items').offset().top
		var top = 130 - $('.navs').height() - 10;


		var scroll = function() {
			$('html, body, .snap-content-inner').animate({scrollTop: top}, 100, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
		};
		if ($rootScope.navigation.page != 'restaurant') {
			$rootScope.scrollTop = top;
			$rootScope.navigation.link('/food-delivery/' + permalink, 'instant');
		} else {
			scroll();
		}
	};

	$rootScope.scrollHalf = function(permalink) {
		$('html, body, .snap-content-inner').animate({
			scrollTop: 530 - $('.navs').height() - 10
		}, 100, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	};

	$rootScope.cancelDownload = function() {
		$.totalStorage('_viewmobile2', true, { expires: 1 });
		$rootScope.navigation.link('/location', 'instant');
	};

	$rootScope.$on('userAuth', function(e, data) {

		ReferralService.getStatus();

		$rootScope.$safeApply(function($scope) {
			// @todo: remove double data
			if (data) {
				$rootScope.account.user = data;
				if (App.isPhoneGap) {
					$.totalStorage('token', data.token);
				}
				$rootScope.$broadcast( 'haveUser', $rootScope.account.user );
				App.config.user = data;
			}
			// If the user logged out clean the cart!
			if( !App.config.user.id_user ){
				if (App.isPhoneGap) {
					$.totalStorage('token', null);
				}
				CartService.clean();
			}

			LocationService.init(true);
			if (App.config.user.id_user && App.config.user.location_lat && ($rootScope.navigation.page == 'location' || $rootScope.navigation.page == 'splash')) {
				if (App._handOpenUrlNav) {
					App._handOpenUrlNav = false;
				} else {
					$location.path('/food-delivery');
				}
			}
			// Some new users doesn't have lat nor lon - #5442
			else if (App.config.user.id_user && !App.config.user.location_lat && $rootScope.navigation.page == 'splash') {
				if (App._handOpenUrlNav) {
					App._handOpenUrlNav = false;
				} else {
					$location.path('/location');
				}
				AccountService.forceDontReloadAfterAuth = true;
			}

			App.snap.close();

			// reload the actual controller
			if( !AccountService.forceDontReloadAfterAuth ){
				$rootScope.reload();
			}
			AccountService.forceDontReloadAfterAuth = false;

		});

	});

	setTimeout( function(){
		ReferralService.getStatus();
		ReferralService.newReferredUsersByUser();
	}, 4000 );

	$rootScope.focus = function( selector ){
		setTimeout(function(){
			angular.element( selector ).focus();
		}, 100);
	}

	$rootScope.blur = function( selector ){
		setTimeout(function(){
			angular.element( selector ).blur();
		}, 100);
	}

	/* @info: this is how you watch an object rather than a property so i remeber
	$rootScope.$watch('account.user', function() {
		// indicates that the user object has changed
	}, true);
	*/

	$rootScope.reload = function() {
		$route.reload();
	};

	$rootScope.link = function(link) {
		$rootScope.navigation.link.apply(arguments);
	};

	$rootScope.back = function() {
		App.snap.close();
		var backwards = false;
		switch( $route.current.action ) {
			case 'restaurant':
				backwards = '/food-delivery';
				break;
			case 'restaurants':
				backwards = '/location';
				break;
		}

		if (!backwards && MainNavigationService.navStack.length > 1) {
			MainNavigationService.navStack.pop();
			backwards = MainNavigationService.navStack.pop();
			console.log('setting to', backwards);
		}
		if (backwards) {
			console.log('going to', backwards);
			$rootScope.navigation.link(backwards, 'pop');
		} else {
			console.log('going back failed');
			$rootScope.navigation.link('/location', 'pop');
			//history.back();
		}
	};

	$rootScope.closePopup = function() {
		try {
			$.magnificPopup.close();
		} catch (e) {}
	};

	$rootScope.$safeApply = function(fn) {
		if (!this.$root) {
			return;
		}
		var phase = this.$root.$$phase;
		if (phase == '$apply' || phase == '$digest') {
			if (fn && (typeof(fn) === 'function')) {
				this.$eval(fn);
			}
		} else {
			this.$apply(fn);
		}
	};

	// @todo: we might need this in the future for when we update to angular 1.2 with animations
	/*
	// determine if we are going backwards
	$rootScope.$on('$locationChangeSuccess', function() {
		$rootScope.actualLocation = $location.path();
	});

	$rootScope.$watch(function() {
		return $location.path();
	}, function (newLocation, oldLocation) {
		if ($rootScope.actualLocation === newLocation) {
			// this is backwards
		}
	});
	*/

	$scope.$on('$routeChangeSuccess', function ($event, $currentRoute, $previousRoute) {
		// Store the actual page
		MainNavigationService.page = $route.current.action;
		App.page = MainNavigationService.page;
		App.rootScope.current = MainNavigationService.page;
		if ( $route.current.action === 'restaurants' ) {
			if (!RestaurantsService || !RestaurantsService.community) {
				setTimeout(function(){trackWithCommunity($route.current.action);}, 1500);
			} else{
				trackWithCommunity($route.current.action);
			}
			if (RestaurantsService && RestaurantsService.community) {
				App._trackingCommunity = RestaurantsService.community.id_community;
			}
		} else if ($route.current.action === 'restaurant') {
			if (!RestaurantService || !RestaurantService.id_community) {
				setTimeout(function(){trackWithCommunity($route.current.action);}, 1500);
			} else{
				trackWithCommunity($route.current.action);
			}
			if (RestaurantService && RestaurantService.id_community) {
				App._trackingCommunity = RestaurantService.id_community;
			}
		} else if ($route.current.action === 'order') {
			if ($routeParams.action && $routeParams.action === 'confirm') {
				var isNotRefresh = true;
				if (!$previousRoute || $previousRoute.params.action === 'confirm'){
					isNotRefresh = false;
				}
				if (isNotRefresh) {
					if (!RestaurantService || !RestaurantService.id_community) {
						setTimeout(function () {
							trackWithCommunity($route.current.action);
						}, 1500);
					} else {
						trackWithCommunity($route.current.action);
					}
					if (RestaurantService && RestaurantService.id_community) {
						App._trackingCommunity = RestaurantService.id_community;
					}
				}
			} else {
				trackWithCommunity("orderCheck");
			}
		} else {
			App.track('page', $route.current.action, undefined);
		}
		if ($route.current.$$route && $route.current.$$route.originalPath) {
			MainNavigationService.navStack.push($route.current.$$route.originalPath);
		}

		if (App.isPhoneGap) {
			if (cordova && cordova.plugins) {
				if (cordova.plugins.Keyboard) {
					cordova.plugins.Keyboard.hideKeyboardAccessoryBar(MainNavigationService.page == 'restaurant' || MainNavigationService.page == 'apply' ? false : true);
					//cordova.plugins.Keyboard.disableScroll(true);
				}
			}
		}


		$('body').removeClass(function (index, css) {
			return (css.match (/\bpage-\S+/g) || []).join(' ');
		}).addClass('page-' + MainNavigationService.page);

		$('.nav-top').addClass('at-top');

		App.parallax.bg = null;

		$rootScope.scrollTop = 0;
		App.scrollTop($rootScope.scrollTop);


	});

	// Make the window's size available to all scope
	$rootScope.windowWidth = $('body').width();
	$rootScope.windowHeight = $('body').height();

	// Window resize event
	angular.element( $window ).bind( 'resize',function(){
		$rootScope.windowWidth = $window.outerWidth;
		$rootScope.windowHeight = $window.outerHeight;
		$rootScope.$apply( 'windowWidth' );
		$rootScope.$apply( 'windowHeight' );
		isMobile();
	});

	$rootScope.isMobileWidth = false;

	var isMobile = function(){
		if( $rootScope.windowWidth <= 1024 ){
			$rootScope.isMobileWidth = true;
		} else {
			$rootScope.isMobileWidth = false;
		}
	}

	isMobile();

	$scope.hasLocations = function(){
		return ( LocationService.position.hasValidLocation() );
	}

	AccountService.checkUser();

	LocationService.init();

	ReferralService.check();

	if( App.config.user.id_user ){
		ReferralService.getStatus();
	}

});

App.alert = function(txt, title, useNativeAlert, fn, good_news) {
	setTimeout(function() {
		if (useNativeAlert && App.isPhoneGap) {
			navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else if ( useNativeAlert ) {
			alert( txt );
		} else {
			App.rootScope.$broadcast('notificationAlert', title || 'Woops!', txt, fn, good_news);
		}
	});
};
App.remoteNotification = function(txt, title, fn) {
	setTimeout(function() {
		App.rootScope.$broadcast('notificationRemote', title || '', txt, fn);
	});
};

App.confirm = function(txt, title, fn, buttons) {
	if (App.useNativeConfirm && App.isPhoneGap) {
		return navigator.notification.confirm(txt, fn, title || 'Crunchbutton', buttons || 'Ok,Cancel' );
	} else {
		return confirm(txt);
	}
};


App.connectionError = function() {
	App.rootScope.$broadcast( 'connectionError' );
	App.rootScope.$broadcast('notificationAlert', 'Connection Error', 'Sorry! We could not reach the server right now. Try again when your internet is back!');
};

App.go = function( url, transition ){
	App.rootScope.navigation.link(url, transition, false);
};

App.toggleMenu = function() {
	if (App.snap.state().state == 'left') {
		App.snap.close();
	} else {
		App.snap.open('left');
	}
};


/*
App.setTop = function() {
	$('html, body, .snap-content-inner').scrollTop(0);
	setTimeout(function() {
		$('html, body, .snap-content-inner').scrollTop(0);
	},13);


	$('#ng-view').css('-webkit-transform','translate3d(0,0,0)');
	setTimeout(function() {
		$('#ng-view').css('-webkit-transform','');
	},1000);


};
*/


/**
 * scroll to the top of the page
 */
App.scrollTop = function(top) {
	setTimeout(function() {
		if (!top) {
			setTimeout(function() {
				$('html, body, .snap-content-inner').scrollTop(0);
			},0);
		}
		$('html, body, .snap-content-inner').animate({scrollTop: top || 0}, 10, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	},1);
};


/**
 * Sends a tracking item to google, or to google ads if its an order
 */
App.track = function() {

	// return if we arent talking to live
	if (location.host != 'crunchbutton.com' || (location.host == 'localhost:12344' && App.service != 'https://crunchbutton.com/api/') || App.config.env != 'live') {
		return;
	}

	var event_uri = App.logService + 'events?category=app&action=' + encodeURIComponent(arguments[0]);
	var data = undefined;
	var future;
	if(typeof arguments[1] == 'string') {
		event_uri = event_uri + '&label=' + arguments[1];
		data = arguments[2];
	} else {
		data = arguments[1];
	}
	var trackingCommunity = undefined;
	if(App._trackingCommunity) {
		event_uri = event_uri + '&community=' + App._trackingCommunity;
		trackingCommunity = App._trackingCommunity;
	}
	if(data) {
		future = $.post(event_uri, data)
	} else {
		future = $.post(event_uri);
	}
	future.done(function (resp) { console.log('stored event', resp)})
		  .fail(function (jqXHR, textStatus, errorThrown) { console.log('ERROR STORING EVENT', errorThrown, textStatus)});
	if (arguments[0] == 'Ordered') {
		/*
		if (window._fbq) {
			window._fbq.push(['track', '6025866422535', {'value':arguments[1].total,'currency':'USD'}]);
		}
		*/
		// Testing #6542
		if( fbq ){
			fbq( 'track', 'Purchase', { value: arguments[1].total, currency: 'USD' } );
		}


		if (typeof( ga ) == 'function') {
			var trans = {
				id: arguments[1].id,
				affiliation: 'Crunchbutton',
				revenue: arguments[1].total,
				tax: arguments[1].tax
			};
			ga('ecommerce:addTransaction', trans);


			for (var x in arguments[1].cart) {
				var ii = {
					id: arguments[1].id,
					name: arguments[1].cart[x].details.name,
					sku: arguments[1].cart[x].id,
					category: arguments[1].restaurant,
					price: App.cached['Dish'][arguments[1].cart[x].id].price,
					quantity: '1'
				};
				ga('ecommerce:addItem', ii);
			}

			ga('ecommerce:send');
		}


		$('img.conversion').remove();
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + location.href + '">').appendTo($('body'));
		return;
	}

	if (typeof( ga ) == 'function') {
		if (typeof arguments[1] == 'String' && (typeof arguments[3] == 'Number' || typeof arguments[3] == 'String')) {
			ga('send', 'event', 'app', arguments[0], arguments[1], String(arguments[3]));
		} else if (typeof arguments[1] != 'string' && typeof arguments[3] == 'String' &&
			(typeof arguments[4] == 'Number' || typeof arguments[4] == 'String')) {
			ga('send', 'event', 'app', arguments[0], arguments[3], String(arguments[4]));
		}
		else if (typeof arguments[1] == 'string' ) {
			if (trackingCommunity) {
				ga('send', 'event', 'app', arguments[0], arguments[1], trackingCommunity);
			} else{
				ga('send', 'event', 'app', arguments[0], arguments[1]);
			}
		} else {
			if (trackingCommunity) {
				ga('send', 'event', 'app', arguments[0], undefined, trackingCommunity);
			} else{
				ga('send', 'event', 'app', arguments[0]);
			}
		}
	}
};

/**
* sets the user's community on Google Analytics so we can segment on community.
* will not raise or error on invalid communities.
* @param id_community - Integer or String (that is a valid integer)
*/
App.trackCommunity = function (id_community) {

	if(!isNaN(parseInt(id_community))) {
		try {
			var community_name = App.community_name_by_id[id_community];
			App._trackingCommunity = id_community.toString();
			if (App.config.env != 'live') {
				return;
			}
			if (typeof( ga ) == 'function')  {
				ga('set', COMMUNITY_DIMENSION, community_name);
			}
		} catch(e) {
			console.log('ERROR track community: ', e);
		}
	} else {
		console.log('could not parse community: ', id_community);
	}

}


/**
 * controls the busy state of the app
 * @sky-loader
 */
App.busy = {
	_busy: false,
	_timer: null,
	_maxExec: 35000,
	stage: function() {
		$('#Stage').height('100%').width('100%');
		return AdobeEdge.getComposition('EDGE-977700350').getStage();
	},
	isBusy: function() {
		return $('.app-busy').length ? true : false;
		return App.busy._busy;
	},
	makeBusy: function( gid ) {
		if (App.busy._timer) {
			clearTimeout(App.busy._timer);
		}
		App.busy._timer = setTimeout(function() {
			var errorMessage = function(){
				App.alert('The app timed out processing your order. We can not determine if the order was placed or not. Please check your previous orders. Sorry!');
				App.busy.unBusy();
			};
			if( gid ){
				App.request( App.service + 'order/gid/' + gid,
					function( json ){
						if( json.error ){
							errorMessage();
						}
					}, function(){ errorMessage(); } );
			}
		}, App.busy._maxExec );
		return $('body').append($('<div class="app-busy"></div>').append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>')));
		App.busy._busy = true;
		$('.order-sky-loader').addClass('play');
		App.busy.stage().play(0);
	},
	unBusy: function() {
		clearTimeout(App.busy._timer);
		return $('.app-busy').remove();
		App.busy._busy = false;
		$('.order-sky-loader').removeClass('play');
		App.busy.stage().stop();
	}
};

/**
 * stuff for testing
 */
App.test = {
	card: function() {
		angular.element( 'html' ).injector().get( 'OrderService' )._test();
	},
	logout: function() {
		$.getJSON(App.service + 'logout',function(){ location.reload()});
	},
	cart: function() {
		App.alert(JSON.stringify(App.cart.items));
	},
	clearloc: function() {
		$.totalStorage('community',null);
		$.totalStorage('location_lat',null);
		$.totalStorage('location_lon',null);
		location.href = '/';
	},
	reload: function() {
		location.reload();
	},
	location: function(){
		var position = angular.element( 'html' ).injector().get( 'PositionsService' );
		var locs = position.locs;
		for( x in locs ){
			var values = [];
			$.each( locs[ x ]._properties, function( key, value ) {
				values.push( key );
				values.push( ': ' );
				values.push( ( value || '-' ) );
				values.push( ' | ' );
				if( key == 'results' ){
					$.each( value, function( position, result ) {
						console.log('result: ' + position,result);
					});
				}
			});
			console.log('Position: ' + x, values.join( '' ) );
		}
	}
};

App.processConfig = function(json, user) {
	if (user && !json) {
		App.config.user = user;
	} else {
		App.config = json;
	}
	App.setLoggedIn( App.config && App.config.user && App.config.user.uuid ? true : false);
	App.AB.init();
	// grab community if we have it (we'll overwrite it if the user searches for something different)
	if(App.config.user && App.config.user.last_order && App.config.user.last_order.communities) {
		if(App.config.user.last_order.communities.length >= 1) {
			App.trackCommunity(App.config.user.last_order.communities[0]);
		}
	}
};

/**
 * global event binding and init
 */
App.init = function(config) {
	// ensure this function cant be called twice. can crash the browser if it does.
	if (App._init || REDIRECT) {
		return;
	}

	App._init = true;

	// Check if the device is online or offline
	App.verifyConnection.init();
	App.phoneGapListener.init();

	$(document).on('touchmove', ($('.is-ui2').get(0) ? '.mfp-wrap' : '.snap-drawers, .mfp-wrap, .support-container'), function(e) {
		e.preventDefault();
		e.stopPropagation();
	});

	$(document).on({
		'DOMNodeInserted': function() {
			$('.pac-item, .pac-item span', this).addClass('needsclick');
		}
	}, '.pac-container');

	$(document).on('mousedown', '.pac-item', function() {
		$('body').scrollTop(0);
		$('html').css('height','3000px');
		setTimeout(function() {
			$('html').css('height','');
		});
	});

	// add ios7 styles for nav bar and page height
	if (App.isPhoneGap && !App.iOS7()) {
		$('body').removeClass('ios7');
	}

	$('body').removeClass('no-init');
	setTimeout(function() {
		$('body').addClass('init');
	},500);

	// add the side swipe menu for mobile view
	if (typeof Snap !== 'undefined') {
		App.snap = new Snap({
			element: document.getElementById('snap-content'),
			menu: $('#side-menu'),
			useTransform : App.useTransform,
			menuDragDistance: 95,
			disable: 'right'
		});

		var snapperCheck = function() {
			if ($(window).width() <= 768) {
				App.snap.enable();
			} else {
				App.snap.close();
				App.snap.disable();
			}
		};
		snapperCheck();

		$(window).resize(function() {
			snapperCheck();
		});

	}

	// init the storage type. cookie, or localstorage if phonegap
	$.totalStorage.ls(App.localStorage);

	// phonegap
	if (typeof CB !== 'undefined' && CB.config) {
		App.config = CB.config;
		CB.config = null;
	}

	// @todo: is this isued anymore in ui2?
	$(document).on('click', '.button-deliver-payment, .dp-display-item a, .dp-display-item .clickable', function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});

	// @todo: is this isued anymore in ui2?
	$(document).on({
		mousedown: function() {
			$(this).addClass('button-bottom-click');
		},
		touchstart: function() {
			$(this).addClass('button-bottom-click');
		},
		mouseup: function() {
			$(this).removeClass('button-bottom-click');
		},
		touchend: function() {
			$(this).removeClass('button-bottom-click');
		}
	}, '.button-bottom');
	var community_name_by_id = {};
	var community;
	for(community_name in App.communities) {
		if(App.communities.hasOwnProperty(community_name)) {
			community = App.communities[community_name];
			community_name_by_id[community.id_community] = community_name;
		}
	}
	App.community_name_by_id = community_name_by_id;
	// process the config, and startup angular
	App.processConfig(config || App.config);
	App.AB.init();
	App.NGinit();

	// Remove the old cookies #1705
	$.totalStorage('loc','', { expires : ( new Date(1970,01,01) ) });
	$.totalStorage('locv2','', { expires : ( new Date(1970,01,01) ) });

	// #1774
	// @todo: this no longer seems to happen in ui2
	if (App.iOS() && !$('.is-ui2').get(0)){
		$(':input').focus( function() {
			$(window).scrollTop( $(window).scrollTop() + 1 );
		});
	}

	// show download page only if its ui2 in an ios browser
	if (App.iOS() && !App.isPhoneGap && !$.totalStorage('_viewmobile2') && $('.is-ui2').get(0)) {
		setTimeout(function(){
			$rootScope.navigation.link('/download', 'instant');
		},10);
	}

	// Init the processor
	var processor = ( App.config.processor && App.config.processor.type ) ? App.config.processor.type : false;
	switch( processor ){
		case 'stripe':
			Stripe.setPublishableKey( App.config.processor.stripe );
			break;
		default:
			console.log( 'Processor error::', App.config.processor );
			break;
	}

	window.addEventListener( 'pageshow', function(){
		// the first pageshow should be ignored
		if( App._firstPageShowHasHappened ){
			dateTime.reload();
		}
		App._firstPageShowHasHappened = true;
	}, false );

	// window.addEventListener( 'pagehide', function(){}, false );

	$(window).trigger('nginit');

	/*
	if (!App.isPhoneGap) {
		$(document).mousemove(function(e) {
			if ($('.parallax-bg').length) {
				console.log(e.pageX, e.pageY);
			}
		});
	}
	*/

	// setup for system links
	if (App.isPhoneGap) {
		$(document).on('click', 'a[target=_system]', function(e) {
			e.preventDefault();
			e.stopPropagation();
			parent.window.open(e.currentTarget.href || e.target.href, '_system', 'location=yes');
			return false;
		});

		document.body.oncopy = function() {
			if (!parent.navigator || !parent.navigator.splashscreen) {
				return;
			}
			parent.navigator.splashscreen.show();
			parent.navigator.splashscreen.hide();
		}
	}
};



App.handleOpenURL = function(url) {
	// only happens if being pased from a url in the native app

	var handler = 'crunchbutton://';

	if (!App.isPhoneGap || url.indexOf(handler) < 0) {
		return;
	}

	url = url.replace(handler, '');
	url = url.replace(/^\//,'');
	url = '/' + url;
	url = url.split('?');
	url = url[0];

	App._handOpenUrlNav = url;

	if (App._init) {
		// already launched. just nav
		App.go(url);
	} else {
		// launching with url params
		$(window).on('nginit', function() {
			App.go(url);
		});
	}
}

var handleOpenURL = App.handleOpenURL;


/**
 * dialog functions
 */
App.dialog = {
	show: function() {
		if (arguments[1]) {
			// its a title and message
			var src = '<div class="zoom-anim-dialog small-container">' +
				'<h1>' + arguments[0] + '</h1>' +
				'<div class="small-container-content">' + arguments[1] + '</div>' +
				'</div>';
		} else if ($(arguments[0]).length) {
			// its a dom selector
			var src = $(arguments[0]);

			// fix to prevent 2 dialogs from ever appearing. only show the second. #2919
			if (src.length > 1) {
				for (var x = 0; x < src.length - 1; x++) {
					src.get(x).remove();
				}
				var src = $(arguments[0]);
			}

		} else {
			console.log('ERROR WITH DIALOG',arguments);
			return;
		}

		$.magnificPopup.open({
			items: {
				src: src,
				type: 'inline'
			},
			fixedContentPos: true,
			fixedBgPos: true,
			closeBtnInside: true,
			preloader: false,
			midClick: true,
			removalDelay: 300,
			overflowY: 'auto',
			mainClass: 'my-mfp-zoom-in',
			callbacks: {
				open: function() {
					setTimeout(function() {
						if( App.iOS() ){
							// #1774
							var width = angular.element('.mfp-bg').width();
							angular.element('.mfp-bg').width( width + 10 );
						}
					},1);
				},
				close: function() {
					$('.wrapper').removeClass('dialog-open-effect-a dialog-open-effect-b dialog-open-effect-c dialog-open-effect-d');
					App.rootScope.$broadcast( 'modalClosed' );
				}
			}
			//my-mfp-zoom-in
		});
	},
	isOpen : function(){
		return $.magnificPopup && $.magnificPopup.instance && $.magnificPopup.instance.isOpen;
	}
};


/**
 * play crunch audio sound
 */
App.playAudio = function(audio) {
	var path = 'assets/audio/';
	if (App.isPhoneGap) {
		window.plugins.NativeAudio.play(audio);
	} else {
		var sound = new Howl({
			urls: ['/' + path + audio + '.mp3', path + audio + '.ogg']
		}).play();
	}

}

App.vibrate = function() {
	if (App.isPhoneGap) {
		try {
			navigator.vibrate(100);
		} catch (e) {}
	}
}

// Methods used by phoneGap
App.verifyConnection = {
	isOffLine: false,
	forceReload: false,
	init: function () {
		if (App.isPhoneGap) {
			App.verifyConnection.check( function( online ){
				var timer = ( online ) ? 3000 : ( 3500 );
				// hide splashscreen
				/*setTimeout( function(){
					if ( !App.splashHidden && navigator.splashscreen ) {
						App.splashHidden = true;
						navigator.splashscreen.hide();
					}
				}, timer );*/
			} );
		}
	},
	check: function ( callback ) {
		var networkState = (navigator && navigator.connection && navigator.connection.type) ? navigator.connection.type : null;
		var online = true;
		if (networkState !== null && networkState == Connection.NONE ) {
			// If the app starts without internet, force reload it.
			App.verifyConnection.forceReload = true;
			App.verifyConnection.goOffline();
			online = false;
		}
		if( callback ){
			callback( online );
		}
	},
	goOffline: function () {
		if (App._remoteConfig) {
			return;
		}
		$('.connection-error').show();
		App.verifyConnection.isOffLine = true;
	},
	goOnline: function () {
		if (App.verifyConnection.isOffLine) {
			if (App.verifyConnection.forceReload) {
				window.location.reload(true);
				App.verifyConnection.forceReload = false;

			} else {
				App.rootScope.reload();
			}
		}
		App.verifyConnection.isOffLine = false;
		$('.connection-error').hide();
	}
}

App.setNotificationBarStatus = function( status ){
	App.rootScope.notificationBarStatus = status;
	App.rootScope.$safeApply();
}

// Phonegap events listeners
App.phoneGapListener = {
	init : function(){
		if( App.isPhoneGap ){
			document.addEventListener( 'deviceready', App.phoneGapListener.deviceready , false );
			document.addEventListener( 'pause', App.phoneGapListener.pause , false );
			document.addEventListener( 'resume', App.phoneGapListener.resume , false );
			document.addEventListener( 'online', App.phoneGapListener.online , false );
			document.addEventListener( 'offline', App.phoneGapListener.offline , false );
			if( !navigator.onLine ){
				App.phoneGapListener.offline();
			}
		}
	},
	deviceready : function(){
		// deviceready

		if (App.iOS() && window.ApplePay) {
			ApplePay.getAllowsApplePay(function(){
				App.hasApplePay = true;
			}, function(){
				App.hasApplePay = false;
			})
		}

	},
	resume : function(){
		dateTime.restart();
		App.rootScope.$broadcast( 'appResume', false );
	},
	pause : function(){
		// pause
	},
	online : function(){
		// online
		App.verifyConnection.goOnline();
	},
	offline: function(){
		// offline
		App.verifyConnection.goOffline();
	}
};

App.profile = {
	_timer:false,
	log: function(n) {
		// @debug: remove this return line to see the profile output
		return;
		var now = new Date();
		if (App.profile._timer) {
			console.debug('>> PROFILE',now.getTime() - App.profile._timer.getTime(),n);
		} else {
			console.debug('>> PROFILE',0,n);
		}
		App.profile._timer = now;
	}
}

App.share = function(params) {

	var pic = params.picture || 'http://crunchbutton.com/assets/images/facebook-like.png';

	if (App.isPhoneGap && App.iOS() && window.facebookConnectPlugin) {
		facebookConnectPlugin.showDialog({
			method: 'feed',
			user_message_prompt: 'Crunchbutton',
			link: params.url,
			href: params.url,
			picture: pic,
			name: params.name,
			caption:params.caption,
			description: params.description,
			attachment: {
				name: 'Crunchbutton',
				caption: ' ',
				description: params.url,
				href: params.url,
				media:[{
					type: 'image',
					src: pic,
					href: params.url
				}],
			},
			action_links: [{ text: 'Crunchbutton', href: 'https://crunchbutton.com' } ],
			description: params.description
		}, function(response) {
			console.log(response);
			if (response && response.post_id) {
				if (typeof params.success === 'function') {
					params.success(response);
				}
			} else {
				if (typeof params.fail === 'function') {
					params.fail(response);
				}
			}
		});



	} else {
		FB.ui({
			method: 'feed',
			user_message_prompt: 'Crunchbutton',
			link: params.url,
			href: params.url,
			picture: pic,
			name: params.name,
			caption:params.caption,
			description: params.description,
			attachment: {
				name: 'Crunchbutton',
				caption: ' ',
				description: params.url,
				href: params.url,
				media:[{
					type: 'image',
					src: pic,
					href: params.url
				}],
			},
			action_links: [{ text: 'Crunchbutton', href: 'https://crunchbutton.com' } ],
			description: params.description
		}, function(response) {
			console.log(response);
			if (response && response.post_id) {
				if (typeof params.success === 'function') {
					params.success(response);
				}
			} else {
				if (typeof params.fail === 'function') {
					params.fail(response);
				}
			}
		});
	}
}

App.isUI2 = function() {
	if (App._UI2IS) {
		return true;
	}
	if (App._UI2ISNT) {
		return false;
	}
	if ($('.is-ui2').get(0)) {
		return App._UI2IS = true;
	} else {
		return App._UI2ISNT = false;
	}
}

App.loadConfig = function() {
	App.request(App.service + 'config/extended', function(r) {
		App.quotes = r.site.quotes;
		var extract = ['aliases','locations','facebookScope','communities','topCommunities'];
		for (var x in extract) {
			App[extract[x]] = r[extract[x]];
			r[extract[x]] = null;
		}
		App._remoteConfig = true;
		App.init(r);
	}, function() {
		App._remoteConfig = false;
		App.init({});
	});
};

$(function() {
	if (!App.isPhoneGap) {
		App.loadConfig();
	}
});

var selScrollable = '.snap-content-inner, .snap-drawer';

// Uses body because jQuery on events are called off of the element they are
// added to, so bubbling would not work if we used document instead.
$('body').on('touchstart', selScrollable, function(e) {
  if (e.currentTarget.scrollTop === 0) {
    e.currentTarget.scrollTop = 1;
  } else if (e.currentTarget.scrollHeight === e.currentTarget.scrollTop + e.currentTarget.offsetHeight) {
    e.currentTarget.scrollTop -= 1;
  }
});

window.addEventListener( 'focus', function(){ App.rootScope.$broadcast( 'window-focus' ); });
