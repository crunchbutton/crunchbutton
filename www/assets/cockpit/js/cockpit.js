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
	isCordova: document.location.protocol == 'file:',
	useNativeAlert: true,
	useNativeConfirm: true,
	ajaxTimeout: 5000,
	version: null
};

// enable localstorage on cordova
App.localStorage = App.isCordova;

if (App.isCordova) {
	App.service = 'https://cockpit.la/api/';
}

if ( navigator.appVersion.indexOf( 'Win' ) !=-1 ){
	App.isWindows = true;
};

console.debug((App.isCordova ? 'Is' : 'Is not') + ' Cordova')

var NGApp = angular.module('NGApp', ['chart.js', 'ngRoute', 'ngResource', 'ngAnimate', 'angularFileUpload', 'angularMoment', 'btford.socket-io', 'cfp.hotkeys', 'ngMap', 'ui.select','pasvaz.bindonce' , 'ngSanitize'], function( $httpProvider ) {

});

NGApp.constant('angularMomentConfig', {
    timezone: 'America/Los_Angeles'
});

NGApp.config(function($compileProvider){
	$compileProvider.aHrefSanitizationWhitelist(/.*/);
});


NGApp.factory('errorInterceptor', function($q) {
	var errorFromResponse = function(response) {
		if (response.headers && typeof(response.headers) !== 'function') {
			return true;
		}
		var headers = response.headers();
		if (headers && headers['php-fatal-error']) {
			console.error(headers['php-fatal-error']);
			App.alert('<b>Error</b>. Please report this to dev:<br><br><ul class="ul-inputs"><li class="li-input"><div class="input"><textarea class="fatal-error-content">' + headers['php-fatal-error'] + '</textarea></div></li></ul>');
			return false;
		}

		if( response.status == 401 && response.data && response.data.login ){
			window.location.reload();
		}
		return true;
	};
	var unteruptable = [
		App.service + 'config',
		App.service + 'driver/orders/',
		'/assets/view/'
	];
	var removeUrl = function(url) {
		for (var x in errorInterceptor.cancelers) {
			if (errorInterceptor.cancelers[x].url == url) {
				errorInterceptor.cancelers.splice(x, 1);
				break;
			}
		}
	};

	var errorInterceptor = {
		responseError: function(response) {
			removeUrl(response.url || response.config.url);
			errorFromResponse(response);
			return $q.reject(response);
		},
		response: function(response) {
			removeUrl(response.url || response.config.url);

			if (!errorFromResponse(response)) {
				return $q.reject(response);
			} else {
				return response;
			}
		},
		request: function(config) {
			//console.log(config);
			var ignore = errorInterceptor.isInteruptable(config.url);

			if (!ignore) {
				var canceler = $q.defer();
				config.timeout = config.timeout || canceler.promise;
				errorInterceptor.cancelers.push({canceler: config.canceler || canceler, url: config.url});
			}
			return config;
		},
		cancelers: [],
		cancelAll: function() {
			for (var x in errorInterceptor.cancelers) {
				console.debug('>> canceling all requests...');
				errorInterceptor.cancelers[x].canceler.resolve();
			}
		},
		isInteruptable: function(url) {
			var ignore = false;
			for (var x in unteruptable) {
				if (url.indexOf(unteruptable[x]) === 0) {
					// dont interupt
					ignore = true;
					break;
				}
			}
			return ignore;
		}
	};
	return errorInterceptor;
});

NGApp.config(['$httpProvider', function($httpProvider) {
	$httpProvider.defaults.headers.common['Http-Error'] = 1;
	$httpProvider.interceptors.push('errorInterceptor');
}]);

NGApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider ) {

	$routeProvider

		/* Settlement */
		.when('/settlement', {
			action: 'settlement',
			controller: 'SettlementCtrl',
			templateUrl: '/assets/view/settlement.html'
		})
		.when('/settlement/list', {
			action: 'settlement-list',
			controller: 'SettlementListCtrl',
			templateUrl: '/assets/view/settlement-list.html',
			reloadOnSearch: false
		})
		.when('/settlement/queue', {
			action: 'settlement',
			controller: 'SettlementQueueListCtrl',
			templateUrl: '/assets/view/settlement-queue.html',
			reloadOnSearch: false
		})
		.when('/settlement/restaurants', {
			action: 'settlement',
			controller: 'SettlementRestaurantsCtrl',
			templateUrl: '/assets/view/settlement-restaurants.html'
		})
		.when('/settlement/restaurants/no-payment-method', {
			action: 'settlement',
			controller: 'SettlementRestaurantsNoPaymentCtrl',
			templateUrl: '/assets/view/settlement-restaurants-no-payment-method.html'
		})
		.when('/settlement/restaurants/scheduled', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledCtrl',
			templateUrl: '/assets/view/settlement-restaurants-scheduled.html'
		})
		.when('/settlement/restaurants/payment/arbitrary/:id_restaurant?', {
			action: 'settlement',
			controller: 'SettlementRestaurantsPaymentArbitraryCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payment-arbitrary.html'
		})
		.when('/settlement/restaurants/scheduled/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/archived/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/deleted/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/payment/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsPaymentCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payment.html'
		})
		.when('/settlement/restaurants/summary/:id', {
			action: 'settlement',
			controller: 'SettlementRestaurantsSummaryCtrl',
			templateUrl: '/assets/view/settlement-summary.html'
		})
		.when('/settlement/restaurants/payments', {
			action: 'settlement',
			controller: 'SettlementRestaurantsPaymentsCtrl',
			templateUrl: '/assets/view/settlement-restaurants-payments.html'
		})
		.when('/settlement/drivers', {
			action: 'settlement',
			controller: 'SettlementDriversCtrl',
			templateUrl: '/assets/view/settlement-drivers.html'
		})
		.when('/settlement/drivers/scheduled', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledCtrl',
			templateUrl: '/assets/view/settlement-drivers-scheduled.html',
			reloadOnSearch: false
		})
		.when('/settlement/drivers/old-payments/:id?', {
			action: 'settlement',
			controller: 'SettlementDriversOldPaymentsCtrl',
			templateUrl: '/assets/view/settlement-drivers-old-payments.html',
		})
		.when('/settlement/drivers/archived', {
			action: 'settlement',
			controller: 'SettlementDriversArchivedCtrl',
			templateUrl: '/assets/view/settlement-drivers-scheduled.html',
		})
		.when('/settlement/drivers/deleted', {
			action: 'settlement',
			controller: 'SettlementDriversDeletedCtrl',
			templateUrl: '/assets/view/settlement-drivers-scheduled.html',
		})
		.when('/settlement/drivers/deleted/:id', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/archived/:id', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/scheduled/:id', {
			action: 'settlement',
			controller: 'SettlementDriversScheduledViewCtrl',
			templateUrl: '/assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/payments', {
			action: 'settlement',
			controller: 'SettlementDriversPaymentsCtrl',
			templateUrl: '/assets/view/settlement-drivers-payments.html',
			reloadOnSearch: false
		})
		.when('/settlement/drivers/payment/arbitrary/:id_driver?', {
			action: 'settlement',
			controller: 'SettlementDriversPaymentArbitraryCtrl',
			templateUrl: '/assets/view/settlement-drivers-payment-arbitrary.html'
		})
		.when('/settlement/drivers/payment/:id', {
			action: 'settlement',
			controller: 'SettlementDriversPaymentCtrl',
			templateUrl: '/assets/view/settlement-drivers-payment.html'
		})
		.when('/settlement/drivers/summary/:id', {
			action: 'settlement',
			controller: 'SettlementDriversSummaryCtrl',
			templateUrl: '/assets/view/settlement-summary.html'
		})
		/* Pexcard */
		.when('/pexcard', {
			action: 'pexcard',
			controller: 'PexCardCtrl',
			templateUrl: '/assets/view/pexcard.html'
		})
		.when('/pexcard/log', {
			action: 'pexcard',
			controller: 'PexCardLogCtrl',
			templateUrl: '/assets/view/pexcard-log.html'
		})
		.when('/pexcard/config', {
			action: 'pexcard',
			controller: 'PexConfigCtrl',
			templateUrl: '/assets/view/pexcard-config.html'
		})
		.when('/pexcard/card/log', {
			action: 'pexcard',
			controller: 'PexCardCardLogCtrl',
			templateUrl: '/assets/view/pexcard-card-log.html'
		})
		.when('/pexcard/log/:id', {
			action: 'pexcard',
			controller: 'PexCardLogViewCtrl',
			templateUrl: '/assets/view/pexcard-log-view.html'
		})
		.when('/pexcard/card/driver/:id', {
			action: 'pexcard',
			driver: true,
			controller: 'PexCardIdCtrl',
			templateUrl: '/assets/view/pexcard-pex-id.html'
		})
		.when('/pexcard/card/:id?', {
			action: 'pexcard',
			controller: 'PexCardIdCtrl',
			templateUrl: '/assets/view/pexcard-pex-id.html'
		})
		.when('/pexcard/report/old', {
			action: 'pexcard',
			controller: 'PexCardReportOldCtrl',
			templateUrl: '/assets/view/pexcard-report.html'
		})
		.when('/pexcard/report', {
			action: 'pexcard',
			controller: 'PexCardReportCtrl',
			templateUrl: '/assets/view/pexcard-report.html'
		})
		/* Driver shifts */
		.when('/drivers/dashboard', {
			action: 'drivers-dashboard',
			controller: 'DriversDashboardCtrl',
			templateUrl: '/assets/view/drivers-dashboard.html'
		})
		.when('/drivers/orders', {
			action: 'drivers-orders',
			controller: 'DriversOrdersCtrl',
			templateUrl: '/assets/view/drivers-orders.html',
			back: false
		})
		.when('/drivers/order/signature/:id', {
			action: 'drivers-order',
			controller: 'DriversOrderSignatureCtrl',
			templateUrl: '/assets/view/drivers-order-signature.html'
		})
		.when('/drivers/order/:id', {
			action: 'drivers-order',
			controller: 'DriversOrderCtrl',
			templateUrl: '/assets/view/drivers-order.html'
		})
		.when('/drivers/shifts', {
			action: 'drivers-shifts',
			controller: 'DriversShiftsCtrl',
			templateUrl: '/assets/view/drivers-shifts.html',
			back: false
		})
		.when('/drivers/shifts/schedule-old', {
			action: 'drivers-shift-preferences',
			controller: 'DriversShiftsScheduleCtrl',
			templateUrl: '/assets/view/drivers-shifts-schedule.html',
			back: false
		})
		.when('/drivers/shifts/schedule', {
			action: 'drivers-shift-preferences',
			controller: 'DriversShiftsScheduleRatingCtrl',
			templateUrl: '/assets/view/drivers-shifts-schedule-rating.html',
			back: false
		})
		.when('/schedule', {
			action: 'redirecting',
			redirectTo: '/drivers/shifts/schedule'
		})
		/* Driver summaries and payments information */
		.when('/drivers/summary', {
			action: 'drivers-summary',
			controller: 'DriversSummaryCtrl',
			templateUrl: '/assets/view/drivers-summary.html',
			back: false
		})
		.when('/drivers/summary/:id', {
			action: 'drivers-summary',
			controller: 'DriversSummaryCtrl',
			templateUrl: '/assets/view/drivers-summary.html'
		})
		.when('/drivers/payments', {
			action: 'drivers-summary',
			controller: 'DriversPaymentsCtrl',
			templateUrl: '/assets/view/drivers-payments.html'
		})
		.when('/drivers/payments/:id', {
			action: 'drivers-summary',
			controller: 'DriversPaymentsCtrl',
			templateUrl: '/assets/view/drivers-payments.html'
		})
		.when('/drivers/payment/:id', {
			action: 'drivers-summary',
			controller: 'DriversPaymentCtrl',
			templateUrl: '/assets/view/drivers-payment.html'
		})

		/* driver welcome shit */
		.when('/drivers/welcome', {
			action: 'drivers-welcome-home',
			controller: 'DriversWelcomeHomeCtrl',
			templateUrl: '/assets/view/drivers-welcome-home.html',
			back: false
		})
		.when('/drivers/welcome/info', {
			action: 'drivers-welcome-info',
			controller: 'DriversWelcomeInfoCtrl',
			templateUrl: '/assets/view/drivers-welcome-info.html',
			back: true
		})
		.when('/drivers/welcome/location', {
			action: 'drivers-welcome-location',
			controller: 'DriversWelcomeLocationCtrl',
			templateUrl: '/assets/view/drivers-welcome-location.html',
			back: true
		})
		.when('/drivers/welcome/push', {
			action: 'drivers-welcome-push',
			controller: 'DriversWelcomePushCtrl',
			templateUrl: '/assets/view/drivers-welcome-push.html',
			back: true
		})
		.when('/drivers/welcome/wahoo', {
			action: 'drivers-welcome-wahoo',
			controller: 'DriversWelcomeWahooCtrl',
			templateUrl: '/assets/view/drivers-welcome-wahoo.html',
			back: true
		})
		/* other */
		.when('/login', {
			action: 'login',
			controller: 'LoginCtrl',
			templateUrl: '/assets/view/general-login.html'
		})
		.when('/legal', {
			action: 'legal',
			controller: 'LegalCtrl',
			templateUrl: '/assets/view/general-legal.html'
		})
		.when('/info', {
			action: 'info',
			controller: 'InfoCtrl',
			templateUrl: '/assets/view/general-info.html'
		})
		.when('/drivers/help', {
			action: 'drivers-help',
			controller: 'DriversHelpCtrl',
			templateUrl: '/assets/view/drivers-help.html',
			back: false,
			reloadOnSearch: false
		})
		.when('/drivers/feedback', {
			action: 'drivers-feedback',
			controller: 'DriversFeedbackCtrl',
			templateUrl: '/assets/view/drivers-feedback.html',
			back: false,
			reloadOnSearch: false
		})
		.when('/invite', {
			action: 'drivers-invite',
			controller: 'InviteCtrl',
			templateUrl: '/assets/view/drivers-invite.html',
			back: false,
			reloadOnSearch: false
		})
		.when('/drivers/drivers-locations', {
			action: 'drivers-locations',
			controller: 'DriversLocationsCtrl',
			templateUrl: '/assets/view/drivers-locations.html',
			back: false,
			reloadOnSearch: false
		})
		.when('/drivers/help/credit-card', {
			action: 'drivers-help',
			controller: 'DriversHelpCreditCardCtrl',
			templateUrl: '/assets/view/drivers-help-credit-card.html'
		})
		.when('/profile', {
			action: 'profile',
			controller: 'ProfileCtrl',
			templateUrl: '/assets/view/general-profile.html',
			back: false
		})
		.when('/profile/password', {
			action: 'profile',
			controller: 'ProfilePasswordCtrl',
			templateUrl: '/assets/view/general-profile-password.html',
			back: false
		})
		/* Driver onBoarding Routes */
		.when('/drivers/onboarding', {
			action: 'drivers-onboarding',
			controller: 'DriversOnboardingCtrl',
			templateUrl: '/assets/view/drivers-onboarding-list.html',
			reloadOnSearch: false
		})
		.when('/drivers/onboarding/docs', {
			action: 'drivers-onboarding-docs',
			controller: 'DriversOnboardingDocsCtrl',
			templateUrl: '/assets/view/drivers-onboarding-docs.html'
		})
		.when('/drivers/onboarding/new', {
			action: 'drivers-onboarding-new',
			controller: 'DriversOnboardingFormCtrl',
			templateUrl: '/assets/view/drivers-onboarding-form.html'
		})
		.when('/drivers/onboarding/:id', {
			action: 'drivers-onboarding-edit',
			controller: 'DriversOnboardingFormCtrl',
			templateUrl: '/assets/view/drivers-onboarding-form.html'
		})
		.when('/drivers/docs', {
			action: 'drivers-documents',
			controller: 'DriversDocsFormCtrl',
			templateUrl: '/assets/view/drivers-docs-form.html',
			back: false
		})
		.when('/drivers/docs/payment', {
			redirectTo: '/drivers/docs/'
			// action: 'drivers-documents',
			// controller: 'DriversPaymentFormCtrl',
			// templateUrl: '/assets/view/drivers-payment-info-form.html'
		})
		.when('/drivers/docs/pexcard', {
			action: 'drivers-pex-card',
			controller: 'DriversPexCardCtrl',
			templateUrl: '/assets/view/drivers-pexcard.html',
			back: false
		})
		.when('/setup/:phone', {
			action: 'drivers-setup',
			controller: 'DriversOnboardingSetupCtrl',
			templateUrl: '/assets/view/drivers-onboarding-setup.html'
		})
		.when('/onboarding/', {
			action: 'onboarding',
			controller: 'PreOnboardingCtrl',
			templateUrl: '/assets/view/pre-onboarding.html'
		})
		.when('/onboarding/:id', {
			action: 'onboarding',
			controller: 'PreOnboardingCtrl',
			templateUrl: '/assets/view/pre-onboarding.html'
		})
		.when('/home', {
			action: 'home',
			controller: 'HomeCtrl',
			templateUrl: '/assets/view/general-home.html',
			back: false
		})
		.otherwise({
			action: 'default',
			controller: 'DefaultCtrl',
			templateUrl: '/assets/view/general-default.html'
		});
	// only use html5 enabled location stuff if its not in a cordova container
	//$locationProvider.html5Mode(!App.isCordova);

	$locationProvider.html5Mode({
		enabled: true,
		requireBase: false
	});
}]);

