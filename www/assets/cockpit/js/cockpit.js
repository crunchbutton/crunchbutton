/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */

var App = {
	service: '/api/',
	logService: 'http://log.crunchbutton.com/api/',
	server: '/',
	imgServer: '/',
	cached: {},
	config: null,
	_init: false,
	localStorage: false,
	isPhoneGap: document.location.protocol == 'file:',
	useNativeAlert: false,
	useNativeConfirm: true,
	ajaxTimeout: 5000,
};

// enable localstorage on phonegap
App.localStorage = App.isPhoneGap;

App.NGinit = function() {
	$('body').attr('ng-controller', 'AppController');
	angular.bootstrap(document,['NGApp']);
	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
};

var NGApp = angular.module('NGApp', [ 'ngRoute', 'ngResource', 'angularFileUpload' ], function( $httpProvider ) {
	$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	var param = function(obj) {
		var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

		for(name in obj) {
			value = obj[name];

			if(value instanceof Array) {
				for(i=0; i<value.length; ++i) {
					subValue = value[i];
					fullSubName = name + '[' + i + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			}
			else if(value instanceof Object) {
				for(subName in value) {
					subValue = value[subName];
					fullSubName = name + '[' + subName + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			}
			else if(value !== undefined && value !== null)
				query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
		}

		return query.length ? query.substr(0, query.length - 1) : query;
	};

	// Override $http service's default transformRequest
	$httpProvider.defaults.transformRequest = [function(data) {
		return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
	}];
});;

NGApp.config(function($compileProvider){
	$compileProvider.aHrefSanitizationWhitelist(/.*/);
});

NGApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider ) {
	$routeProvider
		

		/* Restaurants */
		.when('/restaurant/order/new', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderNew',
			templateUrl: 'assets/view/restaurant-order-new.html'
		})
		.when('/restaurant/order/list', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderList',
			templateUrl: 'assets/view/restaurant-order-list.html'
		})
		.when('/restaurant/order/:id', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderView',
			templateUrl: 'assets/view/restaurant-order-view.html'
		})
		/* Settlement */
		.when('/settlement', {
			action: 'settlement',
			controller: 'SettlementCtrl',
			templateUrl: 'assets/view/settlement.html'
		})
		.when('/settlement/restaurants', {
			action: 'settlement',
			controller: 'SettlementRestaurantsCtrl',
			templateUrl: 'assets/view/settlement-restaurants.html'
		})
		.when('/settlement/restaurants/scheduled', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledCtrl',
			templateUrl: 'assets/view/settlement-restaurants-scheduled.html'
		})
		.when('/settlement/restaurants/scheduled/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledViewCtrl',
			templateUrl: 'assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/payment/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsPaymentCtrl',
			templateUrl: 'assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/summary/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsSummaryCtrl',
			templateUrl: 'assets/view/settlement-summary.html'
		})
		.when('/settlement/restaurants/payments', {
			action: 'settlement',
			controller: 'SettlementRestaurantsPaymentsCtrl',
			templateUrl: 'assets/view/settlement-restaurants-payments.html'
		})
		.when('/settlement/drivers', {
			action: 'settlement',
			controller: 'SettlementDriversCtrl',
			templateUrl: 'assets/view/settlement-drivers.html'
		})
		.when('/settlement/drivers/scheduled', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledCtrl',
			templateUrl: 'assets/view/settlement-drivers-scheduled.html'
		})
		.when('/settlement/drivers/scheduled/:id', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledViewCtrl',
			templateUrl: 'assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/payments', {
			action: 'settlement',
			controller: 'SettlementDriversPaymentsCtrl',
			templateUrl: 'assets/view/settlement-drivers-payments.html'
		})
		.when('/settlement/drivers/payment/:id', {
			action: 'settlement',
			controller: 'SettlementDriversPaymentCtrl',
			templateUrl: 'assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/summary/:id', {
			action: 'settlement',
			controller: 'SettlementDriversSummaryCtrl',
			templateUrl: 'assets/view/settlement-summary.html'
		})
		/* Driver shifts */

		.when('/drivers/dashboard', {
			action: 'drivers-dashboard',
			controller: 'DriversDashboardCtrl',
			templateUrl: 'assets/view/drivers-dashboard.html'
		})
		.when('/drivers/orders', {
			action: 'drivers-orders',
			controller: 'DriversOrdersCtrl',
			templateUrl: 'assets/view/drivers-orders.html'
		})
		.when('/drivers/order/:id', {
			action: 'drivers-order',
			controller: 'DriversOrderCtrl',
			templateUrl: 'assets/view/drivers-order.html'
		})
		.when('/drivers/shifts', {
			action: 'drivers-shifts',
			controller: 'DriversShiftsCtrl',
			templateUrl: 'assets/view/drivers-shifts.html'
		})
		.when('/drivers/shifts/schedule', {
			action: 'drivers-shifts',
			controller: 'DriversShiftsScheduleCtrl',
			templateUrl: 'assets/view/drivers-shifts-schedule.html'
		})
		.when('/drivers/summary', {
			action: 'drivers-summary',
			controller: 'DriversSummaryCtrl',
			templateUrl: 'assets/view/drivers-summary.html'
		})
		.when('/login', {
			action: 'login',
			controller: 'LoginCtrl',
			templateUrl: 'assets/view/login.html'
		})
		.when('/drivers/help', {
			action: 'drivers-help',
			controller: 'DriversHelpCtrl',
			templateUrl: 'assets/view/drivers-help.html'
		})
		.when('/drivers/help/credit-card', {
			action: 'drivers-help',
			controller: 'DriversHelpCreditCardCtrl',
			templateUrl: 'assets/view/drivers-help-credit-card.html'
		})
		/* Admins payment info */
		.when('/staff/list', {
			action: 'staff',
			controller: 'StaffListCtrl',
			templateUrl: 'assets/view/staff-list.html'
		})
		.when('/staff/payinfo/:id', {
			action: 'staff',
			controller: 'StaffPayInfoCtrl',
			templateUrl: 'assets/view/staff-payinfo.html'
		})
		/* Driver onBoarding Routes */
		.when('/drivers/onboarding/', {
			action: 'drivers-onboarding',
			controller: 'DriversOnboardingCtrl',
			templateUrl: 'assets/view/drivers-onboarding-list.html'
		})
		.when('/drivers/onboarding/new', {
			action: 'drivers-onboarding',
			controller: 'DriversOnboardingFormCtrl',
			templateUrl: 'assets/view/drivers-onboarding-form.html'
		})
		.when('/drivers/onboarding/:id', {
			action: 'drivers-onboarding',
			controller: 'DriversOnboardingFormCtrl',
			templateUrl: 'assets/view/drivers-onboarding-form.html'
		})
		.when('/drivers/docs', {
			action: 'drivers-documents',
			controller: 'DriversDocsFormCtrl',
			templateUrl: 'assets/view/drivers-docs-form.html'
		})
		.when('/setup/:phone', {
			action: 'drivers-setup',
			controller: 'DriversOnboardingSetupCtrl',
			templateUrl: 'assets/view/drivers-onboarding-setup.html'
		})
		.when('/onboarding/', {
			action: 'onboarding',
			controller: 'PreOnboardingCtrl',
			templateUrl: 'assets/view/pre-onboarding.html'
		})
		.when('/onboarding/:id', {
			action: 'onboarding',
			controller: 'PreOnboardingCtrl',
			templateUrl: 'assets/view/pre-onboarding.html'
		})
		.otherwise({
			action: 'home',
			controller: 'DefaultCtrl',
			templateUrl: 'assets/view/home.html'
		});
	// only use html5 enabled location stuff if its not in a phonegap container
	$locationProvider.html5Mode(!App.isPhoneGap);
}]);

