/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */
var App = {
	cartHighlightEnabled: false,
	currentPage: null,
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
	defaultTip: 'autotip',
	defaultRange : 2,
	modal: {},
	hasBack: false,
	_init: false,
	_pageInit: false,
	_identified: false,
	isDeliveryAddressOk : false,
	tips: [0,10,15,18,20,25,30],
	touchX: null,
	touchY: null,
	touchOffset: null,
	crunchSoundAlreadyPlayed : false,
	useCompleteAddress : false, /* if true it means the address field will be fill with the address found by google api */
	completeAddressWithZipCode : true,
	boundingBoxMeters : 8000,
	localStorage: false,
	isPhoneGap: document.location.protocol == 'file:'
};

// enable localstorage on phonegap
App.localStorage = App.isPhoneGap;


App.alert = function(txt, title) {
	setTimeout(function() {
		// @todo: #1546
		if (1==2 && App.isPhoneGap) {
			navigator.notification.alert(txt, null, title || 'Crunchbutton');
		} else {
			alert(txt);
		}
	});
};

App.go = function(url) {
	App.rootScope.$apply(function($location) {
		$location.path(App.isPhoneGap ? url : 'index.html#' + url);
	});
};


App.toggleMenu = function() {
	if (App.snap.state().state == 'left') {
		App.snap.close();
	} else {
		App.snap.open('left');
	}
};



/**
 * Loads up "community" keyword pages
 */
 /*
	TODO: replace this method by the CommunityAliasService
 */
App.routeAlias = function( id, success, error ) {
	id = id.replace('/','').toLowerCase();
	alias = App.aliases[id] || false;
	success = success || function(){};
	error = error || function(){};

	if (alias) {
		// Get the location of the alias
		var loc = App.locations[alias.id_community];

		if (loc.loc_lat && loc.loc_lon) {

			var res = new Location({
				lat: loc.loc_lat,
				lon: loc.loc_lon,
				type: 'alias',
				verified: true,
				prep: alias.prep,
				city: alias.name_alt,
				address: alias.name_alt
			});
			success({alias: res});
			return;
		}
	}

	error();
};


App.NGinit = function() {
	$('body').attr('ng-controller', 'AppController');
	angular.bootstrap(document,['NGApp']);
	App.credit.tooltip.init();

	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
};


var NGApp = angular.module('NGApp', []);

// Press enter directive
NGApp.directive( 'ngEnter', function() {
		return function( scope, element, attrs ) {
				element.bind( 'keydown keypress', function( event ) {
					if( event.which === 13 ) {
						scope.$apply( function() {
							scope.$eval( attrs.ngEnter );
						} );
						event.preventDefault();
					}
				} );
		};
} );


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
NGApp.controller('AppController', function ($scope, $route, $routeParams, $rootScope, $location, AccountService, MainNavigationService, LocationService) {

	App.rootScope = $rootScope;
	App.location = $location;

	AccountService.checkUser();

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

	$scope.$on(

		'$routeChangeSuccess',
		function ($currentRoute, $previousRoute) {

			// Store the actual page
			MainNavigationService.page = $route.current.action;
			return;

			var renderAction = $route.current.action;
			var renderPath = renderAction.split('.');

			$scope.renderAction = renderAction;
			$scope.renderPath = renderPath;

			console.debug('ROUTE >',$route.current.action, renderAction);


			if (App.isChromeForIOS()){
				App.message.chrome();
			}

			setTimeout(function() {
				scrollTo(0, 1);
			}, 80);

			/*
			if (!App.isNarrowScreen()) {
				return false;
			}

			$( '.sign-in-icon' ).removeClass( 'config-icon-mobile-hide' );
			$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );
			switch (renderAction) {
				case 'restaurant':
				case 'order':
					$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
					break;

				case 'home':
					$( '.config-icon' ).addClass( 'config-icon-back-home' );
					break;

				case 'orders':
					$( '.sign-in-icon' ).addClass( 'config-icon-mobile-hide' );
					$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
					break;

				case 'restaurants':
					$( '.sign-in-icon' ).addClass( 'left' );
					$( '.config-icon' ).addClass( 'right' );
					break;
			}


			$('.content').addClass('smaller-width');
			$('.main-content').css('width','auto');


	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	$( '.nav-back' ).addClass( 'nav-back-show' );

			*/

		}
	);
});


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
		$('[name="pay-card-number"]').val('4242424242424242');
		$('[name="pay-card-month"]').val('1');
		$('[name="pay-card-year"]').val('2020');

		$('[name="pay-name"]').val('MR TEST');
		$('[name="pay-phone"]').val('***REMOVED***');
		$('[name="pay-address"]').val( App.restaurant.address || "123 main\nsanta monica ca" );

		App.order.cardChanged = true;
		App.creditCard.changeIcons( $( '[name="pay-card-number"]' ).val() );
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
		App.order['pay_type'] = App.config.user['pay_type'];
		App.order['delivery_type'] = App.config.user['delivery_type'];
		var lastTip = App.config.user['last_tip'] || 'autotip';
		lastTip = App.lastTipNormalize( lastTip );
		App.order['tip'] = lastTip;
	}
};


