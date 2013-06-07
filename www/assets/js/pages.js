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
NGApp.controller('home', function ($scope, $http, $location) {
	if (App.loc.pos().valid('restaurants') && App.restaurants.list) {
		// we have a location, show the restaurants
		$location.path('/' + App.restaurants.permalink);
	} else {
		// we dont have a location. let the user enter it
		$location.path('/location');
	}
});


/**
 * Alias / unknown controller
 */
NGApp.controller('default', function ($scope, $http) {

	// @TODO THIS
	App.routeAlias( path[ 0 ],
		function( result ){
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


/**
 * Show the restaurants
 */
NGApp.controller('restaurants', function ($scope, $http, $location) {
	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';
	
	$scope.display = function() {
		if (!this.restaurant.open()) {
			App.alert("This restaurant is currently closed. It will be open during the following hours (" + this.restaurant._tzabbr + "):\n\n" + this.restaurant.closedMessage());
			App.busy.unBusy();

		} else {
			$location.path('/' + App.restaurants.permalink + '/' + this.restaurant.permalink);
		}
	};

	if (App.loc.pos().valid('restaurants')) {

		// sort the restaurants
		var sortRestaurants = function() {

			for (var x in App.restaurants.list) {
				// recalculate restaurant open status on relist
				App.restaurants.list[x].open();

				// determine which tags to display
				if (!App.restaurants.list[x]._open) {
					App.restaurants.list[x]._tag = 'closed';
				} else {
					if (App.restaurants.list[x].delivery != '1') {
						App.restaurants.list[x]._tag = 'takeout';
					} else if (App.restaurants.list[x].isAboutToClose()) {
						App.restaurants.list[x]._tag = 'closing';
					}
				}

				// show short description
				App.restaurants.list[x]._short_description = (App.restaurants.list[x].short_description || ('Top Order: ' + (App.restaurants.list[x].top_name ? (App.restaurants.list[x].top_name || App.restaurants.list[x].top_name) : '')));
			};

			App.restaurants.list.sort(sort_by(
				{
					name: '_open',
					reverse: true
				},
				{
					name: 'delivery',
					reverse: true
				},
				{
					name: '_weight',
					primer: parseInt,
					reverse: true
				}
			));
		};

		var displayRestaurants = function() {

			sortRestaurants();

			var titles = App.foodDelivery.localizedContent();

			$scope.restaurants = App.restaurants.list;
			$scope.slogan = titles.slogan;
			$scope.tagline = titles.tagline;

			if (App.restaurants.list.length == 4) {
				$('.content').addClass('short-meal-list');
			} else {
				$('.content').removeClass('short-meal-list');
			}
			$('.content').removeClass('smaller-width');

			$('.nav-back').removeClass('nav-back-show');
		};


		// get the list of restaurants
		if (App.restaurants.list === false) {
			var url = App.service + 'restaurants?lat=' + App.loc.pos().lat() + '&lon=' + App.loc.pos().lon() + '&range=' + ( App.loc.range || App.defaultRange );
			App.restaurants.list = false;

			$http.get(url, {cache: true}).success(function(data) {
				App.restaurants.list = [];

				// There is no restaurant near to the user. Go home and show the error.
				if (typeof data.restaurants == 'undefined' || data.restaurants.length == 0) {
					console.debug('THERE WAS A LOC ERROR?');

				} else {
					for (var x in data.restaurants) {
						App.restaurants.list[App.restaurants.list.length] = new Restaurant(data.restaurants[x]);
					}
					console.debug('THERE WAS A LOC SUCCESS!!');
				}

				displayRestaurants();
			});

		} else {
			displayRestaurants();
		}

	} else {
		// we dont have a location. let the user enter it
		$location.path('/location');
	}
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
NGApp.controller('location', function ($scope, $http, $location) {
	$scope.isUser = App.config.user.has_auth;
	$scope.notUser = !App.config.user.has_auth;
	$scope.topCommunities = App.topCommunities;
	$scope.yourArea = App.loc.pos().city() || 'your area';

	// lets eat button
	$scope.letsEat = function() {
		var address = $.trim($('.location-address').val());

		if (!address) {
			$('.location-address').val('').attr('placeholder','Please enter your address here');
		} else {
			App.loc.addVerify(address, function() {
				$scope.$apply(function() {
					$location.path('/' + App.restaurants.permalink);
				});
			}, function() {
				$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
			});
		}
	}
});



/**
 * restaurant page
 */
NGApp.controller('restaurant', function ($scope, $http, $routeParams) {

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	App.cartHighlightEnabled = false;

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	App.cache('Restaurant', $routeParams.id, function() {
		if (App.restaurant && App.restaurant.permalink != $routeParams.id) {
			App.cart.resetOrder();
		}

		App.restaurant = this;
		var community = App.getCommunityById(App.restaurant.id_community);

		var lastOrderDelivery = false;
		var lastPayCash = false;

		if( App.config && App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant] ){
			// Check if the last user's order at this restaurant was a delivery type
			lastOrderDelivery = App.config.user.presets[App.restaurant.id_restaurant].delivery_type;
			// Check if the last user's order at this restaurant was cash type
			lastPayCash = App.config.user.presets[App.restaurant.id_restaurant].pay_type;
			App.order['delivery_type'] = lastOrderDelivery;
			App.order['pay_type'] = lastPayCash;
		}


//			title: App.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton',

		var complete = function() {
		
			var date = new Date().getFullYear();
			var years = [];
			for (var x=date; x<=date+20; x++) {
				years[years.length] = x;
			}
		
			$scope.restaurant = App.restaurant;
			$scope.presets = App.config.user.presets;
			$scope.lastOrderDelivery = lastOrderDelivery;
			$scope.user = App.config.user;
			$scope.community = community;
			$scope.AB = {
				dollar: (App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '',
				changeablePrice: function(dish) {
					return (App.config.ab && App.config.ab.changeablePrice == 'show' && dish.changeable_price) ? '+' : ''
				},
				restaurantPage: (App.config.ab && App.config.ab.restaurantPage == 'restaurant-page-noimage') ? ' restaurant-pic-wrapper-hidden' : ''
			};

			$scope.form = {
				tip: App.order.tip,
				name: App.config.user.name,
				phone: App.phone.format(App.config.user.phone),
				address: (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>'),
				notes: (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) ? App.config.user.presets[App.restaurant.id_restaurant].notes : '',
				card: {
					number: App.config.user.card,
					month: App.config.user.card_exp_month,
					year: App.config.user.card_exp_year
				},
				months: [1,2,3,4,5,6,7,8,9,10,11,12],
				years: years
			};


			$scope.cart = {
				totalFixed: parseFloat(App.restaurant.delivery_min - App.cart.total()).toFixed(2)
			}
		};

		// double check what phase we are in
		if (!$scope.$$phase) {
			$scope.$apply(complete);
		} else {
			complete();
		}

		// If the typed address is different of the user address the typed one will be used #1152
		if( App.loc.changeLocationAddressHasChanged && App.loc.pos() && App.loc.pos().addressEntered && App.loc.pos().addressEntered != App.config.user.address ){
			// Give some time to google.maps.Geocoder() load
			var validatedAddress = function(){
				if( google && google.maps && google.maps.Geocoder ){
					var addressToVerify = App.loc.pos().addressEntered;
					// Success the address was found
					var success = function( results ){
						var address = results[ 0 ];
						if( address ){
							// Valid if the address is acceptable
							if( App.loc.validateAddressType( address ) ){
								// If the flag useCompleteAddress is true
								if( App.useCompleteAddress ){
									$( '[name=pay-address]' ).val( App.loc.formatedAddress( address ) );
									$( '.user-address' ).html( App.loc.formatedAddress( address ) );
								} else {
									$( '[name=pay-address]' ).val( addressToVerify );
									$( '.user-address' ).html( addressToVerify );
								}
							} else {
								console.log('Invalid address: ' + addressToVerify);
							}
						}
					};
					// Error, do nothing
					var error = function(){  };
					App.loc.doGeocode( addressToVerify, success, error );
				} else {
					setTimeout( function(){
						validatedAddress();
					}, 10 );
				}
			}
			validatedAddress();
		}

		if (App.config.user.presets) {
			$('.payment-form').hide();
		}

		if (App.cart.hasItems()) {
			App.cart.reloadOrder();
		} else if (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) {
			try {
				App.cart.loadOrder(App.config.user.presets[App.restaurant.id_restaurant]);
			} catch (e) {
				App.cart.loadOrder(App.restaurant.preset());
			}
		} else {
			App.cart.loadOrder(App.restaurant.preset());
		}

		// As the div restaurant-items has position:absolute this line will make sure the footer will not go up.
		$('.body').css({
			'min-height': $('.restaurant-items').height()
		});

		setTimeout(function() {
			var total = App.cart.updateTotal();
		},200);

		App.cartHighlightEnabled = false;

		if ( App.order['pay_type'] == 'cash' || lastPayCash == 'cash' ) {
			App.trigger.cash();
		} else {
			App.trigger.credit();
		}

		if( lastPayCash == 'cash' ){
			App.trigger.cash();
		} else if ( lastPayCash == 'card' ){
			App.trigger.credit();
		}

		if( App.restaurant.credit != '1' ){
			App.trigger.cash();
		}

		if( App.restaurant.cash != '1' && App.restaurant.credit == '1' ){
			App.trigger.credit();
		}

		// Rules at #669
		if( ( lastOrderDelivery == 'delivery' && App.restaurant.delivery == '1' ) ||
				( App.order['delivery_type'] == 'delivery' && App.restaurant.delivery == '1' ) ||
				( App.restaurant.takeout == '0' ) ||
				( lastOrderDelivery != 'takeout' && App.restaurant.delivery == '1' ) ){
				App.trigger.delivery();
		}

		// If the restaurant doesn't delivery
		if( App.order['delivery_type'] == 'takeout' || App.restaurant.delivery != '1') {
			App.trigger.takeout();
		}

		// If the user has presets at other's restaurants but he did not typed his address yet
		// and the actual restaurant is a delivery only #875
		if( ( App.restaurant.takeout == '0' || App.order['delivery_type'] == 'delivery' ) && !App.config.user.address ){
			$('.payment-form').show();
			$('.delivery-payment-info, .content-padder-before').hide();
		}

		$( '.restaurant-gift' ).hide();

		App.credit.getCredit( function(){
			App.credit.show();
			App.cart.updateTotal();
		} );

		if (!App.config.user.id_user) {
			App.config.user.address = App.loc.enteredLoc;
			App.loc.enteredLoc = '';
		}

		if( App.giftcard.notesCode ){
			setTimeout( function(){
				$( '[name=notes]' ).val( App.giftcard.notesCode + ' ' + $( '[name=notes]' ).val() );
				App.giftcard.notesField.listener();
			}, 300 );
		}
	});
});


/**
 * Order page. displayed after order, or at order history
 */
NGApp.controller('order', function ($scope, $http, $location, $routeParams) {

	App.cache('Order', $routeParams.id, function() {
		var order = this;

		var complete = function() {
			$location.path('/');
		};

		if (!order.uuid) {
			if (!$scope.$$phase) {
				$scope.$apply(complete);
			} else {
				complete();
			}
			return;
		}

		App.cache('Restaurant',order.id_restaurant, function() {
			var complete = function() {

				$scope.order = order;
				$scope.restaurant = this;
				
				if (order['new']) {
					setTimeout(function() {
						order['new'] = false;
					},500);
				}
			};
			if (!$scope.$$phase) {
				$scope.$apply(complete);
			} else {
				complete();
			}
		});
	});
});



/**
 * Orders page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
NGApp.controller('orders', function ($scope, $http, $location) {
	if (!App.config.user.id_user) {
		$location.path('/' + App.restaurants.permalink);
		return;
	}

	$scope.displayRestaurant = function() {
		$location.path('/' + App.restaurants.permalink + '/' + this.order._restaurant_permalink);
	};

	$scope.displayOrder = function() {
		$location.path('/order/' + this.order.id);
	};

	$http.get(App.service + 'user/orders', {cache: true}).success(function(json) {
		for (var x in json) {
			json[x].timeFormat = json[x]._date_tz.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i,'$1/$2 $3');
		}
		$scope.orders = json;
		$scope.user = App.user;
	});
});


/**
 * FoodDelivery's methods
 */
App.foodDelivery = {};

App.foodDelivery.localizedContent = function(){
	// set the slogan and tagline
	try {
		var slogan = App.slogan.slogan;
		var sloganReplace = App.loc.pos().prep() + ' ' + App.loc.pos().city();

		sloganReplace = $.trim(sloganReplace);
		var tagline = App.tagline.tagline.replace('%s', sloganReplace);
		slogan = slogan.replace('%s', sloganReplace);

	} catch (e) {
		console.log('Failed to load dynamic text', App.slogan, App.tagline, e);
		var slogan = '';
		var tagline = '';
	}

	// set title
	var title = App.loc.pos().city() + ' Food Delivery | Order Food from ' + (App.loc.pos().city() || 'Local') + ' Restaurants | Crunchbutton';
	document.title = title;

	return {
		slogan: slogan,
		tagline: tagline,
		title: title
	};
}

/**
 * Gift card page
 */
App.page.giftCard = function( path ){
	App.page.home();
	App.giftcard.show( path );
}

/**
 * Reset password page
 */
App.page.resetPassword = function( path ){
	if( !App.signin.passwordHelp.reset.hasStarted ){
		App.signin.passwordHelp.reset.hasStarted = true;
		$( '.wrapper' ).append( App.signin.passwordHelp.reset.html( path ) );
		App.showReset = true;
		App.page.home( true );
		App.signin.passwordHelp.reset.init();
	}
}