// global route change items
NGApp.controller('AppController', function ($scope, $route, $http, $routeParams, $rootScope, $location, $window, $timeout, MainNavigationService, AccountService, DriverOrdersService, flash, LocationService) {

	// define external pointers
	App.rootScope = $rootScope;
	App.location = $location;
	App.http = $http;

	// define global services
	$rootScope.flash = flash;
	$rootScope.navigation = MainNavigationService;
	$rootScope.isPhoneGap = App.isPhoneGap;
	$rootScope.server = App.server;
	$rootScope.account = AccountService;
	$rootScope.location = LocationService;

	/* todo: turn makeBusy and unBusy in to directives */
	$rootScope.makeBusy = function(){
		if( !$rootScope.isBusy ){
			angular.element( 'body' ).addClass( 'loading' );
			$rootScope.isBusy = true;
		}
	}

	$rootScope.walkTo = function( selector, adjust ){
		adjust = adjust ? adjust : 0;
		var el = angular.element( selector );
		if( el.length ){
			var walkTo = ( $('.snap-content-inner').scrollTop() + el.offset().top ) + adjust;
			$( 'html, body, .snap-content-inner' ).animate( { scrollTop: walkTo }, '500');
		}
	}

	$rootScope.unBusy = function(){
		if( $rootScope.isBusy ){
			setTimeout( function(){
				angular.element( 'body' ).removeClass( 'loading' );
			}, 100 );
			$rootScope.isBusy = false;
		}
	}

	$rootScope.focus = function( selector ){
		setTimeout( function(){
			angular.element( selector ).focus();
		}, 100 );
	}

	$rootScope.blur = function( selector ){
		setTimeout( function(){
			angular.element( selector ).blur();
		}, 100 );
	}

	$rootScope.reload = function() {
		$route.reload();
	};

	$rootScope.link = function(link) {
		App.go.apply(arguments);
	};

	$rootScope.back = function() {
		history.back();
	};

	$rootScope.closePopup = function() {
		try {
			$.magnificPopup.close();
		} catch (e) {}
	};

	$rootScope.$safeApply = function(fn) {
		var phase = this.$root.$$phase;
		if (phase == '$apply' || phase == '$digest') {
			if (fn && (typeof(fn) === 'function')) {
				this.$eval(fn);
			}
		} else {
			this.$apply(fn);
		}
	};

	$rootScope.hasBack = false;

	$scope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {
		// Store the actual page
		MainNavigationService.page = $route.current.action;
		App.rootScope.current = MainNavigationService.page;
		App.track('page', $route.current.action);

		$('body').removeClass(function (index, css) {
			return (css.match (/\bpage-\S+/g) || []).join(' ');
		}).addClass('page-' + MainNavigationService.page);

		$('.nav-top').addClass('at-top');

		App.scrollTop($rootScope.scrollTop);
		$rootScope.scrollTop = 0;
	});

	// Make the window's size available to all scope
	$rootScope.windowWidth = $window.outerWidth;
	$rootScope.windowHeight = $window.outerHeight;

	// Window resize event
	angular.element( $window ).bind( 'resize',function(){
		$rootScope.windowWidth = $window.outerWidth;
		$rootScope.windowHeight = $window.outerHeight;
		$rootScope.$apply( 'windowWidth' );
		$rootScope.$apply( 'windowHeight' );
	});

	// Litle hack to don't show the templates till angularjs finish running
	$scope.angularLoaded = true;

	$rootScope.account.checkUser();

	var badges = function(){
		// Just run if the user is loggedin
		if( $rootScope.account.isLoggedIn() ){

			DriverOrdersService.newOrdersBadge();
			DriverOrdersService.acceptedOrders();
			DriverOrdersService.pickedupOrders();

			// run over and over again every 30 secs
			$timeout( function() { badges() }, 30 * 1000 );
		}

	}
	// Update the badges
	badges();


	// Event called when the app resumes
	$rootScope.$on( 'appResume', function(e, data) {
		badges();
	} );

} );

