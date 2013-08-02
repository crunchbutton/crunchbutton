//OrderService Service
NGApp.factory( 'OrderService', function( $http, AccountService, CartService ){
	var service = {};

	service.account = AccountService;
	service.cart = CartService;
	
	service.restaurant = {};

	// Default values
	service.form = {
		delivery_type : 'delivery',
		pay_type : 'card'
	};

	// Info that will be shown to the user
	service.info = {
		dollarSign : '',
		breakdownDescription : '',
		extraCharges : '',
		deliveryMinDiff : '',
		cartSummary : '',
		totalText : ''
	}

	service.toogleDelivery = function( type ){
		if( type != service.form.delivery_type ){
			service.form.delivery_type = type;
			service.updateTotal();
		}
	}

	service.tooglePayment = function( type ){
		if( type != service.form.pay_type ){
			service.form.pay_type = type;
			service.updateTotal();
		}
	}

	service.init = function(){

		if( App.config.ab && App.config.ab.dollarSign == 'show' ){
			service.info.dollarSign = '$';
		}

		service.form.tip = 'autotip';
		service.form.name = service.account.user.name;
		service.form.phone = App.phone.format( service.account.user.phone );
		service.form.address = service.account.user.address;
		service.form.notes = ( service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) ? service.account.user.presets[service.restaurant.id_restaurant].notes : '';
		service.form.card = {
			number: service.account.user.card,
			month: service.account.user.card_exp_month,
			year: service.account.user.card_exp_year
		};
		service.updateTotal();
	}	

	service.reloadOrder = function () {
		var cart = service.items;
		service.resetOrder();
		service.loadFlatOrder(cart);
	}

	service.loadFlatOrder = function (cart) {
		for (var x in cart) {
			service.add(cart[x].id, {
				options: cart[x].options ? cart[x].options : []
			});
		}
	}

	service.loadOrder = function (order) {
		// @todo: convert this to preset object
		try {
			if (order) {
				var dishes = order['_dishes'];
				for (var x in dishes) {
					var options = [];
					for (var xx in dishes[x]['_options']) {
						options[options.length] = dishes[x]['_options'][xx].id_option;
					}
					if (App.cached.Dish[dishes[x].id_dish] != undefined) {
						service.add(dishes[x].id_dish, {
							options: options
						});
					}

				}
			}
		} catch (e) {
			console.log(e.stack);
			// throw e;
		}
	}

	service.autotip = function () {
		var subtotal = service.totalbreakdown().subtotal;
		var autotipValue
		if (subtotal === 0) {
			autotipValue = 0;
		} else {
			// autotip formula - see github/#940
			autotipValue = Math.ceil(4 * (subtotal * 0.107 + 0.85)) / 4;
		}
		$('[name="pay-autotip-value"]').val(autotipValue);
		var autotipText = autotipValue ? ' (' + service.info.dollarSign + autotipValue + ')' : '';
		$('[name=pay-tip] [value=autotip]').html('Autotip' + autotipText);
	}

	/**
	 * subtotal, delivery, fee, taxes and tip
	 *
	 * @category view
	 */
	service.extraChargesText = function (breakdown) {
		var elements = [];
		var text = '';

		if (breakdown.delivery) {
			elements.push(service.info.dollarSign + breakdown.delivery.toFixed(2) + ' delivery');
		}

		if (breakdown.fee) {
			elements.push(service.info.dollarSign + breakdown.fee.toFixed(2) + ' fee');
		}
		if (breakdown.taxes) {
			elements.push(service.info.dollarSign + breakdown.taxes.toFixed(2) + ' taxes');
		}
		if (breakdown.tip && breakdown.tip > 0) {
			elements.push(service.info.dollarSign + breakdown.tip + ' tip');
		}

		if (elements.length) {
			if (elements.length > 2) {
				var lastOne = elements.pop();
				var elements = [elements.join(', ')];
				elements.push(lastOne);
			}
			var text = elements.join(' & ');
		}
		return text;
	}

	service.subtotal = function () {
		return service.cart.subtotal();
	}

	/**
	 * delivery cost
	 *
	 * @return float
	 */
	service._breackDownDelivery = function () {
		var delivery = 0;
		if (service.restaurant.delivery_fee && service.form.delivery_type == 'delivery') {
			delivery = parseFloat(service.restaurant.delivery_fee);
		}
		delivery = App.ceil(delivery);
		return delivery;
	}

	/**
	 * Crunchbutton service
	 *
	 * @return float
	 */
	service._breackDownFee = function (feeTotal) {
		var fee = 0;
		if (service.restaurant.fee_customer) {
			fee = (feeTotal * (parseFloat(service.restaurant.fee_customer) / 100));
		}
		fee = App.ceil(fee);
		return fee;
	}

	service._breackDownTaxes = function (feeTotal) {
		var taxes = (feeTotal * (service.restaurant.tax / 100));
		taxes = App.ceil(taxes);
		return taxes;
	}

	service._breakdownTip = function (total) {
		var tip = 0;
		if (service.form.pay_type == 'card') {
			if (service.form.tip === 'autotip') {
				return parseFloat($('[name=pay-autotip-value]').val());
			}
			tip = (total * (service.form.tip / 100));
		}
		tip = App.ceil(tip);
		return tip;
	}

	service.total = function () {
		var
		total = 0,
			dish,
			options,
			feeTotal = 0,
			totalItems = 0,
			finalAmount = 0;

		var breakdown = this.totalbreakdown();
		total = breakdown.subtotal;
		feeTotal = total;
		feeTotal += breakdown.delivery;
		feeTotal += breakdown.fee;
		finalAmount = feeTotal + breakdown.taxes;
		finalAmount += this._breakdownTip(total);
		return App.ceil(finalAmount).toFixed(2);
	}

	service.charged = function () {

		var finalAmount = this.total();

		if (App.order.pay_type == 'card' && App.credit.restaurant[service.restaurant.id]) {
			finalAmount = finalAmount - App.ceil(App.credit.restaurant[service.restaurant.id]).toFixed(2);
			if (finalAmount < 0) {
				finalAmount = 0;
			}
		}
		return App.ceil(finalAmount).toFixed(2);
	}

	/**
	 * Returns the elements that calculates the total
	 *
	 * breakdown elements are: subtotal, delivery, fee, taxes and tip
	 *
	 * @return array
	 */
	service.totalbreakdown = function () {
		
		var elements = {};
		var total = this.subtotal();
		var feeTotal = total;

		elements['subtotal'] = this.subtotal();
		elements['delivery'] = this._breackDownDelivery();
		feeTotal += elements['delivery'];
		elements['fee'] = this._breackDownFee(feeTotal);
		feeTotal += elements['fee'];
		elements['taxes'] = this._breackDownTaxes(feeTotal);
		elements['tip'] = this._breakdownTip(total);
		return elements;
	}

	service.resetOrder = function () {
		service.cart.items = {};
	}

	/**
	 * Submits the cart order
	 *
	 * @returns void
	 */
	service.submit = function () {

		if (App.busy.isBusy()) {
			return;
		}

		App.busy.makeBusy();

		var read = $('.payment-form').length ? true : false;

		if (read) {
			App.config.user.name = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val().replace(/[^\d]*/gi, '');
			if (App.order['delivery_type'] == 'delivery') {
				App.config.user.address = $('[name="pay-address"]').val();
			}
			service.form.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart: service.getCart(),
			pay_type: service.form.pay_type,
			delivery_type: App.order['delivery_type'],
			restaurant: service.restaurant.id,
			make_default: $('#default-order-check').is(':checked'),
			notes: $('[name="notes"]').val(),
			lat: (App.loc.pos()) ? App.loc.pos().lat : null,
			lon: (App.loc.pos()) ? App.loc.pos().lon : null
		};

		if (order.pay_type == 'card') {
			order.tip = service.form.tip || '3';
			order.autotip_value = $('[name=pay-autotip-value]').val();
		}

		if (read) {
			order.address = App.config.user.address;
			order.phone = App.config.user.phone;
			order.name = App.config.user.name;
			if (App.order.cardChanged) {
				order.card = {
					number: $('[name="pay-card-number"]').val(),
					month: $('[name="pay-card-month"]').val(),
					year: $('[name="pay-card-year"]').val()
				};
			} else {
				order.card = {};
			}
		}

		console.log('ORDER:', order);

		var errors = {};

		if (!order.name) {
			errors['name'] = 'Please enter your name.';
		}

		if (!App.phone.validate(order.phone)) {
			errors['phone'] = 'Please enter a valid phone #.';
		}

		if (order.delivery_type == 'delivery' && !order.address) {
			errors['address'] = 'Please enter an address.';
		}

		if (order.pay_type == 'card' && ((App.order.cardChanged && !order.card.number) || (!App.config.user.id_user && !order.card.number))) {
			errors['card'] = 'Please enter a valid card #.';
		}

		if (!service.hasItems()) {
			errors['noorder'] = 'Please add something to your order.';
		}

		if (!$.isEmptyObject(errors)) {
			var error = '';
			for (var x in errors) {
				error += errors[x] + "\n";
			}
			$('body').scrollTop($('.payment-form').position().top - 80);
			App.alert(error);
			App.busy.unBusy();
			App.track('OrderError', errors);
			// Log the error
			App.log.order({
				'errors': errors
			}, 'validation error');
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if (App.iOS() && !App.crunchSoundAlreadyPlayed) {
			App.playAudio('get-food-audio');
			App.crunchSoundAlreadyPlayed = true;
		}

		// if it is a delivery order we need to check the address
		if (order.delivery_type == 'delivery') {

			// Correct Legacy Addresses in Database to Avoid Screwing Users #1284
			// If the user has already ordered food 
			if (App.config && App.config.user && App.config.user.last_order) {

				// Check if the order was made at this community
				if (App.config.user.last_order.communities.indexOf(service.restaurant.id_community) > -1) {

					// Get the last address the user used at this community
					var lastAddress = App.config.user.last_order.address;
					var currentAdress = $('[name=pay-address]').val();

					// Make sure the the user address is the same of his last order
					if ($.trim(lastAddress) != '' && $.trim(lastAddress) == $.trim(currentAdress)) {
						App.isDeliveryAddressOk = true;

						// Log the legacy address
						App.log.order({
							'address': lastAddress,
							'restaurant': service.restaurant.name
						}, 'legacy address');
					}
				}
			}
			/*
		// Check if the user address was already validated
		if ( !App.isDeliveryAddressOk	) {

			// Use the aproxLoc to create the bounding box
			if( App.loc.aproxLoc ){
				var latLong = new google.maps.LatLng( App.loc.aproxLoc ? App.loc.aproxLoc.lat : App.loc.pos().lat, App.loc.aproxLoc ? App.loc.aproxLoc.lon : App.loc.pos().lon);	
			}

			// Use the restautant's position to create the bounding box - just for tests
			if( App.useRestaurantBoundingBox ){
				var latLong = new google.maps.LatLng( service.restaurant.loc_lat, service.restaurant.loc_long );
			}

			if( !latLong ){
				//App.alert( 'Could not locate you!' );
				//App.busy.unBusy();
				//return;
			}

			var success = function( results ) {

				// Get the closest address from that lat/lng
				var theClosestAddress = App.loc.theClosestAddress( results, latLong );

				var isTheAddressOk = App.loc.validateAddressType( theClosestAddress );

				if( isTheAddressOk ){
					// Now lets check if the restaurant deliveries at the given address
					var lat = theClosestAddress.geometry.location.lat();
					var lon = theClosestAddress.geometry.location.lng();

					
					if( App.useCompleteAddress ){
						$( '[name=pay-address]' ).val( App.loc.formatedAddress( theClosestAddress ) );
					}

					if (!service.restaurant.deliveryHere({ lat: lat, lon: lon})) {
						App.alert( 'Sorry, you are out of delivery range or have an invalid address. \nPlease check your address, or order takeout.' );
						

						// Write the found address at the address field, so the user can check it.
						$( '[name=pay-address]' ).val( App.loc.formatedAddress( theClosestAddress ) );

						// Log the error
						App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : service.restaurant.name } , 'address out of delivery range' );
					
						App.busy.unBusy();
						return;
					
					} else {

						if( App.completeAddressWithZipCode ){

							// Get the address zip code
							var zipCode = App.loc.zipCode( theClosestAddress );
							var typed_address = $( '[name=pay-address]' ).val();

							// Check if the typed address already has the zip code
							if( typed_address.indexOf( zipCode ) < 0 ){
								var addressWithZip = typed_address + ' - ' + zipCode;
								$( '[name=pay-address]' ).val( addressWithZip );
							}
						}

						App.busy.unBusy();
						App.isDeliveryAddressOk = true;
						service.submit();
					}

				} else {
					// Address was found but it is not valid (for example it could be a city name)
					App.alert( 'Oops, it looks like your address is incomplete. \nPlease enter a street name, number and zip code.' );
					App.busy.unBusy();						
					// Make sure that the form will be visible
					$('.payment-form').show();
						$('.delivery-payment-info, .content-padder-before').hide();
					$( '[name="pay-address"]' ).focus();
					// Log the error
					App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : service.restaurant.name } , 'address not found or invalid' );
				}
			}

			// Address not found!
			var error = function() {
				App.alert( 'Oops, it looks like your address is incomplete. \nPlease enter a street name, number and zip code.' );
				App.busy.unBusy();
				// Log the error
				App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : service.restaurant.name } , 'address not found' );
			};

			// Call the geo method
			App.loc.doGeocodeWithBound(order.address, latLong, success, error);
			return;
		} 
		*/
		}

		if (order.delivery_type == 'takeout') {
			App.isDeliveryAddressOk = true;
		}
		App.isDeliveryAddressOk = true;
		if (!App.isDeliveryAddressOk) {
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if (!App.crunchSoundAlreadyPlayed) {
			App.playAudio('get-food-audio');
			App.crunchSoundAlreadyPlayed = true;
		}

		$.ajax({
			url: App.service + 'order',
			data: order,
			dataType: 'html',
			type: 'POST',
			complete: function (json) {
				try {
					json = $.parseJSON(json.responseText);
				} catch (e) {
					// Log the error
					App.log.order(json.responseText, 'processing error');
					json = {
						status: 'false',
						errors: ['Sorry! Something went horribly wrong trying to place your order!']
					};
				}

				if (json.status == 'false') {
					var error = '';
					for (x in json.errors) {
						error += json.errors[x] + "\n";
					}
					App.track('OrderError', json.errors);
					App.alert(error);
					// Log the error
					App.log.order({
						'errors': json.errors
					}, 'validation error - php');
				} else {

					if (json.token) {
						$.totalStorage('token', json.token);
					}

					$('.link-orders').show();

					order.cardChanged = false;
					App.justCompleted = true;
					App.giftcard.notesCode = false;

					var totalItems = 0;

					for (var x in service.items) {
						totalItems++;
					}

					$.getJSON('/api/config', App.processConfig);

					App.cache('Order', json.uuid, function () {
						App.track('Ordered', {
							'total': this.final_price,
							'subtotal': this.price,
							'tip': this.tip,
							'restaurant': service.restaurant.name,
							'paytype': this.pay_type,
							'ordertype': this.order_type,
							'user': this.user,
							'items': totalItems
						});

						App.order.cardChanged = false;
						App.loc.changeLocationAddressHasChanged = false;
						delete tipHasChanged;
						App.go('/order/' + this.uuid);

					});
				}
				setTimeout(function () {
					App.busy.unBusy();
				}, 400);
			}
		});

	} // end service.submit()

	/**
	 * Gets called after the cart is updarted to refresh the total
	 *
	 * @todo Gets called many times before the cart is updated, on load, and shouldn't
	 *
	 * @return void
	 */
	service.updateTotal = function(){

		// Stop runing the method if the restaurant wasn't loaded yet 
		if( !service.restaurant.id_restaurant ){
			return;
		}

		service.info.totalText = service.info.dollarSign + service.charged();

		var tipText = '',
				feesText = '',
				totalItems = 0,
				credit = 0,
				hasFees = ((service.restaurant.delivery_fee && service.form.delivery_type == 'delivery') || service.restaurant.fee_customer) ? true : false;

			if (App.credit.restaurant[service.restaurant.id]) {
				credit = parseFloat(App.credit.restaurant[service.restaurant.id]);
			}

			for (var x in service.items) {
				totalItems++;
			}
			service.autotip();

			/* If the user changed the delivery method to takeout and the payment is card
			 * the default tip will be 0%. If the delivery method is delivery and the payment is
			 * card the default tip will be autotip.
			 * If the user had changed the tip value the default value will be the chosen one.
			 */
			var wasTipChanged = false;
			if (service.form.delivery_type == 'takeout' && service.form.pay_type == 'card') {
				if (typeof tipHasChanged == 'undefined') {
					service.form.tip = 0;
					wasTipChanged = true;
				}
			} else if (service.form.delivery_type == 'delivery' && service.form.pay_type == 'card') {
				if (typeof tipHasChanged == 'undefined') {
					service.form.tip = (App.config.user.last_tip) ? App.config.user.last_tip : 'autotip';
					service.form.tip = App.lastTipNormalize(service.form.tip);
					wasTipChanged = true;
				}
			}

			if (wasTipChanged) {
				$('[name="pay-tip"]').val(service.form.tip);
				// Forces the recalculation of total because the tip was changed.
				service.info.totalText = service.info.dollarSign + this.charged();
			}

			var _total = service.restaurant.delivery_min_amt == 'subtotal' ? service.subtotal() : service.total();
			if (service.restaurant.meetDeliveryMin(_total) && service.form.delivery_type == 'delivery') {
				service.info.deliveryMinDiff = service.restaurant.deliveryDiff(_total);
			} else {
				service.info.deliveryMinDiff = '';
			}
			service.info.totalItems = service.cart.totalItems();
			service.info.extraCharges = service.extraChargesText( service.totalbreakdown() );
			service.info.breakdownDescription = service.info.dollarSign + this.subtotal().toFixed(2);
			service.info.cartSummary = service.cart.summary();

			if (App.order.pay_type == 'card' && credit > 0) {
				var creditLeft = '';
				if (this.total() < credit) {
					var creditLeft = '<span class="gift-left"> - You\'ll still have ' + service.info.dollarSign + App.ceil((credit - this.total())).toFixed(2) + ' gift card left </span>';
					credit = this.total();
				}
				$('.cart-gift').html('&nbsp;(- ' + service.info.dollarSign + App.ceil(credit).toFixed(2) + ' credit ' + creditLeft + ') ');
			} else {
				$('.cart-gift').html('');
			}

			setTimeout(function () {
				if (App.order.pay_type == 'cash' && credit > 0 /* && App.giftcard.showGiftCardCashMessage */ ) {
					$('.cart-giftcard-message').html('<span class="giftcard-payment-message">Pay with a card, NOT CASH, to use your  ' + service.info.dollarSign + App.ceil(credit).toFixed(2) + ' gift card!</span>');
				} else {
					$('.cart-giftcard-message').html('');
				}
			}, 1000);	

			/* TODO: find out what this piece of code does
			$('.cart-item-customize-price').each(function () {
				var dish = $(this).closest('.cart-item-customize').attr('data-id_cart_item'),
					option = $(this).closest('.cart-item-customize-item').attr('data-id_option'),
					cartitem = service.items[dish],
					opt = App.cached['Option'][option],
					price = opt.optionPrice(cartitem.options);

				$(this).html(service.customizeItemPrice(price));
			});
			*/
	}

	// Credit card years
	service._years = function(){
		var date = new Date().getFullYear();
		var years = [];
		for (var x = date; x <= date + 20; x++) {
			years[years.length] = x;
		}
		return years;
	}

	// Credit card months
	service._months = function(){
		return [1,2,3,4,5,6,7,8,9,10,11,12];
	}

	// Tips %
	service._tips = function(){
		return  [0,10,15,18,20,25,30];
	}

	return service;
} );



// OrdersService service
NGApp.factory('OrdersService', function ($http, $location) {

	var service = {
		list: false
	};

	service.all = function () {

		$http.get(App.service + 'user/orders', {
			cache: true
		}).success(function (json) {
			for (var x in json) {
				json[x].timeFormat = json[x]._date_tz.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i, '$1/$2 $3');
			}
			service.list = json;
		});

	}

	service.restaurant = function (permalink) {
		$location.path('/' + App.restaurants.permalink + '/' + permalink);
	};

	service.receipt = function (id_order) {
		$location.path('/order/' + id_order);
	};

	return service;

});

// OrdersService service
NGApp.factory('OrderViewService', function ( $routeParams, $location, $rootScope, FacebookService) {

	var service = {};

	service.facebook = FacebookService;

	App.cache( 'Order', $routeParams.id, function () {
		service.order = this;

		var complete = function () {
				$location.path('/');
			};

		if (!service.order.uuid) {
			if (!$rootScope.$$phase) {
				$rootScope.$apply(complete);
			} else {
				complete();
			}
			return;
		}

		service.facebook._order_uuid = service.order.uuid;
		service.facebook.preLoadOrderStatus();

		App.cache('Restaurant', service.order.id_restaurant, function () {

			service.restaurant = this;

			var complete = function () {

					if (service.order['new']) {
						setTimeout(function () {
							service.order['new'] = false;
						}, 500);
					}
				};

			if (!$rootScope.$$phase) {
				$rootScope.$apply(complete);
			} else {
				complete();
			}
		});
	});
	return service;

});