// global route change items
NGApp.controller('AppController', function ($scope, $route, $http, $routeParams, $rootScope, $location, $window, $timeout, MainNavigationService, AccountService, DriverOrdersService, flash, LocationService, HeartbeatService, PushService, TicketViewService, CallService, DriverOrdersViewService, errorInterceptor, TwilioService, AppAvailabilityService) {

	if (App.isCordova) {
		$http.defaults.headers.common['app-version'] = App.version;
	}

	$rootScope._current_bundle = _current_bundle;

	$rootScope.ngRepeatFinished = function(){
		$rootScope.$broadcast( 'ng-repeat-finished', {} );
	}

	var url = App.service + 'config?init=1';
	$http.get( url, {
		cache: false
	} ).success( function ( response ) {
		App._remoteConfig = true;
		App.init( response );
	} ).error( function(){
		App._remoteConfig = false;
		App.init( {} );
	} );

	// define external pointers
	App.rootScope = $rootScope;
	App.location = $location;
	App.http = $http;

	// define global services
	$rootScope.title = '';
	$rootScope.flash = flash;
	$rootScope.navigation = MainNavigationService;
	$rootScope.isCordova = App.isCordova;
	$rootScope.server = App.server;
	$rootScope.account = AccountService;
	$rootScope.location = LocationService;
	$rootScope.twilio = TwilioService;

	$rootScope.supportToggled = false;
	$rootScope.supportToggle = function() {
		$rootScope.supportToggled = !$rootScope.supportToggled;
		App.rootScope.$broadcast( 'support-toggle' );
	};
	Mousetrap.bind('alt+s', function() {
		$rootScope.$apply(function() {
			$rootScope.supportToggle();
		});
		return false;
	});

	$rootScope.menuToggled = true;
	$rootScope.menuToggle = function() {
		$rootScope.menuToggled = !$rootScope.menuToggled;
		$rootScope.$broadcast('menu-toggle');
	};
	Mousetrap.bind('alt+m', function() {
		$rootScope.$apply(function() {
			$rootScope.menuToggle();
		});
		return false;
	});

	/* todo: turn makeBusy and unBusy in to directives */
	$rootScope.makeBusy = function(){
		if( !$rootScope.isBusy ){
			angular.element( 'body' ).addClass( 'loading' );
			$rootScope.isBusy = true;
		}
		setTimeout( function(){
			$rootScope.unBusy();
		}, 10000 );
	}

	$rootScope.openMap = function( type, address1, address2 ){
		$rootScope.$broadcast( 'openMapsDialog', { type: type, address1: address1, address2: address2 } );
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
		}, 300 );
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

	if (App.isCordova) {
		$('body').addClass('cordova');
	}

	if (App.iOS()) {
		$('body').addClass('ios');
	}

	if (App.isAndroid()) {
		$('body').addClass('android');
	}



	$rootScope.instant = function() {
		$('body').addClass('instant');
		setTimeout(function(){
			$('body').removeClass('instant');
			$rootScope.$safeApply();
		},400);
	};

	$rootScope.back = function() {
		$('body').addClass('back');

		setTimeout(function(){
			$rootScope.$safeApply();
		},100);

		setTimeout(function(){
			$('body').removeClass('back');
			$rootScope.$safeApply();
		},400);

		setTimeout(function(){
			$rootScope.$safeApply();
		},1200);

		history.go(-1);
		if( navigator && navigator.app && navigator.app.backHistory ){
			navigator.app.backHistory();
		} else if( window.navigator && window.navigator.app && window.navigator.app.backHistory ){
			window.navigator.app.backHistory();
		}

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

	$rootScope.triggerViewTicket = function(ticket) {
		$rootScope.$broadcast('triggerViewTicket', ticket);
	};

	$rootScope.callText = function(num) {
		$rootScope.$broadcast('callText', num);
	};

	$rootScope.creditDialog = function(id_user, id_order) {
		$rootScope.$broadcast('creditDialog', id_user, id_order);
	};

	$rootScope.hasBack = false;

	var camelCase = function(str) {
		return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	};

	var makeTitle = function() {
		var title = ( $route.current && $route.current.action ? camelCase($route.current.action.replace(/-/g,' ') ) : $route.current.title );
		return title;
	};

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

	$rootScope.$on( 'configLoaded', function(e, data) {

		$rootScope.isLive = ( App.config.env == 'live' );
		$rootScope.isBeta = !$rootScope.isLive;
		$rootScope.config = App.config.site;
		$rootScope.bundle = App.config.version;

		// display update page
		if (App.config.site['cockpit-min-app-version'] && App.version) {
			if (App.config.site['cockpit-min-app-version'] > App.version) {
				setTimeout(function(){
					MainNavigationService.link('/update');
					$rootScope.$apply();
				},100);
			}
		}

		$rootScope.account.checkUser();

		// Litle hack to don't show the templates till angularjs finish running
		$scope.angularLoaded = true;

		$rootScope.configLoaded = true;

	} );

	$scope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {

		$rootScope.unBusy();

		$rootScope.navTitle = '';
		// Store the actual page

		$rootScope.hasBack = $route.current.back === false ? false : true;
		//$('#ng-view').removeClass('view-animate');


		MainNavigationService.page = $route.current.action;
		App.rootScope.current = MainNavigationService.page;

		$rootScope.title = makeTitle();
		App.track('page', $route.current.action);

		$('body').removeClass(function (index, css) {
			return (css.match (/\bpage-\S+/g) || []).join(' ');
		}).addClass('page-' + MainNavigationService.page);

		$('.nav-top').addClass('at-top');

		$('html, body, .snap-content-inner').scrollTop(0);

		App.scrollTop($rootScope.scrollTop);
		if( App.snap && App.snap.close ){
			App.snap.close();
		}

		setTimeout(function() {
			//$('#ng-view').addClass('view-animate');
		},200);

		$rootScope.scrollTop = 0;
	});

	$scope.$on( '$routeChangeStart', function (event, next, current) {

		if (errorInterceptor.isInteruptable($location.url())) {
			errorInterceptor.cancelAll();
		}

		var run = function(){
			if( $rootScope.configLoaded ){
				if (!$rootScope.account.isLoggedIn()) {
					var isAllowed = false;
					angular.forEach( [ '/login', '/setup', '/onboarding', '/info' ], function( allowed ){
					 if( $location.url().indexOf( allowed ) >= 0 ){
						isAllowed = true;
					 }
					} );
					if( $location.url().indexOf( '/drivers/' ) >= 0 ){
						isAllowed = false;
					}
					if( !isAllowed  ) {
						$.totalStorage( 'redirect_to', $location.url() );
						// Force login page
						if( App.isCordova ){
							MainNavigationService.link( '/login' );
						} else {
							window.stop();
							window.location.href = '/login';
							return false;
						}
					}
				} else {
					if( $location.url() == '/login') {
						MainNavigationService.link( '/home' );
					}
				}
			} else {
				setTimeout( function(){ run() }, 100 );
			}
		}

		run();

	});

});

