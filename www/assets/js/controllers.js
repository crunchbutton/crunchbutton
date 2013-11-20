/**
 * splash page
 */
NGApp.controller('SplashCtrl', function ($scope, $http, AccountFacebookService) {
	$scope.facebook = AccountFacebookService;
	if (App.parallax.setupBackgroundImage) {
		App.parallax.setupBackgroundImage($('.home-top').get(0));
	}
});


/**
 * jobs page
 */
NGApp.controller('JobsCtrl', function ($scope, $http) {
	var reps = 'moc.nottubhcnurc@spersupmac'.split('').reverse().join('');
	var devs = 'moc.nottubhcnurc@ylnosratskcor'.split('').reverse().join('');
	$scope.reps = reps;
	$scope.devs = devs;
});

/**
 * owners page
 */
NGApp.controller('OwnersCtrl', function ($scope, $http) {
	var reps = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');
	$scope.reps = reps;
});

/**
 * About page
 */
NGApp.controller('AboutCtrl', function ($scope, $http) {

});

/**
 * legal page
 */
NGApp.controller('LegalCtrl', function ($scope, $http) {
	if (!App.isPhoneGap) {
		$http.get(App.service + 'legal').success( function( data ) {
			$scope.legal = data.data.replace( '[email]', 'moc.nottubhcnurc@eybdoog'.split('').reverse().join('') );
		});
	}
});

/**
 * help page
 */
NGApp.controller('HelpCtrl', function ($scope, $http, $compile, MainNavigationService) {

	$scope.legal = function(){
		MainNavigationService.link( '/legal' );
	}

	if (!App.isPhoneGap) {

		$http.get(App.service + 'help').success(function(data) {

			var help = 'moc.nottubhcnurc@sremotsucyppah'.split('').reverse().join('');
			var join = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');

			$scope.help = data.data
				.replace('[email]', '<a href="mailto:' + help + '">' + help + '</a>')
				.replace('[joinemail]', '<a href="mailto:' + join + '">' + join + '</a>')
			$scope.help = $compile( $scope.help )( $scope );
		});
	}
});


/**
 * Home controller
 */
NGApp.controller('HomeCtrl', function ($scope, $http, $location, RestaurantsService, LocationService) {
	if (!App.isPhoneGap) {
		LocationService.init();
	} else {
		// @hack
		// just force the location to the food-delivery page. if we dont have a loc it sends us back to location anyway
		$location.path( '/' + RestaurantsService.permalink );	
	}	
	// If it have a valid restaurant position just reditect to restaurants page
	if( LocationService.position.pos().valid( 'restaurants' ) ){
		$location.path( '/' + RestaurantsService.permalink );
	}
});


/**
 * Alias / unknown controller
 */
NGApp.controller('DefaultCtrl', function ($scope, $http, $location, CommunityAliasService, RestaurantsService ) {
	var community = CommunityAliasService;
	community.route( $location.path(),
		// Success
		function( results, restaurant ){
			if (results.alias) {
				community.position.addLocation( new Location( {
					address: results.alias.address(),
					entered: results.address,
					type: 'alias',
					lat: results.alias.lat(),
					lon: results.alias.lon(),
					city: results.alias.city(),
					prep: results.alias.prep()
				} ) );
				var url = '/' + RestaurantsService.permalink + ( restaurant ? '/' + restaurant : '' )
				$location.path( url );
			}
		},
		// Error
		function(){
			$location.path( '/location' );
		}
	);
});

/**
 * Show the restaurants
 */
