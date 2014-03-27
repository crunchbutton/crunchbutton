/**
 * splash page
 */
NGApp.controller('DownloadCtrl', function ($scope, $http) {
	$scope.text = {
		number: '',
		sent: false,
		send: function() {
			$http.post(App.service + 'download?num=' + $scope.text.number.replace(/-/g,''));				
			$scope.text.sent = true;
		}
	};
});

/**
 * splash page
 */
NGApp.controller('SplashCtrl', function ($scope, AccountFacebookService) {
	$scope.facebook = AccountFacebookService;
	if (App.parallax.setupBackgroundImage) {
		App.parallax.setupBackgroundImage($('.home-top').get(0));
	}
});

/**
 * jobs page
 */
NGApp.controller('JobsCtrl', function ($scope) {
	var reps = 'moc.nottubhcnurc@spersupmac'.split('').reverse().join('');
	var devs = 'moc.nottubhcnurc@ylnosratskcor'.split('').reverse().join('');
	$scope.reps = reps;
	$scope.devs = devs;
});

/**
 * owners page
 */
NGApp.controller('OwnersCtrl', function ($scope) {
	var join = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');
	$scope.join = join;
});

/**
 * About page
 */
NGApp.controller('AboutCtrl', function ($scope) {
});

/**
 * legal page
 */
NGApp.controller('LegalCtrl', function ($scope) {
	var join = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');
	var goodbye = 'moc.nottubhcnurc@eybdoog'.split('').reverse().join('');
	$scope.join = join;
	$scope.goodbye = goodbye;
});

/**
 * help page
 */
NGApp.controller('HelpCtrl', function ($scope) {
	var customers = 'moc.nottubhcnurc@sremotsucyppah'.split('').reverse().join('');
	var join = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');
	$scope.customers = customers;
	$scope.join = join;
});


/**
 * Home controller
 */