// Check user's auth
/* todo: check user's permission too */
NGApp.run( function ( $rootScope, $location, MainNavigationService ) {
	$rootScope.$on( '$routeChangeStart', function ( event, next, current ) {
		if( !$rootScope.account.isLoggedIn() ){
			var isAllowed = false;
			angular.forEach( [ '/login', '/setup', '/onboarding' ], function( allowed ){
			 if( $location.url().indexOf( allowed ) >= 0 ){
				isAllowed = true;
			 }
			} );
			if( !isAllowed  ) {
				MainNavigationService.link( '/login' );
			}
		}
		if( $location.url() == '/login' && $rootScope.account.isLoggedIn() ) {
			MainNavigationService.link( '/' );
		}
	});
});

App.alert = function( txt, title, useNativeAlert ) {
	setTimeout(function() {
		if (useNativeAlert && App.isPhoneGap) {
			navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else if ( useNativeAlert ) {
			alert( txt );
		} else {
			App.rootScope.$broadcast( 'notificationAlert', title || 'Woops!', txt );
		}
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
	// Remove the animation from rootScope #2827 before start the new one
	App.rootScope.animationClass = '';
	if( App.isNarrowScreen() || App.transitionForDesktop ){
		setTimeout( function(){
			App.rootScope.animationClass = transition ? 'animation-' + transition : '';
			App.rootScope.$safeApply();
			// @todo: do some tests to figure out if we need this or not
			// App.location.path(!App.isPhoneGap ? url : 'index.html#' + url);
			App.location.path( url || '/' );
			App.rootScope.$safeApply();
		}, 10 );
	} else {
		App.location.path( url || '/' );
		App.rootScope.$safeApply();
	}
};

App.toggleMenu = function(side) {
	side = side || 'left';
	if (App.snap.state().state == side) {
		App.snap.close();
	} else {
		App.snap.open(side);
	}
};



/**
 * scroll to the top of the page
 */
App.scrollTop = function(top) {
	setTimeout(function() {
		if (!top) {
			setTimeout(function() {
				$('html, body, .snap-content-inner').scrollTop(0);
			},10);
		}
		$('html, body, .snap-content-inner').animate({scrollTop: top || 0}, 10, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	},3);
};


/**
 * Sends a tracking item to google, or to google ads if its an order
 */
App.track = function() {
	if (App.config.env != 'live') {
		return;
	}

	if (arguments[0] == 'Ordered') {
		$('img.conversion').remove();
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + location.href + '">').appendTo($('body'));
	}

	if ( typeof( ga ) == 'function' ) {
		ga('send', 'event', 'app', arguments[0], arguments[1]);
	}
};


App.processConfig = function(json, user) {
	if (user && !json) {
		App.config.user = user;
	} else {
		App.config = json;
	}
};

/**
 * global event binding and init
 */
App.init = function(config) {

	// ensure this function cant be called twice. can crash the browser if it does.
	if (App._init) {
		return;
	}

	App._init = true;

	$(document).on('touchmove', '.mfp-wrap', function(e) {
		e.preventDefault();
		e.stopPropagation();
	});

	// replace normal click events for mobile browsers
	FastClick.attach(document.body);

	// add ios7 styles for nav bar and page height
	if (App.isPhoneGap && !App.iOS7()) {
		$('body').removeClass('ios7');
	}

	$('body').removeClass('no-init');

	// add the side swipe menu for mobile view
	if (typeof Snap !== 'undefined') {

		App.snap = new Snap({
			element: document.getElementById('snap-content'),
			menu: $('#side-menu, #side-menu-right'),
			menuDragDistance: 95,
			disable: ''
		});

		var snapperCheck = function() {
			if ($(window).width() <= 1024) {
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

	App.snap.disable();

	// init the storage type. cookie, or localstorage if phonegap
	$.totalStorage.ls(App.localStorage);

	// phonegap
	if (typeof CB !== 'undefined' && CB.config) {
		App.config = CB.config;
		CB.config = null;
	}

	// process the config, and startup angular
	App.processConfig(config || App.config);
	App.NGinit();

	window.addEventListener( 'pageshow', function(){
		// the first pageshow should be ignored
		if( App._firstPageShowHasHappened ){
			dateTime.reload();
		}
		App._firstPageShowHasHappened = true;
	}, false );

	$( window ).trigger( 'nginit' );

	/*
	if (!App.isPhoneGap) {
		$(document).mousemove(function(e) {
			if ($('.parallax-bg').length) {
				console.log(e.pageX, e.pageY);
			}
		});
	}
	*/

};

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

// Service to show a flash message
NGApp.factory( 'flash', function( $timeout ) {

	var message = [];

	// $rootScope.$on('$routeChangeSuccess', function() {
	// 	clearMessage();
	// } );

	var clearMessage = function(){
		message = false;
	}

	service = {};
	service.setMessage = function( text, level ){
		var level = ( level ) ? level : 'success';
		message = { level : level, text : text };
		$timeout( function() { clearMessage() }, 5 * 1000 );
	}

	service.hasMessage = function(){
		return message != false;
	}

	service.getLevel = function(){
		if( service.hasMessage() ){
			return message.level;
		}
	}

	service.getMessage = function(){
		if( service.hasMessage() ){
			return message.text;
		}
	}

	return service;

} );

