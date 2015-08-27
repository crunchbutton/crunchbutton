NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		/* Restaurants Order Placement */
		.when('/restaurant/order/placement/dashboard/:id?', {
			action: 'restaurant-order-placement',
			controller: 'RestaurantOrderPlacementDashboard',
			templateUrl: 'assets/view/restaurant-order-placement-dashboard.html'
		})
		.when('/restaurant/order/placement/new/:id?', {
			action: 'restaurant-order-placement',
			controller: 'RestaurantOrderPlacementNew',
			templateUrl: 'assets/view/restaurant-order-placement-new.html'
		})
		.when('/restaurant/order/placement/list/:id?', {
			action: 'restaurant-order-placement',
			controller: 'RestaurantOrderPlacementList',
			templateUrl: 'assets/view/restaurant-order-placement-list.html'
		})
		.when('/restaurant/order/placement/:id', {
			action: 'restaurant-order-placement',
			controller: 'RestaurantOrderPlacementView',
			templateUrl: 'assets/view/restaurant-order-placement-view.html'
		})

		/* Restaurants */
		.when('/restaurants', {
			action: 'restaurants',
			controller: 'RestaurantsCtrl',
			templateUrl: 'assets/view/restaurants.html',
			reloadOnSearch: false
		})
		.when('/restaurants/weight-adjustment', {
			action: 'restaurant',
			controller: 'RestaurantWeightAdjustmentCtrl',
			templateUrl: 'assets/view/restaurants-weight-adjustment.html'
		})
		.when('/restaurant/:id', {
			action: 'restaurant',
			controller: 'RestaurantCtrl',
			templateUrl: 'assets/view/restaurants-restaurant.html'
		})
		.when('/restaurant/order/new', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderNew',
			templateUrl: 'assets/view/restaurant-order-new.html'
		})
		.when('/restaurant/order/list', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderList',
			templateUrl: 'assets/view/restaurant-order-list.html'
		})
		.when('/restaurant/order/:id', {
			action: 'restaurant-order-new',
			controller: 'RestaurantOrderView',
			templateUrl: 'assets/view/restaurant-order-view.html'
		})
		.when('/restaurant/payment-info/:id', {
			action: 'restaurant-payment-info',
			controller: 'RestaurantPaymentInfoCtrl',
			templateUrl: 'assets/view/restaurant-payment-info.html'
		});
}]);

NGApp.controller( 'RestaurantWeightAdjustmentCtrl', function ($rootScope, $scope, $routeParams, RestaurantService, CommunityService, PositionService ) {

	$scope.address = '';

	$scope.position = {};

	$scope.search = function(){

		if( $scope.address ){
			$scope.loading = true;
			$scope.restaurants = null;

			CommunityService.by_alias( $scope.address, function( community ){
				if( community.id_community ){
					$scope.position.lat = community.loc_lat;
					$scope.position.lon = community.loc_lon;
					load();
				} else {
					// search by address
					PositionService.find( $scope.address,
					function( address ){
						var pos = PositionService.getPosition( address );
						if( pos ){
							$scope.position.lat = pos.lat;
							$scope.position.lon = pos.lon;
							load();
						} else {
							App.alert( 'Address not found!' );
							$scope.loading = false;
						}
					} );
				}
			} );

		} else {
			App.alert( 'Type an address or an alias!' );
		}
	}

	$scope.save_weight = function( restaurant ){
		if( angular.isNumber( restaurant.weight_adj ) ){
			RestaurantService.save_weight( restaurant, function( json ){
				if( json.error ){
					App.alert( 'Error saving weight: ' + json.error );
				} else {
					load();
				}
			} );
		} else {
			App.alert( 'Please type a valid number!' );
		}
	}

	var load = function(){
		$scope.loading = true;
		RestaurantService.weight_adjustment( $scope.position, function( data ){
			$scope.restaurants = data;
			if( !$scope.restaurants.length ){
				$scope.restaurants = null;
				App.alert( 'There is restaurant at this address!' );
			}
			$scope.loading = false;
		} );
	}

} );

