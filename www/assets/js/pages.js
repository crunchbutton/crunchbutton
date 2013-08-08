/**
 * legal page
 */
NGApp.controller('legal', function ($scope, $http) {
	$http.get(App.service + 'legal').success(function(data) {
		$scope.legal = data.data;
	});
});


/**
 * help page
 */
NGApp.controller('help', function ($scope, $http) {
	$http.get(App.service + 'help').success(function(data) {
		$scope.help = data.data;
		$('.crunchbutton-join-mail').html('moc.nottubhcnurc@nioj'.split('').reverse().join(''));
	});
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
		function( results ){
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
				$location.path( '/' + App.restaurants.permalink );
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
NGApp.controller( 'restaurants', function ( $scope, $http, $location, RestaurantsService ) {

	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';

	$scope.restaurants = RestaurantsService;

	$scope.display = function() {
		if ( !this.restaurant.open() ) {
			App.alert("This restaurant is currently closed. It will be open during the following hours (" + this.restaurant._tzabbr + "):\n\n" + this.restaurant.closedMessage());
			App.busy.unBusy();
		} else {
			$location.path('/' + App.restaurants.permalink + '/' + this.restaurant.permalink);
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
NGApp.controller( 'location', function ($scope, $http, $location, RestaurantsService, RecommendRestaurantService, LocationService, AccountService ) {
	
	var account = AccountService;

	$scope.isUser = account.user.has_auth;
	$scope.notUser = !account.user.has_auth;
	$scope.topCommunities = App.topCommunities;

	$scope.location = LocationService;

	setTimeout(function() {
		$scope.location._start();
	}, 10 );

	$scope.yourArea = $scope.location.position.pos().city() || 'your area';

	$scope.restaurantsService = RestaurantsService;

	$scope.locationError = false;

	$scope.recommend = RecommendRestaurantService;

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
						function(){
							$location.path( '/' + App.restaurants.permalink );	
						},
						// Error
						function(){
							$scope.recommend.greetings = false;
							$scope.locationError = true;
						} );
				}, 
				// Address not ok
				function() {
					$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
				}
			);
		}
	}
});

/**
 * restaurant page
 */
NGApp.controller('restaurant', function ($scope, $http, $routeParams, RestaurantService, OrderService, CreditService, GiftCardService) {

	$scope.restaurantService = RestaurantService;
	$scope.order = OrderService;
	$scope.order.loaded = false;

	$scope.credit = CreditService;
	$scope.giftcard = GiftCardService;

	// Alias to ServiceAccount.user
	$scope.user = $scope.order.account.user;

	$scope.restaurantService.init();

	$scope.checkGiftCard = function(){
		$scope.giftcard.notes_field.content = $scope.order.form.notes;
		$scope.giftcard.notes_field.start();
	}

	$scope.AB = {
				dollar: (App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '',
				changeablePrice: function (dish) {
					return (App.config.ab && App.config.ab.changeablePrice == 'show' && dish.changeable_price) ? '+' : ''
				},
				restaurantPage: (App.config.ab && App.config.ab.restaurantPage == 'restaurant-page-noimage') ? ' restaurant-pic-wrapper-hidden' : ''
			};

	$scope.$watch( 'restaurantService.loaded', function( newValue, oldValue, scope ) {
		if( newValue ){
			$scope.restaurant	 = $scope.restaurantService.restaurant;
			$scope.order.restaurant = $scope.restaurant;
			$scope.order.init();

			$scope.giftcard.notes_field.id_restaurant = $scope.restaurant.id_restaurant;
			$scope.giftcard.notes_field.restaurant_accepts = ( $scope.restaurant.giftcard > 0 );

			$scope.credit.getCredit( $scope.restaurant.id_restaurant );

/*
			$scope.lastOrderDelivery = $scope.service.lastOrderDelivery;
			$scope.community = $scope.service.community;
			$scope.showRestaurantDeliv = $scope.service.showRestaurantDeliv;*/
		}
	});

	// watch credit changes
	$scope.$watch( 'credit.value', function( newValue, oldValue, scope ) {
		$scope.order.updateTotal();
	});

	// watch cart changes
	$scope.$watch( 'order.cart.items', function( newValue, oldValue, scope ) {
		$scope.order.updateTotal();
	}, true);

	// Validate gift card at the notes field
	$scope.$watch( 'order.form.notes', function( newValue, oldValue, scope ) {
		$scope.checkGiftCard();
	});

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	// As the div restaurant-items has position:absolute this line will make sure the footer will not go up.
	$('.body').css({
		'min-height': $('.restaurant-items').height()
	});


/*
	// If the typed address is different of the user address the typed one will be used #1152
	if (false && App.loc.changeLocationAddressHasChanged && App.loc.pos() && App.loc.pos().addressEntered && App.loc.pos().addressEntered != service.account.user.address) {
		// Give some time to google.maps.Geocoder() load
		var validatedAddress = function () {
			if (google && google.maps && google.maps.Geocoder) {
				var addressToVerify = App.loc.pos().addressEntered;
				// Success the address was found
				var success = function (results) {
					var address = results[0];
					if (address) {
						// Valid if the address is acceptable
						if (App.loc.validateAddressType(address)) {
							// If the flag useCompleteAddress is true
							if (App.useCompleteAddress) {
								$('[name=pay-address]').val(App.loc.formatedAddress(address));
								$('.user-address').html(App.loc.formatedAddress(address));
							} else {
								$('[name=pay-address]').val(addressToVerify);
								$('.user-address').html(addressToVerify);
							}
						} else {
							console.log('Invalid address: ' + addressToVerify);
						}
					}
				};
				// Error, do nothing
				var error = function () {};
				App.loc.doGeocode(addressToVerify, success, error);
			} else {
				setTimeout(function () {
					validatedAddress();
				}, 10);
			}
		}
		validatedAddress();
	}


	if (App.order['pay_type'] == 'cash' || lastPayCash == 'cash') {
		App.trigger.cash();
	} else {
		App.trigger.credit();
	}

	if (lastPayCash == 'cash') {
		App.trigger.cash();
	} else if (lastPayCash == 'card') {
		App.trigger.credit();
	}

	if (service.restaurant.credit != '1') {
		App.trigger.cash();
	}

	if (service.restaurant.cash != '1' && service.restaurant.credit == '1') {
		App.trigger.credit();
	}

	// Rules at #669
	if ((lastOrderDelivery == 'delivery' && service.restaurant.delivery == '1') ||
		(App.order['delivery_type'] == 'delivery' && service.restaurant.delivery == '1') ||
		(service.restaurant.takeout == '0') ||
		(lastOrderDelivery != 'takeout' && service.restaurant.delivery == '1')) {
		App.trigger.delivery();
	}

	// If the restaurant doesn't delivery
	if (App.order['delivery_type'] == 'takeout' || service.restaurant.delivery != '1') {
		App.trigger.takeout();
	}

	// If the user has presets at other's restaurants but he did not typed his address yet
	// and the actual restaurant is a delivery only #875
	if ((service.restaurant.takeout == '0' || App.order['delivery_type'] == 'delivery') && !service.account.user.address) {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	}


	if (!service.account.user.id_user) {
		service.account.user.address = App.loc.enteredLoc;
		App.loc.enteredLoc = '';
	}
	//*/



	
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