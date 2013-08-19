/**
 * legal page
 */
NGApp.controller('legal', function ($scope, $http) {
	if (!App.isPhoneGap) {
		$http.get(App.service + 'legal').success(function(data) {
			$scope.legal = data.data;
		});
	}
});


/**
 * help page
 */
NGApp.controller('help', function ($scope, $http) {
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
NGApp.controller('home', function ($scope, $http, $location, RestaurantsService) {
	$scope.restaurantsService = RestaurantsService;
	$scope.restaurantsService.list( 
		// Success
		function(){
			$location.path( '/' + App.restaurants.permalink );
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
NGApp.controller('default', function ($scope, $http, $location, CommunityAliasService ) {
	var community = CommunityAliasService;
	community.route( $location.path(),
		// success
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
				var url = '/' + App.restaurants.permalink + ( restaurant ? '/' + restaurant : '' )
				$location.path( url );
			}
		},
		// error
		function(){
			$location.path( '/location' );
		}
	);
});


/**
 * Show the restaurants
 */
NGApp.controller( 'restaurants', function ( $scope, $rootScope, $http, $location, RestaurantsService) {

	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';
	$scope.restaurants = RestaurantsService;

	$scope.display = function($event) {
		var restaurant = this.restaurant;

		if (!restaurant.open()) {
			App.rootScope.$broadcast('restaurantClosedClick', restaurant);
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
				App.go('/' + App.restaurants.permalink + '/' + restaurant.permalink);
			}, function() {
				App.connectionError();
				s.stop();
			});
		}
	};

	$scope.restaurants.list( function(){
		try {
				var slogan = App.slogan.slogan;
				var sloganReplace = $scope.restaurants.position.pos().prep() + ' ' +  $scope.restaurants.position.pos().city();

				sloganReplace = $.trim(sloganReplace);
				var tagline = App.tagline.tagline.replace('%s', sloganReplace);
				slogan = slogan.replace('%s', sloganReplace);

			} catch (e) {
				console.log('Failed to load dynamic text', App.slogan, App.tagline, e);
				var slogan = '';
				var tagline = '';
			}

		document.title = $scope.restaurants.position.pos().city() + ' Food Delivery | Order Food from ' + ($scope.restaurants.position.pos().city() || 'Local') + ' Restaurants | Crunchbutton';

		$scope.restaurants = $scope.restaurants.sort();
		$scope.slogan = slogan;
		$scope.tagline = tagline;

		if ($scope.restaurants.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}
		$('.content').removeClass('smaller-width');

		$('.nav-back').removeClass('nav-back-show');

	}, function(){
		$location.path( '/location' );
	} );
});


/**
 * show cities
 */
NGApp.controller('cities', function ($scope, $http) {
 	$scope.topCommunities = App.topCommunities;
});


/**
 * Change location
 */
NGApp.controller( 'location', function ($scope, $http, $location, RestaurantsService, LocationService, AccountService ) {
	

	var account = AccountService;

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

	$scope.restaurantsService = RestaurantsService;

	$scope.locationError = false;

//	$scope.recommend = RecommendRestaurantService;

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
		console.debug('locationError');
		$scope.recommend.greetings = false;
		$scope.locationError = true;
	});
	
	var proceed = function() {
		App.go('/' + App.restaurants.permalink);
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
					$scope.restaurantsService.list( 
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
NGApp.controller('restaurant', function ($scope, $http, $routeParams, RestaurantService, OrderService, CreditService, GiftCardService, PositionsService, MainNavigationService, CreditCardService) {

	// we dont need to put all the Service methods and variables at the $scope - it is expensive
	var order = OrderService;
	order.loaded = false;
	$scope.order = {};
	$scope.order.form = order.form;
	$scope.order.info = order.info;
	$scope.order.showForm = order.showForm;
	$scope.open = false;
	
	var creditCard = CreditCardService;
	
	// update if the restaurant is closed or open
	App.rootScope.updateOpen = setInterval(function() {
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
NGApp.controller('order', function ($scope, $http, $location, $routeParams, AccountService, AccountModalService, OrderViewService) {
	
	$scope.account = AccountService;
	
	if( !$scope.account.isLogged() ){
		$location.path( '/' + App.restaurants.permalink );
		return;
	}

	$scope.modal = AccountModalService;
	$scope.order = OrderViewService;
	
	$scope.order.load();

	$scope.callPhone = function( phone ){
		return App.callPhone( phone );
	}

	$scope.facebook = function(){
		$scope.order.facebook.postOrder();
	}
});


/**
 * Orders page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
NGApp.controller('orders', function ($scope, $http, $location, AccountService, AccountSignOut, OrdersService) {
	$scope.account = AccountService;
	if( !$scope.account.isLogged() ){
		$location.path( '/' + App.restaurants.permalink );
		return;
	}
	$scope.signout = AccountSignOut;
	$scope.orders = OrdersService;
	$scope.orders.all();
});

NGApp.controller( 'giftcard', function ($scope, $location, GiftCardService ) {
	$scope.giftcard = GiftCardService;
	$scope.giftcard.parseURLCode();
	$location.path( '/location' );
	setTimeout( function(){ $scope.giftcard.giftCardModal.open(); }, 300 );
});

NGApp.controller('reset', function ($scope, $location, AccountModalService) {
	$scope.modal = AccountModalService;
	$scope.modal.resetOpen();
	$location.path( '/' );
});

/**
 * FoodDelivery's methods
 */
App.foodDelivery = {};