NGApp.controller('RestaurantPaymentInfoCtrl', function ($rootScope, $scope, $routeParams, RestaurantService ) {

	$scope.yesNo = RestaurantService.yesNo();
	$scope.summaryMethod = RestaurantService.summaryMethod();
	$scope.paymentMethod = RestaurantService.paymentMethod();
	$scope.accountType = RestaurantService.accountType();

	var load = function(){

		RestaurantService.get( $routeParams.id, function(d) {

			$rootScope.title = d.name + ' | Payment type';
			$scope.restaurant = d;
			$scope.ready = true;

			$scope.restaurant.stripeAccount = {};

			RestaurantService.payment_method( $routeParams.id, function( d ){

				if( !d.id_restaurant ){
					App.alert( 'Error loading payment method: ' + json.error );
				} else {
					$scope.restaurant.payment_type = d;

					$scope.restaurant.stripeAccount.formStripe = true;
					$scope.restaurant.stripeAccount.formStripeMigrate = false;

					if( $scope.restaurant.payment_type.stripe_id && $scope.restaurant.payment_type.stripe_id ){
						$scope.restaurant.stripeAccount.formStripe = false;
					}

					if( $scope.restaurant.payment_type.stripe_id && $scope.restaurant.payment_type.balanced_bank ){
						$scope.restaurant.stripeAccount.formStripeMigrate = true;
						$scope.restaurant.stripeAccount.formStripe = false;
					}

					$scope.restaurant.payment_type.has_balanced_bank = ( $scope.restaurant.payment_type.balanced_bank && !$scope.restaurant.payment_type.stripe_id );

					// prepopulate from the data we already have
					if ($scope.restaurant.stripeAccount.formStripe) {
						$scope.restaurant.payment_type.summary_email = $scope.restaurant.payment_type.summary_email || $scope.restaurant.email;
						$scope.restaurant.payment_type.legal_name_payment = $scope.restaurant.payment_type.legal_name_payment || $scope.restaurant.name;
						//$scope.restaurant.stripeAccount.account_type = 'corporate';

						if( !$scope.restaurant.payment_type.check_address ||
								!$scope.restaurant.payment_type.check_address_city ||
								!$scope.restaurant.payment_type.check_address_state ||
								!$scope.restaurant.payment_type.check_address_zip ||
								!$scope.restaurant.payment_type.check_address_country ){

							var geocoder = new google.maps.Geocoder();
							geocoder.geocode({ address:$scope.restaurant.address }, function(results, status) {

								if (status == google.maps.GeocoderStatus.OK) {
									var parts = [];
									for (var x in results[0].address_components) {
										parts[results[0].address_components[x].types[0]] = results[0].address_components[x].short_name;
									}

									$scope.$apply(function() {
										$scope.restaurant.payment_type.check_address = parts.street_number + ' ' + parts.route;
										$scope.restaurant.payment_type.check_address_city = parts.locality;
										$scope.restaurant.payment_type.check_address_state = parts.administrative_area_level_1;
										$scope.restaurant.payment_type.check_address_zip = parts.postal_code;
										$scope.restaurant.payment_type.check_address_country = parts.country;
									});
								}
							});
						}
					}

				}
			});
		} );
	}

	$scope.migrateFromBalancedToStripe = function(){

		if( $scope.restaurant.payment_type.stripe_id && $scope.restaurant.payment_type.balanced_bank ){

			$scope.isMigrating = true;

			RestaurantService.balanced_to_sprite( $scope.restaurant.id_restaurant, function( d ){
				App.alert( 'Stripe info saved' );
				load();
			} );

		} else {
			App.alert( 'Error: the restaurant must to have a balanced account!' );
		}
	}

	$scope.testAccount = function(){

		$scope.restaurant.stripeAccount.routing_number = '111000025';
		$scope.restaurant.stripeAccount.account_number = '000123456789';
		$scope.restaurant.stripeAccount.account_type = 'individual';
		$scope.restaurant.payment_type.tax_id = '000000000';
		$scope.restaurant.payment_type.check_address = '4690 Eldarado Parkway';
		$scope.restaurant.payment_type.check_address_city = 'McKinney';
		$scope.restaurant.payment_type.check_address_state = 'TX';
		$scope.restaurant.payment_type.check_address_zip = '75070';
		$scope.restaurant.payment_type.check_address_country = 'US';
	}

	$scope.saveStripeAccount = function(){

		if( $scope.formStripeAccount.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.stripeAccountSubmitted = true;
			return;
		}

		$scope.isSavingStripeAccount = true;

		RestaurantService.payment_method_save( $scope.restaurant.payment_type, function( d ){

			var saveInfo = function(token) {
				var params = {
					'id_restaurant': $scope.restaurant.id_restaurant,
					'name': $scope.restaurant.payment_type.legal_name_payment,
					'tax_id': $scope.restaurant.payment_type.tax_id,
					'account_type': $scope.restaurant.stripeAccount.account_type,
					'email': $scope.restaurant.payment_type.summary_email
				};

				if (token) {
					params.token = token;
				}

				RestaurantService.stripe( params, function( d ){
					if( d.id_restaurant ){
						App.alert( 'Stripe info saved' );
						$scope.restaurant.stripeAccount.formStripe = false;
						$scope.restaurant.stripeAccount.routing_number = '';
						$scope.restaurant.stripeAccount.account_number = '';
					} else {
						App.alert( 'Error updating Stripe info' );
					}
					$scope.isSavingStripeAccount = false;
				} );
			};

			if ($scope.restaurant.stripeAccount.routing_number && $scope.restaurant.stripeAccount.account_number) {

				Stripe.bankAccount.createToken( {
					country: 'US',
					currency: 'USD',
					routing_number: $scope.restaurant.stripeAccount.routing_number,
					account_number: $scope.restaurant.stripeAccount.account_number
				}, function( header, response ){

					if( response.id ){
						saveInfo(response.id);
					} else {
						App.alert( 'Error creating a Stripe token' );
						$scope.isSavingStripeAccount = false;
					}
				} );
			} else {
				saveInfo(null);
			}


		} );
	}

	$scope.save = function(){

		if( $scope.formBasic.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		if( $scope.restaurant.id_restaurant ){
			RestaurantService.payment_method_save( $scope.restaurant.payment_type, function( d ){
				$scope.isSaving = false;
				App.alert( 'Payment info saved' );
				load();
			} );
		}

	}

	load();

} );

NGApp.controller('RestaurantsCtrl', function ($rootScope, $scope, RestaurantService, ViewListService, CommunityService) {
	$rootScope.title = 'Restaurants';

	$scope.show_more_options = false;

	$scope.moreOptions = function(){

		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options ){
			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}

		if( $scope.show_more_options ){
			if( !$scope.payments ){
				$scope.payments = [];
				$scope.payments.push( { 'label': 'Check', 'value': 'check' } );
				$scope.payments.push( { 'label': 'Deposit', 'value': 'deposit' } );
				$scope.payments.push( { 'label': 'Does Not Need Payment', 'value': 'no payment' } );
				$scope.payments.push( { 'label': 'Empty', 'value': 'empty' } );
			}
		}

	}

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			community: '',
			payment_method: '',
			fullcount: false
		},
		update: function() {
			RestaurantService.list( $scope.query, function(d) {
				$scope.restaurants = d.results;
				$scope.complete(d);

					if( ( $scope.query.community || $scope.query.payment_method ) && !$scope.show_more_options ){
						$scope.moreOptions();
					}

			});
		}
	});
});