NGApp.controller( 'RestaurantsCtrl', function ( $scope, $rootScope, $http, $location, $timeout, RestaurantsService, LocationService) {
	$scope.restaurants = false;

	$scope.showMoreRestaurants = function(){
		var step = 3;
		$scope.restaurantsToShow += step;
	}

	var location = LocationService;
	if( !location.initied ){
		location.init();
		$location.path( '/' );
		return;
	}
	
	var motivationText = ['You are awesome','You are loved','You are beautiful','You\'re at the top of your game','You are rad'];
	$scope.motivationText = motivationText[Math.floor(Math.random() * motivationText.length)];

	var restaurants = RestaurantsService;

	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';

	// Update the close/open/about_to_close status from the restaurants
	var updateStatus = function(){
		updateRestaurantsStatus = $timeout( function(){
			// Update status of the restaurant's list
			$scope.restaurants = restaurants.getStatus();
			$rootScope.$safeApply();
			updateStatus();
		} , 1000 * 35 );
	}

	$scope.$on( '$destroy', function(){
		RestaurantsService.forceGetStatus = true;
		// Kills the timer when the controller is changed
		try{
			$timeout.cancel( updateRestaurantsStatus );	
		} catch(e){}
		
	});

	// It means the list is already loaded so we need to update the restaurant's status
	if( RestaurantsService.forceGetStatus ){
		setTimeout( function(){
			$scope.restaurants = restaurants.getStatus();
			updateStatus();
			$rootScope.$safeApply();
		}, 1 );
	}

	$rootScope.$on( 'appResume', function(e, data) {
		if( $location.path() == '/' + RestaurantsService.permalink ){
			$scope.restaurants = restaurants.getStatus();
			updateStatus();
		}
	});

	$scope.display = function($event) {
		var restaurant = this.restaurant;
		restaurant.closesIn();
		if ( !restaurant._open ) {
			$rootScope.$broadcast('restaurantClosedClick', restaurant);
		} else {
			var el = $($event.target).parents('.meal-item').find('.meal-item-content');
			var s = $(el).data('spinner');
			if (s) {
				s.start();
			}
			// @todo: this is kind of redundundant
			// make sure that the restaurant is actulay loaded first
			App.cache('Restaurant', restaurant.permalink, function () {
				App.go( '/' + restaurants.permalink + '/' + restaurant.permalink);
			}, function() {
				App.connectionError();
				s.stop();
			});
		}
	};

	var prep = restaurants.position.pos().prep();
	var city = restaurants.position.pos().city();

	restaurants.list( 
		// Success
		function(){
			App.profile.log('return from list');

			// Limit the number of restaurants to be rended when page loads
			if (App.restaurantsPaging.enabled) {
				$scope.restaurantsToShow = App.isMobile() ? App.restaurantsPaging.mobile : App.restaurantsPaging.desktop;
			} else {
				$scope.restaurantsToShow = 100;	
			}
			
			try {
				var slogan = App.slogan.slogan;
				var sloganReplace = ( prep || 'in' ) + ' ' + ( city || 'your area' );
				sloganReplace = $.trim(sloganReplace);
				var tagline = App.tagline.tagline.replace('%s', sloganReplace);
				slogan = slogan.replace('%s', sloganReplace);
			} catch (e) {
				console.log('Failed to load dynamic text', App.slogan, App.tagline, e);
				var slogan = '';
				var tagline = '';
			}

			document.title = ( city || '' ) + ' Food Delivery | Order Food from ' + (city || 'Local') + ' Restaurants | Crunchbutton';

			$scope.restaurants = restaurants.sort();
			App.profile.log('returned sorting');
			// Wait one minute until update the status of the restaurants
			setTimeout( function(){
				updateStatus();
			}, 1000 * 60 );
			$scope.slogan = slogan;
			$scope.tagline = tagline;
			
			App.profile.log('finished everything');

			if ( $scope.restaurants.length == 4 ) {
				$('.content').addClass('short-meal-list');
			} else {
				$('.content').removeClass('short-meal-list');
			}
			$('.content').removeClass('smaller-width');

		}, 
		// Error
		function(){
			$location.path( '/location' );
		}
	);
});


/**
 * show cities
 */
NGApp.controller( 'CitiesCtrl', function ( $scope ) {
 	$scope.topCommunities = App.topCommunities;
});


/**
 * Change location
 */
