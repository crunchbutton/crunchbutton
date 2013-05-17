
App.page.home = function(force) {

	if (!force && !App.loc.loaded) {
		// if we arent forcing and theres no pos, then this was a home request. we need to wait a second
		App.loc.bind('location-loaded', function() {
			App.page.home(force);
		});
		App.loc.bind('location-detected', function() {
			App.page.home(force);
		});

		return;
	}

	var homeSuccess = function() {
		
		App.showPage({
			page: 'home',
			title: 'Crunchbutton',
			data: {
				topCommunities: App.topCommunities,
				yourArea: App.loc.city() || 'your area',
				autofocus: $(window).width() >= 768 ? ' autofocus="autofocus"' : ''
			}
		});
	
		$('.nav-back').removeClass('nav-back-show');
		$('.config-icon').addClass('config-icon-mobile-hide, config-icon-desktop-hide');
		$('.content').addClass('short-meal-list');

		// @todo: put these in the css. @hacks
		if (navigator.userAgent.toLowerCase().indexOf('safari') > -1 && navigator.userAgent.toLowerCase().indexOf('mobile') == -1 && navigator.userAgent.toLowerCase().indexOf('chrome') == -1) {
			// safari desktop
			$('.location-detect').css({
				'margin-top': '2px',
				'height': '50px'
			});
		} else if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
			// firefox desktop
			$('.location-detect').css({
				'margin-top': '0px',
				'height': '52px'
			});
		}
	};

	if (!force && App.loc.address() && App.restaurants.list === false) {
		// we have an address, but no restaurants
		App.page.foodDelivery();

		return;

	} else {
		homeSuccess();
	}
		
	if (!force && App.loc.address() && App.restaurants.list && App.restaurants.list.length == 0) {
		
		// Log the location
		App.loc.log();

		// Move the scroll to the top
		setTimeout(scrollTo, 0, 0, 1);

		// Shows the back button
		$( '.nav-back' ).addClass( 'nav-back-show' );
		App.loc.locationNotServed = true;

		// Log the error
		App.log.location( { 'address' : App.loc.address(), 'lat' : App.loc.pos().lat, 'lon' : App.loc.pos().lon  } , 'address not served' );

		$('.home-greeting, .enter-location, .button-letseat-form').hide();
		$('.error-location').show();
		
		App.track('Location Error', {
			lat: App.loc.pos().lat,
			lon: App.loc.pos().lon,
			address: App.loc.address()
		});

	} else {
		$('.location-address').val('');
		$('.error-location').hide();
		$('.home-greeting, .enter-location, .button-letseat-form').show();

	}



};

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
 * Legal page. loaded from xhr.
 */
App.page.legal = function() {
	App.currentPage = 'legal';
	$.getJSON('/api/legal',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
		setTimeout( function(){
			// I put this way to avoid spammers robots #1219
			$( '.crunchbutton-join-mail' ).html( 'moc.nottubhcnurc@nioj'.split('').reverse().join('') );
		}, 100 );
	});
};


/**
 * Help page. loaded from xhr.
 */
App.page.help = function() {
	App.currentPage = 'help';
	$.getJSON('/api/help',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
		setTimeout( function(){
			// I put this way to avoid spammers robots
			$( '.crunchbutton-join-mail' ).html( 'moc.nottubhcnurc@nioj'.split('').reverse().join('') );
		}, 100 );
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

		$(document).on('touchclick', '.order-restaurant', function() {
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

// before we change the url we need to make sure that there are restaurants at the typed place.
App.foodDelivery.getRestaurants = function(success, error) { 
	if (!App.loc.pos()) {
		error();
		return;
	}

	var url = App.service + 'restaurants?lat=' + App.loc.pos().lat + '&lon=' + App.loc.pos().lon + '&range=' + ( App.loc.range || App.defaultRange );
	App.restaurants.list = false;

	$.getJSON(url, function(json) {
		App.restaurants.list = [];

		// There is no restaurant near to the user. Go home and show the error.
		if (typeof json['restaurants'] == 'undefined' || json['restaurants'].length == 0) {
			error();

		} else {

			for (var x in json.restaurants) {
				var res = new Restaurant(json.restaurants[x]);
				res.open();
				App.restaurants.list[App.restaurants.list.length] = res;
			};
			success();
		}
	});
}

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
 * food delivery page
 */
App.page.foodDelivery = function(refresh) {
	if (refresh) {
		App.restaurants.list = false;
	}
	
	//App.loc.reverseGeocode(App.loc.pos().lat, App.loc.pos().lon, success, error);
	var success = function() {
		// if we have a success and
		var loc = '/' + App.restaurants.permalink;
		if (loc != location.pathname) {
			History.pushState({}, 'Crunchbutton', '/' + App.restaurants.permalink);
			return;
		}

		$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );
		$( '.nav-back' ).removeClass( 'nav-back-show' );

		if (App.restaurants.list.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}
		$('.content').removeClass('smaller-width');
	
		App.currentPage = 'food-delivery';
	
		var titles = App.foodDelivery.localizedContent();
		
		// sort the list by open or not
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
		
		App.showPage({
			page: 'restaurants',
			data: {
				slogan: titles.slogan,
				tagline: titles.tagline,
				restaurants: App.restaurants.list
			}
		});
	};
	
	// we dont have any restaurants
	var error = function() {
		App.loadHome();
	};

	if (App.restaurants.list === false) {
		// we have not checked for restaurants. do that now.
		App.foodDelivery.getRestaurants(success, error);

	} else if (App.restaurants.list === []) {
		// there are no restaurants for this area. go back home
		error();

	} else {
		// we already have a restaurant list. display the restaurant page
		success();
	}
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