NGApp.controller('RestaurantNotesToDriverCtrl', function ($scope, $rootScope, RestaurantService ) {

	var id_restaurant = null;
	var callback = null;

	$rootScope.$on( 'openEditNotesToDriver', function(e, data) {
		id_restaurant = data.id_restaurant;
		callback = data.callback;
		App.dialog.show('.notes-to-drivers-container');
		RestaurantService.get( id_restaurant, function(d) {
			$scope.restaurant = d;
		});
	});
	$scope.save_notes_to_driver = function(){
		RestaurantService.save_notes_to_driver( $scope.restaurant, function( json ){
			if( callback ){
				callback();
			}
			id_restaurant = null;
			callback = null;
		} );
	}
});


NGApp.controller('RestaurantCtrl', function ($scope, $routeParams, MapService, RestaurantService, OrderService, $rootScope) {
	$scope.loading = true;
	$scope.loadingOrders = true;
	$scope.id_restaurant = $routeParams.id;

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		update();
	});

	var update = function() {
		if (!$scope.map || !$scope.restaurant) {
			return;
		}

		MapService.trackRestaurant({
			map: $scope.map,
			restaurant: $scope.restaurant,
			scope: $scope,
			id: 'restaurant-location'
		});
	};

	RestaurantService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Restaurant';
		$scope.restaurant = d;
		$scope.loading = false;

		update();

		OrderService.list({restaurant: d.id_restaurant, limit: 5}, function(d) {
			$scope.orders = d.results;
			$scope.count = d.count;
			$scope.pages = d.pages;
			$scope.loadingOrders = false;
		});
	});
});