NGApp.controller( 'LocationCtrl', function ($scope, $http, $location, $rootScope, RestaurantsService, LocationService, AccountService, PositionsService, RecommendRestaurantService ) {

	var account = AccountService;
	var restaurants = RestaurantsService;

	if (App.parallax.setupBackgroundImage) {
		App.parallax.setupBackgroundImage($('.home-top').get(0));
	}

	$scope.warningPlaceholder = false;

	$scope.isProcessing = false;

	$scope.isUser = account.user.has_auth;
	$scope.notUser = !account.user.has_auth;
	$scope.topCommunities = App.topCommunities;
	$scope.recommend = RecommendRestaurantService;

	$scope.location = LocationService;

	// @todo: this function prevents angular from rendering on phonegap correctly until it gets a response back from google (about 9 seconds)
	if (!App.isPhoneGap) {
		$scope.location.init();
	}
	
	document.title = 'Food Delivery | Crunchbutton';

	$scope.yourArea = $scope.location.position.pos().city() || 'your area';

	$scope.locationError = false;

	$scope.openCity = function( city ){
		$location.path( '/' + city );
	}

	$scope.resetFormLocation = function(){
		$scope.location.form.address = '';
		$scope.locationError = false;
		$scope.warningPlaceholder = false;
		setTimeout( function(){
			$scope.focus( '.location-address' );
		}, 250 );
	}

	$scope.$watch( 'location.position.pos().city()', function( newValue, oldValue, scope ) {
		$scope.yourArea = $scope.location.position.pos().city() || 'your area';
	});

	$scope.$on( 'locationError', function(e, data) {
		spin.stop();
		// If the entered address does not have zip code show the enter zip code message #1763
		var entered = $scope.location.position.pos().entered();
		var isStreet = $scope.location.position.pos().valid( 'order' );
		if( isStreet && !entered.match(new RegExp( /\d{5}(?:[-\s]\d{4})?/ )) ){
			$('.location-address').val('').attr('placeholder','Please include a zip code');	
			$scope.$broadcast( 'locationNotServed',  true );
		} else {
			$scope.locationError = true;
		}
	});

	$rootScope.$on( 'NewLocationAdded', function(e, data) {
		$scope.recommend.greetings = false;
	});

	$scope.$on( 'locationNotServed', function(e, data) {
		spin.stop();
		var pos = PositionsService.pos();
		if( pos.type() == 'user' ){
			var entered = $scope.location.position.pos().entered();
			var isStreet = $scope.location.position.pos().valid( 'order' );
			if( isStreet && !entered.match(new RegExp( /\d{5}(?:[-\s]\d{4})?/ )) ){
				$('.location-address').val('').attr('placeholder','Please include a zip code');	
			} else {
				$('.location-address').val('').attr('placeholder','Please include a zip code or city name');	
			}
		} else {
			$('.location-address').val('').attr('placeholder','Please enter an address or zip');	
		}

		$rootScope.locationPlaceholder = $('.location-address').attr('placeholder');
		$rootScope.warningPlaceholder = true;
		$scope.warningPlaceholder = true;
		// the user might be typing his login/pass - so blur it
		if( !App.dialog.isOpen() ){
			$scope.focus( '.location-address' );
		}
	});
	

	if( $rootScope.locationPlaceholder ){
		$('.location-address').val('').attr( 'placeholder', $rootScope.locationPlaceholder );
	}

	if( $rootScope.locationPlaceholder ){
		$scope.warningPlaceholder = $rootScope.warningPlaceholder;
	}

	$scope.$on( '$destroy', function(){
		$rootScope.locationPlaceholder = false;
		$rootScope.warningPlaceholder = false;
		AccountService.forceDontReloadAfterAuth = false;
	});

	var proceed = function() {
		$location.path( '/' + restaurants.permalink );
		$scope.location.form.address = '';
		$scope.warningPlaceholder = false;
		$scope.isProcessing = false;
		if (!$scope.$$phase){
			$scope.$apply();	
		}
	};

	// lets eat button
	$scope.letsEat = function() {

		// Start the spinner
		spin.start();

		$scope.location.form.address = $.trim( $scope.location.form.address );
		if ( $scope.location.form.address == '' ) {
			$('.location-address').val('').attr('placeholder',$('<div>').html('&#10148; Please enter your address here').text());
			spin.stop();
			$scope.warningPlaceholder = true;
			// the user might be typing his login/pass - so blur it
			if( !App.dialog.isOpen() ){
				$scope.focus( '.location-address' );
			}
		} else {
			// If the address searching is already in process ignores this request.
			if( $scope.isProcessing ){
				// To prevent any kind of problem, set this variable to false after 2 secs.
				setTimeout( function() { $scope.isProcessing = false; }, 2000 );
				return;
			}

			$scope.isProcessing = true;

			$scope.location.addVerify( $scope.location.form.address, 
				// Address ok
				function() {
					// Verify if the address has restaurant
					restaurants.list( 
						// Success
						proceed,
						// Error
						function(){
							$scope.$broadcast( 'locationError' );
							$scope.isProcessing = false;
						} );
				}, 
				// Address not ok
				function() {
					spin.stop();
					var oopsText = App.isPhoneGap ? 'Oops! Please enter an address' : '&#9785; Oops! Please enter a street name, number, and city';
					$('.location-address').val('').attr('placeholder',$('<div>').html(oopsText).text());
					$scope.warningPlaceholder = true;
					$scope.focus( '.location-address' );
					$scope.isProcessing = false;
					if (!$scope.$$phase){
						$scope.$apply();	
					}
				}
			);
		}
	}
	
	$scope.locEat = function() {
		var locSpin = $( '.location-detect' ).data( 'spinner' );
		var error = function(){
			$scope.$broadcast( 'locationNotServed' );
			locSpin.stop();
		}
		locSpin.start();
		$scope.location.getLocationByBrowser( function(loc) {
			// Add the position at the locations
			$scope.location.position.addLocation(loc);
			// Verify if user address is served
			restaurants.list( 
				// Success
				proceed,
				// Error
				error 
			);
		}, error );
	};

	var spin = {
		start : function(){
			if( spin.obj ){
				spin.obj.start();
			}
		},
		stop : function(){
			if( spin.obj ){
				spin.obj.stop();
			}
		}
	};
	setTimeout( function(){ spin.obj = $('.button-letseat-form').data('spinner'); }, 500 );

});

