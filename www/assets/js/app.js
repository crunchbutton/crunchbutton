/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */
var App = {
	tagline: '',
	service: '/api/',
	server: '/',
	imgServer: '/',
	cached: {},
	community: null,
	page: {},
	config: null,
	forceHome: false,
	cookieExpire: new Date(3000,01,01),
	order: {
		cardChanged: false,
		pay_type: 'card',
		delivery_type: 'delivery',
		tip: 'autotip'
	},
	signin : {},
	suggestion : {},
	restaurants: {
		permalink : 'food-delivery',
		forceLoad: false
	},
	defaultRange : 2,
	modal: {},
	hasBack: false,
	_init: false,
	_pageInit: false,
	_identified: false,
	isDeliveryAddressOk : false,
	touchX: null,
	touchY: null,
	touchOffset: null,
	boundingBoxMeters : 8000,
	localStorage: false,
	isPhoneGap: document.location.protocol == 'file:',
	useNativePrompt: false,
	ajaxTimeout: 5000
};

// enable localstorage on phonegap
App.localStorage = App.isPhoneGap;

App.alert = function(txt, title) {
	setTimeout(function() {
		// @todo: #1546
		if (App.useNativePrompt && App.isPhoneGap) {
			navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else {
			alert(txt);
		}
	});
};

App.confirm = function(txt, title) {
	if (App.useNativePrompt && App.isPhoneGap) {
		return navigator.notification.confirm(txt, null, title || 'Crunchbutton');
	} else {
		return confirm(txt);
	}
};

App.connectionError = function() {
	App.rootScope.$broadcast('notificationAlert', 'Connection Error', 'Sorry! We could not reach the server right now. Try again when your internet is back!');
};

App.go = function(url) {
	// @todo: do some tests to figure out if we need this or not
	// App.location.path(!App.isPhoneGap ? url : 'index.html#' + url);
	App.location.path(url);
	App.rootScope.$safeApply();

};


App.toggleMenu = function() {
	if (App.snap.state().state == 'left') {
		App.snap.close();
	} else {
		App.snap.open('left');
	}
};


App.NGinit = function() {
	$('body').attr('ng-controller', 'AppController');
	angular.bootstrap(document,['NGApp']);
	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
};

var NGApp = angular.module('NGApp', []);

NGApp.config(function($compileProvider){
	$compileProvider.urlSanitizationWhitelist(/.*/);
});

NGApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
	$routeProvider
		.when('/location', {
			action: 'location',
			controller: 'location',
			templateUrl: 'assets/view/location.html'
		})
		.when('/' + App.restaurants.permalink, {
			action: 'restaurants',
			controller: 'restaurants',
			templateUrl: 'assets/view/restaurants.html'
		})
		.when('/' + App.restaurants.permalink + '/:id', {
			action: 'restaurant',
			controller: 'restaurant',
			templateUrl: 'assets/view/restaurant.html'
		})
		.when('/legal', {
			action: 'legal',
			controller: 'legal',
			templateUrl: 'assets/view/legal.html'
		})
		.when('/help', {
			action: 'help',
			controller: 'help',
			templateUrl: 'assets/view/help.html'
		})
		.when('/orders', {
			action: 'orders',
			controller: 'orders',
			templateUrl: 'assets/view/orders.html'
		})
		.when('/order/:id', {
			action: 'order',
			controller: 'order',
			templateUrl: 'assets/view/order.html'
		})
		.when('/cities', {
			action: 'cities',
			controller: 'cities',
			templateUrl: 'assets/view/cities.html'
		})
		.when('/giftcard', {
			action: 'giftcard',
			controller: 'giftcard',
			templateUrl: 'assets/view/home.html'
		})
		.when('/giftcard/:id', {
			action: 'giftcard',
			controller: 'giftcard',
			templateUrl: 'assets/view/home.html'
		})
		.when('/reset', {
			action: 'reset',
			controller: 'reset',
			templateUrl: 'assets/view/home.html'
		})
		.when('/reset/:id', {
			action: 'reset',
			controller: 'reset',
			templateUrl: 'assets/view/home.html'
		})
		.when('/', {
			action: 'home',
			controller: 'home',
			templateUrl: 'assets/view/home.html'
		})
		.otherwise({
			action: 'home.default',
			controller: 'default',
			templateUrl: 'assets/view/home.html'
		})
	;

	// only use html5 enabled location stuff if its not in a phonegap container
	$locationProvider.html5Mode(!App.isPhoneGap);
}]);