NGApp.controller('RestaurantOrderPlacementDashboard', function ( $scope, RestaurantOrderPlacementService, $routeParams ) {

	// Load restaurants that are allowed to place orders
	var restaurants = function(){
		RestaurantOrderPlacementService.restaurant.all( function( json ){
			$scope.restaurants = json;
		} );
	}

	var start = function(){
		RestaurantOrderPlacementService.restaurant.status( $scope.id_restaurant, function( json ){
			if( !json.error ){
				$scope.id_restaurant = json.id_restaurant;
				$scope.status = json;
				$scope.ready = true;
			}
		} );
	}

	$scope.load_restaurant = function(){
		$scope.navigation.link( '/restaurant/order/placement/dashboard/' + $scope.id_restaurant );
	}

	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}

	$scope.new = function(){
		$scope.navigation.link( '/restaurant/order/placement/new/' + $scope.id_restaurant );
	}

	if( $scope.account.isLoggedIn() ){
		if( $scope.account.isAdmin ){
			restaurants();
			if( $routeParams.id ){
				$scope.id_restaurant = parseInt( $routeParams.id );
			}
		}
		start();
	}
} );

NGApp.controller('RestaurantOrderPlacementView', function ( $scope, RestaurantOrderPlacementService ) {
	RestaurantOrderPlacementService.get( function( json ){
		if( json.id_order ){
			$scope.order = json;
			if( $scope.account.isAdmin ){
				$scope.id_restaurant = json.id_restaurant;
			}
		} else {
			$scope.error = true;
		}
		$scope.ready = true;
	} );
	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}
} );

NGApp.controller('RestaurantOrderPlacementList', function ( $scope, RestaurantOrderPlacementService, $routeParams ) {

	// Load restaurants that are allowed to place orders
	var restaurants = function(){
		RestaurantOrderPlacementService.restaurant.all( function( json ){
			$scope.restaurants = json;
		} );
	}

	var start = function(){
		RestaurantOrderPlacementService.list( $scope.id_restaurant, function( json ){
			if( !json.error ){
				$scope.orders = json.orders;
				$scope.id_restaurant = json.id_restaurant;
			}
			$scope.ready = true;
		} );
	}

	$scope.new = function(){
		$scope.navigation.link( '/restaurant/order/placement/new/' + $scope.id_restaurant );
	}

	$scope.open = function( id_order ){
		$scope.navigation.link( '/restaurant/order/placement/' + id_order );
	}

	$scope.load_restaurant = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}

	if( $scope.account.isLoggedIn() ){
		if( $scope.account.isAdmin ){
			restaurants();
			if( $routeParams.id ){
				$scope.id_restaurant = parseInt( $routeParams.id );
			}
		}
		start();
	}

} );