/**
 * restaurant page
 */
NGApp.controller('RestaurantCtrl', function ($scope, $http, $routeParams, $rootScope, $timeout, RestaurantService, OrderService, CreditService, GiftCardService, PositionsService, MainNavigationService, CreditCardService) {

	// we dont need to put all the Service methods and variables at the $scope - it is expensive
	var order = OrderService;
	order.loaded = false;
	order.startStoreEntederInfo = false;
	$scope.order = {};
	$scope.order.form = order.form;
	$scope.order.info = order.info;
	$scope.open = false;
	
	$scope.Math = window.Math;

	$scope.nothingInTheCartDesktop = App.AB.pluck('nothingInTheCartDesktop', App.config.ab.nothingInTheCartDesktop).line;
	$scope.nothingInTheCartMobile = App.AB.pluck('nothingInTheCartMobile', App.config.ab.nothingInTheCartMobile).line;

	$scope.order.creditCardChanged = function() {
		order._cardInfoHasChanged = true;
	};
	
	var creditCard = CreditCardService;
	
	// update if the restaurant is closed or open
	var updateStatus = function(){
		updateRestaurantStatus = $timeout( function(){
			$scope.restaurant.closesIn();
			var open = $scope.restaurant._open;
			if ($scope.open != open) {
				$scope.open = open;
			}
			if (!$scope.$$phase){
				$scope.$apply();	
			}
			updateStatus();
		} , 1000 * 15 );
	}

	$scope.$on( '$destroy', function(){
		// Kills the timer when the controller is changed
		$timeout.cancel( updateRestaurantStatus );
	});

	$rootScope.$on( 'appResume', function(e, data) {
		updateStatus();
	});

	updateStatus();


	// Set the id_restaurant 
	order.cart.setRestaurant( $routeParams.id );

	MainNavigationService.order = $scope.order;

	// Alias to OrderService 'public' methods
	$scope.order.updateTotal = function(){
		return order.updateTotal();
	}
	$scope.order.subtotal = function(){
		return order.subtotal();
	}
	$scope.order.total = function(){
		return order.total();
	}
	$scope.order.submit = function(){
		return order.submit();
	}
	$scope.order.cardInfoChanged = function(){
		return order.cardInfoChanged();
	}
	$scope.order.toogleDelivery = function( type ){
		return order.toogleDelivery( type );
	}
	$scope.order.tooglePayment = function( type ){
		return order.tooglePayment( type );
	}
	$scope.order._years = function(){
		return order._years();
	}
	$scope.order._months = function(){
		return order._months();
	}
	$scope.order._tips = function(){
		return order._tips();
	}
	$scope.showCreditPayment = function(){
		$scope.order.tooglePayment( 'card' );
		$scope.order.showForm = true;
		$rootScope.closePopup();
	}
	$scope.addressLetMeChangeIt = function(){
		$rootScope.closePopup();
		$scope.order.showForm = true;
		// Give time to close the modal.
		setTimeout( function(){ $scope.focus( '#pay-address' );	}, 450 );
	}
	$scope.addressPlaceAnyway = function(){
		$rootScope.closePopup();
		// Give time to close the modal.
		setTimeout( function(){ order.submit( true );;	}, 300 );
	}
	
	$scope.giftCardCreditPayment = function(){
		$rootScope.closePopup();
		$scope.order.tooglePayment( 'card' );
		$scope.order.showForm = true;
	}
	$scope.order.tipChanged = function(){
		return order.tipChanged();
	}

	$rootScope.$on( 'orderProcessingError', function(e, data) {
		order.showForm = true;
		$scope.order.showForm = order.showForm;
	});

	// Event will be called when the order loaded
	$scope.$on( 'orderLoaded', function(e, data) {
		$scope.order.loaded = order.loaded;
		$scope.order.showForm = order.showForm;

		// watch form changes and stores it
		$scope.$watch( 'order.form', function( newValue, oldValue, scope ) {
			if( order.startStoreEntederInfo && !order.account.user.id_user ){
				var userEntered = angular.copy( order.form );
				userEntered.cardNumber = '';
				$.totalStorage( 'userEntered', userEntered );
			}
		}, true);
		GiftCardService.notes_field.lastValidation = false;
		$scope.checkGiftCard();
	});

	// Alias to CartService 'public' methods
	$scope.order.cart = {};
	$scope.order.cart.add = function( item ){
		return order.cart.add( item );
	}
	$scope.order.cart.remove = function( item ){
		return order.cart.remove( item );
	}
	$scope.order.cart.customizeItem = function(option, item){
		return order.cart.customizeItem( option, item );
	}
	$scope.order.cart.hasItems = function(){
		return order.cart.hasItems();
	}
	// watch cart changes
	$scope.$watch( 'order.cart.items', function( newValue, oldValue, scope ) {
		$scope.order.updateTotal();
	}, true);

	// Alias to ServiceAccount.user
	$scope.user = order.account.user;

	$scope.AB = {
		dollar: (App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '',
		restaurantPage: (App.config.ab && App.config.ab.restaurantPage == 'restaurant-page-noimage') ? ' restaurant-pic-wrapper-hidden' : ''
	};

	var giftcard = GiftCardService;
	$scope.giftcard = { giftcards : {} };
	// Event will be called when the gift card changes
	$scope.$on( 'giftCardUpdate', function(e, data) {
		$scope.giftcard.giftcards.success = giftcard.notes_field.giftcards.success;
		$scope.giftcard.giftcards.error = giftcard.notes_field.giftcards.error;
		$scope.giftcard.value = giftcard.notes_field.value;
		$scope.giftcard.removed = giftcard.notes_field.removed;
		$scope.giftcard.hasGiftCards = giftcard.notes_field.hasGiftCards;
		$scope.giftcard.justOneGiftCardError = giftcard.notes_field.justOneGiftCardError;
		$scope.giftcard.hasValue = ( parseFloat( giftcard.notes_field.value ) > 0 );
	});

	$scope.checkGiftCard = function(){
		giftcard.notes_field.content = $scope.order.form.notes;
		giftcard.notes_field.start();
	}

	var credit = CreditService;
	$scope.credit = { hasValue : false };
	// Event will be called when the credit changes
	$scope.$on( 'creditChanged', function(e, data) {
		$scope.credit.value = credit.value;
		$scope.credit.number = parseFloat( credit.value );
		$scope.credit.redeemed = credit.redeemed;
		$scope.order.updateTotal();
		if( parseFloat( $scope.credit.value ) > 0 ){
			$scope.credit.hasValue = true;
		} else {
			$scope.credit.hasValue = false;
		}
	});

	$scope.$on( 'creditCardInfoChanged', function(e, data) {
		$scope.order.creditCardChanged();
	});

	var restaurantService = RestaurantService;
	// Event will be called after the restaurant load
	$scope.$on( 'restaurantLoaded', function(e, data) {
		var community = data.community;
		$scope.restaurant = data.restaurant;
		order.restaurant = $scope.restaurant;
		MainNavigationService.restaurant = $scope.restaurant;
		$scope.restaurant.closesIn();
		$scope.open = $scope.restaurant._open;
		
		order.init();
		// Update some gift cards variables
		giftcard.notes_field.id_restaurant = $scope.restaurant.id_restaurant;
		giftcard.notes_field.restaurant_accepts = ( $scope.restaurant.giftcard > 0 );
		
		// Load the credit info
		if( OrderService.account.user && OrderService.account.user.id_user ){
			credit.getCredit( $scope.restaurant.id_restaurant );	
		}
		
		document.title = $scope.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton';

		var position = PositionsService;
		var address = position.pos();

		// If the typed address is valid (order) and the user address is empty use the typed one #1152 and #1989 
		if( !order.account.user || order.account.user.address == '' ){
			if( address.type() == 'user' && address.valid( 'order' ) ){
				if( order._useCompleteAddress ){
					$scope.order.form.address = address.formatted();
				} else {
					$scope.order.form.address = address.entered();
				}
			}
		}

		$scope.order.cart.items = order.cart.getItems();

		// @todo: do we still neded this??
		// $('.body').css({ 'min-height': $('.restaurant-items').height()});

		// Place cash order even if the user has gift card see #1485
		$scope.ignoreGiftCardWithCashOrder = false;

		setTimeout( function(){ $scope.checkGiftCard(); }, 500 );

	});

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');
	
	// Finally Load the restaurant
	restaurantService.init();
	
});


/**
 * Order page. displayed after order, or at order history
 */
NGApp.controller('OrderCtrl', function ($scope, $http, $location, $routeParams, $filter, AccountService, AccountModalService, OrderViewService, ReferralService ) {

	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}
	$scope.account = { user : AccountService.user, has_auth : AccountService.user.has_auth };
	$scope.modal = { signupOpen : AccountModalService.signupOpen };
	$scope.order = {};
	$scope.restaurant = {};
	$scope.width = $(window).width();
	
	OrderViewService.load();

	$scope.isMobile = App.isMobile();

	$scope.facebook = function(){
		OrderViewService.facebook.postOrder();
	}

	$scope.referral = {
		invite_url : ReferralService.invite_url,
		value : ReferralService.value,
		enabled : ReferralService.enabled
	}	

	// Load the invite_url
	if( !ReferralService.invite_url ){
		ReferralService.getStatus();
	}

	$scope.$on( 'referralStatusLoaded', function(e, data) {
		$scope.referral.invite_url = ReferralService.invite_url;
		$scope.referral.invite_url_cleared = 
		$scope.referral.value = ReferralService.value;
		$scope.referral.enabled = ReferralService.enabled;
	});

	$scope.$on( 'OrderViewLoadedOrder', function(e, order) {
		$scope.order = order;	
		$scope.$safeApply();
	});

	$scope.referral.cleaned_url = function(){
		return ReferralService.cleaned_url();
	}

});