App.alert = function(txt, title, useNativeAlert, fn, unselectable ) {

	setTimeout(function() {
		App.rootScope.closePopup();
		if (useNativeAlert && App.isCordova && parent.window.navigator && parent.window.navigator.notification) {
			parent.window.navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else {

			if( txt && !title && !fn && !unselectable && txt.search( /<|\\|\//i ) == -1 ){
				var level = ( txt.search(/please|erro/i) >= 0 ) ? 'error' : 'success';
				App.rootScope.flash.setMessage( txt, level );
				return;
			}
			App.rootScope.$broadcast('notificationAlert', title || '', txt, fn, unselectable);
		}
	});
};

App.agreementBox = function(txt, title, success, fail) {
	setTimeout(function() {
		App.rootScope.$broadcast('agreementBox', title || '', txt, success, fail);
	} );
};

App.confirm = function(txt, title, success, fail, buttons, force) {
	if ( force || ( App.useNativeConfirm && App.isCordova && parent.window.navigator && parent.window.navigator.notification ) ) {
		setTimeout(function() {
			App.rootScope.$broadcast('notificationConfirm', title || '', txt, success, fail, buttons);
		});
	} else {
		if (confirm(txt)) {
			success();
		} else {
			fail();
		}
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
			// App.location.path(!App.isCordova ? url : 'index.html#' + url);
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
		if (!top && top !== 0) {
			setTimeout(function() {
				$('html, body, .snap-content-inner').scrollTop(0);
			},10);
			return;
		}
		$('html, body, .snap-content-inner').animate({scrollTop: top || 0}, 200, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	},3);
};


/**
 * Sends a tracking item to google, or to google ads if its an order
 */
App.track = function() {
	if (App && App.config && App.config.env != 'live') {
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

	if (json.timezones) {
		moment.tz.load(json.timezones);
	}

	App.rootScope.$broadcast( 'configLoaded' );
};

/**
 * global event binding and init
 */
App.init = function(config) {

	// ensure this function cant be called twice. can crash the browser if it does.
	if (App._init) {
		return;
	}

	App.cordovaListener.init();

	App._init = true;

	$(document).on('touchmove', '.mfp-wrap', function(e) {
		e.preventDefault();
		e.stopPropagation();
	});

	// replace normal click events for mobile browsers
	FastClick.attach(document.body);

	// add ios7 styles for nav bar and page height
	if (App.isCordova && !App.iOS7()) {
		$('body').removeClass('ios7');
	}

	$('body').removeClass('no-init');

	// add the side swipe menu for mobile view
	if (typeof Snap !== 'undefined') {

		App.snap = new Snap({
			element: document.getElementById('snap-content'),
			menu: $('#side-menu, #side-menu-right'),
			menuDragDistance: ( App.isWindows ? 0 : 95 ),
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

		setTimeout( function(){
			snapperCheck();
		}, 1000 );

		$(window).resize(function() {
			snapperCheck();
		});

	}

	// init the storage type. cookie, or localstorage if cordova
	$.totalStorage.ls(App.localStorage);

	// cordova
	if (typeof CB !== 'undefined' && CB.config) {
		App.config = CB.config;
		CB.config = null;
	}

	// process the config, and startup angular
	App.processConfig(config || App.config);

	if ( App.config.env == 'live' ) {
		$( '.footer' ).addClass( 'footer-hide' );
	}

	window.addEventListener( 'pageshow', function(){
		// the first pageshow should be ignored
		if( App._firstPageShowHasHappened ){
			dateTime.reload();
		}
		App._firstPageShowHasHappened = true;
	}, false );

	$( window ).trigger( 'nginit' );

	/*
	if (!App.isCordova) {
		$(document).mousemove(function(e) {
			if ($('.parallax-bg').length) {
				console.log(e.pageX, e.pageY);
			}
		});
	}
	*/


	if (App.isCordova) {
		// setup for system links
		$(document).on('click', 'a[target=_system], a[target=_blank]', function(e) {
			e.preventDefault();
			e.stopPropagation();
			console.debug('Opening link', e.currentTarget.href, e.target.href);
			parent.window.open(e.currentTarget.href || e.target.href, '_system', 'location=yes');
			return false;
		});

		// tap copy bug hax
		document.body.oncopy = function() {
			if (!parent.navigator || !parent.navigator.splashscreen) {
				return;
			}
			parent.navigator.splashscreen.show();
			parent.navigator.splashscreen.hide();
		}
	}

	// preload loader image
	var image = new Image();
	image.src = '/assets/images/ajax_loader_orange_512.gif';

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
	},
	close: function(){
		try {
			$.magnificPopup.close();
		} catch (e) {}
	}
};

// Service to show a flash message
NGApp.factory( 'flash', function( $timeout, $rootScope ) {

	var message = { level : null, text : null };

	var clearMessage = function(){
		$rootScope.$apply(function() {
			var level = ( level ) ? level : 'success';
			message = { level : null, text : null };
		});
	}

	service = {};
	service.setMessage = function( text, level ){
		$rootScope.$apply(function() {
			var level = ( level ) ? level : 'success';
			message = { level : level, text : text };
		});
		$timeout( function() { clearMessage() }, 3 * 1000 );
	}

	service.hasMessage = function(){
		return message.level;
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

App.path = function() {
	var path = parent.loation.hash.replace('#').replace('cockpit.phtml');
};

document.addEventListener('statusTap', function() {
	App.scrollTop(0);
});




// cordova events listeners
App.cordovaListener = {
	init : function(){
		if( App.isCordova ){
			document.addEventListener( 'deviceready', App.cordovaListener.deviceready , false );
			document.addEventListener( 'pause', App.cordovaListener.pause , false );
			document.addEventListener( 'resume', App.cordovaListener.resume , false );
			document.addEventListener( 'online', App.cordovaListener.online , false );
			document.addEventListener( 'offline', App.cordovaListener.offline , false );
			if( !navigator.onLine ){
				App.cordovaListener.offline();
			}
		}
	},
	deviceready : function(){
		// deviceready
	},
	resume : function(){
		// resume
		App.rootScope.$broadcast( 'appResume', false );
	},
	pause : function(){
		// pause
		App.rootScope.$broadcast( 'appPause', false );
	},
	online : function(){
		// online

	},
	offline: function(){
		// offline

	}
};

/**
 * play crunch audio sound
 */
App.playAudio = function(audio) {

	if( App.isCordova && App.isAndroid() ){
		return;
	}

	var path = (App.isCordova ? 'https://cockpit.la/assets/cockpit/audio/' : '/') + 'assets/cockpit/audio/';
	var sound = new Howl({
		urls: [path + audio + '.mp3', path + audio + '.ogg']
	}).play();
}

function handleOpenURL(url) {
	// only happens if being pased from a url in the native app

	var handler = 'cockpit://';

	if (!App.isCordova || url.indexOf(handler) < 0) {
		return;
	}

	url = url.replace(handler, '');
	url = url.replace(/^\//,'');
	url = '/' + url;
	url = url.split('?');
	url = url[0];

	if (App._init) {
		// already launched. just nav
		App.go(url);
	} else {
		// launching with url params
		$(window).on('nginit', function() {
			App.go(url);
		});
	}
};


NGApp.filter('propsFilter', function() {
  return function(items, props) {
    var out = [];

    if (angular.isArray(items)) {
      items.forEach(function(item) {
        var itemMatches = false;

        var keys = Object.keys(props);
        for (var i = 0; i < keys.length; i++) {
          var prop = keys[i];
          var text = props[prop].toLowerCase();
          if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
            itemMatches = true;
            break;
          }
        }

        if (itemMatches) {
          out.push(item);
        }
      });
    } else {
      // Let the output be the input untouched
      out = items;
    }

    return out;
  };
});


var loadImage = new Image()
loadImage.src = '/assets/images/ajax_loader_orange_512.gif';

if (parent.window.getAppVersion) {
	parent.window.getAppVersion(function(v) {
		App.version = v;
	});
}

window.addEventListener( 'focus', function(){ App.rootScope.$broadcast( 'window-focus' ); });