NGApp.controller( 'RestaurantOrderPlacementNew', function ( $scope, RestaurantService, RestaurantOrderPlacementService, PositionService, $routeParams ) {

	$scope.order = { 'tip_type': 'dollar', 'pay_type': 'card' };
	$scope.tip = { 'dollar' : '', 'percent': '10' };
	$scope.card = { 'month': 0, 'year': 0 };
	$scope.map = {};

	// Load restaurants that are allowed to place orders
	var restaurants = function(){
		RestaurantOrderPlacementService.restaurant.all( function( json ){
			$scope.restaurants = json;
		} );
	}

	$scope.load_restaurant = function(){
		$scope.navigation.link( '/restaurant/order/placement/new/' + $scope.id_restaurant );
	}

	var start = function(){
		$scope.card._months = RestaurantOrderPlacementService.cardMonths();
		$scope.card._years = RestaurantOrderPlacementService.cardYears();
		$scope.tip._percents = RestaurantOrderPlacementService.tipPercents();

		// get info about the restaurant
		RestaurantOrderPlacementService.restaurant.get( $scope.id_restaurant, function( json ){
			if( json.id_restaurant ){
				$scope.restaurant = json;
				$scope.id_restaurant = $scope.restaurant.id_restaurant;
				PositionService.bounding( $scope.restaurant.lat, $scope.restaurant.lon );
				App.config.processor = { type: 'balanced' };
			}
			$scope.ready = true;
		} );
	}

	$scope.$watchCollection('[order.subtotal, order.tip]', function(newValues, oldValues){
		calcTotal();
	} );

	$scope.$watch( 'order.pay_type', function( newValue, oldValue, scope ) {
		$scope.order.tip = 0;
	} );

	$scope.$watch( 'order.tip_type', function( newValue, oldValue, scope ) {
		if( oldValue == 'dollar' ){
			$scope.tip.dollar = $scope.order.tip;
			$scope.order.tip = $scope.tip.percent;
		} else {
			$scope.tip.percent = $scope.order.tip;
			$scope.order.tip = $scope.tip.dollar;
		}
	} );

	var calcTotal = function(){
		$scope.finalAmount = 0;
		if( $scope.order && $scope.restaurant ){
			$scope.finalAmount = RestaurantOrderPlacementService.calcTotal( $scope.order, $scope.restaurant );
		}
	}

	$scope.checkAddress = function(){

		$scope.map.link = false;
		$scope.map.distance = false;
		$scope.map.img = false;
		$scope.map.out_of_range = false;

		if( $scope.order.address ){
			PositionService.find( $scope.order.address,
				function( address ){
					var pos = PositionService.getPosition( address );
					if( pos ){
						var distance = PositionService.checkDistance( pos.lat, pos.lon );
						if( distance ){
							$scope.map.distance = parseFloat( distance );
							$scope.restaurant.range = parseFloat( $scope.restaurant.range );
							$scope.map.out_of_range = ( $scope.map.distance > $scope.restaurant.range );
							console.log('$scope.map.out_of_range',$scope.map.out_of_range);
							$scope.$safeApply( function(){
								$scope.map.out_of_range = ( $scope.map.distance > $scope.restaurant.range );
							} );
							setTimeout( function(){
								$scope.$safeApply( function(){
									var zoom = 13;
									$scope.map.img = PositionService.getMapImageSource( { 'lat': pos.lat, 'lon': pos.lon }, { 'lat': $scope.restaurant.lat, 'lon': $scope.restaurant.lon }, zoom );
								} );
							}, 1 );

						} else {
							// error
							$scope.map.distance = -1;
						}
					}
				},
				// error
				function(){ $scope.map.distance = -1; }
			);
			$scope.map.link = PositionService.getDirectionsLink( $scope.restaurant.address, $scope.order.address );
		} else {
			$scope.distance = false;
		}
	}

	$scope.processOrder = function(){

		if( $scope.map.out_of_range ){
			App.alert( 'The address: ' + $scope.order.address + '\nis out of the range.' );
			$scope.submitted = true;
			return;
		}

		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;
		var order = angular.copy( $scope.order );
		if( $scope.order.tip_type == 'dollar' ){
			order.autotip_value = $scope.order.tip;
			order.tip = 'autotip';
		} else {
			order.tip = $scope.order.tip;
		}
		order.restaurant = $scope.restaurant.id_restaurant;
		RestaurantOrderPlacementService.process( order, $scope.card, function( data ){
			$scope.isProcessing = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				if( data.id_order ) {
					$scope.navigation.link( '/restaurant/order/placement/' + data.id_order );
				} else {
					var errors = '';
					var error = '';
					for ( var x in data.errors ) {
						if( x != 'debug' ){
							error += '<li></i>' + data.errors[x] + '</li>';
						}
					}
					App.alert('<ul>' + error + '</ul>');
				}
			}
		} );
	}

	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}

	$scope.test = function (){
		$scope.card.number = '4111111111111111';
		$scope.card.year = '2015';
		$scope.card.month = '2';
		$scope.order = { name: 'MR TEST', phone: '646-783-1444', pay_type: 'card', delivery_type: 'delivery', address: $scope.restaurant.address, notes: 'Second floor', subtotal:10, tip:1.50, tip_type:'dollar' };
		setTimeout( function(){ calcTotal(); $scope.checkAddress() }, 1000 );
	}

	if( $scope.account.isLoggedIn() ){
		if( $scope.account.isAdmin ){
			restaurants();
			if( $routeParams.id ){
				$scope.id_restaurant = parseInt( $routeParams.id );
			}
		}
		start();
	}

} );
