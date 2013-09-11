//OrderService Service
NGApp.factory('OrderService', function ($http, $location, $rootScope, $filter, AccountService, CartService, LocationService, CreditService, GiftCardService, OrderViewService ) {

	var service = {};
	service.location = LocationService;
	service.account = AccountService;
	service.cart = CartService;
	service.giftcard = GiftCardService;
	service.credit = CreditService;
	service.restaurant = {};

	// Listener to user signin/signout
	$rootScope.$on( 'userAuth', function(e, data) {
		service.account = AccountService;
		service.showForm = true;
	});

	service._previousTip = 0;

	// Default values
	service.form = {
		delivery_type: 'delivery',
		pay_type: 'card',
		make_default: true
	};
	// If the user has presets this variable should be set as false
	service.showForm = true;
	service.loaded = false;
	// Info that will be shown to the user
	service.info = {
		dollarSign: '',
		breakdownDescription: '',
		extraCharges: '',
		deliveryMinDiff: '',
		cartSummary: '',
		totalText: '',
		creditLeft: ''
	}

	service.toogleDelivery = function (type) {
		if (type != service.form.delivery_type) {
			service.form.delivery_type = type;
			if( service.form.delivery_type == 'takeout' ){
				service.form.tip = 0;
			}
			service.updateTotal();
		}
	}

	service.tooglePayment = function (type) {
		if (type != service.form.pay_type) {
			service.form.pay_type = type;
			service.updateTotal();
		}
	}

	service.init = function () {

		// If this variable is true the restaurant's page will be loaded after the location get started
		service.location.loadRestaurantsPage = false;
		service.location.init();

		if (App.config.ab && App.config.ab.dollarSign == 'show') {
			service.info.dollarSign = '$';
		}
		// Tip stuff
		if (service.account.user && service.account.user.last_tip) {
			var tip = service.account.user.last_tip;
		} else {
			var tip = 'autotip';
		}
		// Some controls
		service._deliveryAddressOk = false;
		service._tipHasChanged = false;
		service._cardInfoHasChanged = false;
		service._crunchSoundPlayded = false;
		service._useRestaurantBoundingBox = false;
		service._useCompleteAddress = false; /* if true it means the address field will be fill with the address found by google api */
		service._completeAddressWithZipCode = true;

		service.form.pay_type = (service.account.user && service.account.user.pay_type) ? service.account.user.pay_type : 'card';
		// If the restaurant does not accept card
		if( service.restaurant.credit != 1 && service.form.pay_type == 'card' ){
			service.form.pay_type = 'cash';
		}
		// If the restaurant does not accept cash
		if( service.restaurant.cash != 1 && service.form.pay_type == 'cash' ){
			service.form.pay_type = 'card';
		}

		// Rules at #669
		service.form.delivery_type = (service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) ? service.account.user.presets[service.restaurant.id_restaurant].delivery_type : 'delivery';

		// If the restaurant does not delivery
		if( service.restaurant.delivery != 1 ){
			service.form.delivery_type = 'takeout';
		}
		// If the restaurant does not takeout
		if( service.restaurant.takeout != 1 && service.form.delivery_type == 'takeout' ){
			service.form.delivery_type = 'delivery';
		}

		service.form.autotip = 0;
		service.form.tip = service._lastTipNormalize(tip);
		service.form.name = service.account.user.name;
		service.form.phone = $filter( 'formatPhone' )( service.account.user.phone );
		service.form.address = service.account.user.address;
		service.form.notes = ( service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant] && service.account.user.presets[service.restaurant.id_restaurant].notes ) ? service.account.user.presets[service.restaurant.id_restaurant].notes : '';

		if( service.form.delivery_type == 'takeout' ){
			service.form.tip = 0;
		}

		if( service.giftcard.code ){
			service.form.notes += ' '	+ service.giftcard.code;
		}

		// Credit card stuff
		service.form.cardNumber = service.account.user.card;
		service.form.cardMonth = ( service.account.user.card_exp_month ) ? service.account.user.card_exp_month : '';
		service.form.cardYear = ( service.account.user.card_exp_year ) ? service.account.user.card_exp_year : '';
		service.updateTotal();

		// If the user has presets at other's restaurants but he did not typed his address yet
		// and the actual restaurant is a delivery only #875
		if ( service.account.user && ( service.form.delivery_type == 'takeout' || ( service.form.delivery_type == 'delivery' && service.account.user.address ) ) ) {
			service.showForm = false;
		} else {
			service.showForm = true;
		}

		// Load the order
		if (service.cart.hasItems()) {
			service.reloadOrder();
			// Load user presets
		} else if (service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) {
			try {
				service.loadOrder(service.account.user.presets[service.restaurant.id_restaurant]);
			} catch (e) {
				if( service.restaurant.preset ){
					service.loadOrder(service.restaurant.preset());	
				}
			}
		} else {
			if( service.restaurant.preset ){
				service.loadOrder(service.restaurant.preset());	
			}
		}
		service.loaded = true;
		$rootScope.$broadcast( 'orderLoaded',  true );
	}
	service.reloadOrder = function () {
		var cart = service.cart.getCart();
		service.cart.reset()
		service.loadFlatOrder(cart);
	}
	service.loadFlatOrder = function (cart) {
		for (var x in cart) {
			service.cart.add(cart[x].id, {
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
						service.cart.add(dishes[x].id_dish, {
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
				return parseFloat( service.form.autotip );
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
		var credit = parseFloat(service.credit.value);
		if (service.form.pay_type == 'card' && credit) {
			finalAmount = finalAmount - credit;
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

	service.submit = function( forceAddressOk ){
		if( forceAddressOk ){
			service._deliveryAddressOk = true;	
		} else {
			service._deliveryAddressOk = false;
		}
		service.processOrder();
	}
	
	service.errors = function(errors) {
		var error = '';
		for (var x in errors) {
			error += '<li><i class="icon-li icon-warning-sign"></i>' + errors[x] + '</li>';
		}
		App.alert('<ul class="icons-ul">' + error + '</ul>');
	}

	/**
	 * Submits the cart order
	 *
	 * @returns void
	 */
	service.processOrder = function () {
		if (App.busy.isBusy()) {
			return;
		}

		App.busy.makeBusy();

		if( service.form.address && service.form.address != '' ){
			service.form.address = service.location.ordinalReplace( service.form.address );	
		}

		var order = {
			address: service.form.address,
			phone: service.form.phone,
			name: service.form.name,
			cart: service.cart.getCart(),
			pay_type: service.form.pay_type,
			delivery_type: service.form.delivery_type,
			restaurant: service.restaurant.id,
			make_default: service.form.make_default,
			notes: service.form.notes,
			lat: service.location.position.pos().lat(),
			lon: service.location.position.pos().lon()
		};

		if (order.pay_type == 'card') {
			order.tip = service.form.tip;
			order.autotip_value = service.form.autotip;
		}

		if (service._cardInfoHasChanged) {
			order.card = {
				number: service.form.cardNumber,
				month: service.form.cardMonth,
				year: service.form.cardYear
			};
		} else {
			order.card = {};
		}
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
		if (order.pay_type == 'card' && ((service._cardInfoHasChanged && !order.card.number) || (!service.account.user.id_user && !order.card.number))) {
			errors['card'] = 'Please enter a valid card #.';
		}
		/*if (order.pay_type == 'card' && service._cardInfoHasChanged && ( order.card.month == '' || !order.card.month ) ) {
			errors['card_month'] = 'Please enter the card expiration month.';
		}
		if (order.pay_type == 'card' && service._cardInfoHasChanged && ( order.card.year == '' || !order.card.year ) ) {
			errors['card_year'] = 'Please enter the card expiration year.';
		}*/
		if (!service.cart.hasItems()) {
			errors['noorder'] = 'Please add something to your order.';
		}

		var _total = service.restaurant.delivery_min_amt == 'subtotal' ? service.subtotal() : service.total();
		if (service.restaurant.meetDeliveryMin(_total) && service.form.delivery_type == 'delivery') {
			errors['delivery_min'] = 'Please meet the delivery minimum of ' + service.info.dollarSign + service.restaurant.delivery_min + '.';
		}

		if (!$.isEmptyObject(errors)) {
			$('body').scrollTop($('.payment-form').position().top - 80);
			service.errors(errors);
			App.busy.unBusy();

			App.track('OrderError', errors);
			// Log the error
			App.log.order({
				'errors': errors
			}, 'validation error');
			return;
		}
		// Play the crunch audio just once, when the user clicks at the Get Food button
		if (App.iOS() && !service._crunchSoundPlayded) {
			App.playAudio('get-food-audio');
			service._crunchSoundPlayded = true;
		}
		// if it is a delivery order we need to check the address
		if (order.delivery_type == 'delivery') {
			// Correct Legacy Addresses in Database to Avoid Screwing Users #1284
			// If the user has already ordered food 
			if (service.account && service.account.user && service.account.user.last_order) {
				// Check if the order was made at this community
				if (service.account.user.last_order.communities.indexOf(service.restaurant.id_community) > -1) {
					// Get the last address the user used at this community
					var lastAddress = service.account.user.last_order.address;
					var currentAdress = service.form.address;
					// Make sure the the user address is the same of his last order
					if ($.trim(lastAddress) != '' && $.trim(lastAddress) == $.trim(currentAdress)) {
						service._deliveryAddressOk = true;
						// Log the legacy address
						App.log.order({
							'address': lastAddress,
							'restaurant': service.restaurant.name
						}, 'legacy address');
					}
				}
			}

			// Check if the user address was already validated
			if (!service._deliveryAddressOk) {

				if (service.location.bounding) {
					var latLong = new google.maps.LatLng( service.location.bounding.lat, service.location.bounding.lon );
				}
				
				// Use the restautant's position to create the bounding box - just for tests only
				if (service._useRestaurantBoundingBox) {
					var latLong = new google.maps.LatLng( service.restaurant.loc_lat, service.restaurant.loc_long );
				}
				
				if (!latLong) {
					App.busy.unBusy();
					App.dialog.show( '.address-not-found-warning' );
					return;
				}

				var success = function (results) {
					// Get the closest address from that lat/lng
					var theClosestAddress = service.location.theClosestAddress(results, latLong);
					var isTheAddressOk = service.location.validateAddressType(theClosestAddress.result);
					if (isTheAddressOk) {
						theClosestAddress = theClosestAddress.location;
						if( service.form.address != theClosestAddress.formatted() ){
							theClosestAddress.setEntered( service.form.address );
						}
						// Now lets check if the restaurant deliveries at the given address
						var lat = theClosestAddress.lat();
						var lon = theClosestAddress.lon();
						if( service._useCompleteAddress ){
							service.form.address = theClosestAddress.formatted();
							order.address = service.form.address;
						}

						var distance = service.location.distance( { from : { lat : lat, lon : lon }, to : { lat : service.restaurant.loc_lat, lon : service.restaurant.loc_long } } );
						distance = service.location.km2Miles( distance );

						if (!service.restaurant.deliveryHere(distance)) {
							App.alert('Sorry, you are out of delivery range or have an invalid address. <br>Please check your address, or order takeout.');
							
							App.busy.unBusy();

							$rootScope.$safeApply( function(){
								// Make sure that the form will be visible
								service.showForm = true;
								$('[name="pay-address"]').focus();
								// Write the found address at the address field, so the user can check it.
								service.form.address = theClosestAddress.formatted();
							} );

							// Log the error
							App.log.order({
								'address': $('[name=pay-address]').val(),
								'restaurant': service.restaurant.name
							}, 'address out of delivery range');
							return;

						} else {
							if (service._completeAddressWithZipCode) {
								// Get the address zip code
								var zipCode = theClosestAddress.zip();
								var typed_address = service.form.address;
								// Check if the typed address already has the zip code
								if ( typed_address.indexOf(zipCode) < 0 ) {
									var addressWithZip = typed_address + ' - ' + zipCode;
									service.form.address = addressWithZip;
								}
							}
							App.busy.unBusy();
							service._deliveryAddressOk = true;
							service.processOrder();
						}
					} else {
						// Address was found but it is not valid (for example it could be a city name)
						App.alert('Oops, it looks like your address is incomplete. <br>Please enter a street name, number and zip code.');
						App.busy.unBusy();
						// Make sure that the form will be visible
						$rootScope.$safeApply( function(){
							service.showForm = true;
							$('[name="pay-address"]').focus();
						} );

						// Log the error
						App.log.order({
							'address': $('[name=pay-address]').val(),
							'restaurant': service.restaurant.name
						}, 'address not found or invalid');
					}
				}
				// Address not found!
				var error = function () {
					App.alert('Oops, it looks like your address is incomplete. <br>Please enter a street name, number and zip code.');
					App.busy.unBusy();
					// Log the error
					App.log.order({
						'address': $('[name=pay-address]').val(),
						'restaurant': service.restaurant.name
					}, 'address not found');
				};
				// Call the geo method
				service.location.doGeocodeWithBound( order.address, latLong, success, error );
				return;
			}
		}

		if (order.delivery_type == 'takeout') {
			service._deliveryAddressOk = true;
		}

		if (!service._deliveryAddressOk) {
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if (!service._crunchSoundPlayded) {
			App.playAudio('get-food-audio');
			service._crunchSoundPlayded = true;
		}

		var url = App.service + 'order';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( order),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( json ) {
				try {
					if( json.uuid ){
						var uuid = json.uuid;
					} else {
						App.log.order(json, 'processing error');	
						if( !json.errors ){
							json = {
								status: 'false',
								errors: ['Sorry! Something went horribly wrong trying to place your order! <br/> Please make sure your credit card info is correct!']
							};	
						}
						$rootScope.$broadcast( 'orderProcessingError', true );
					}
				} catch (e) {
					App.log.order(json, 'processing error');
					json = {
						status: 'false',
						errors: ['Sorry! Something went horribly wrong trying to place your order! <br/> Please make sure your credit card info is correct!']
					};
					$rootScope.$broadcast( 'orderProcessingError', true );
				}
				if (json.status == 'false') {
					service.errors(json.errors);
					App.track('OrderError', json.errors);
					// Log the error
					App.log.order({
						'errors': json.errors
					}, 'validation error - php');
				} else {
					if (json.token) {
						$.cookie( 'token', json.token );
					}

					service.account.updateInfo();
					App.cache('Order', json.uuid, function () {
						App.track('Ordered', {
							'total': this.final_price,
							'subtotal': this.price,
							'tip': this.tip,
							'restaurant': service.restaurant.name,
							'paytype': this.pay_type,
							'ordertype': this.order_type,
							'user': this.user,
							'items': service.cart.totalItems()
						});
						// Clean the cart
						service.cart.clean();
						// Resets the gift card notes field
						service.giftcard.notes_field.reset();
						$rootScope.$safeApply( function(){
							$rootScope.$broadcast( 'newOrder' );
							OrderViewService.newOrder = true;
							$location.path( '/order/' + uuid );	
						} );
					});
				}
				setTimeout(function () {
					App.busy.unBusy();
				}, 400);
			}	);
	} // end service.processOrder

	service.tipChanged = function () {
		service._tipHasChanged = true;
		service._previousTip = service.form.tip;
		service.updateTotal();
	}
	service.cardInfoChanged = function () {
		service._cardInfoHasChanged = true;
	}
	/**
	 * Gets called after the cart is updarted to refresh the total
	 *
	 * @todo Gets called many times before the cart is updated, on load, and shouldn't
	 *
	 * @return void
	 */
	service.updateTotal = function () {

		// Stop runing the method if the restaurant wasn't loaded yet 
		if (!service.restaurant.id_restaurant) {
			return;
		}
		service.info.totalText = service.info.dollarSign + service.charged();
		var tipText = '',
			feesText = '',
			totalItems = 0,
			credit = parseFloat(service.credit.value),
			hasFees = ((service.restaurant.delivery_fee && service.form.delivery_type == 'delivery') || service.restaurant.fee_customer) ? true : false;

		for (var x in service.items) {
			totalItems++;
		}
		service._autotip();
		/* If the user changed the delivery method to takeout and the payment is card
		 * the default tip will be 0%. If the delivery method is delivery and the payment is
		 * card the default tip will be autotip.
		 * If the user had changed the tip value the default value will be the chosen one.
		 */
		var wasTipChanged = false;
		if (service.form.delivery_type == 'takeout' && service.form.pay_type == 'card') {
			wasTipChanged = true;
		} else if (service.form.delivery_type == 'delivery' && service.form.pay_type == 'card') {
			if (!service._tipHasChanged) {
				service.form.tip = (service.account.user.last_tip) ? service.account.user.last_tip : 'autotip';
				service.form.tip = service._lastTipNormalize(service.form.tip);
				wasTipChanged = true;
			} else {
				service.form.tip = service._previousTip;
				wasTipChanged = true;
			}
		}
		if (wasTipChanged) {
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
		service.info.extraCharges = service.extraChargesText(service.totalbreakdown());
		service.info.breakdownDescription = service.info.dollarSign + this.subtotal().toFixed(2);
		service.info.cartSummary = service.cart.summary();
		if (service.form.pay_type == 'card' && credit > 0) {
			service.info.creditLeft = '';
			if (this.total() < credit) {
				service.info.creditLeft = App.ceil((credit - this.total())).toFixed(2);
				credit = this.total();
			}
		} 
	}
	service._autotip = function () {
		var subtotal = service.totalbreakdown().subtotal;
		var autotipValue
		if (subtotal === 0) {
			autotipValue = 0;
		} else {
			// autotip formula - see github/#940
			autotipValue = Math.ceil(4 * (subtotal * 0.107 + 0.85)) / 4;
		}
		service.form.autotip = autotipValue;
	}
	service._autotipText = function () {
		var autotipText = service.form.autotip ? ' (' + service.info.dollarSign + service.form.autotip + ')' : '';
		return 'Autotip' + autotipText;
	}
	// Credit card years
	service._years = function () {
		var years = [];
		years.push({
			value: '',
			label: 'Year'
		});
		var date = new Date().getFullYear();
		for (var x = date; x <= date + 20; x++) {
			years.push({
				value: x.toString(),
				label: x.toString()
			});
		}
		return years;
	}
	// Credit card months
	service._months = function () {
		var months = [];
		months.push({
			value: '',
			label: 'Month'
		});
		for (var x = 1; x <= 12; x++) {
			months.push({
				value: x.toString(),
				label: x.toString()
			});
		}
		return months;
	}
	// Tips
	service._tips = function () {
		var tips = [];
		tips.push({
			value: 'autotip',
			label: service._autotipText()
		});
		tips.push({
			value: 0,
			label: 'Tip with cash'
		});
		var _tips = [10, 15, 18, 20, 25, 30];
		for (var x in _tips) {
			tips.push({
				value: _tips[x],
				label: 'tip ' + _tips[x] + ' %'
			});
		}
		return tips;
	}
	service._lastTipNormalize = function (lastTip) {
		/* The default tip is autotip */
		if (lastTip === 'autotip') {
			return lastTip;
		}
		if (service.account.user && service.account.user.last_tip_type && service.account.user.last_tip_type == 'number') {
			return 'autotip';
		}
		// it means the last tipped value is not at the permitted value, return default.
		lastTip = parseInt(lastTip);
		var tips = service._tips();
		for (x in tips) {
			if (lastTip == parseInt(tips[x].value)) {
				return lastTip;
			}
		}
		return 'autotip';
	}

	service._test = function(){
		$rootScope.$safeApply( 
			function(){
			service._useRestaurantBoundingBox = true;
			service.form.name = 'MR TEST';
			service.form.phone = '***REMOVED***';
			service.form.address = '123 main';
			service.form.cardNumber = '4242424242424242';
			service.form.cardMonth = '2';
			service.form.cardYear = '2016';
			service.form.tip = 'autotip';
			service.tooglePayment( 'card' );
			// Add one dish of each category
			if( !service._askedAboutFood ){
				service._askedAboutFood = true;
				if( confirm( 'Add some food?' ) ){
					var categories = service.restaurant.categories()
					for( x in categories ){
						category = categories[ x ];
						var dishes = category.dishes();
						for( y in dishes ){
							var dish = dishes[ y ];
							service.cart.add( dish.id_dish );
						}
					}
				}
			}
			$rootScope.$broadcast( 'creditCardInfoChanged', true  );
		});
	}

	return service;
});
// OrdersService service
NGApp.factory('OrdersService', function ($http, $location, $rootScope, RestaurantsService, OrderViewService) {
	var service = {
		list: false,
		reload : true
	};

	var restaurants = RestaurantsService;

	service.load = function () {
		if( service.list && !service.reload ){
			return service.list;
		}
		OrderViewService.newOrder = false;
		console.log('OrderViewService',OrderViewService.newOrder);
		list = false;
		service.list = list;
		$http.get(App.service + 'user/orders', {
			cache: false
		}).success(function (json) {
			service.reload = false;
			if( json ){
				for (var x in json) {
					json[x].timeFormat = json[x]._date_tz.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i, '$1/$2 $3');	
				}	
				list = json;
			} else {
				// User has no orders
				list = true;	
			}
			service.list = list;
			$rootScope.$broadcast( 'OrdersLoaded', service.list );
		});
	}

	service.restaurant = function (permalink) {
		$location.path('/' + restaurants.permalink + '/' + permalink);
	};

	service.receipt = function (id_order) {
		$location.path('/order/' + id_order);
	};

	// Reload the orders list
	$rootScope.$on( 'userAuth', function(e, data) {
		service.reload = true;
	});

	// Reload the orders list
	$rootScope.$on( 'newOrder', function(e, data) {
		service.reload = true;
	});

	return service;
});

// OrdersService service
NGApp.factory('OrderViewService', function ($routeParams, $location, $rootScope, $http, FacebookService) {

	var service = { order : false, reload : true, newOrder : false };

	service.facebook = FacebookService;

	service.load = function(){

		var url = App.service + 'order/' + $routeParams.id;

		var error = function(){
			$location.path('/');
		}

		$http( {
			method: 'GET',
			url: url,
			cache: true
			} ).success( function( data ) {
				service.order = data;
				if ( service.order.uuid ) {
					service.order._final_price = parseFloat( service.order.final_price ).toFixed(2);

					if( service.order.credit ){
						service.order._credit = parseFloat( service.order.credit ).toFixed(2);
					}

					if( service.newOrder ){
						service.order.new = service.newOrder;
					} else {
						service.order.new = false;
					}

					service.facebook._order_uuid = service.order.uuid;
					service.facebook.preLoadOrderStatus();
					
					$rootScope.$broadcast( 'OrderViewLoadedOrder', service.order );

				} else {
					error();
				}
			} ).error( function( data ) {
				error();
			});
	}
	return service;
});