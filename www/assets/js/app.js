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
	cached: {},
	community: null,
	page: {},
	locs: [],
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
		list: false
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
	useRestaurantBoundingBox : false
};

App.alert = function(txt) {
	setTimeout(function() {
		alert(txt);
	});
};

App.loadRestaurant = function(id) {

};


/**
 * Loads up "community" keyword pages
 */
App.routeAlias = function(id, success, error) {
	id = id.toLowerCase();
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

App.loadHome = function(force) {
	$('input').blur();

	App.currentPage = 'home';
	History.pushState({}, 'Crunchbutton', '/');
	
	App.page.home(force);

};

App.render = function(template, data) {
	var compiled = _.template($('.template-' + template).html());
	return compiled(data);
};

App.showPage = function(params) {

	// Hides the pacman
	App.controlMobileIcons.hidePacman();

	// Hides the gift card message
	App.credit.hide();

	// switch here for AB testing
	App.currentPage = params.page;
	if (params.title) {
		document.title = params.title;
	}

	// #1227 - on mobile view switch change location and profile buttons
	App.controlMobileIcons.process( params.page );

	// track different AB pages
	if (params.tracking) {
		App.track(params.tracking.title, params.tracking.data);
	}
};


App.NGinit = function() {
	$('body').attr('ng-controller', 'AppController');
	angular.bootstrap(document,['NGApp']);
	App.loc.init();
	
	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
	
	setTimeout( function(){ App.signin.checkUser(); }, 300 );
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
			templateUrl: 'view/location.html'
		})
		.when('/food-delivery', {
			action: 'restaurants',
			controller: 'restaurants',
			templateUrl: 'view/restaurants.html'
		})
		.when('/food-delivery/:id', {
			action: 'user.view',
			controller: 'user',
			templateUrl: 'view/user.html'
		})
		.when('/legal', {
			action: 'legal',
			controller: 'legal',
			templateUrl: 'view/legal.html'
		})
		.when('/help', {
			action: 'help',
			controller: 'help',
			templateUrl: 'view/help.html'
		})
		.when('/order/:id', {
			action: 'order',
			controller: 'order',
			templateUrl: 'view/order.html'
		})
		.when('/cities', {
			action: 'cities',
			controller: 'cities',
			templateUrl: 'view/cities.html'
		})
		.when('/giftcard/:id', {
			action: 'giftcard',
			controller: 'giftcard',
			templateUrl: 'view/giftcard.html'
		})
		.when('/reset', {
			action: 'reset',
			controller: 'reset',
			templateUrl: 'view/reset.html'
		})
		.when('/', {
			action: 'home',
			controller: 'home',
			templateUrl: 'view/home.html'
		})
		.otherwise({
			action: 'home.default',
			controller: 'default',
			templateUrl: 'view/home.html'
		})
	;
	$locationProvider.html5Mode(true);
}]);


// global route change items
NGApp.controller('AppController', function ($scope, $route, $routeParams) {

	render = function() {
		$('.splash').fadeOut(200, function() {
			$('.content').fadeIn(300);
		});

		var renderAction = $route.current.action;
		var renderPath = renderAction.split('.');

		$scope.renderAction = renderAction;
		$scope.renderPath = renderPath;
	};

	$scope.$on(
		'$routeChangeSuccess',
		function ($currentRoute, $previousRoute) {
			console.log('route change' , $currentRoute)
			// Update the rendering.
			render();

			if (App.isChromeForIOS()){
				App.message.chrome();
			}

		}
	);

});



/**
 * Refresh the pages layout for a blank page
 */
App.refreshLayout = function() {
	setTimeout(function() {
		scrollTo(0, 1);
	}, 80);
};


/**
 * Sends a tracking item to mixpanel, or to google ads if its an order
 */
