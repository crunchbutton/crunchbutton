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
	logService: 'http://log.crunchbutton.com/api/',
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
	isPhoneGap: document.location.protocol == 'file:',
	useNativeAlert: false,
	useNativeConfirm: true,
	ajaxTimeout: 5000,
	splashHidden: false,
	restaurantsPaging: false
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

var NGApp = angular.module('NGApp', []);

NGApp.config(function($compileProvider){
	$compileProvider.urlSanitizationWhitelist(/.*/);
});

// This config will intercept all the ajax requests and take care of the errors
NGApp.config(function ( $httpProvider) {

	var interceptor = function ( $q, $injector ) {

		function success( response ) {
			return response;
		}

		function error( response ) {
			var status = response.status;
			// URL not found or offline
			if( status == 0 ){
				var showError = false;
				var url = response.config.url;
				if ( url ) {
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
				}
			}
			return $q.reject( response );
		}

		return function ( promise ) {
			return promise.then( success, error );
		}
	};
	$httpProvider.responseInterceptors.push(interceptor);
});

NGApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider, RestaurantsService) {
	$routeProvider
		.when('/location', {
			action: 'location',
			controller: 'LocationCtrl',
			templateUrl: 'assets/view/location.html'
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
		.when('/invite/:id', {
			action: 'invite',
			controller: 'InviteCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.when('/', {
			action: 'home',
			controller: 'HomeCtrl',
			templateUrl: 'assets/view/home.html'
		})
		.otherwise({
			action: 'home',
			controller: 'DefaultCtrl',
			templateUrl: 'assets/view/home.html'
		})
	;
	// only use html5 enabled location stuff if its not in a phonegap container
	$locationProvider.html5Mode(!App.isPhoneGap);
}]);

