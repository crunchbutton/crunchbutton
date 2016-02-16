/**
 * splash page
 */
NGApp.controller('DownloadCtrl', function ($scope, $http, AccountService, AccountFacebookService) {
	if (App.isAndroid()) {
		$scope.downloadLink = 'https://play.google.com/store/apps/details?id=com.crunchbutton';
	} else {
		$scope.downloadLink = 'https://itunes.apple.com/app/id721780390';
	}

	$scope.facebook = AccountFacebookService;

	$scope.text = {
		number: '',
		sent: false,
		send: function() {
			if (AccountService.user.phone && !$scope.text.number) {
				$scope.text.number = AccountService.user.phone;
			}
			$http.post(App.service + 'download?num=' + $scope.text.number.replace(/-/g,''));
			$scope.text.sent = true;
		}
	};
});

NGApp.controller('ApplyCtrl', function ($scope, $http, ApplyService, $location) {

	$scope.communities = [];

	ApplyService.communities( function( data ){
		$scope.communities = data;
	} );


	$scope.apply = {};
	$scope.errors = {};
    $scope.post = function(){
    	$scope.errors = {};
    	if (!$scope.apply.firstName) {
    		$scope.errors.firstName = true;
    	}
    	if (!$scope.apply.lastName) {
    		$scope.errors.lastName = true;
    	}
    	if (!$scope.apply.email) {
    		$scope.errors.email = true;
    	}
    	if (!$scope.apply.university) {
    		$scope.errors.university = true;
    	}
		if ($scope.apply.university == 'Other') {
			if (!$scope.apply.otherUniversity){
				$scope.errors.otherUniversity = true;
			}
		}
    	if (!$scope.apply.number) {
    		$scope.errors.number = true;
    	}
    	if (!$scope.apply.phone) {
    		$scope.errors.phone = true;
    	}
    	if (!$scope.apply.carrier) {
    		$scope.errors.carrier = true;
    	}
    	if ($scope.apply.carrier == 'Other') {
    		if (!$scope.apply.otherCarrier){
    		$scope.errors.otherCarrier = true;
    		}
    	}
    	//if (!$scope.apply.transport) {
    	//	$scope.errors.transport = true;
    	//}
    	if (!$scope.apply.hours) {
    		$scope.errors.hours = true;
    	}
    	if (!$scope.apply.applicant) {
    		$scope.errors.applicant = true;
    	}
    	if (!$scope.apply.source) {
    		$scope.errors.source = true;
    	}
    	if ($scope.apply.source == 'other') {
    		if (!$scope.apply.otherSource){
    		$scope.errors.otherSource = true;
    		}
    	}

    	if (jQuery.isEmptyObject($scope.errors)) {
    			ApplyService.post($scope.apply, function(data){
    				$location.path( '/thankyou' );
        		console.log(data);
    			})
    	} else {
			App.alert('Please fill out all of the fields.');
		}
    };
});

NGApp.controller('RepsApplyShareCtrl', function ($scope, $location, $routeParams, ApplyService ) {

	var login = $routeParams.id;
	if( !login ){
		$location.path( '/reps/apply/' );
	}

	ApplyService.code( login, function( json ){
		if( json.error ){
			$location.path( '/reps/apply/' );
		} else {
			$scope.code = json.code;
			$scope.loaded = true;

			var text = 'I love @crunchbutton delivery :) use my code ' + $scope.code + ' in the Notes section for $3 off!';

			if( App.iOS() ){
				$scope.sms = 'sms:&body=' + text;
			} else {
				$scope.sms = 'sms:?body=' + text;
			}
		}
	} );

	$scope.facebook = function(){
		var text = 'I love @crunchbutton delivery :) use my code ' + $scope.code + ' in the Notes section for $3 off!';
		var link = 'https://crunchbutton.com/invite/' + $scope.code;
		App.share({
			url: link,
			name: 'Crunchbutton',
			caption: ' ',
			description: text
		});
	}

	$scope.twitter = function(){
		var link = 'https://crunchbutton.com/invite/' + $scope.code;
		var text = 'I love @crunchbutton delivery :) use my code ' + $scope.code + ' in the Notes section for $3 off!';
		window.open('https://twitter.com/intent/tweet?url=' + link + '&text=' + text + '&hashtags=Crunchbutton' ,'_system');
	}

});

NGApp.controller('RepsApplyCtrl', function ($scope, $location, ApplyService) {

	$scope.communities = [];

	ApplyService.communities( function( data ){
		$scope.communities = data;
	} );

	$scope.step = 1;

	$scope.apply = {};
	$scope.saveBasicInfo = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;
		$scope.apply.step = 1;
		ApplyService.save_rep( $scope.apply, function( json ){
			if( json.error ){
				App.alert( json.error );
			} else {
				$scope.step = 2;
				$scope.rep = json.success;
			}
			$scope.isSaving = false;
		});
	};

	$scope.saveAddress = function(){
		$scope.isSaving = true;
		$scope.rep.step = 2;
		ApplyService.save_rep( $scope.rep, function( json ){
			if( json.error ){
				App.alert( json.error );
			} else {
				$scope.step = 2;
				$location.path( '/reps/apply/' + json.success.login );
			}
			$scope.isSaving = false;
		});
	};

});


/**
 * splash page
 */
NGApp.controller('SplashCtrl', function ($scope, AccountFacebookService) {

	$scope.facebook = AccountFacebookService;

	if (App.parallax.setupBackgroundImage) {
		App.parallax.setupBackgroundImage( $( '.home-top' ).get( 0 ) );
		if( App.isCordova && App.isAndroid() ){
			// Android App: Tough to Get to Enter Address #2905
			angular.element( '.fixed-599' ).css( 'position', 'static' ).css( 'marginTop', 10 );
		}
	}
});

/**
 * work page
 */
NGApp.controller('WorkCtrl', function ($scope) {

	var reps = 'moc.nottubhcnurc@gnitekram'.split('').reverse().join('');
	var devs = 'moc.nottubhcnurc@reebdnaedoc'.split('').reverse().join('');
	var recruiting = 'moc.nottubhcnurc@gnitiurcer'.split('').reverse().join('');
	var resumes = 'moc.nottubhcnurc@semuser'.split('').reverse().join('');

	$scope.resumes = resumes;
	$scope.recruiting = recruiting;
	$scope.reps = reps;
	$scope.devs = devs;
});

/**
 * thank you page
 */
NGApp.controller('ThankyouCtrl', function ($scope) {
	var reps = 'moc.nottubhcnurc@gnitekram'.split('').reverse().join('');
	var devs = 'moc.nottubhcnurc@reebdnaedoc'.split('').reverse().join('');
	$scope.reps = reps;
	$scope.devs = devs;
});

/**
 * owners page
 */