App.track = function() {
	if (App.config.env != 'live') {
		// return;
	}
	if (arguments[0] == 'Ordered') {
		$('img.conversion').remove();
		mixpanel.people.track_charge(arguments[1].total);
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + History.getState().url + '">').appendTo($('body'));
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
	if (!App.config) {
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
		//return;
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
 * generate ab formulas
 */
App.AB = {
	options: {
		tagline: [
			{
				name: 'tagline-for-free',
				tagline: 'Order the top food %s. For free. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'
			},
			{
				name: 'tagline-no-free',
				tagline: 'Order the top food %s. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'		
			}
		],
		slogan: [
			{
				name: 'slogan-push-food',
				slogan: 'Push a button. Get Food.'
			}
		],
		restaurantPage: [
			{
				name: 'restaurant-page-noimage'
			},
			{
				name: 'restaurant-page-image',
				disabled: true
			}
		],
		dollarSign: [
			{
				name : 'show'
			},
			{
				name : 'hide'
			}
		],
		changeablePrice: [
			{
				name : 'show'
			},
			{
				name : 'hide'
			}
		]
	},
	init: function() {
		if (!App.config.ab) {
			// we dont have ab variables. generate them
			App.AB.create(true);
		}
		App.AB.load();
	},
	create: function(clear) {
		if (clear) {
			App.config.ab = {};
		}
		
		_.each(App.AB.options, function(option, key) {
			if (App.config.ab[key]) {
				return;
			}
			var opts = _.filter(App.AB.options[key], function(o) { return o.disabled ? false : true; });
			var opt = opts[Math.floor(Math.random()*opts.length)];
			App.config.ab[key] = opt.name
			App.trackProperty('AB-' + key, opt.name);
		});
		
		App.AB.save();
		console.log(App.config.ab);
		
	},
	load: function() {
		App.slogan = _.findWhere(App.AB.options.slogan, {name: App.config.ab.slogan});
		App.tagline = _.findWhere(App.AB.options.tagline, {name: App.config.ab.tagline});

		if (!App.slogan || !App.tagline) {
			App.AB.create(true);
			App.AB.load(true);
		}
	},
	save: function() {
		$.ajax({
			url: App.service + 'config',
			data: {ab: App.config.ab},
			dataType: 'json',
			type: 'POST',
			complete: function(json) {

			}
		});
	}
};

App.busy = {
	isBusy: function() {
		return $('.app-busy').length ? true : false;
	},
	makeBusy: function() {
		//el.addClass('button-bottom-disabled');
		var busy = $('<div class="app-busy"></div>')
				.append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>'))


		$('body').append(busy);
	},
	unBusy: function() {
		$('.app-busy').remove();
		//el.removeClass('button-bottom-disabled');
	}
};


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
		$.getJSON('/api/logout',function(){ location.reload()});
	},
	cart: function() {
		App.alert(JSON.stringify(App.cart.items));
	},
	clearloc: function() {
		$.cookie('community', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lat', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lon', '', { expires: new Date(3000,01,01), path: '/'});
		location.href = '/';
	},
	init: function() {
		$('.test-card').tap(function() {
			App.test.card();
		});
		$('.test-logout').tap(function() {
			App.test.logout();
		});
		$('.test-cart').tap(function() {
			App.test.cart();
		});
		$('.test-clearloc').tap(function() {
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

App.updateAutotipValue = function() {
	var subtotal = App.cart.totalbreakdown().subtotal;
	var autotipValue
	if(subtotal === 0) {
		autotipValue = 0;
	}
	else {
		// the holy formula - see github/#940
		autotipValue = Math.ceil(4*(subtotal * 0.107 + 0.85)) / 4;
	}
	$('[name="pay-autotip-value"]').val(autotipValue);
	var autotipText = autotipValue ? ' (' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + autotipValue + ')' : '';
	$('[name=pay-tip] [value=autotip]').html('Autotip' + autotipText);
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


$(function() {

	App.test.init();

	$(document).on('touchclick', '.signout-button', function() {
		App.signin.signOut();
	});

	$(document).on('touchclick', '.signup-add-facebook-button', function() {
		App.signin.facebook.login();
	});

	$(document).on('touchclick', '.change-location-inline', function() {
		App.loadHome(true);
	});

	$(document).on('submit', '.button-letseat-formform', function() {
		$('.button-letseat-form').trigger('touchclick');
		return false;
	});

	// confirm, test location on location page
	$(document).on('touchclick', '.button-letseat-form', function() {
		var address = $.trim($('.location-address').val());
		
		if (!address) {
			$('.location-address').val('').attr('placeholder','Please enter your address here');
		} else {
			App.loc.addVerify(address, function() {
				History.pushState({}, 'Crunchbutton', '/' + App.restaurants.permalink);
			}, function() {
				$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
			});
		}
	});

	$(document).on('touchclick', '.delivery-toggle-delivery', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.delivery();
		App.track('Switch to delivery');
	});

	$(document).on('touchclick', '.delivery-toggle-takeout', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.takeout();
		App.track('Switch to takeout');
	});

	$(document).on('touchclick', '.pay-toggle-credit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.credit();
		App.track('Switch to card');
		App.giftcard.notesField.listener();
	});

	$(document).on('touchclick', '.pay-toggle-cash', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.cash();
		App.track('Switch to cash');
		App.giftcard.notesField.listener();
	});

	$(document).on('touchclick', '.location-detect', function() {
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
	

	$('.link-help').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - About', '/help');
	});

	$('.link-legal').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - Legal', '/legal');
	});

	$('.link-orders').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - Orders', '/orders');
	});
	
	if (App.isMobile()) {

		// prevent double trigger
		$(document).on('touchclick','input[type="checkbox"]', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		// manually rebind checkbox events
		$('input[type="checkbox"]').tap(function(e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).checkToggle();
		});
		
		// manually rebind labels
		$('label[for]').tap(function(e) {
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
		$('a[href]').tap(function(e) {
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


		// ignore all click events from acidently triggering on mobile. only use touchclick
		$(document).on('click', function(e, force) {
			e.stopPropagation();
			e.preventDefault();
		});
	

	}

	$('.dish-item').tap(function() {
		App.cart.add($(this).attr('data-id_dish'));
	});

	$('.your-orders a').tap(function() {
		if ($(this).attr('data-id_order')) {
			History.pushState({},'Crunchbutton - Your Order', '/order/' + $(this).attr('data-id_order'));
		}
	});

	$('.cart-button-remove').tap(function() {
		App.cart.remove($(this).closest('.cart-item'));
	});

	$('.cart-button-add').tap(function() {
		App.cart.clone($(this).closest('.cart-item'));
	});

	$('.cart-item-config a').tap(function() {
		App.cart.customize($(this).closest('.cart-item'));
	});

	$('.button-submitorder-form').tap(function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.crunchSoundAlreadyPlayed = false;
		App.isDeliveryAddressOk = false;
		App.cart.submit($(this),true);
	});

	$(document).on('touchclick', '.button-deliver-payment, .dp-display-item a, .dp-display-item .clickable', function() {
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

	$(document).on('change', '.cart-customize-select', function() {
		App.cart.customizeItem($(this));
	});

	$( '.default-order-check' ).tap( function(){
		setTimeout( function(){
			$( '#default-order-check' ).checkToggle();
		}, 1 );
	} );

	$('.cart-customize-check').tap( function() {
		var checkbox = $(this);
		setTimeout( function(){
			if( !App.isMobile() ){
				checkbox.checkToggle();	
			}
			App.cart.customizeItem( checkbox );
		}, 1 );
	});

	$('.cart-item-customize-item label').tap(function() {
		$(this).prev('input').checkToggle();
		App.cart.customizeItem( $(this).prev('input') );
	});

	$(document).on('change', '[name="pay-tip"]', function() {
		App.order.tip = $(this).val();
		App.order.tipHasChanged = true;
		var total = App.cart.total();
		App.cart.updateTotal();
	});

	$('.nav-back').tap(function() {
		// App.controlMobileIcons.showPacman( 'left', function(){ $('.nav-back').removeClass('nav-back-show'); } );
		$('.nav-back').removeClass('nav-back-show');
		if( App.loc.locationNotServed ){
			App.loc.locationNotServed = false;
			App.loadHome(true);
		} else {
			History.back();	
		}
	});

	$('.link-home').tap(function() {
		if( App.restaurants.list && App.restaurants.list.length > 0 ){
			App.page.foodDelivery();
		} else {
			App.loadHome(true);
		}
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

	// Listener to verify if the user typed a gift card at the notes field
	$(document).on('blur', '[name=notes]', function(){
		App.giftcard.notesField.listener();
	} );
	

	$(document).on('change, keyup', '[name="pay-card-number"]', function() {
		App.creditCard.changeIcons( $(this).val() );
	} );

	$(document).on('keyup', '[name="pay-phone"]', function() {
		$(this).val( App.phone.format($(this).val()) );
	});

	// make sure we have our config loaded
	var haveConfig = function(json) {
		$(document).trigger('have-config');
		App.processConfig(json);
		App._init = true;
		App.NGinit();
	};

	if (App.config) {
		haveConfig(App.config)
	} else {
		$.getJSON('/api/config', haveConfig);
	}

	$('.cart-summary').tap(function(e) {
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
	if (App.isMobile() && !App.isAndroid()) {
		setInterval(function() {
			var focused = $(':focus');
			if (!focused.length) {
				$('[data-position="fixed"]').show();
				return;
			}

			focused = focused.get(0);

			if (focused.tagName == 'SELECT' || focused.tagName == 'INPUT' || focused.tagName == 'TEXTAREA') {
				$('[data-position="fixed"]').hide();
			} else {
				$('[data-position="fixed"]').show();
			}
		}, 100);
	}


	var checkForDistance = function() {
		if (App.order['delivery_type'] == 'takeout') {
			return;
		}
	};

	$(document).on('blur', '[name="pay-address"]', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 100);
	});

	$(document).on('change', '[name="pay-address"]', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 1000);
	});
	
	$(document).on('touchclick', '.config-icon', function() {
		if (App.isNarrowScreen() && $(this).hasClass('config-icon-back-home')) {
			App.controlMobileIcons.backHome();
		} else {
			var pacmanSide = (App.currentPage == 'restaurants') ? 'right' : 'left';
			App.controlMobileIcons.showPacman( pacmanSide, function(){ $( '.sign-in-icon' ).addClass( 'config-icon-mobile-hide' ); } );
			App.loadHome(true);
		}
	});

	$(document).on('change', '[name="pay-address"], [name="pay-name"], [name="pay-phone"], [name="pay-card-number"], [name="notes"]', function() {
		App.config.user.name = $('[name="pay-name"]').val();
		App.config.user.phone = App.phone.format($('[name="pay-phone"]').val());
		App.config.user.address = $('[name="pay-address"]').val();
		App.config.user.card = $('[name="pay-card-number"]').val();
		App.config.user.notes = $('[name="notes"]').val();
		App.config.user.card_exp_month = $('[name="pay-card-month"]').val();
		App.config.user.card_exp_year = $('[name="pay-card-year"]').val();
	});


	$(document).on('touchclick', '.content-item-locations-city', function() {
		$( '.main-content' ).html( '' );
		var permalink = $( this ).attr( 'permalink' );
		App.routeAlias( permalink, function( result ){
			App.loc.realLoc = {
				addressAlias: result.alias.address,
				lat: result.alias.lat,
				lon: result.alias.lon,
				prep: result.alias.prep,
				city: result.alias.city
			};
			App.loc.setFormattedLocFromResult();
			App.page.foodDelivery( true );
		});
	});
	App.signin.init();
	App.signup.init();
	App.suggestion.init();
	App.recommend.init();
	App.loc.init();
	App.credit.tooltip.init();

	$( '.ui-dialog-titlebar-close' ).tap( function(){
		try{
			$( '.ui-dialog-content' ).dialog( 'close' );
		} catch(e){}
	} );

});


App.modal.contentWidth = function(){
	if( $( window ).width() > 700 ){
		return 280;
	}
	if( $( window ).width() <= 700 ){
		return $( window ).width() - 50;
	}
}

App.getCommunityById = function( id ){
	for (x in App.communities) {	
		if( App.communities[x].id_community == id ){
			return App.communities[x];
		}
	}
	return false;
}

App.message = {};
App.message.show = function( title, message ) {
	if( $( '.message-container' ).length > 0 ){
		$( '.message-container' ).html( '<h1>' + title + '</h1><div class="message-container-content">' +   message + '</div>' );
	} else {
		var html = '<div class="message-container">' +
			'<h1>' + title + '</h1>' +
			'<div class="message-container-content">' + 
			message +
			'</div>' +
			'</div>';
		$('.wrapper').append(html);
	}

	$('.message-container')
		.dialog({
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			close: function( event, ui ) { App.modal.shield.close(); },
		});

}

App.playAudio = function( audio, callback ){
	var audio = $( '#' + audio ).get(0);
	try{
		audio.addEventListener( 'ended', function() {
		if( callback ){
			callback();
			}
		});
		audio.play();	
	} catch( e ){}
}

App.registerLocationsCookies = function() {
	$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
	$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});
	$.cookie('location_range', ( App.loc.range || App.defaultRange ), { expires: new Date(3000,01,01), path: '/'});
}

App.message.chrome = function( ){
	var title = 'How to use Chrome',
		message = '<p>' +
		'Just tap "Request Desktop Site.' +
		'</p>' +
		'<p align="center">' +
		'<img style="border:1px solid #000" src="/assets/images/chrome-options.png" />' + 
		'</p>';
	App.message.show(title, message);
}

// Issue #1227
App.controlMobileIcons = {};
App.controlMobileIcons.process = function( page ){

	if( !App.isNarrowScreen() ){
		return false;
	}

	App.controlMobileIcons.normalize();

	App.loc.locationNotServed = false;
	$( '.sign-in-icon' ).removeClass( 'config-icon-mobile-hide' );
	$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );
	switch( page ){
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
}

App.controlMobileIcons.backHome = function(){
	if( App.loc.locationNotServed ){
		App.page.home( true );
	} else {
		if( App.restaurants.list && App.restaurants.list.length > 0 ){
			App.page.foodDelivery();
		} else {
			History.pushState( {}, 'Crunchbutton', '/bycity' );
		}	
	}
}

App.controlMobileIcons.normalize = function(){
	$( '.sign-in-icon' ).removeClass( 'left' );
	$( '.config-icon' ).removeClass( 'right' );
	$( '.config-icon' ).removeClass( 'config-icon-back-home' );
}

App.controlMobileIcons.showPacman = function( side, call ){
	$( '.pacman-' + side ).addClass( 'pacman-show' );
	if( call ){ call(); }
}

App.controlMobileIcons.hidePacman = function(){
	$( '.pacman-loading' ).removeClass( 'pacman-show' );
}


