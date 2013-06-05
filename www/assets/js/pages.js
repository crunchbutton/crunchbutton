/**
 * legal page
 */
NGApp.controller('legal', function ($scope, $http) {
	App.controlMobileIcons.normalize();
	$http.get(App.service + 'legal').success(function(data) {
		$scope.legal = data.data;
	});
});


/**
 * help page
 */
NGApp.controller('help', function ($scope, $http) {
	App.controlMobileIcons.normalize();
	$http.get(App.service + 'help').success(function(data) {
		$scope.help = data.data;
		$('.crunchbutton-join-mail').html('moc.nottubhcnurc@nioj'.split('').reverse().join(''));
	});
});


/**
 * Home controller
 */
NGApp.controller('home', function ($scope, $http) {
	if (App.loc.pos() && App.loc.pos().address() && App.restaurants.list) {
		// we have a location, show the restaurants
		History.replaceState({}, 'Crunchbutton', '/food-delivery');
	} else {
		// we dont have a location. let the user enter it
		History.replaceState({}, 'Crunchbutton', '/location');
	}
});


/**
 * Alias / unknown controller
 */
NGApp.controller('default', function ($scope, $http) {

	// TODO THIS
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
NGApp.controller('restaurants', function ($scope, $http) {
	$scope.mealItemClass = App.isAndroid() ? 'meal-food-android' : '';

	if (App.loc.pos().address()) {

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

		var displayRestaurants = function($scope) {

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
			var url = App.service + 'restaurants?lat=' + App.loc.pos().lat + '&lon=' + App.loc.pos().lon + '&range=' + ( App.loc.range || App.defaultRange );
			App.restaurants.list = false;

			$http.get(url).success(function(data) {
				App.restaurants.list = [];

				// There is no restaurant near to the user. Go home and show the error.
				if (typeof data.restaurants == 'undefined' || data.restaurants.length == 0) {
					error();

				} else {
					for (var x in data.restaurants) {
						App.restaurants.list[App.restaurants.list.length] = new Restaurant(data.restaurants[x]);
					};
					success();
				}

				displayRestaurants($scope);
			});

		} else {
			displayRestaurants($scope);
		}

	} else {
		// we dont have a location. let the user enter it
		History.pushState({}, 'Crunchbutton', '/location');
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
NGApp.controller('location', function ($scope, $http) {
	$scope.isUser = App.config.user.has_auth;
	$scope.notUser = !App.config.user.has_auth;
	$scope.topCommunities = App.topCommunities;
	$scope.yourArea = App.loc.pos().city() || 'your area';
});


App.page.restaurant = function(id) {

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	App.cartHighlightEnabled = false;

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	App.cache('Restaurant', id, function() {
		if (App.restaurant && App.restaurant.permalink != id) {
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

		App.showPage({
			tracking: {
				title: 'Restaurant page loaded',
				data: {
					restaurant: App.restaurant.name
				}
			},
			page: 'restaurant',
			title: App.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton',
			data: {
				restaurant: App.restaurant,
				presets: App.config.user.presets,
				lastOrderDelivery: lastOrderDelivery,
				user: App.config.user,
				community: community,
				form: {
					tip: App.order.tip,
					name: App.config.user.name,
					phone: App.phone.format(App.config.user.phone),
					address: App.config.user.address,
					notes: (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) ? App.config.user.presets[App.restaurant.id_restaurant].notes : '',
					card: {
						number: App.config.user.card,
						month: App.config.user.card_exp_month,
						year: App.config.user.card_exp_year
					}
				}
			}
		});

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

};


/**
 * Order page. displayed after order, or at order history
 */
App.page.order = function(id) {

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	$( '.nav-back' ).addClass( 'nav-back-show' );

	if (App.justCompleted) {
		App.justCompleted = false;
	}

	// Just to make sure the user button will be shown
	App.signin.checkUser();

	App.cache('Order', id, function() {
		var order = this;

		if (!order.uuid) {
			History.replaceState({},'Crunchbutton','/orders');
			return;
		}

		App._order_uuid = id;

		App.facebook.preLoadOrderStatus();

		App.cache('Restaurant',order.id_restaurant, function() {
			var restaurant = this;

			$('.content').addClass('smaller-width');
			$('.main-content').css('width','auto');

			App.showPage({
				title: 'Crunchbutton - Your Order',
				page: 'order',
				data: {
					order: order,
					restaurant: restaurant,
					user: App.config.user.has_auth
				}
			});

		});
	});
};



/**
 * Order page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
App.page.orders = function() {

	if (!App.config.user.id_user) {
		History.pushState({}, 'Crunchbutton', '/');
		return;
	}

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	$( '.sign-in-icon' ).addClass( 'config-icon-mobile-hide' );

	$.getJSON('/api/user/orders',function(json) {
		App.showPage({
			title: 'Your Account',
			page: 'orders',
			data: {
				orders: json,
				user: App.user
			}
		});

		$( '.nav-back' ).addClass( 'nav-back-show' );

		$('.order-restaurant').tap(function(e) {
			var permalink = $( this ).attr( 'permalink' );
			var name = $( this ).attr( 'name' );
			var loc = '/' + App.restaurants.permalink + '/' + permalink;
			History.pushState({}, 'Crunchbutton - ' + name, loc);
		});
	});

	App.refreshLayout();
};


/**
 * FoodDelivery's methods
 */
App.foodDelivery = {};

App.foodDelivery.localizedContent = function(){
	// set the slogan and tagline
	try {
		var slogan = App.slogan.slogan;
		var sloganReplace = App.loc.prep() + ' ' + App.loc.city();

		sloganReplace = $.trim(sloganReplace);
		var tagline = App.tagline.tagline.replace('%s', sloganReplace);
		slogan = slogan.replace('%s', sloganReplace);

	} catch (e) {
		console.log('Failed to load dynamic text', App.slogan, App.tagline);
		var slogan = '';
		var tagline = '';
	}

	// set title
	var title = App.loc.city() + ' Food Delivery | Order Food from ' + (App.loc.city() || 'Local') + ' Restaurants | Crunchbutton';
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