NGApp.controller('OwnersCtrl', function ($scope) {
	var join = 'moc.nottubhcnurc@sunem'.split('').reverse().join('');
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
	var join = 'moc.nottubhcnurc@sunem'.split('').reverse().join('');
	var goodbye = 'moc.nottubhcnurc@eybdoog'.split('').reverse().join('');
	$scope.join = join;
	$scope.goodbye = goodbye;
});

/**
 * Free food page
 */
NGApp.controller('FreeFoodCtrl', function ($scope, $location, AccountService, ReferralService, FacebookService, AccountModalService ) {

	if( !AccountService.isLogged() ){
		// waiting devin's comment
		// $location.path( '/' );
		// return;
	}

	$scope.modal = { signupOpen : AccountModalService.signupOpen };

	$scope.account = AccountService;

	var twitter_text = App.AB.get('share-twitter-text');

	$scope.referral = {
		invite_url : ReferralService.invite_url,
		value : ReferralService.value,
		limit : ReferralService.limit,
		invites : ReferralService.invites,
		enabled : ReferralService.enabled,
		invite_code : ReferralService.invite_code,
		sms : ReferralService.sms(),
		text : twitter_text
	}

	$scope.referral.cleaned_url = function(){
		return ReferralService.cleaned_url();
	}

	$scope.$on( 'referralStatusLoaded', function( e, data ) {
		$scope.referral.invites = ReferralService.invites;
		$scope.referral.limit = ReferralService.limit;
		$scope.referral.invite_url = ReferralService.invite_url;
		$scope.referral.value = ReferralService.value;
		$scope.referral.enabled = ReferralService.enabled;
		$scope.referral.invite_code = ReferralService.invite_code;
		$scope.referral.sms = ReferralService.sms();
	});

	$scope.isMobile = App.isMobile();

	$scope.referral.facebook = function(){
		FacebookService.postInvite( $scope.referral.invite_url, $scope.referral.invite_code );
	}

	$scope.referral.twitter = function(){
		window.open('https://twitter.com/intent/tweet?url=' + $scope.referral.invite_url + '&text=' + $scope.referral.text + '&hashtags=Crunchbutton' ,'_system');
	}

});


/**
 * help page
 */
NGApp.controller('HelpCtrl', function ($scope) {
	var happy_customers = 'moc.nottubhcnurc@sremotsucyppah'.split('').reverse().join('');
	var join = 'moc.nottubhcnurc@sunem'.split('').reverse().join('');
	var angry_customers = 'moc.nottubhcnurc@sremotsucyrgna'.split('').reverse().join('');
	$scope.happy_customers = happy_customers;
	$scope.angry_customers = angry_customers;
	$scope.join = join;
});


/**
 * Home controller
 */