// global route change items
NGApp.controller('AppController', function ($scope, $route, $routeParams, $rootScope, $location, AccountService, MainNavigationService, AccountSignOut) {

	// define external pointers
	App.rootScope = $rootScope;
	App.location = $location;

	// define global services
	$rootScope.account = AccountService;
	$rootScope.navigation = MainNavigationService;
	$rootScope.signout = AccountSignOut;
	
	$rootScope.$on('userAuth', function(e, data) {
		$rootScope.$safeApply(function($scope) {
			// @todo: remove double data
			if (data) {
				App.rootScope.account.user = data;
				App.config.user = data;
			}
		});
	});

	/* @todo: remove this. this is how you watch an object rather than a property so i remeber
	$rootScope.$watch('account.user', function() {
		// indicates that the user object has changed
	}, true);
	*/
	
	$rootScope.link = function(link) {
		$location.path(link || '/');
	};

	$rootScope.back = function() {
		history.back();
	};

	$rootScope.nl2br = function(t) {
		return App.nl2br(t);
	};

	$rootScope.formatPhone = function(t) {
		return App.phone.format(t);
	};

	$rootScope.formatPrice = function(t) {
		return parseFloat(t).toFixed(2);
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

	$scope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {

			// Store the actual page
			MainNavigationService.page = $route.current.action;

			var renderAction = $route.current.action;
			var renderPath = renderAction.split('.');

			$scope.renderAction = renderAction;
			$scope.renderPath = renderPath;
			
			console.log($scope.renderAction);

			if (App.isChromeForIOS()){
				App.message.chrome();
			}

			$('body').removeClass(function (index, css) {
				return (css.match (/\broute-\S+/g) || []).join(' ');
			}).addClass('route-' + renderAction);
			
			setTimeout(function() {
				App.scrollTop();
			},1);

			/*
			$('.content').addClass('smaller-width');
			$('.main-content').css('width','auto');

			$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
			$( '.nav-back' ).addClass( 'nav-back-show' );
			*/

		}
	);

	AccountService.checkUser();
});


/**
 * scroll to the top of the page
 */
App.scrollTop = function() {
	$('html, body, .snap-content-inner').scrollTop(0);
};


/**
 * Sends a tracking item to mixpanel, or to google ads if its an order
 */
App.track = function() {
	if (App.config.env != 'live') {
		return;
	}
	if (arguments[0] == 'Ordered') {
		$('img.conversion').remove();
		mixpanel.people.track_charge(arguments[1].total);
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + location.href + '">').appendTo($('body'));
	}
	if (arguments[1]) {
		mixpanel.track(arguments[0],arguments[1]);
	} else {
		mixpanel.track(arguments[0]);
	}
};


/**
 * Tracks a property to mixpanel
 */
App.trackProperty = function(prop, value) {
	//  || App.config.env != 'live'
	if (App.config.env != 'live') {
		return;
	}

	var params = {};
	params[prop] = value;

	mixpanel.register_once(params);
};

/**
 * Itendity the user to mixpanel
 */
App.identify = function() {
	if (App.config.env != 'live') {
		return;
	}
	if (App.config.user.uuid) {
		mixpanel.identify(App.config.user.uuid);
		mixpanel.people.set({
			$name: App.config.user.name,
			$ip: App.config.user.ip,
			$email: App.config.user.email
		});
	}
};

/**
 * controls the busy state of the app
 */