App.lastTipNormalize = function( lastTip ){

	if( lastTip === 'autotip' ) {
		return lastTip;
	}

	lastTip = parseInt( lastTip );
	if( App.config.user && App.config.user.last_tip_type && App.config.user.last_tip_type == 'number' ){
		return App.defaultTip;
	}
	// it means the last tipped value is not at the permitted value, return default.
	if( App.tips.indexOf( lastTip ) > 0 ){
		lastTip = lastTip;
	} else {
		lastTip = App.defaultTip;
	}
	return lastTip;
}

App.trigger = {
	delivery: function() {
		$('.delivery-toggle-takeout').removeClass('toggle-active');
		$('.delivery-toggle-delivery').addClass('toggle-active');
		$('.delivery-only').show();
		App.order['delivery_type'] = 'delivery';
		App.cart.updateTotal();
	},
	takeout: function() {
		$('.delivery-toggle-delivery').removeClass('toggle-active');
		$('.delivery-toggle-takeout').addClass('toggle-active');
		$('.delivery-only, .field-error-zip').hide();
		App.order['delivery_type'] = 'takeout';
		App.cart.updateTotal();
	},
	credit: function() {
		$('.pay-toggle-cash').removeClass('toggle-active');
		$('.pay-toggle-credit').addClass('toggle-active');
		$('.card-only').show();
		App.order['pay_type'] = 'card';
		App.cart.updateTotal();
	},
	cash: function() {
		$('.pay-toggle-credit').removeClass('toggle-active');
		$('.pay-toggle-cash').addClass('toggle-active');
		$('.card-only').hide();
		App.order['pay_type'] = 'cash';
		App.cart.updateTotal();
	}
}


/**
 * global event binding and init
 */
App.init = function(config) {
	// ensure this function cant be called twice. can crash the browser if it does.
	if (App._init) {
		return;
	}
	App._init = true;

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

	// bind global events
	// @todo: should probably move some of these to angular event bindings
	$(document).on('click', '.delivery-toggle-delivery', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.delivery();
		App.track('Switch to delivery');
	});

	$(document).on('click', '.delivery-toggle-takeout', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.takeout();
		App.track('Switch to takeout');
	});

	$(document).on('click', '.pay-toggle-credit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.credit();
		App.track('Switch to card');
	});

	$(document).on('click', '.pay-toggle-cash', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.cash();
		App.track('Switch to cash');
	});

	$(document).on('click', '.location-detect', function() {
		// detect location from the browser
		$('.location-detect-loader').show();
		$('.location-detect-icon').hide();

		var error = function() {
			$('.location-address').val('Oh no! We couldn\'t locate you');
			$('.location-detect-loader').hide();
			$('.location-detect-icon').show();
		};

		var success = function() {
			App.page.foodDelivery();
//			$('.location-detect-loader').hide();
//			$('.location-detect-icon').show();
//			$('.button-letseat-form').click();
		};

		App.loc.getLocationByBrowser(success, error);
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
/*
	$('.dish-item').click(function() {
		App.cart.add($(this).attr('data-id_dish'));
	});

	$('.cart-button-remove').click(function() {
		App.cart.remove($(this).closest('.cart-item'));
	});

	$('.cart-button-add').click(function() {
		App.cart.clone($(this).closest('.cart-item'));
	});

	$('.cart-item-config a').click(function() {
		App.cart.customize($(this).closest('.cart-item'));
	});
*/
	$('.button-submitorder-form').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.crunchSoundAlreadyPlayed = false;
		App.isDeliveryAddressOk = false;
		App.cart.submit($(this),true);
	});

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
/*
	$(document).on('change', '.cart-customize-select', function() {
		App.cart.customizeItem($(this));
	});
*/
	$( '.default-order-check' ).click( function(){
		setTimeout( function(){
			$( '#default-order-check' ).checkToggle();
		}, 1 );
	} );