/**
 * Orders page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
NGApp.controller('OrdersCtrl', function ($scope, $http, $location, AccountService, AccountSignOut, OrdersService, AccountModalService, ReferralService, FacebookService ) {
	
	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}

	$scope.account = AccountService;

	// Alias to method AccountSignOut.do()
	$scope.signout = AccountSignOut.do;
	$scope.facebook = AccountModalService.facebookOpen;
	$scope.orders = {};

	// Alias to OrdersService methods
	$scope.orders.restaurant = OrdersService.restaurant;
	$scope.orders.receipt =  OrdersService.receipt;

	if( OrdersService.reload ){
		OrdersService.load();
	} else {
		$scope.orders.list = OrdersService.list;	
	}

	$scope.$on( 'OrdersLoaded', function(e, data) {
		$scope.orders.list = OrdersService.list;	
	});

	$scope.referral = {
		invite_url : ReferralService.invite_url,
		value : ReferralService.value,
		limit : ReferralService.limit,
		invites : ReferralService.invites,
		enabled : ReferralService.enabled
	}	

	$scope.referral.cleaned_url = function(){
		return ReferralService.cleaned_url();
	}

	// Load the invite_url
	if( !ReferralService.invite_url ){
		ReferralService.getStatus();
	}

	$scope.$on( 'referralStatusLoaded', function(e, data) {
		$scope.referral.invites = ReferralService.invites;
		$scope.referral.limit = ReferralService.limit;
		$scope.referral.invite_url = ReferralService.invite_url;
		$scope.referral.value = ReferralService.value;
		$scope.referral.enabled = ReferralService.enabled;
	});

	$scope.referral.facebook = function(){
		FacebookService.postInvite( $scope.referral.invite_url );
	}
	
	$scope.referral.twitter = function(){
		window.open('https://twitter.com/intent/tweet?url=' + $scope.referral.invite_url + '&text=#nom','_system');
	}

});

NGApp.controller( 'GiftcardCtrl', function ($scope, $location, GiftCardService ) {
	setTimeout( function(){ GiftCardService.open(); }, 300 );
});


NGApp.controller( 'AccountModalHeaderCtrl', function ( $scope, $http, AccountModalService ) {
	$scope.modal = AccountModalService;
});


NGApp.controller( 'AccountFacebookCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
});

NGApp.controller( 'AccountSignInCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;
});

NGApp.controller( 'AccountSignUpCtrl', function ( $scope, $http, AccountModalService, AccountService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	// Watch the variable user
	$scope.$watch( 'account.user', function( newValue, oldValue, scope ) {
		$scope.account.user = newValue;
		if( newValue ){
			$scope.modal.header = false;
		}
	});
});

NGApp.controller( 'AccountResetCtrl', function ( $scope, $http, $location, AccountResetService, AccountModalService ) {
	if( $location.path().indexOf( 'reset' ) >= 0 ){
		$scope.reset = AccountResetService;
		AccountModalService.resetOpen();
		$location.path( '/' );	
	}
});

NGApp.controller( 'GiftCardCtrl', function ( $scope, $http, $rootScope, GiftCardService ) {
	$scope.giftcard = {};
	$scope.user = GiftCardService.account.user;
	$scope.modal = GiftCardService.modal;
	$scope.giftcard.value = GiftCardService.value;
	$rootScope.$on( 'GiftCardProcessed', function(e, data) {
		// Update the scope
		$scope.user = GiftCardService.account.user;
		$scope.giftcard.value = GiftCardService.value;
		$scope.modal = GiftCardService.modal;
	});
});

NGApp.controller( 'MainHeaderCtrl', function ( $scope, MainNavigationService, OrderService ) {
	$scope.navigation = MainNavigationService;
	$scope.order = OrderService;
	$scope.$watch('navigation.page', function( newValue, oldValue, scope ) {
		$scope.navigation.control();
	});
});

NGApp.controller( 'RecommendRestaurantCtrl', function ( $scope, $http, $rootScope, RecommendRestaurantService, AccountService, AccountModalService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.account = AccountService;
	$scope.modal = AccountModalService;
	$scope.signupOpen = function(){
		AccountService.forceDontReloadAfterAuth = true;
		AccountModalService.signupOpen();
	}

	$rootScope.$on('userCreated', function(e, data) {
		RecommendRestaurantService.relateUser();
	});
});

NGApp.controller( 'RestaurantClosedCtrl', function ( $scope, $rootScope ) {
	$rootScope.$on('restaurantClosedClick', function(e, r) {
		if ($scope.$$phase) {
			$scope.restaurant = r;
			App.dialog.show('.restaurant-closed-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.restaurant = r;
				App.dialog.show('.restaurant-closed-container');
			}); 
		}			
	});
});

NGApp.controller( 'RecommendFoodCtrl', function ( $scope, $http, RecommendFoodService  ) {
	$scope.recommend = RecommendFoodService;
});

NGApp.controller( 'SupportCtrl', function ( $scope, $http, SupportService  ) {
	$scope.support = SupportService;
});

NGApp.controller( 'SideMenuCtrl', function () {

});

NGApp.controller( 'NotificationAlertCtrl', function ( $scope, $rootScope  ) {
	$rootScope.$on('notificationAlert', function(e, title, message) {
		if ($scope.$$phase) {
			$scope.title = title;
			$scope.message = message;
			App.dialog.show('.notification-alert-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.title = title;
				scope.message = message;
				App.dialog.show('.notification-alert-container');
			}); 
		}			
	});
});

NGApp.controller( 'InviteCtrl', function ( $scope, $routeParams, $location, ReferralService ) {
	// Just store the cookie, it will be used later
	$.cookie( 'referral', $routeParams.id );
	$location.path( '/' );
});

NGApp.controller( 'NoInternetCtrl', function ( $scope ) {
	// Just store the cookie, it will be used later
	$.cookie( 'referral', $routeParams.id );
	$location.path( '/' );
});