NGApp.controller('HomeCtrl', function ($scope, $http, $location, RestaurantsService, LocationService) {
	if (!App.isPhoneGap) {
		// If it have a valid restaurant position just reditect to restaurants page
		if (LocationService.position.pos().valid('restaurants')) {
			$location.path('/' + RestaurantsService.permalink);
		} else {
			$location.path('/location');
		}
	}
	$location.replace();
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
					prep: results.alias.prep(),
					image: results.alias.image(),
					permalink: results.alias.permalink()
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
NGApp.controller( 'RestaurantsCtrl', function ( $scope, $rootScope, $http, $location, $timeout, RestaurantsService, LocationService, RestaurantService, CommunityAliasService ) {

	$scope.restaurants = false;
	
	$scope.loadingRestaurant = false;

	var showMoreStage = 1; // stage 1: show top 6 maximized, stage 2: show all maximized, stage 3: show all - #2456

	$scope.showMoreRestaurants = function() {
		showMoreStage++;
		if( showMoreStage == 2 ){
			var restaurantsToShow = 0;
			for ( var x in $scope.restaurants ) {
				if( $scope.restaurants[ x ]._maximized ){
					restaurantsToShow++;
				}
			}
			$scope.restaurantsToShow = restaurantsToShow;
			return;
		}
		if( showMoreStage == 3 ){
			$scope.restaurantsToShow = $scope.restaurants.length;
			return;
		}
	};

	var location = LocationService;
	if (!location.initied) {
		location.init();
		$location.path( '/' );
		return;
	}
	
	var motivationText = ['You are awesome','You are loved','You are beautiful','You\'re at the top of your game','You are rad'];
	$scope.motivationText = motivationText[Math.floor(Math.random() * motivationText.length)];

	var restaurants = RestaurantsService;

	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';
	
	var checkOpen = function() {
		var allClosed = true;
		for (var x in $scope.restaurants) {
			if ($scope.restaurants[x]._open) {
				allClosed = false;
				break;
			}
		}
		$scope.allClosed = allClosed;
					
	};

	// Update the close/open/about_to_close status from the restaurants
	var updateStatus = function(){
		updateRestaurantsStatus = $timeout( function(){
			// Update status of the restaurant's list
			$scope.restaurants = restaurants.getStatus();
			$rootScope.$safeApply();
			updateStatus();
			checkOpen();
		}, 1000 * 35 );
	}

	$scope.$on( '$destroy', function(){
		RestaurantsService.forceGetStatus = true;
		// Kills the timer when the controller is changed
		if( typeof( updateRestaurantStatus ) !== 'undefined' && updateRestaurantStatus ){
			try{ $timeout.cancel( updateRestaurantsStatus );} catch(e){}
		}
	});

	// It means the list is already loaded so we need to update the restaurant's status
	if( RestaurantsService.forceGetStatus ){
		setTimeout( function(){
			$scope.restaurants = restaurants.getStatus();
			updateStatus();
			$rootScope.$safeApply();
		}, 1 );
	}

	var status;

	$rootScope.$on( 'appResume', function(e, data) {
		var checkDateTime = function(){
			if( dateTime && dateTime.getNow && dateTime.getNow() ){
				if( $location.path() == '/' + RestaurantsService.permalink ){
					$scope.restaurants = restaurants.getStatus();
					updateStatus();
				}
			} else {
				setTimeout( function(){
					checkDateTime();
				}, 50 );
			}
		}
		checkDateTime();
	});

	$scope.display = function($event){
		if ($scope.loadingRestaurant) {
			return;
		}
		$scope.loadingRestaurant = true;
		var restaurant = this.restaurant;

		// if ( !restaurant.open( dateTime.getNow(), true ) ) {
		if ( !restaurant.openRestaurantPage( dateTime.getNow() ) ) {
			$rootScope.$broadcast( 'restaurantClosedClick', restaurant );
			$scope.restaurants = restaurants.getStatus();
			$scope.loadingRestaurant = false;
		} else {
			// Store the load info of the clicked restaurant to optmize the restaurant page load
			RestaurantService.basicInfo = restaurant;
			App.go( '/' + restaurants.permalink + '/' + restaurant.permalink, 'push' );
		}
	};

	var prep = restaurants.position.pos().prep();
	var city = restaurants.position.pos().city();
	var image = restaurants.position.pos().getImage();

	// add the community class
	if( restaurants.position.pos().type() == 'alias' ){
		CommunityAliasService.communityStyle( restaurants.position.pos().permalink() );
	} else {
		CommunityAliasService.removeCommunityStyle();
	}

	restaurants.list( 
		// Success
		function(){

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
			checkOpen();

			var restaurantsToShow = 0;
			for ( var x in $scope.restaurants ) {
				if( $scope.restaurants[ x ]._maximized ){
					restaurantsToShow++;
				}
			}
			if( restaurantsToShow > 6 ){
				restaurantsToShow = 6;
			} else if ( restaurantsToShow < 6 ) {
				showMoreStage = 2;
			}
			$scope.restaurantsToShow = restaurantsToShow;

			// Wait one minute until update the status of the restaurants
			setTimeout( function(){
				updateStatus();
			}, 1000 * 60 );

			$scope.slogan = slogan;
			$scope.tagline = tagline;
			$scope.image = image;

			if ( $scope.restaurants.length == 4 ) {
				$('.content').addClass('short-meal-list');
			} else {
				$('.content').removeClass('short-meal-list');
			}
			$('.content').removeClass('smaller-width');

		}, 
		// Error
		function(){
			App.go( '/location' );
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

	var reps = 'moc.nottubhcnurc@spersupmac'.split('').reverse().join('');
	$scope.reps = reps;


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
		App.go( '/' + city, 'push' );
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
		
		if (isStreet && entered && !App.isUI2() && !entered.match(new RegExp(/\d{5}(?:[-\s]\d{4})?/))) {
			$('.location-address').val('').attr('placeholder','Please include a zip code');	
			$scope.$broadcast( 'locationNotServed',  true );
		} else {
			$scope.locationError = true;
		}
		// Remove the location from cockie
		PositionsService.removeNotServedLocation();
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

		// if we detect his location and it is not served #2311
		if( typeof( pos ) != 'undefined' && pos.type() == 'geolocation' ){
			$scope.locationError = true;
		}

		// Remove the location from cockie
		PositionsService.removeNotServedLocation();

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
		App.go( '/' + restaurants.permalink, 'push' );
		$scope.location.form.address = '';
		$scope.warningPlaceholder = false;
		$scope.isProcessing = false;
		if (!$scope.$$phase){
			$scope.$apply();	
		}
	};

	// lets eat button
	$scope.letsEat = function() {

		$scope.location.form.address = $.trim( $scope.location.form.address );
		
		if ( $scope.location.form.address == '' ) {
			var locSpin = $( '.location-detect' ).data( 'spinner' );
			locSpin.start();
			$scope.location.getLocationByBrowser(
			// Success, got location
			function(loc) {
				// As it should return a new loc we can remove the previous geolocation
				// that way we don't have two equals location
				$scope.location.position.removeNotServedLocation();
				// Add the position at the locations
				$scope.location.position.addLocation( loc );
				// Verify if user address is served
				restaurants.list( 
					// Yay the user's location is served
					proceed,
					// Error not served
					function(){
						var error = function() {
							locSpin.stop();
							$scope.$broadcast( 'locationNotServed' );
						}
					}
				);
			}, 
			// Error, user doesn't shared his location
			function(){
				locSpin.stop();
				$('.location-address').val('').attr('placeholder',$('<div>').html('&#10148; Please enter your address here').text());
				$scope.warningPlaceholder = true;
				// the user might be typing his login/pass - so blur it
				if( !App.dialog.isOpen() ){
					$scope.focus( '.location-address' );
				}
			} );

		} else {
			// Start the spinner
			spin.start();
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
			// As it should return a new loc we can remove the previous geolocation
			// that way we don't have two equals location
			$scope.location.position.removeNotServedLocation();
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
NGApp.controller( 'RestaurantCtrl', function ($scope, $http, $routeParams, $rootScope, $timeout, RestaurantService, OrderService, CreditService, GiftCardService, PositionsService, MainNavigationService, CreditCardService) {

	var order = OrderService;
	order.loaded = false;
	$scope.order = {};
	$scope.open = false;

	$scope.restaurantLoaded = RestaurantService.alreadyLoaded();

	$scope.restaurant = false;

	if( RestaurantService.basicInfo ){
		$scope.restaurant = RestaurantService.basicInfo;
		$scope.open = $scope.restaurant._open;
	}

	// we dont need to put all the Service methods and variables at the $scope - it is expensive
	order.startStoreEntederInfo = false;
	$scope.order.form = order.form;
	$scope.order.info = order.info;
	
	$scope.Math = window.Math;

	$scope.nothingInTheCartDesktop = App.AB.pluck('nothingInTheCartDesktop', App.config.ab.nothingInTheCartDesktop).line;
	$scope.nothingInTheCartMobile = App.AB.pluck('nothingInTheCartMobile', App.config.ab.nothingInTheCartMobile).line;

	$scope.order.creditCardChanged = function() {
		order._cardInfoHasChanged = true;
	};
	
	var creditCard = CreditCardService;
	
	// update if the restaurant is closed or open every 35 seconds
	var updateStatus = function(){
		updateRestaurantStatus = $timeout( function(){
			$scope.restaurant.open();
			$scope.restaurant.reloadHours();
			var open = $scope.restaurant._open;
			if ($scope.open != open) {
				$scope.open = open;
			}
			if (!$scope.$$phase){
				$scope.$apply();	
			}
			updateStatus();
		}, 1000 * 35 );
	}

	$scope.$on( '$destroy', function(){
		// Kills the timer when the controller is changed
		if( typeof( updateRestaurantStatus ) !== 'undefined' && updateRestaurantStatus ){
			try{ $timeout.cancel( updateRestaurantStatus ); } catch(e){}
		}
		if( typeof( forceReloadTimer ) !== 'undefined' && forceReloadTimer ){
			try{ $timeout.cancel( forceReloadTimer ); } catch(e){}
		}
	});

	$rootScope.$on( 'appResume', function(e, data) {
		var checkDateTime = function(){
			if( dateTime && dateTime.getNow && dateTime.getNow() ){
				updateStatus();
			} else {
				setTimeout( function(){
					checkDateTime();
				}, 50 );
			}
		}
		checkDateTime();
	});

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
	$scope.order.checkout = function(){
		return order.checkout();
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
	$scope.addressOutOfRangePlaceAnyway = function(){
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

		$scope.restaurantLoaded = RestaurantService.alreadyLoaded();
		
		var community = data.community;

		$scope.restaurant = data.restaurant;
		
		order.restaurant = $scope.restaurant;

		MainNavigationService.restaurant = $scope.restaurant;
		
		$scope.open = $scope.restaurant.open();

		document.title = $scope.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton';

		setTimeout( function(){

			var process = function(){

				order.init();

				// Update some gift cards variables
				giftcard.notes_field.id_restaurant = $scope.restaurant.id_restaurant;
				giftcard.notes_field.restaurant_accepts = ( $scope.restaurant.giftcard > 0 );
				
				// Load the credit info
				if( OrderService.account.user && OrderService.account.user.id_user ){
					credit.getCredit( $scope.restaurant.id_restaurant );	
				}

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
				
				$rootScope.$safeApply( function($scope) {} );

				// @todo: do we still neded this??
				// $('.body').css({ 'min-height': $('.restaurant-items').height()});

				// Place cash order even if the user has gift card see #1485
				$scope.ignoreGiftCardWithCashOrder = false;

			}

			// Call anyway
			process();

			// Method that checks if the preset must to be reloaded #1988
			OrderService.account.checkPresetUpdate( data.restaurant.id_restaurant, 
				// will be called if the preset was reloaded
				function(){
					// reset cart
					order.resetCart();
					// call process again
					process();
				}
			);

		} );

	});

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	if( RestaurantService.basicInfo ){
		// If we have the basic info of the restaurant we just wait till the transition animation ends before load the restaurant data
		$scope.$on( '$routeChangeSuccess', function ( event ) {
			setTimeout( function(){ restaurantService.init(); updateStatus(); }, 10 );
		} );
	} else {
		restaurantService.init();
		updateStatus();
	}

	// force reload case it crashed
	var forceReload = function(){
		forceReloadTimer = $timeout( function(){
			if( !order.loaded ){
				restaurantService.init();
				updateStatus();
				forceReload();
				$rootScope.$safeApply();
			} else {
				$rootScope.$safeApply();
			}
		} , 1000 * 10 );
	};
	forceReload();

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
		$scope.referral.invite_url_cleared = ReferralService.value;
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
	
	$scope.print = function() {
		window.open('http://google.com', '_blank');
		$('.order-print').get(0).contentWindow.document.body.innerHTML = $('.order-print-content').html();
		$('.order-print').get(0).contentWindow.print();
	};

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
	$scope.signout = AccountSignOut.signout;
	$scope.facebook = AccountModalService.facebookOpen;
	$scope.orders = [];

	// Alias to OrdersService methods
	$scope.orders.restaurant = OrdersService.restaurant;
	$scope.orders.receipt =  OrdersService.receipt;

	if( OrdersService.reload ){
		OrdersService.load();
	} else {
		$scope.orders.list = OrdersService.list;
		// Check if the orders list need to be updated #1988
		OrdersService.checkUpdate();
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

NGApp.controller( 'AccountModalHeaderCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;
	$scope.resetModal = function(){
		$scope.modal.toggleSignForm( 'signin' );
		$scope.help.show( false );
	}
});



NGApp.controller( 'AccountFacebookCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
});

NGApp.controller( 'AccountSignInCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService, AccountFacebookService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;
	$scope.facebook = AccountFacebookService;
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

NGApp.controller( 'BottomCtrl', function ( $scope, MainNavigationService, OrderService ) {
	$scope.navigation = MainNavigationService;
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
			$scope.closedMessage = r.closedMessage();
			$scope.acceptingOrders = ( parseInt( r.open_for_business ) > 0 );
			App.dialog.show('.restaurant-closed-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.restaurant = r;
				scope.closedMessage = r.closedMessage();
				$scope.acceptingOrders = ( parseInt( r.open_for_business ) > 0 );
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

