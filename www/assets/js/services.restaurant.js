// Restaurant list service
NGApp.factory('RestaurantsService', function ($http, PositionsService) {

	var service = {};
	var restaurants = false;
	var isSorted = false;

	service.reset = function () {
		restaurants = false;
		isSorted = false;
	}

	service.position = PositionsService;

	service.sort = function () {

		if (isSorted) {
			return restaurants;
		}

		var list = restaurants;

		for (var x in list) {

			// recalculate restaurant open status on relist
			list[x].open();

			// determine which tags to display
			if (!list[x]._open) {
				list[x]._tag = 'closed';
			} else {
				if (list[x].delivery != '1') {
					list[x]._tag = 'takeout';
				} else if (list[x].isAboutToClose()) {
					list[x]._tag = 'closing';
				}
			}
			// show short description
			list[x]._short_description = (list[x].short_description || ('Top Order: ' + (list[x].top_name ? (list[x].top_name || list[x].top_name) : '')));
		};
		list.sort(sort_by({
			name: '_open',
			reverse: true
		}, {
			name: 'delivery',
			reverse: true
		}, {
			name: '_weight',
			primer: parseInt,
			reverse: true
		}));
		isSorted = true;
		restaurants = list;
		return restaurants;
	}

	service.list = function (success, error) {
		if (!service.position.pos().valid('restaurants')) {
			if (error) {
				error();
			}
			return false;
		}

		if (restaurants === false || service.restaurants.forceLoad) {

			var url = App.service + 'restaurants?lat=' + service.position.pos().lat() + '&lon=' + service.position.pos().lon() + '&range=' + (service.position.range || App.defaultRange);

			$http.get(url, {
				cache: true
			}).success(function (data) {

				var list = [];
				if (typeof data.restaurants == 'undefined' || data.restaurants.length == 0) {
					if (error) {
						error();
						return false;
					}
				} else {
					for (var x in data.restaurants) {
						list[list.length] = new Restaurant(data.restaurants[x]);
					}
					restaurants = list;
					if (success) {
						success(list);
					}
					return list;
				}
			});
			isSorted = false;
			service.restaurants.forceLoad = false;
		} else {
			if (success) {
				success(restaurants);
			}
			return restaurants;
		}
	}

	return service;
});

//RestaurantService Service
NGApp.factory('RestaurantService', function ($http, $routeParams, $rootScope, AccountService ) {
	
	var service = {
		loaded : false
	};

	service.init = function(){
		console.log( 'init' );
		service.restaurant = false;
		loaded = false;
		service.load();
	}

service.load = function(){

	service.account = AccountService;

	App.cache('Restaurant', $routeParams.id, function () {
		 
		if (service.restaurant && service.restaurant.permalink != $routeParams.id) {
			service.cartService.resetOrder();
		}

		service.restaurant = this;
		console.log('service.restaurant',service.restaurant);
		service.community = App.getCommunityById( service.restaurant.id_community );
		
		var lastOrderDelivery = false;
		var lastPayCash = false;

		if ( service.account.user && service.account.user.presets && service.account.user.presets[ service.restaurant.id_restaurant ] ) {
			// Check if the last user's order at this restaurant was a delivery type
			lastOrderDelivery = service.account.user.presets[ service.restaurant.id_restaurant ].delivery_type;
			// Check if the last user's order at this restaurant was cash type
			lastPayCash = service.account.user.presets[ service.restaurant.id_restaurant ].pay_type;
			App.order['delivery_type'] = lastOrderDelivery;
			App.order['pay_type'] = lastPayCash;
		}

		//			title: service.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton',

		var complete = function () {

			var date = new Date().getFullYear();
			var years = [];
			for (var x = date; x <= date + 20; x++) {
				years[years.length] = x;
			}

			service.loaded = true;

			service.lastOrderDelivery = lastOrderDelivery;
			
			service.showRestaurantDeliv = ((lastOrderDelivery == 'delivery' || service.restaurant.delivery == '1' || service.restaurant.takeout == '0') && lastOrderDelivery != 'takeout');

			service.AB = {
				dollar: (App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '',
				changeablePrice: function (dish) {
					return (App.config.ab && App.config.ab.changeablePrice == 'show' && dish.changeable_price) ? '+' : ''
				},
				restaurantPage: (App.config.ab && App.config.ab.restaurantPage == 'restaurant-page-noimage') ? ' restaurant-pic-wrapper-hidden' : ''
			};

			service.form = {
				tip: App.order.tip,
				name: service.account.user.name,
				phone: App.phone.format(service.account.user.phone),
				address: service.account.user.address,
				notes: ( service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) ? service.account.user.presets[service.restaurant.id_restaurant].notes : '',
				card: {
					number: service.account.user.card,
					month: service.account.user.card_exp_month,
					year: service.account.user.card_exp_year
				},
				months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
				years: years
			};
			/*
			// Validate gift card at the notes field
			service.$watch( 'form.notes', function( newValue, oldValue, scope ) {
				service.giftcard.text.content = service.form.notes;
				service.giftcard.text.start();
			});
*/
			// service.cart = {
				// totalFixed: parseFloat(service.restaurant.delivery_min - service.cartService.total()).toFixed(2)
			// }
		};

		// double check what phase we are in
			if (!$rootScope.$$phase) {
				$rootScope.$apply(complete);
			} else {
				complete();
			}
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

		if (service.account.user.presets) {
			$('.payment-form').hide();
		}

		if (service.cartService.hasItems()) {
			service.cartService.reloadOrder();
		} else if (service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) {
			try {
				service.cartService.loadOrder(service.account.user.presets[service.restaurant.id_restaurant]);
			} catch (e) {
				service.cartService.loadOrder(service.restaurant.preset());
			}
		} else {
			service.cartService.loadOrder(service.restaurant.preset());
		}

		// As the div restaurant-items has position:absolute this line will make sure the footer will not go up.
		$('.body').css({
			'min-height': $('.restaurant-items').height()
		});

		setTimeout(function () {
			var total = service.cartService.updateTotal();
		}, 200);

		service.cartServiceHighlightEnabled = false;

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

		$('.restaurant-gift').hide();

		App.credit.getCredit(function () {
			App.credit.show();
			service.cartService.updateTotal();
		});

		if (!service.account.user.id_user) {
			service.account.user.address = App.loc.enteredLoc;
			App.loc.enteredLoc = '';
		}
		if( App.giftcard.notesCode ){
			setTimeout( function(){
				$( '[name=notes]' ).val( App.giftcard.notesCode + ' ' + $( '[name=notes]' ).val() );
				App.giftcard.notesField.listener();
			}, 300 );
		}
		//*/
	});
}
	return service;
});