// global route change items
NGApp.controller('AppController', function ($scope, $route, $routeParams, $rootScope, $location, $window, AccountService, MainNavigationService, AccountSignOut, CartService) {

	// define external pointers
	App.rootScope = $rootScope;
	App.location = $location;

	// define global services
	$rootScope.account = AccountService;
	$rootScope.navigation = MainNavigationService;
	$rootScope.signout = AccountSignOut;
	
	$rootScope.test = App.test;
	
	$rootScope.cartScroll = function() {
		$('html, body, .snap-content-inner').animate({scrollTop: 156}, 100, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	};
	
	$rootScope.$on('userAuth', function(e, data) {
		$rootScope.$safeApply(function($scope) {
			// @todo: remove double data
			if (data) {
				$rootScope.account.user = data;
				App.config.user = data;
			}
			// If the user logged out clean the cart!
			if( !App.config.user.id_user ){
				CartService.clean();
			}
			// reload the actual controller
			console.log('userAuth!');
			$rootScope.reload();
		});
	});

	$rootScope.focus = function( selector ){
		setTimeout(function(){
			angular.element( selector ).focus();
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
		App.go.apply(arguments);
	};

	$rootScope.back = function() {
		App.snap.close();
		var backwards = false;

		switch($route.current.action) {
			case 'order':
				backwards = '/orders';
				break;
			case 'restaurant':
				backwards = '/food-delivery';
				break;
			case 'restaurants':
				backwards = '/location';
				break;
		}

		if (backwards) {
			App.go(backwards);
		} else {
			history.back();
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

		$('body').removeClass(function (index, css) {
			return (css.match (/\bpage-\S+/g) || []).join(' ');
		}).addClass('page-' + MainNavigationService.page);
		
		setTimeout(function() {
			App.scrollTop();
		},1);
		
		clearInterval($rootScope.updateOpen);
		
		if (App.isPhoneGap && !App.splashHidden && navigator.splashscreen) {
			App.splashHidden = true;
			setTimeout(function() {
				navigator.splashscreen.hide();
			},1);
		}
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

	AccountService.checkUser();

});

App.alert = function(txt, title) {
	setTimeout(function() {
		// @todo: #1546
		if (App.useNativeAlert && App.isPhoneGap) {
			navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else {
			App.rootScope.$broadcast('notificationAlert', title || null, txt);
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
	App.rootScope.$broadcast('notificationAlert', 'Connection Error', 'Sorry! We could not reach the server right now. Try again when your internet is back!');
};

App.go = function(url) {
	// @todo: do some tests to figure out if we need this or not
	// App.location.path(!App.isPhoneGap ? url : 'index.html#' + url);
	App.location.path(url || '/');
	App.rootScope.$safeApply();

};

App.toggleMenu = function() {
	if (App.snap.state().state == 'left') {
		App.snap.close();
	} else {
		App.snap.open('left');
	}
};


/**
 * scroll to the top of the page
 */
App.scrollTop = function() {
	setTimeout(function() {
		$('html, body, .snap-content-inner').animate({scrollTop: 0}, 10, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
	},1);
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
 * @sky-loader
 */
App.busy = {
	_busy: false,
	stage: function() {
		$('#Stage').height('100%').width('100%');
		return AdobeEdge.getComposition('EDGE-977700350').getStage();
	},
	isBusy: function() {
		return $('.app-busy').length ? true : false;
		return App.busy._busy;
	},
	makeBusy: function() {
		return $('body').append($('<div class="app-busy"></div>').append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>')));
		App.busy._busy = true;
		$('.order-sky-loader').addClass('play');
		App.busy.stage().play(0);
	},
	unBusy: function() {
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
		$.totalstorage('community',null);
		$.totalstorage('location_lat',null);
		$.totalstorage('location_lon',null);
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

	App.verifyConnection.init();

	// temporary fix for drawers overslcrolling
	$(document).on('touchmove', '.snap-drawers, .mfp-wrap', function(e) {
		e.preventDefault();
		e.stopPropagation();
	});

	/* @todo: need to finish this
	var lastX, lastY, dThresh = 10;
	$(document).on('touchmove', 'body', function(e) {
		e = e.originalEvent;
		return;

		var currentY = e.touches[0].clientY;
		if (currentY > lastY) {
			console.log('DOWN');
		} else {
			console.log('UP');
		}
		lastY = currentY;
	
		var currentX = e.touches[0].clientX;
		if (currentX > lastX) {
			console.log('RIGHT');
			e.preventDefault();
		} else {
			console.log('LEFT');
			e.preventDefault();
		}
		lastX = currentX;		
	});
	*/

	
	// set a timeout for when ajax requests timeout
	if (App.isPhoneGap) {
		$.ajaxSetup({
			timeout: App.ajaxTimeout
		});
	}


	// replace normal click events for mobile browsers
	FastClick.attach(document.body);
	
	// add ios7 styles for nav bar and page height
	if (App.isPhoneGap && !App.iOS7()) {
		$('body').removeClass('ios7');
	}
	
	// add the side swipe menu for mobile view
	App.snap = new Snap({
		element: document.getElementById('snap-content'),
		disable: 'right'
	});

	App.snap.on( 'end', function(){
		App.applyIOSPositionFix();
	});

	App.snap.on( 'start', function(){
		App.applyIOSPositionFix();
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

	$(document).on('click', '.button-deliver-payment, .dp-display-item a, .dp-display-item .clickable', function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});

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
	App.NGinit();

	// Remove the old cookies #1705
	$.cookie('loc','', { expires : ( new Date(1970,01,01) ) });
	$.cookie('locv2','', { expires : ( new Date(1970,01,01) ) });

/*
	if( App.config.user.id_user ){
		var account = angular.element( 'html' ).injector().get( 'AccountService' );
		account.user = App.config.user;
		account.updateInfo();
	}
*/
	// #1774
	if( App.iOS() ){
		$(':input').focus( function() {
			$(window).scrollTop( $(window).scrollTop() + 1 );
		});
	}

	App.phoneGapListener.init();

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
						if( App.iOS() ){
							// #1774
							var width = angular.element('.mfp-bg').width();
							angular.element('.mfp-bg').width( width + 10 );
						}
					},1);
				},
				close: function() {
					$('.wrapper').removeClass('dialog-open-effect-a dialog-open-effect-b dialog-open-effect-c dialog-open-effect-d');
					App.applyIOSPositionFix();
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

// Hack to fix iOS the problem with body position when the keyboard is shown #1774
App.applyIOSPositionFix = function(){
	if( App.iOS() ){
		setTimeout( function(){
			angular.element('body').css('width', '+=1').css('width', '-=1');
			$('.navs').css('width','+=1').css('width','-=1');
			$('.navs').css('left', '+=1').css('left', '-=1');
			// Again to make sure it will really fix it!
			setTimeout( function(){
				angular.element('body').css('width', '+=1').css('width', '-=1');
				$('.navs').css('width','+=1').css('width','-=1');
				$('.navs').css('left', '+=1').css('left', '-=1');
			}, 300 );
		}, 200 );
	}
}

// Methods used by phoneGap
App.verifyConnection = {
	isOffLine: false,
	forceReload: false,
	init: function () {
		if (App.isPhoneGap) {
			App.verifyConnection.check();
		}
	},
	check: function () {
		var networkState = navigator.connection.type;
		if (networkState == Connection.NONE) {
			// If the app starts without internet, force reload it.
			App.verifyConnection.forceReload = true;
			App.verifyConnection.goOffline();
		}
	},
	goOffline: function () {
		if (App._remoteConfig) {
			return;
		}
		$('.connection-error').show();
		navigator.splashscreen.hide();
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

// Phonegap events listeners
App.phoneGapListener = {
	init : function(){
		if( App.phoneGap ){
			document.addEventListener( 'deviceready', App.phoneGapListener.deviceready , false );
			document.addEventListener( 'pause', App.phoneGapListener.pause , false );
			document.addEventListener( 'resume', App.phoneGapListener.resume , false );
			// document.addEventListener( 'online', App.phoneGapListener.online , false );
			// document.addEventListener( 'offline', App.phoneGapListener.offline , false );
		}
	},
	deviceready : function(){
		// deviceready
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
		// App.verifyConnection.goOnline();
	},
	offline : function(){
		// offline
		// App.verifyConnection.goOffline();
	}
};

App.tokenizeCard = function(card, complete) {
	balanced.card.create(card, function(response) {
		var res = {
			status: false
		};
		switch (response.status) {
			case 201:
				res.status = true;
				res.id = response.data.id;
				res.uri = response.data.uri;
				res.lastfour = response.data.last_four;
				res.month = card.expiration_month;
				res.year = card.expiration_year;
				break;

			case 400:
				res.error = 'Missing fields';
				break;

			case 402:
				res.error = 'Unable to authorize';
				break;

			case 404:
				res.error = 'Unexpected error';
				break;
				
			case 409:
				res.error = 'Unable to validate';
				break;

			case 500:
				res.error = 'Error processing payment';
				break;
				
			case 999:
				res.error = 'Unable to reach payment server';
				break;
		}
		console.debug('Balanced tokenization response',response);
		complete(res);
	});
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

	if (App.isPhoneGap && App.iOS()) {
		var description = params.caption + ".\r\n" + params.description.replace(/(<br \/>)|(\n)/g,'');
		CDV.FB.share([params.url, params.name, '', description, pic], params.success, params.fail);

	} else {
		FB.ui({
			method: 'feed',
			user_message_prompt: 'CrunchButton',
			link: params.url,
			href: params.url,
			picture: pic,
			name: params.name,
			caption:params.caption,
			description: params.description,
			attachment: {
				name: 'CrunchButton',
				caption: ' ',
				description: params.url,
				href: params.url,
				media:[{
					type: 'image',
					src: pic,
					href: params.url
				}],
			},
			action_links: [{ text: 'CrunchButton', href: 'https://crunchbutton.com' } ],
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