NGApp.controller('HomeCtrl', function ($scope, $http, $location, RestaurantsService, LocationService) {
	if (!App.isCordova) {
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
NGApp.controller( 'RestaurantsCtrl', function ( $scope, $rootScope, $http, $location, $timeout, $route, PositionsService, RestaurantsService, LocationService, RestaurantService, CommunityAliasService, AccountService ) {

	var error = function(){
		$rootScope.navigation.link('/location', 'instant' );
	}

	$scope.$on( 'window-focus', function(e, data) {
		updateRestaurantsHours();
	});

	var updateRestaurantsHours = function(){
		restaurants.reloadAllHours( function( data ){
			$rootScope.$safeApply( function(){
				$scope.restaurants = restaurants.sort();
			} );
		} );
	}

	$scope.show_suggestions = false;

	$scope.restaurants = false;

	$scope.loadingRestaurant = false;

	$scope.entered = PositionsService.pos().entered() || ( AccountService.user && AccountService.user.address );

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
		if( allClosed ){
			for (var x in $scope.restaurants) {
				if( $scope.restaurants[x].next_open_time_message &&
						$scope.restaurants[x].next_open_time_message.today &&
						$scope.restaurants[x].next_open_time_message.today == 'Today' ){
					$scope.openSoon = true;
				}
			}
		}
		$scope.allClosed = allClosed;
		$scope.community_closed = restaurants.community_closed;
	};

	var updateRestaurantsStatus = null;

	var updateStatus = function(){
		updateRestaurantsStatus = $timeout( function(){
			// Update status of the restaurant's list
			$rootScope.$safeApply();
			updateStatus();
			checkOpen();
		}, 1000 * 35 );
	}

	var checkLoadedRestaurants = true;

	$scope.$on( '$destroy', function(){
		checkLoadedRestaurants = false;
		if( App.isCordova && App.isAndroid() && checkIfRestaurantsWereLoaded ){
			$timeout.cancel( checkIfRestaurantsWereLoaded );
		}
		RestaurantsService.forceGetStatus = true;
		// Kills the timer when the controller is changed
		if( typeof( updateRestaurantsStatus ) !== 'undefined' && updateRestaurantsStatus ){
			try{ $timeout.cancel( updateRestaurantsStatus );} catch(e){}
		}
	});

	// It means the list is already loaded so we need to update the restaurant's status
	if( RestaurantsService.forceGetStatus ){
		setTimeout( function(){
			updateStatus();
			$rootScope.$safeApply();
		}, 1 );
	}

	var status;

	$rootScope.$on( 'appResume', function(e, data) {
		var checkDateTime = function(){
			if( dateTime && dateTime.getNow && dateTime.getNow() ){
				if( $location.path() == '/' + RestaurantsService.permalink ){;
					$rootScope.$safeApply();
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

	$scope.activeCssStyle = App.activeButtonsEnable;

	$scope.display = function($event){

		if ( $scope.loadingRestaurant || $rootScope.navigation.page != 'restaurants') {
			return;
		}

		var restaurant = this.restaurant;

		$scope.loadingRestaurant = [];
		$scope.loadingRestaurant[restaurant.id_restaurant] = true;

		var checkHours = function(){
			if (restaurant.permalink.match(/^(launching|drive|drivers|driving)-.*/)){
				$rootScope.navigation.link('/drivers/apply', 'push' );
				return;
			}
			if ( restaurant.openRestaurantPage( dateTime.getNow() ) || restaurant.allow_preorder ) {
				// Store the load info of the clicked restaurant to optmize the restaurant page load
				RestaurantService.basicInfo = restaurant;

				$rootScope.navigation.link( '/' + restaurants.permalink + '/' + restaurant.permalink, 'push' );
			} else {
				$scope.loadingRestaurant = false;
				$rootScope.$broadcast( 'restaurantClosedClick', restaurant );
				$scope.restaurants = restaurants.getStatus();
			}
		}
		// See #2799
		restaurant.isActive(
			function( active ){
				if( active ){
					checkHours();
				} else {
					$scope.loadingRestaurant = false;
					restaurant.inactive = true;
					$rootScope.$broadcast( 'restaurantClosedClick', restaurant );
					// Remove the inactive restaurant from list
					$rootScope.$safeApply( function(){
						restaurants.removeInactive( restaurant.id_restaurant );
						updateRestaurantsHours();
					} );
				}
			}
		);

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

	var listLoaded = false;

	// See #5129
	if( App.isCordova && App.isAndroid() ){
		var checkIfRestaurantsWereLoaded = $timeout(function() {
			if( $route.current && $route.current.$$route && $route.current.$$route.action == 'restaurants' && checkLoadedRestaurants && !listLoaded ){
				error();
			}
		}, 25000 );
	}

	var id_community = null;

	var loadList = function(){
			restaurants.list(
				// Success
				function(){

					try {

						$rootScope.$broadcast( 'updateQuote', RestaurantsService.community.id_community );

						var slogan = App.slogan.slogan;
						var tagline = '';
						if( RestaurantsService.community && RestaurantsService.community.tagline1 ){
							tagline = RestaurantsService.community.tagline1;
						}
						if( RestaurantsService.community && RestaurantsService.community.tagline2 ){
							tagline += '<br>' + RestaurantsService.community.tagline2;
						}

						if( RestaurantsService.community && RestaurantsService.community.operation_hours ){
							$scope.operation_hours = RestaurantsService.community.operation_hours;
						} else {
							$scope.operation_hours = false;
						}

						if( $.trim( tagline ) == '' ){
							var sloganReplace = ( prep || 'in' ) + ' ' + ( city || 'your area' );
							sloganReplace = $.trim(sloganReplace);
							tagline = App.tagline.tagline.replace('%s', sloganReplace);
						}

					} catch (e) {
						console.log('Failed to load dynamic text', App.slogan, App.tagline, e);
						var slogan = '';
						var tagline = '';
					}

					document.title = ( city || '' ) + ' Food Delivery | Order Food from ' + (city || 'Local') + ' Restaurants | Crunchbutton';

					$scope.restaurants = restaurants.sort();
					checkOpen();

					var id_community = null;

					$scope.restaurantsToShow = $scope.restaurants.length;

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

					listLoaded = true;

					$scope.show_suggestions = true;

				},
				// Error
				function(){
					error();
				}
			);
		setTimeout(function(){
			restaurants.forceLoad = true;
			loadList();
		}, 15 * 60 * 1000 );

	}

	loadList();


	$scope.suggestion = function(){
		$rootScope.$broadcast( 'restaurantsSuggestion', RestaurantsService.community.id_community );
	}

});


/**
 * show cities
 */
NGApp.controller( 'CitiesCtrl', function ( $scope ) {

});


/**
 * Change location
 */
NGApp.controller( 'LocationCtrl', function ($scope, $http, $location, $rootScope, RestaurantsService, LocationService, AccountService, PositionsService, RecommendRestaurantService ) {

	var reps = 'moc.nottubhcnurc@gnitekram'.split('').reverse().join('');
	var devs = 'moc.nottubhcnurc@reebdnaedoc'.split('').reverse().join('');
	$scope.reps = reps;
	$scope.devs = devs;

	$scope.loadingLocation = false;
	$scope.loadingGetfood = false;


	$scope.rage = function() {
		App.vibrate();
		$('.location-gorillia').addClass('shake');
		setTimeout(function() {
			$('.location-gorillia').removeClass('shake');
		},800);

	};


	var account = AccountService;
	var restaurants = RestaurantsService;

	if (App.parallax.setupBackgroundImage) {
		App.parallax.setupBackgroundImage($('.home-top').get(0));
	}

	$scope.warningPlaceholder = false;

	$scope.isProcessing = false;

	$scope.isUser = account.user.has_auth;
	$scope.notUser = !account.user.has_auth;
	$scope.recommend = RecommendRestaurantService;

	$scope.location = LocationService;

	// @todo: this function prevents angular from rendering on cordova correctly until it gets a response back from google (about 9 seconds)
	if (!App.isCordova) {
		$scope.location.init();
	}

	document.title = 'Food Delivery | Crunchbutton';

	$scope.yourArea = $scope.location.position.pos().city() || 'your area';

	$scope.locationError = false;

	$scope.openCity = function( city ){
		$rootScope.navigation.link( '/' + city, 'push');
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

	// on connection error - reset
	$rootScope.$on( 'connectionError', function(e, data) {
		$scope.isProcessing = false;
		$scope.loadingLocation = $scope.loadingGetfood = false;
	} );

	$scope.$on( 'locationError', function(e, data) {
		$scope.loadingLocation = $scope.loadingGetfood = false;
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

		$rootScope.navigation.link('/location/unavailable', 'instant');
	});

	$rootScope.$on( 'NewLocationAdded', function(e, data) {
		$scope.recommend.greetings = false;
	});

	$scope.$on( 'locationNotServed', function(e, data) {

		$rootScope.navigation.link('/location/unavailable', 'instant');

		$scope.loadingLocation = true;
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
		$rootScope.navigation.link( '/' + restaurants.permalink, 'push' );

		if( AccountService.account && AccountService.account.user ){
			AccountService.account.user.enteredLocation = $scope.location.position.pos().entered();
		}
		$scope.location.form.address = '';
		$scope.warningPlaceholder = false;
		$scope.isProcessing = false;
		if (!$scope.$$phase){
			$scope.$apply();
		}
	};

	$scope.city = function( city ){
		$scope.focus( '.location-address' );
		$scope.location.form.address = city;
		$scope.letsEat();
	}

	// lets eat button
	$scope.letsEat = function() {

		$scope.blur( '.location-address' );

		$scope.location.form.address = $.trim( $scope.location.form.address );

		if ( $scope.location.form.address == '' ) {
			$scope.loadingLocation = $scope.loadingGetfood = true;
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
							$scope.loadingLocation = $scope.loadingGetfood = false;
							$scope.$broadcast( 'locationNotServed' );
						}
					}
				);
			},
			// Error, user doesn't shared his location
			function(){
				$scope.loadingLocation = $scope.loadingGetfood = false;
				$('.location-address').val('').attr('placeholder',$('<div>').html('&#10148; Please enter your address here').text());
				$scope.warningPlaceholder = true;
				// the user might be typing his login/pass - so blur it
				if( !App.dialog.isOpen() ){
					$scope.focus( '.location-address' );
				}
			} );

		} else {
			// Start the spinner
			$scope.loadingGetfood = true;
			// If the address searching is already in process ignores this request.
			if( $scope.isProcessing ){
				// To prevent any kind of problem, set this variable to false after 2 secs.
				setTimeout( function() { $scope.isProcessing = false; }, 2000 );
				return;
			}

			$scope.isProcessing = true;

			if( $scope.location.form.address ){
				var parts = $scope.location.form.address.toLowerCase().split( ',' );
				if( App && App.aliases ){
					for( x in parts ){
						if( parts[ x ] != '' ){
							if( App.aliases[parts[x]] ){
								$scope.location.form.address = parts[x];
								break;
							}
						}
					}
				}
			}

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
					$scope.loadingLocation = $scope.loadingGetfood = false;
					var oopsText = App.isCordova ? 'Oops! Please enter an address' : '&#9785; Oops! Please enter a street name, number, and city';
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
		var error = function(){
			$scope.$broadcast( 'locationNotServed' );
			$scope.loadingLocation = $scope.loadingGetfood = false;
		}
		$scope.loadingLocation = true;

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
	setTimeout( function(){ $scope.focus( '.location-address' ); }, 200 );
});

NGApp.controller('LocationUnavailableCtrl', function ($scope, $http, $location, $rootScope, RestaurantsService, LocationService, AccountService, PositionsService, RecommendRestaurantService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.notifyMe = function() {
		$scope.loadingNotifyme = true;
		RecommendRestaurantService.notify( function(){
			$scope.loadingNotifyme = false;
			$scope.complete = true;
		} );
	};
});

/**
 * restaurant page
 */

NGApp.controller( 'RestaurantCtrl', function ($scope, $http, $routeParams, $rootScope, $timeout, $window, RestaurantService, OrderService, CreditService, GiftCardService, PositionsService, MainNavigationService, CreditCardService) {

	var updateHoursOnFocus = $scope.$on( 'window-focus', function(e, data) {
		if( OrderService.restaurant && OrderService.restaurant.id_restaurant && OrderService.restaurant.id_restaurant == $scope.restaurant.id_restaurant ){
			$scope.restaurant.reloadHours( true, function( restaurant ){
				$scope.restaurant = restaurant;
				$scope.restaurant.open();
				$scope.open = $scope.restaurant._open;
				loadPreOrderInfo( restaurant );
				if (!$scope.$$phase){
					$scope.$apply();
				}
			} );
		}
	});

	var order = OrderService;
	order.geomatched = 1;

	$scope.show_suggestions = false;

	order.loaded = false;
	$scope.order = {};
	$scope.open = false;
	$scope.seeFullMenu = function() {
		$scope.fullmenu = 1;
	};
	$scope.seeSmallMenu = function() {
		$scope.fullmenu = 0;
	};

	$scope.isIOs = App.iOS();

	if ($rootScope.config['apple-pay'] == '1' && App.hasApplePay) {
		$scope.applePay = true;
	} else {
		$scope.applePay = false;
	}

	$scope.restaurantLoaded = RestaurantService.alreadyLoaded();

	$scope.restaurant = false;

	if( RestaurantService.basicInfo ){
		$timeout( function() {
			$scope.restaurant = RestaurantService.basicInfo;
			$scope.open = $scope.restaurant._open;
		}, 50 );
	}

	// we dont need to put all the Service methods and variables at the $scope - it is expensive
	order.startStoreEntederInfo = false;
	$scope.order.form = order.form;


	if(!$scope.order.cardMonth){
		$scope.order.cardMonth = '';
		$scope.order.cardYear = '';
	}
	$scope.order.info = order.info;

	$scope.Math = window.Math;

	$scope.nothingInTheCartDesktop = App.AB.pluck('nothingInTheCartDesktop', App.config.ab.nothingInTheCartDesktop).line;
	$scope.nothingInTheCartMobile = App.AB.pluck('nothingInTheCartMobile', App.config.ab.nothingInTheCartMobile).line;

	$scope.order.creditCardChanged = function() {
		order._cardInfoHasChanged = true;
	};

	$scope.$watch( 'isMobileWidth', function( newValue, oldValue, scope ) {
		if( !$scope.fullmenu && !$scope.isMobileWidth ){
			$scope.fullmenu = true;
		}
	});

	var creditCard = CreditCardService;

	// update if the restaurant is closed or open every 35 seconds
	var updateStatus = function(){
		if( OrderService.restaurant && OrderService.restaurant.id_restaurant && OrderService.restaurant.id_restaurant == $scope.restaurant.id_restaurant ){
			updateRestaurantStatus = $timeout( function(){
				$scope.restaurant.open();
				$scope.restaurant.reloadHours( true, function( restaurant ){
					$scope.restaurant = restaurant;
					var open = $scope.restaurant._open;
					if ($scope.open != open) {
						$scope.open = open;
					}
					loadPreOrderInfo( $scope.restaurant );
					if (!$scope.$$phase){
						$scope.$apply();
					}
				} );
				updateStatus();
			}, 1000 * 60 );
		}
	}
	//

	if (!App.minimalMode) {
		$scope.$on( '$destroy', function(){
			// Kills the timer when the controller is changed
			if( typeof( updateRestaurantStatus ) !== 'undefined' && updateRestaurantStatus ){
				try{ $timeout.cancel( updateRestaurantStatus ); } catch(e){}
			}
			if( typeof( forceReloadTimer ) !== 'undefined' && forceReloadTimer ){
				try{ $timeout.cancel( forceReloadTimer ); } catch(e){}
			}
			if( updateHoursOnFocus ){
				updateHoursOnFocus();
			}
		} );

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
	}

	// Set the id_restaurant
	order.cart.setRestaurant( $routeParams.id );

	MainNavigationService.order = $scope.order;

	$scope.getFoodButton = function(){
		MainNavigationService.getFood( order.cart.hasItems() );
	}

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
		order.toogleDelivery( type );
		$scope.order._tips = order._tips();
	}
	$scope.order.toogleDeliveryTime = function( type ){
		order.toogleDeliveryTime( type );
	}
	$scope.order.tooglePayment = function( type ){
		return order.tooglePayment( type );
	}
	$scope.order._years = order._years();
	$scope.order._months = order._months();
	$scope.order._tips = order._tips();

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
		order.geomatched = 0;
		// Give time to close the modal.
		setTimeout( function(){ order.submit( true, true );;	}, 300 );
	}
	$scope.addressOutOfRangePlaceAnyway = function(){
		$rootScope.closePopup();
		// Give time to close the modal.
		order.geomatched = 0;
		setTimeout( function(){ order.submit( true, true );;	}, 300 );
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
		if (App.busy.isBusy()) {
			App.busy.unBusy();
		}
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
		$rootScope.$safeApply();
	});

	// Alias to CartService 'public' methods
	$scope.order.cart = {};
	$scope.order.cart.add = function( item ){
		order.cart.add( item );
		$scope.getFoodButton();
		$rootScope.$broadcast( 'itemAdded', true );
		return;
	}
	$scope.order.cart.remove = function( item ){
		order.cart.remove( item );
		$scope.getFoodButton();
		return;
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
		$scope.order._tips = order._tips();
	}, true);

	// Alias to ServiceAccount.user
	$scope.user = order.account.user;

	$scope.AB = {
		dollar: (App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '',
		restaurantPage: (App.config.ab && App.config.ab.restaurantPage == 'restaurant-page-noimage') ? ' restaurant-pic-wrapper-hidden' : ''
	};


	var using_delivery_free = false;

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

		if( giftcard.notes_field.giftcards && giftcard.notes_field.giftcards.success && giftcard.notes_field.giftcards.success[0] && giftcard.notes_field.giftcards.success[0].delivery_free ){
			using_delivery_free = true;
			order.removeDeliveryFee();
			$scope.order.updateTotal();
		} else {
			using_delivery_free = false;
			order.restoreDeliveryFee();
			$scope.order.updateTotal();
		}
		$scope.giftcard.hasValue = ( parseFloat( giftcard.notes_field.value ) > 0 );
		$scope.$safeApply();
	});

	$scope.$watch( 'order.form.phone', function( newValue, oldValue, scope ) {
		if( !$scope.user.id_user ){
			giftcard.notes_field.lastValidation = '';
			$scope.checkGiftCard();
		}
	});

	$scope.checkGiftCard = function(){
		if( validateGiftCard ){
			giftcard.notes_field.content = $scope.order.form.notes;
			giftcard.notes_field.start( $scope.order.form.phone, $scope.restaurant.id_restaurant );
		}
	}

	var credit = CreditService;
	$scope.credit = { hasValue : false };

	// Event will be called when the credit changes
	$rootScope.$on( 'creditChanged', function(e, data) {
		$scope.$safeApply( function($scope) {
			$scope.credit.value = credit.value;
			$scope.credit.number = parseFloat( credit.value );
			$scope.credit.redeemed = credit.redeemed;
			$scope.order.updateTotal();
			if( parseFloat( $scope.credit.value ) > 0 ){
				$scope.credit.hasValue = true;
			} else {
				$scope.credit.hasValue = false;
			}
		} );
	});

	// Credit card info was changed
	$scope.$on( 'creditCardInfoChanged', function(e, data) {
		$scope.order.creditCardChanged();
	} );

	var validateGiftCard = false;

	var restaurantService = RestaurantService;


	$scope.suggestion = function(){
		$rootScope.$broadcast( 'restaurantSuggestion', $scope.restaurant );
	}

	$scope.deliveryDayChanged = function(){
		$scope.order._preOrderHours = [];
		var _tempDeliveryHours = $scope.order.form.deliveryHour;
		$scope.order.form.deliveryHour = null;
		for( x in $scope.order._preOrderDays ){
			var day = $scope.order._preOrderDays[ x ];
			if( $scope.order.form.deliveryDay == day.value ){
				$scope.order._preOrderHours = day[ 'hours' ];
				if( $scope.order._preOrderHours.length ){
					for( y in $scope.order._preOrderHours ){
						if( $scope.order._preOrderHours[ y ].value == _tempDeliveryHours ){
							$scope.order.form.deliveryHour = _tempDeliveryHours;
						}
					}
					if( !$scope.order.form.deliveryHour ){
						$scope.order.form.deliveryHour = $scope.order._preOrderHours[ 0 ].value;
					}
				}
			}
		}
	}

	var loadPreOrderInfo = function( restaurant ){
		if( restaurant.allow_preorder && restaurant._preOrderDays.length ){
			$scope.order._preOrderDays = restaurant._preOrderDays;
			$scope.order.form.deliveryDay = $scope.order._preOrderDays[ 0 ].value;
			$scope.deliveryDayChanged();
		}
	}


	// Event will be called after the restaurant load
	$scope.$on( 'restaurantLoaded', function(e, data) {

		$rootScope.$broadcast( 'updateQuoteRestaurant', data.restaurant.id_restaurant );

		$scope.restaurantLoaded = RestaurantService.alreadyLoaded();

		var community = data.community;

		$scope.restaurant = data.restaurant;

		loadPreOrderInfo( data.restaurant );

		order.restaurant = $scope.restaurant;

		MainNavigationService.restaurant = $scope.restaurant;

		$scope.open = $scope.restaurant.open();
		$scope.force_pre_order = $scope.restaurant.force_pre_order;

		if( !$scope.open && $scope.restaurant.force_pre_order ){
			$scope.open = true;
		}

		document.title = $scope.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton';

		$scope.show_suggestions = true;

		var process = function(){

			// Point redemption system improvements for customer-customer referrals #4248
			$scope.points = order.account.user.points;

			order.init();

			// Update some gift cards variables
			giftcard.notes_field.id_restaurant = $scope.restaurant.id_restaurant;
			giftcard.notes_field.restaurant_accepts = ( $scope.restaurant.giftcard > 0 );

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

			$scope.getFoodButton();

		}

		setTimeout( function(){
			// add that its been loaded so we can show the overpull peeking guy
			$('body').addClass('page-restaurant-loaded');

			// Call anyway
			process();

			// see #2799
			$scope.$on( 'UserRestaurantInfoLoaded', function( e, data ) {
				UserRestaurantInfoLoaded = true;
				validateGiftCard = true;
				// process credit
				credit.processCredit( parseFloat( data.credit ) );
				// process gift card
				giftcard.notes_field.processJson( data.giftcard );
				// process the preset stuff
				var id_preset = data.id_preset;
				// Method that checks if the preset must to be reloaded #1988
				OrderService.account.checkPresetUpdate( id_preset, $scope.restaurant.id_restaurant,
					// will be called if the preset was reloaded
					function(){
						// reset cart
						order.resetCart();
						// Process again
						process();
					}
				);
			} );

			var UserRestaurantInfoLoaded = false;

			// see #2799
			order.loadUserRestaurantInfo( function( json ){ $rootScope.$broadcast( 'UserRestaurantInfoLoaded', json ); } );

		} );

	});

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	if( RestaurantService.basicInfo ){
		// If we have the basic info of the restaurant we just wait till the transition animation ends before load the restaurant data
		$scope.$on( '$routeChangeSuccess', function ( event ) {
			setTimeout( function(){ restaurantService.init(); updateStatus(); }, 200 );
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
NGApp.controller('OrderCtrl', function ($interval, $rootScope, $scope, $http, $location, $routeParams, $filter, AccountService, AccountModalService, OrderViewService, ReferralService, FacebookService, TwitterService ) {

	// Force unbusy
	//App.busy.unBusy();

	$scope.account = { user : AccountService.user, has_auth : AccountService.user.has_auth };
	$scope.modal = { signupOpen : AccountModalService.signupOpen };
	$scope.order = {};
	$scope.restaurant = {};
	//$scope.width = $(window).width();
	$scope.confirm = $routeParams.action == 'confirm' ? true : false;

	$scope.receipt = function () {
		$rootScope.navigation.link('/order/' + $scope.order.uuid, 'push');
	};

	AccountService.updatePoints( function( points ){
		$scope.account.user.points = points;
		$scope.account.user.invite_code = points.invite_code;
	} );

	$scope.Math = window.Math;

	OrderViewService.load();

	$scope.isMobile = App.isMobile();
	$scope.isAndroid = App.isAndroid();

	$scope.facebook = function(){
		OrderViewService.facebook.postOrder();
	}

	$scope.referral = {
		invite_url : ReferralService.invite_url,
		value : ReferralService.value,
		limit : ReferralService.limit,
		invites : ReferralService.invites,
		enabled : ReferralService.enabled,
		invite_code: ReferralService.invite_code,
		sms: ReferralService.sms()
	}

	$scope.referral.facebook = function(){
		FacebookService.shareOrder( $scope.referral.invite_url, AccountService.user.invite_code );
	}

	$scope.referral.twitter = function(){
		var text = App.AB.get('share-order-text-twitter').replace('%c', AccountService.user.invite_code).replace('%r', $scope.order._restaurant_name);
		window.open('https://twitter.com/intent/tweet?url=' + $scope.referral.invite_url + '&text=' + text ,'_system');
	}

	$scope.$watch( 'referral.invite_url', function( newValue, oldValue, scope ) {
		$scope.twitterUrl = ReferralService.invite_url;
		$scope.twitterText = TwitterService.referralText( AccountService.user.invite_code );
		$scope.twitterHashtags = TwitterService.referralHashtags();
	});


	$scope.twitterTweet = function( ev ){
		TwitterService.tweet( $scope.order.uuid );
		if( !$scope.order.reward.shared ){
			$scope.order.reward = { shared: { twitter: true } };
		} else {
			$scope.order.reward.shared.twitter = true;
		}
		$scope.$safeApply();
	}

	$scope.$on( 'orderSharedFacebook', function(e, data) {
		$scope.order.reward.shared.facebook = true;
		$scope.$safeApply();
	} );


	$scope.$on( 'referralStatusLoaded', function(e, data) {
		$scope.referral.invite_url = ReferralService.invite_url;
		$scope.referral.invite_url_cleared = ReferralService.value;
		$scope.referral.value = ReferralService.value;
		$scope.referral.enabled = ReferralService.enabled;
		$scope.referral.invite_code = ReferralService.invite_code;
		$scope.referral.sms = ReferralService.sms();
	});

	$scope.$on( 'OrderViewLoadedOrder', function(e, order) {
		$scope.$safeApply( function(){
			$scope.order = order;
		} );
	});

	$scope.referral.cleaned_url = function(){
		return ReferralService.cleaned_url();
	}

	$scope.print = function() {
		window.open('http://google.com', '_blank');
		$('.order-print').get(0).contentWindow.document.body.innerHTML = $('.order-print-content').html();
		$('.order-print').get(0).contentWindow.print();
	};
	if ( App.busy.isBusy() ) {
		setTimeout( function(){
			App.busy.unBusy();
		}, 500 );
	}

});

NGApp.controller('ProfileCtrl', function ($scope, $filter, AccountService ) {
	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}

	$scope.account = AccountService.user;

	$scope.account.phone = $filter( 'formatPhone' )( $scope.account.phone );

	$scope.updatingProfile = false;

	$scope.save = function(){

		if( $scope.updatingProfile ){
			return;
		}

		$scope.updatingProfile = true;

		var errors = {};

		if (!$scope.account.name) {
			errors['name'] = 'Please enter your name.';
		}

		if (!App.phone.validate($scope.account.phone)) {
			errors['phone'] = 'Please enter a valid phone #.';
		}

		if ( $scope.account.email && !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( $scope.account.email ) ){
			errors['email'] = 'Please enter a valid email.';
		}
		if ( !$scope.account.address ) {
			errors['address'] = 'Please enter an address.';
		}
		var er = displayErrors(errors);
		if (er) {
			$scope.updatingProfile = false;
			return;
		}

		AccountService.update( $scope.account, function( json ){
			$scope.updatingProfile = false;
			if( json.error ){
				displayErrors( json.error );
			} else {
				App.alert('', 'Profile info saved!', null, null, true );
				updateInfo();
			}
		} )

	}

	$scope.removePaymentMethod = function(){
		var remove = function(){
			$scope.removingPaymentMethod = true;
			AccountService.removePaymentMethod( function( json ){
				if( json.error ){
					displayErrors( json.error );
				} else {
					App.alert('', 'Credit card removed!', null, null, true );
					updateInfo();
				}
				$scope.removingPaymentMethod = false;
			} );
		}

		if (App.useNativeConfirm && App.isCordova) {
			App.confirm( 'Confirm remove credit card?',' Confirm?', remove );
		} else {
			if( App.confirm( 'Confirm remove credit card?' ) ){
				remove();
			}
		}
	}

	var displayErrors = function(errors) {
		if (!$.isEmptyObject(errors)) {
				var error = '';
				for (var x in errors) {
					error += '<li><i class="icon-li icon-warning-sign"></i>' + errors[x] + '</li>';
				}
				App.alert('<ul class="icons-ul">' + error + '</ul>');
			return true;
		}
		return false;
	}

	var updateInfo = function(){
		$scope.account = AccountService.user;
	}

	updateInfo();

});


/**
 * Orders page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */

NGApp.controller('OrdersCtrl', function ($timeout, $scope, $http, $location, AccountService, AccountSignOut, OrdersService, MainNavigationService, AccountModalService, ReferralService, FacebookService, CreditService ) {

	if( !AccountService.isLogged() ){
		$location.path( '/' );
		return;
	}

	if( !AccountService.user.invite_code ){
		ReferralService.getInviteCode();
	}

	$scope.account = AccountService;

	// Alias to method AccountSignOut.do()
	$scope.signout = AccountSignOut.signout;
	$scope.facebook = AccountModalService.facebookOpen;
	$scope.modal = AccountModalService;
	$scope.orders = [];

	// Alias to OrdersService methods
	$scope.orders.restaurant = OrdersService.restaurant;
	$scope.orders.receipt =  OrdersService.receipt;

	if( OrdersService.reload ){
		OrdersService.load();
	} else {
		$timeout(function() {
			$scope.orders.list = OrdersService.list;
			// Check if the orders list need to be updated #1988
			OrdersService.checkUpdate();
		},10);
	}

	$scope.$on( 'OrdersLoaded', function(e, data) {
		$scope.orders.list = OrdersService.list;
	});

	$scope.referral = {
		invite_url : ReferralService.invite_url,
		value : ReferralService.value,
		limit : ReferralService.limit,
		invites : ReferralService.invites,
		enabled : ReferralService.enabled,
		invite_code: ReferralService.invite_code,
		sms: ReferralService.sms()
	}

	$scope.referral.cleaned_url = function(){
		return ReferralService.cleaned_url();
	}

	$scope.$on( 'referralStatusLoaded', function(e, data) {
		$scope.referral.invites = ReferralService.invites;
		$scope.referral.limit = ReferralService.limit;
		$scope.referral.invite_url = ReferralService.invite_url;
		$scope.referral.value = ReferralService.value;
		$scope.referral.enabled = ReferralService.enabled;
		$scope.referral.invite_code = ReferralService.invite_code;
		$scope.referral.sms = ReferralService.sms();
	});

	$scope.isMobile = App.isMobile();

	$scope.referral.facebook = function(){
		FacebookService.postInvite( $scope.referral.invite_url, AccountService.user.invite_code );
	}

	$scope.referral.twitter = function(){
		var text = App.AB.get('share-text-twitter').replace('%c', AccountService.user.invite_code);
		window.open('https://twitter.com/intent/tweet?url=' + $scope.referral.invite_url + '&text=' + text ,'_system');
	}

	CreditService.getCredit( '' );
	$scope.$on( 'creditChanged', function(e, data) {
		$scope.credit = data.value;
	});

});

NGApp.controller( 'GiftcardCtrl', function ($scope, $location, GiftCardService ) {
	setTimeout( function(){ GiftCardService.open(); }, 300 );
});

NGApp.controller( 'AccountModalHeaderCtrl', function ( $scope, $rootScope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;
	$scope.resetModal = function(){
		$scope.modal.toggleSignForm( 'signin' );
		$scope.help.show( false );
	}
	if( AccountService && AccountService.user && AccountService.user.email ){
		$scope.account.form.email = AccountService.user.email;
	}
	$scope.$on( 'userUpdated', function(e, user) {
		if( !$scope.account.form.email ){
			$scope.account.form.email = user.email;
		}
	} );
});

NGApp.controller( 'AccountSevenCtrl', function ( $scope, $http, AccountModalService, AccountService, AccountHelpService, AccountFacebookService ) {
	$scope.tab = 'facebook'
	$scope.account = AccountService;
	if( AccountService && AccountService.user && AccountService.user.email ){
		$scope.account.form.email = AccountService.user.email;
	}
	$scope.$on( 'userUpdated', function(e, user) {
		if( !$scope.account.form.email ){
			$scope.account.form.email = user.email;
		}
	} );
	$scope.help = AccountHelpService;
	$scope.facebook = AccountFacebookService;
	$scope.changeTab = function( tab ){
		$scope.account.errorReset();
		$scope.help.reset();
		$scope.tab = tab;
	}
	$scope.resetModal = function(){
		$scope.changeTab( 'facebook' );
	}
	$scope.$on( 'facebookIdAlreadyUsed', function(e, data) {
		$scope.facebook.facebook.wait = false;
		$scope.account.errors.push( 'It seems this facebook user is already associated with another user.' );
	} );
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

NGApp.controller( 'RewardCtrl', function ( $scope, $http, $rootScope, ReferralService, AccountService, FacebookService ) {

	$rootScope.$on( 'ReferralInvitedUsers', function(e, data) {
		$rootScope.$apply(function(scope) {
			scope.invitedUsers = ReferralService.invitedUsers;
			App.dialog.show( '.referral-container' );

		});
	});

	$scope.referral = {}

	if( !AccountService.user.invite_code ){
		ReferralService.getInviteCode();
	}

	$rootScope.$on( 'referralStatusLoaded', function(e, data) {

		setTimeout( function(){
			$rootScope.$apply(function(scope) {
				$scope.referral = {};
				$scope.referral.invites = ReferralService.invites;
				$scope.referral.limit = ReferralService.limit;
				$scope.referral.invite_url = ReferralService.invite_url;
				$scope.referral.value = ReferralService.value;
				$scope.referral.enabled = ReferralService.enabled;
				$scope.referral.invite_code = ReferralService.invite_code;
				$scope.referral.sms = ReferralService.sms();
				$scope.referral.url = ReferralService.cleaned_url();
			});
		}, 1000 );
	});

	$scope.referral.facebook = function(){
		FacebookService.postInvite( $scope.referral.url, $scope.referral.invite_code );
	}

	$scope.referral.twitter = function(){
		window.open('https://twitter.com/intent/tweet?url=' + $scope.referral.url + '&text=' + App.AB.get('share-twitter-text') + '&hashtags=Crunchbutton' ,'_system');
	}

	$scope.modal = { close: function(){
		$.magnificPopup.close();
	} };
});

NGApp.controller( 'GiftCardCtrl', function ( $scope, $http, $rootScope, GiftCardService ) {
	$scope.giftcard = {};
	$scope.user = GiftCardService.account.user;
	$scope.modal = GiftCardService.modal;
	$scope.giftcard.value = GiftCardService.value;
	$scope.giftcard.message = GiftCardService.message;
	$rootScope.$on( 'GiftCardProcessed', function(e, data) {
		// Update the scope
		$scope.user = GiftCardService.account.user;
		$scope.giftcard.value = GiftCardService.value;
		$scope.giftcard.message = GiftCardService.message;
		$scope.modal = GiftCardService.modal;
	});
});

NGApp.controller( 'MainHeaderCtrl', function ( $scope, $rootScope, $timeout, MainNavigationService, OrderService ) {
	$scope.navigation = MainNavigationService;
	$scope.order = OrderService;
	$scope.cart = { blink : false };

	$scope.$watch('navigation.page', function( newValue, oldValue, scope ) {
		$scope.navigation.control();
		$scope.navigation.getFood( false );
	});

	$scope.$on( 'itemAdded', function(e, data) {
		$scope.cart.blink = true;
		$timeout( function(){
			$scope.cart.blink = false;
		}, 1000 );
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
			$scope.active = ( r.inactive ? false : true );
			$scope.acceptingOrders = r.open_for_business;
			App.dialog.show('.restaurant-closed-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.restaurant = r;
				scope.closedMessage = r.closedMessage();
				$scope.active = ( r.inactive ? false : true );
				$scope.acceptingOrders = r.open_for_business;
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

NGApp.controller( 'NotificationAlertCtrl', function ($scope, $rootScope ) {
	$rootScope.$on('notificationAlert', function(e, title, message, fn, good_news) {
		var complete = function() {
			$rootScope.closePopup();

			if (typeof fn === 'function') {
				fn();
			}
		};

		$rootScope.$safeApply(function(scope) {
			scope.title = title;
			scope.message = message;
			scope.complete = complete;
			if( good_news ){
				scope.icon = 'happy';
			} else {
				scope.icon = 'sad';
			}

			App.dialog.show('.notification-alert-container');
		});
	});
});
NGApp.controller( 'NotificationRemoteCtrl', function ($scope, $rootScope ) {
	$rootScope.$on('notificationRemote', function(e, title, message, fn) {
		var complete = function() {
			$rootScope.closePopup();

			if (typeof fn === 'function') {
				fn();
			}
		};
		var cancel = function() {
			$rootScope.closePopup();
		};

		$rootScope.$safeApply(function(scope) {
			scope.title = title;
			scope.message = message;
			scope.complete = complete;
			scope.cancel = cancel;
			scope.hasFn = typeof fn === 'function';
			App.dialog.show('.notification-remote-container');
		});
	});
});

NGApp.controller( 'DeliverySignUpCtrl', function( $scope, $location, DeliverySignUpService ) {

	$scope.ready = false;
	$scope.submitted = false;

	$scope.delivery = {};

	$scope.sending = false;

	DeliverySignUpService.restaurants( function( data ){
		$scope.restaurants = data;
	} );

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.sending = true;

		$scope.delivery.restaurants = [];

		angular.forEach( $scope.restaurants, function(value, key) {
			if( value.checked ){
				$scope.delivery.restaurants.push( value.name );
			}
		} );

		if( $scope.delivery.otherRestaurant ){
			$scope.delivery.restaurants.push( $scope.delivery.otherRestaurant );
		}

		DeliverySignUpService.save( $scope.delivery, function( json ){
			if( json.success ){
				$scope.sent = true;
			} else {
				App.alert( json.error );
			}
			$scope.sending = false;
		} );
	}
} );

NGApp.controller( 'InviteCtrl', function ( $scope, $routeParams, $location, ReferralService ) {
	// Just store the cookie, it will be used later
	$.totalStorage( 'referral', $routeParams.id );
	$location.path( '/' );
});

NGApp.controller( 'NoInternetCtrl', function ( $scope ) {
	// Just store the cookie, it will be used later
	$.totalStorage( 'referral', $routeParams.id );
	$location.path( '/' );
});

NGApp.controller( 'QuoteCtrl', function ( $scope, OrderService ) {

	var quotes = { pages: [], communities: { all: [] }, restaurants: { all: [] } };
	var processed = false;

	var defaultQuote = function(){
		$scope.quote = {
				name: 'Devin Smith',
				title: 'CTO & co-founder',
				image: 'https://graph.facebook.com/659828729/picture?width=120&height=120',
				quote: 'Here is something funny written by someone else.',
			}
	}

	var pickAQuote = function( where, id ){
		var avaiable = [];
		switch( where ){
			case 'pages':
				avaiable = quotes.pages;
				break;
			case 'restaurant':
				if( id ){
					avaiable = ( quotes.restaurants[ id ] ) ? quotes.restaurants[ id ] : quotes.restaurants[ 'all' ];
				} else {
					avaiable = quotes.restaurants[ 'all' ];
				}
				break;
			default:
				if( where ){
					avaiable = ( quotes.communities[ where ] ) ? quotes.communities[ where ] : quotes.communities[ 'all' ];
				}
		}
		if( avaiable && avaiable.length ){
			var sorted = Math.floor( Math.random() * avaiable.length );
			$scope.quote = avaiable[ sorted ];
		} else {
			defaultQuote();
		}
	}

	var writeQuote = function( action ){
		if( processed ){
			switch( action ){
				case 'restaurants':
				case 'restaurant':
					pickAQuote( 'restaurant' );
					return;
					break;
				default:
					pickAQuote( 'pages' );
					break;
			}
		} else {
			quoteProcess();
			defaultQuote();
		}
	}

	var quoteProcess = function(){
		if(App.quotes && App.quotes.length ){
			for( x in App.quotes ){
				var quote = App.quotes[ x ];
				if( quote.pages ){
					quotes.pages.push( quote );
				}
				if( quote.all ){
					quotes.communities.all.push( quote );
				}
				if( quote.all_restaurants ){
					quotes.restaurants.all.push( quote );
				}
				if( quote.communities && quote.all_restaurants ){
					for( y in quote.communities ){
						var community = quote.communities[ y ];
						if( !quotes.communities[ community ] ){
							quotes.communities[ community ] = [];
						}
						quotes.communities[ community ].push( quote );
					}
				}
				if( quote.restaurants ){
					for( y in quote.restaurants ){
						var restaurant = quote.restaurants[ y ];
						if( !quotes.restaurants[ restaurant ] ){
							quotes.restaurants[ restaurant ] = [];
						}
						quotes.restaurants[ restaurant ].push( quote );
					}
				}
			}
			processed = true;
		}
	}

	$scope.$on( 'updateQuote', function(e, id_community) {
		pickAQuote( id_community );
	});

	$scope.$on( 'updateQuoteRestaurant', function(e, id_restaurant) {
		pickAQuote( 'restaurant', id_restaurant );
	});

	$scope.$on( '$routeChangeSuccess', function ( event, next, current ) {
		if( next.action ){
			writeQuote( next.action );
		} else {
			writeQuote();
		}
	} );

	if( !processed ){
		quoteProcess();
	}

	defaultQuote();

});

NGApp.controller( 'RestaurantSuggestionCtrl', function ( $scope, $rootScope, SuggestionService ) {

	var restaurant = null;

	$rootScope.$on( 'restaurantSuggestion', function(e, data) {
		reset();
		restaurant = data;
		App.dialog.show( '.restaurant-suggestion-container' );
	});

	var reset = function(){
		$scope.change_tab( 'form' );
		$scope.suggestion = { content: '', add_more_content: '',  }
		$scope.add_more = false;
	}

	$scope.send_form = function(){
		var data = {};
		data.id_restaurant = restaurant.id_restaurant;
		data.type = 'suggestion';

		if( $scope.suggestion.content ){
			data.content = $scope.suggestion.content;
		}

		if( $scope.suggestion.add_more_content ){
			data.type = 'dish';
			if( data.content ){
				data.content += "\n";
			}
			data.content += $scope.suggestion.add_more_content;
		}

		SuggestionService.save( data, function( json ){
			$scope.change_tab( 'thank-you' );
		} );
	}

	$scope.change_tab = function( tab ){
		$scope.tab = tab;
	}

	$scope.close = function(){
		$rootScope.closePopup();
	}

});

NGApp.controller( 'RestaurantsSuggestionCtrl', function ( $scope, $rootScope, SuggestionService ) {

	var id_community = null;

	$rootScope.$on( 'restaurantsSuggestion', function(e, data) {
		reset();
		id_community = data;
		App.dialog.show( '.restaurants-suggestion-container' );
	});

	var reset = function(){
		$scope.change_tab( 'form' );
		$scope.suggestion = { content: '', add_more_content: '',  }
		$scope.add_more = false;
	}

	$scope.send_form = function(){
		var data = {};
		data.id_community = id_community;
		data.type = 'restaurant';
		if( $scope.suggestion.add_more_content ){
			data.content = 'Add: ' + $scope.suggestion.add_more_content;
		} else {
			data.content = 'Add more restaurants!';
		}

		SuggestionService.save( data, function( json ){
			$scope.change_tab( 'thank-you' );
		} );
	}

	$scope.change_tab = function( tab ){
		$scope.tab = tab;
	}

	$scope.close = function(){
		$rootScope.closePopup();
	}

});
