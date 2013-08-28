/**
 * legal page
 */
NGApp.controller('LegalCtrl', function ($scope, $http) {
	if (!App.isPhoneGap) {
		$http.get(App.service + 'legal').success(function(data) {
			$scope.legal = data.data;
		});
	}
});


/**
 * help page
 */
NGApp.controller('HelpCtrl', function ($scope, $http) {
	var fm = function() {
		$('.crunchbutton-join-mail').html('moc.nottubhcnurc@nioj'.split('').reverse().join(''));	
	}
	if (!App.isPhoneGap) {
		$http.get(App.service + 'help').success(function(data) {
			$scope.help = data.data;
			fm();
		});
	}
});


/**
 * Home controller
 */
NGApp.controller('HomeCtrl', function ($scope, $http, $location, RestaurantsService) {
	var restaurants = RestaurantsService;
	restaurants.list( 
		// Success
		function(){
			$location.path( '/' + restaurants.permalink );
		},
		// Error
		function(){
			$location.path( '/location' );
		} 
	);
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
NGApp.controller( 'RestaurantsCtrl', function ( $scope, $rootScope, $http, $location, RestaurantsService) {

	var restaurants = RestaurantsService;

	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';
	$scope.restaurants = {};

	$scope.display = function($event) {
		var restaurant = this.restaurant;
		if (!restaurant.open()) {
			$rootScope.$broadcast('restaurantClosedClick', restaurant);
		} else {
			var el = $($event.target).closest('.meal-item').find('.meal-item-content');
			var s = $(el).data('spinner');
			if (!s) {
				s = Ladda.create(el.get(0));
				$(el).data('spinner', s);
			}
			s.start();

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
			try {
				var slogan = App.slogan.slogan;
				var sloganReplace = prep + ' ' +  city;
				sloganReplace = $.trim(sloganReplace);
				var tagline = App.tagline.tagline.replace('%s', sloganReplace);
				slogan = slogan.replace('%s', sloganReplace);
			} catch (e) {
				console.log('Failed to load dynamic text', App.slogan, App.tagline, e);
				var slogan = '';
				var tagline = '';
			}

			document.title = city + ' Food Delivery | Order Food from ' + (city || 'Local') + ' Restaurants | Crunchbutton';

			$scope.restaurants = restaurants.sort();
			$scope.slogan = slogan;
			$scope.tagline = tagline;

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
NGApp.controller( 'LocationCtrl', function ($scope, $http, $location, RestaurantsService, LocationService, AccountService ) {

	var account = AccountService;
	var restaurants = RestaurantsService;

	$scope.isUser = account.user.has_auth;
	$scope.notUser = !account.user.has_auth;
	$scope.topCommunities = App.topCommunities;
	$scope.recommend = {};

	$scope.location = LocationService;

	// @todo: this function prevents angular from rendering on phonegap correctly until it gets a response back from google (about 9 seconds)
	if (!App.isPhoneGap) {
		$scope.location.init();
	}

	$scope.yourArea = $scope.location.position.pos().city() || 'your area';

	$scope.locationError = false;

	$scope.openCity = function( city ){
		$location.path( '/' + city );
	}

	$scope.resetFormLocation = function(){
		$scope.location.form.address = '';
		$scope.locationError = false;
	}

	$scope.$watch( 'location.position.pos().city()', function( newValue, oldValue, scope ) {
		$scope.yourArea = $scope.location.position.pos().city() || 'your area';
	});

	$scope.$on( 'locationError', function(e, data) {
		$scope.recommend.greetings = false;
		$scope.locationError = true;
	});

	$scope.$on( 'locationNotServed', function(e, data) {
		$('.location-address').val('').attr('placeholder','Please enter a zip code or city name');
	});
	
	var proceed = function() {
		$location.path( '/' + restaurants.permalink );
	};

	// lets eat button
	$scope.letsEat = function() {
		$scope.location.form.address = $.trim( $scope.location.form.address );
		if ( $scope.location.form.address == '' ) {
			$('.location-address').val('').attr('placeholder','Please enter your address here');
		} else {
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
						} );
				}, 
				// Address not ok
				function() {
					$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
				}
			);
		}
	}
	
	$scope.locEat = function() {
		$scope.location.getLocationByBrowser(function(loc) {
			$scope.location.position.addLocation(loc);
			proceed();
		});
	};

});

/**
 * restaurant page
 */
NGApp.controller('RestaurantCtrl', function ($scope, $http, $routeParams, $rootScope, RestaurantService, OrderService, CreditService, GiftCardService, PositionsService, MainNavigationService, CreditCardService) {

	// we dont need to put all the Service methods and variables at the $scope - it is expensive
	var order = OrderService;
	order.loaded = false;
	$scope.order = {};
	$scope.order.form = order.form;
	$scope.order.info = order.info;
	$scope.open = false;
	
	var creditCard = CreditCardService;
	
	// update if the restaurant is closed or open
	$rootScope.updateOpen = setInterval(function() {
		var open = $scope.restaurant.open();
		if ($scope.open != open) {
			$scope.open = open;
		}
		$scope.$apply();
	},1000 * 15);
	
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
	$scope.order.submit = function(){
		if( ( CreditService.value != '0.00' && OrderService.form.pay_type == 'cash' ) && !$scope.ignoreGiftCardWithCashOrder ){
			App.dialog.show( '.giftcard-payment-warning' );
		} else {
			return order.submit();
		}
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
	$scope.placeAnyway = function(){
		$rootScope.closePopup();
		$scope.ignoreGiftCardWithCashOrder = true;
		setTimeout(function(){
			$scope.order.submit();
		}, 1000 );
	}
	$scope.order.creditCardChanged = function(){
		 creditCard.validate( order.form.cardNumber );
		 creditCard.changeIcons( order.form.cardNumber );
		 return order.cardInfoChanged();
	}
	$scope.order.tipChanged = function(){
		return order.tipChanged();
	}

	// Event will be called when the order loaded
	$scope.$on( 'orderLoaded', function(e, data) {
		$scope.order.loaded = order.loaded;
		$scope.order.showForm = order.showForm;
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
		changeablePrice: function (dish) {
			return (App.config.ab && App.config.ab.changeablePrice == 'show' && dish.changeable_price) ? '+' : ''
		},
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
	});

	$scope.checkGiftCard = function(){
		giftcard.notes_field.content = $scope.order.form.notes;
		giftcard.notes_field.start();
	}
	// Validate gift card at the notes field
	$scope.$watch( 'order.form.notes', function( newValue, oldValue, scope ) {
		$scope.checkGiftCard();
	});

	var credit = CreditService;
	$scope.credit = {};
	// Event will be called when the credit changes
	$scope.$on( 'creditChanged', function(e, data) {
		$scope.credit.value = credit.value;
		$scope.credit.redeemed = credit.redeemed;
		$scope.order.updateTotal();
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
		$scope.open = $scope.restaurant.open();
		
		order.init();
		// Update some gift cards variables
		giftcard.notes_field.id_restaurant = $scope.restaurant.id_restaurant;
		giftcard.notes_field.restaurant_accepts = ( $scope.restaurant.giftcard > 0 );
		
		// Load the credit info
		credit.getCredit( $scope.restaurant.id_restaurant );
		
		document.title = $scope.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton';

		var position = PositionsService;
		var address = position.pos();

		// If the typed address is different of the user address the typed one will be used #1152
		if( address.type() == 'user' && address.valid( 'order' ) ){
			if( order._useCompleteAddress ){
				$scope.order.form.address = address.formatted();
			} else {
				$scope.order.form.address = address.entered();
			}
		}
		
		App.scrollTop();

		$scope.order.cart.items = order.cart.getItems();

		// @todo: do we still neded this??
		// $('.body').css({ 'min-height': $('.restaurant-items').height()});

		// Place cash order even if the user has gift card see #1485
		$scope.ignoreGiftCardWithCashOrder = false;
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
NGApp.controller('OrderCtrl', function ($scope, $http, $location, $routeParams, $filter, AccountService, AccountModalService, OrderViewService ) {

	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}

	$scope.account = { user : AccountService.user, has_auth : AccountService.user.has_auth };
	$scope.modal = { signupOpen : AccountModalService.signupOpen };

	$scope.order = {};
	$scope.restaurant = {};
	
	OrderViewService.load();

	$scope.isMobile = App.isMobile();

	$scope.facebook = function(){
		OrderViewService.facebook.postOrder();
	}

	$scope.$on( 'OrderViewLoadedOrder', function(e, order) {
		$scope.order = order;	
		$scope.$safeApply();
	});

	$scope.$on( 'OrderViewLoadedRestaurant', function(e, restaurant) {
		$scope.restaurant.phone = $filter( 'formatPhone' )( restaurant.phone );
		$scope.$safeApply();
	});
});


/**
 * Orders page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
NGApp.controller('OrdersCtrl', function ($scope, $http, $location, AccountService, AccountSignOut, OrdersService, AccountModalService ) {
	
	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}

	$scope.account = { hasFacebook : AccountService.user.facebook };
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

});

NGApp.controller( 'GiftcardCtrl', function ($scope, $location, GiftCardService ) {
	setTimeout( function(){ GiftCardService.open(); }, 300 );
});

NGApp.controller('reset', function ($scope, $location, AccountModalService) {
	$scope.modal = AccountModalService;
	$scope.modal.resetOpen();
	$location.path( '/' );
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

NGApp.controller( 'AccountResetCtrl', function ( $scope, $http, AccountResetService ) {
	$scope.reset = AccountResetService;
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

NGApp.controller( 'RecommendRestaurantCtrl', function ( $scope, $http, RecommendRestaurantService, AccountService, AccountModalService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.account = AccountService;
	$scope.modal = AccountModalService;
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

/*
Invite codes
*/
NGApp.controller( 'ReferralCtrl', function ( $scope, $location, ReferralService, FacebookService ) {
	
	$scope.invite_url = false;
	$scope.value = false;
	$scope.limit = false;
	$scope.invites = false;
	$scope.enabled = false;

	if( !ReferralService.invite_url ){
		ReferralService.getStatus();
	}

	$scope.$on( 'referralStatusLoaded', function(e, data) {
		$scope.invites = ReferralService.invites;
		$scope.limit = ReferralService.limit;
		$scope.invite_url = ReferralService.invite_url;
		$scope.value = ReferralService.value;
		$scope.enabled = ReferralService.enabled;
		$scope.show();
	});

	$scope.show = function(){
		if( $scope.enabled ){
			return true;
		} else {
			return false;
		}
	}

	$scope.facebook = function(){
		FacebookService.postInvite( $scope.invite_url );
	}

});

NGApp.controller( 'InviteCtrl', function ( $scope, $routeParams, $location, ReferralService ) {
	// Just store the cookie, it will be used later
	$.cookie( 'referral', $routeParams.id );
	$location.path( '/' );
});