/*
	$('.cart-customize-check').click( function() {
		var checkbox = $(this);
		setTimeout( function(){
			if( !App.isMobile() ){
				checkbox.checkToggle();
			}
			App.cart.customizeItem( checkbox );
		}, 1 );
	});

	$('.cart-item-customize-item label').click(function() {
		$(this).prev('input').checkToggle();
		App.cart.customizeItem( $(this).prev('input') );
	});
*/
	$(document).on('change', '[name="pay-tip"]', function() {
		App.order.tip = $(this).val();
		App.order.tipHasChanged = true;
		var total = App.cart.total();
		App.cart.updateTotal();
	});

	$(document).on('change', '[name="pay-card-number"], [name="pay-card-month"], [name="pay-card-year"]', function() {
		if( !App.order.cardChanged ){
			var self = $( this );
			var cardInfo = [ '[name="pay-card-number"]', '[name="pay-card-month"]', '[name="pay-card-year"]' ];
			$( cardInfo ).each( function( index, value ){
				var input = $( value );
				if( self.attr( 'name' ) != input.attr( 'name' ) ){
					input.val( '' );
				}
			} )
		}
		App.order.cardChanged = true;
	});

	$(document).on('change, keyup', '[name="pay-card-number"]', function() {
		App.creditCard.changeIcons( $(this).val() );
	});

	$(document).on('keyup', '[name="pay-phone"]', function() {
		$(this).val( App.phone.format($(this).val()) );
	});


	$('.cart-summary').click(function(e) {
		e.stopPropagation();
		e.preventDefault();
		$('html, body').animate({
			scrollTop: $('.cart-items').position().top-80
		}, {
			duration: 500,
			specialEasing: {
				scrollTop: 'easeInOutQuart'
			}
		});
	});

	// hide the top bar when any input is focused
	/*
	if (App.isMobile() && !App.isAndroid()) {
		setInterval(function() {
			var focused = $(':focus');
			if (!focused.length) {
				$('[data-position="fixed"]').show();
				return;
			}

			focused = focused.get(0);

			if (focused.tagName == 'SELECT' || focused.tagName == 'INPUT' || focused.tagName == 'TEXTAREA') {
				// @todo: fix this so it hides
				//$('[data-position="fixed"]').hide();
			} else {
				$('[data-position="fixed"]').show();
			}
		}, 100);
	}
	*/

	$(document).on({
		blur: function() {
			clearTimeout(App.checkForDistance);
			App.checkForDistance = setTimeout(checkForDistance, 100);
		},
		change: function() {
			clearTimeout(App.checkForDistance);
			App.checkForDistance = setTimeout(checkForDistance, 1000);
		}
	}, '[name="pay-address"]');


	$(document).on('change', '[name="pay-address"], [name="pay-name"], [name="pay-phone"], [name="pay-card-number"], [name="notes"]', function() {
		App.config.user.name = $('[name="pay-name"]').val();
		App.config.user.phone = App.phone.format($('[name="pay-phone"]').val());
		App.config.user.address = $('[name="pay-address"]').val();
		App.config.user.card = $('[name="pay-card-number"]').val();
		App.config.user.notes = $('[name="notes"]').val();
		App.config.user.card_exp_month = $('[name="pay-card-month"]').val();
		App.config.user.card_exp_year = $('[name="pay-card-year"]').val();
	});

	// @depreciated: i dont think this is used anymore
	/*
	$( '.ui-dialog-titlebar-close' ).click( function(){
		try{
			$( '.ui-dialog-content' ).dialog( 'close' );
		} catch(e){}
	} );
	*/

	App.processConfig(config || App.config);
	App.test.init();
	App.NGinit();
};

App.getCommunityById = function( id ){
	for (x in App.communities) {
		if( App.communities[x].id_community == id ){
			return App.communities[x];
		}
	}
	return false;
}

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
			mainClass: 'my-mfp-slide-bottom',
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