App.busy = {
	isBusy: function() {
		return $('.app-busy').length ? true : false;
	},
	makeBusy: function() {
		$('body').append($('<div class="app-busy"></div>').append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>')));
	},
	unBusy: function() {
		$('.app-busy').remove();
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
		$.totalstorage('community',null);
		$.totalstorage('location_lat',null);
		$.totalstorage('location_lon',null);
		location.href = '/';
	},
	init: function() {
		$('.test-card').click(function() {
			App.test.card();
		});
		$('.test-logout').click(function() {
			App.test.logout();
		});
		$('.test-cart').click(function() {
			App.test.cart();
		});
		$('.test-clearloc').click(function() {
			App.test.clearloc();
		});
	}
};

App.processConfig = function(json, user) {
	if (user && !json) {
		App.config.user = user;
	} else {
		App.config = json;
	}
	App.AB.init();
	if (App.config.user) {
		App.identify();
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
	
	// set a timeout for when ajax requests timeout
	$.ajaxSetup({
		timeout: App.ajaxTimeout
	});


	// replace normal click events for mobile browsers
	FastClick.attach(document.body);
	
	// add ios7 styles for nav bar and page height
	if (App.isPhoneGap && App.iOS7()) {
		$('body').addClass('ios7');
	}
	
	// add the side swipe menu for mobile view
	App.snap = new Snap({
		element: document.getElementById('snap-content'),
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

	// init the storage type. cookie, or localstorage if phonegap
	$.totalStorage.ls(App.localStorage);
	
	// phonegap
	if (typeof CB !== 'undefined' && CB.config) {
		App.config = CB.config;
		CB.config = null;
	}

	if (App.isMobile()) {

		// prevent double trigger
		$(document).on('click','input[type="checkbox"]', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		// manually rebind checkbox events
		$('input[type="checkbox"]').click(function(e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).checkToggle();
		});

		// manually rebind labels
		$('label[for]').click(function(e) {
			e.stopPropagation();
			e.preventDefault();
			var target = document.getElementById($(this).attr('for'));
			if (target && target.tagName == 'INPUT') {
				switch ($(target).attr('type')) {
					case 'text':
					case 'password':
					case 'number':
					case 'phone':
					case 'tel':
						$(target).focus();
						break;
					case 'checkbox':
						$(target).checkToggle();
						break;
				}
			}
			$(this).checkToggle();
		});

		// manually bind links
		// @todo: intercept for native app
		$('a[href]').click(function(e) {
			var el = $(this);
			var href = el.attr('href');

			if (!href || e.defaultPrevented) {
				return;
			}

			if ($(this).attr('target')) {
				window.open($(this).attr('href'), $(this).attr('target'));
			} else {
				location.href = $(this).attr('href');
			}
		});


		// ignore all click events from acidently triggering on mobile. only use click
		/*
		$(document).on('click', function(e, force) {
			e.stopPropagation();
			e.preventDefault();
		});
		*/
	}

	$(document).on('click', '.button-deliver-payment, .dp-display-item a, .dp-display-item .clickable', function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});

	$(document).on({
		mousedown: function() {
			$(this).addClass('location-detect-click');
		},
		touchstart: function() {
			$(this).addClass('location-detect-click');
		},
		mouseup: function() {
			$(this).removeClass('location-detect-click');
		},
		touchend: function() {
			$(this).removeClass('location-detect-click');
		}
	}, '.location-detect');

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

	App.processConfig(config || App.config);
	App.AB.init();
	App.test.init();
	App.NGinit();
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
			// its a dom element
			var src = $(arguments[0]);

		} else {
			console.log('ERROR WITH DIALOG');
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
			mainClass: 'my-mfp-zoom-in', //my-mfp-slide-bottom
			callbacks: {
				open: function() {
					setTimeout(function() {
						//$('.wrapper').addClass('dialog-open-effect-b');
					},1);
				},
				close: function() {
					$('.wrapper').removeClass('dialog-open-effect-a dialog-open-effect-b dialog-open-effect-c dialog-open-effect-d');
				}
			}
			//my-mfp-zoom-in
		});
	}
};


App.message = {
	chrome: function() {
		var title = 'How to use Chrome',
			message = '<p>' +
			'Just tap "Request Desktop Site."' +
			'</p>' +
			'<p align="center">' +
			'<img style="border:1px solid #000" src="/assets/images/chrome-options.png" />' +
			'</p>';
		App.dialog.show(title, message);
	}
};


/**
 * play crunch audio sound
 */
App.playAudio = function(audio) {
	if (App.isPhoneGap) {
		try {
			navigator.notification.vibrate(100);
		} catch (e) {}
	}
	var audio = $('#' + audio).get(0);
	if (!audio) { return };
	try {
		audio.play();
	} catch(e){}
}
