//OrderService Service
NGApp.factory( 'OrderService', function ($http, $location, $rootScope, $filter, AccountService, CartService, LocationService, CreditService, GiftCardService, OrderViewService , MainNavigationService) {

	var service = {};
	service.location = LocationService;
	service.account = AccountService;
	service.cart = CartService;
	service.giftcard = GiftCardService;
	service.credit = CreditService;
	service.restaurant = {};
	service.startStoreEntederInfo = false;
	service.geomatched = 1;

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
		// remove $ signs from prices #4370
		// if (App.config.ab && App.config.ab.dollarSign == 'show') {
		// service.info.dollarSign = '$';
		// }
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
		service._useCompleteAddress = false; // if true it means the address field will be fill with the address found by google api
		service._completeAddressWithZipCode = true;

		service.form.pay_type = (service.account.user && service.account.user.pay_type) ? service.account.user.pay_type : 'card';
		// If the restaurant does not accept card
		if( !service.restaurant.credit && service.form.pay_type == 'card' ){
			service.form.pay_type = 'cash';
		}
		// If the restaurant does not accept cash
		if( !service.restaurant.cash && service.form.pay_type == 'cash' ){
			service.form.pay_type = 'card';
		}

		service.campus_cash = false;

		service.form.campus_cash_delivery_on_campus_confirmation = false;

		if( service.restaurant.campus_cash ){
			service.campus_cash = { name: service.restaurant.campus_cash_name, fee: service.restaurant.campus_cash_fee };
			if( service.restaurant.campus_cash_delivery_on_campus_confirmation ){
				service.form.campus_cash_delivery_on_campus_confirmation = true;
			}
		}

		if( service.campus_cash && service.account.user && service.account.user.card_type == 'campus_cash'  ){
			service.form.pay_type = 'campus_cash';
		}

		// Rules at #669
		service.form.delivery_type = (service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant]) ? service.account.user.presets[service.restaurant.id_restaurant].delivery_type : 'delivery';

		// If the restaurant does not delivery
		if( !service.restaurant.delivery ){
			service.form.delivery_type = 'takeout';
		}
		// If the restaurant does not takeout
		if(!service.restaurant.takeout && service.form.delivery_type == 'takeout' ){
			service.form.delivery_type = 'delivery';
		}

		// Force the takeout verification
		if(!service.restaurant.takeout){
			service.form.delivery_type = 'delivery';
		}

		service.form.autotip = 0;
		service.form.tip = service._lastTipNormalize(tip);
		service.form.name = service.account.user.name;
		service.form.email = service.account.user.email;
		service.form.phone = $filter( 'formatPhone' )( service.account.user.phone );
		service.form.address = service.account.user.address;
		// Use the last notes #2102
		service.form.notes = ( service.account.user && service.account.user.last_notes ) ? service.account.user.last_notes : '';
		// service.form.notes = ( service.account.user && service.account.user.presets && service.account.user.presets[service.restaurant.id_restaurant] && service.account.user.presets[service.restaurant.id_restaurant].notes ) ? service.account.user.presets[service.restaurant.id_restaurant].notes : '';

		if( service.form.delivery_type == 'takeout' ){
			service.form.tip = 0;
		}

		if( service.giftcard.code ){
			service.form.notes += ' '	+ service.giftcard.code;
		}

		// Credit card stuff
		service.form.cardNumber = service.account.user.card;
		service.form.cardMonth = ( service.account.user.card_exp_month ) ? ( service.account.user.card_exp_month ).toString() : '';
		service.form.cardYear = ( service.account.user.card_exp_year ) ? ( service.account.user.card_exp_year ).toString() : '';

		// Campus cash stuff
		service.form.campusCash = '';

		service.updateTotal();
		if( !service.account.user.id_user ){
			var userEntered = $.totalStorage( 'userEntered' );
			if( userEntered ){
				service.form.name = ( userEntered.name && userEntered.name != '') ? userEntered.name : service.form.name ;
				service.form.phone = $filter( 'formatPhone' )( ( userEntered.phone && userEntered.phone != '') ? userEntered.phone : service.form.phone ) ;
				service.form.address = ( userEntered.address && userEntered.address != '') ? userEntered.address : service.form.address ;
				service.form.notes = ( userEntered.notes && userEntered.notes != '' ) ? userEntered.notes : service.form.notes ;
				service.form.delivery_type = ( userEntered.delivery_type && userEntered.delivery_type != '') ? userEntered.delivery_type : service.form.delivery_type;
				service.form.pay_type = ( userEntered.pay_type && userEntered.pay_type != '') ? userEntered.pay_type : service.form.pay_type ;
				service.form.cardMonth = ( userEntered.cardMonth && userEntered.cardMonth !== '') ? userEntered.cardMonth : service.form.cardMonth;
				service.form.cardMonth = ( service.form.cardMonth ).toString();
				service.form.cardYear = ( userEntered.cardYear && userEntered.cardYear != '') ? userEntered.cardYear : service.form.cardYear ;
				service.form.cardYear = ( service.form.cardYear ).toString();
				if( service.form.cardMonth == '' ){
					service.form.cardMonth = '-';
				}
				if( service.form.cardYear == '' ){
					service.form.cardYear = '-';
				}
				if( userEntered.tip && userEntered.tip != '' ){
					var _tip = userEntered.tip;
					setTimeout(function() {
						service.form.tip = _tip;
						service.tipChanged();
						service.updateTotal();
					}, 10 );
				} else {
					service.updateTotal();
				}
			} else {
				if( service.form.cardMonth == '' ){
					service.form.cardMonth = '-';
				}
				if( service.form.cardYear == '' ){
					service.form.cardYear = '-';
				}
			}
			service.startStoreEntederInfo = true;
		}

		// check if the payment type default should be campus cash
		if( service.restaurant.campus_cash_default_payment ){
			service.form.pay_type = 'campus_cash';
		}

		// If the restaurant does not delivery
		if(!service.restaurant.delivery){
			service.form.delivery_type = 'takeout';
		}
		// If the restaurant does not takeout
		if(!service.restaurant.takeout && service.form.delivery_type == 'takeout' ){
			service.form.delivery_type = 'delivery';
		}

		// Force the takeout verification
		if(!service.restaurant.takeout){
			service.form.delivery_type = 'delivery';
		}

		// If the user has presets at other's restaurants but he did not typed his address yet
		// and the actual restaurant is a delivery only #875
		if ( service.account.user && ( service.form.delivery_type == 'takeout' || ( service.form.delivery_type == 'delivery' && service.account.user.address ) ) ) {
			service.showForm = false;
		} else {
			service.showForm = true;
		}

		if( service.form.pay_type == 'campus_cash' ){
			service.showForm = true;
		}

		if( service.form.pay_type == 'card' && !service.account.user.card ){
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
	service.resetCart = function(){
		service.cart.reset()
	}
	service.reloadOrder = function () {
		var cart = service.cart.getCart();
		service.resetCart();
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
			var delivery_fee = breakdown.delivery;
			// make customer fee display on front end in the regular fee #5597
			if (breakdown.fee) {
				delivery_fee = delivery_fee + breakdown.fee;
				elements.push(service.info.dollarSign + delivery_fee.toFixed(2) + ' delivery fee');
			} else {
				elements.push(service.info.dollarSign + delivery_fee.toFixed(2) + ' delivery');
			}
		} else {
			if (breakdown.fee) {
				elements.push(service.info.dollarSign + breakdown.fee.toFixed(2) + ' service fee');
			}
		}

		if (breakdown.taxes) {
			elements.push(service.info.dollarSign + breakdown.taxes.toFixed(2) + ' taxes');
		}
		if (breakdown.tip && breakdown.tip > 0) {
			elements.push(service.info.dollarSign + breakdown.tip.toFixed(2) + ' tip');
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
	// remove the markup at subtotal
	service.subtotalWithoutMarkup = function () {
		return service.cart.subtotalWithoutMarkup();
	}
	/**
	 * delivery cost
	 *
	 * @return float
	 */
	service._breackDownDelivery = function () {
		if( ( service.form.pay_type == 'card' || service.form.pay_type == 'campus_cash'  ) && service._removeDeliveryFee ){
			return 0;
		}

		var delivery = 0;
		if (service.restaurant.delivery_fee && service.form.delivery_type == 'delivery') {
			delivery = parseFloat(service.restaurant.delivery_fee);
		}

		if( ( service.form.pay_type == 'card' || service.form.pay_type == 'campus_cash' ) &&
				service && service.account &&
				service.account.user.points &&
				service.account.user.points.free_delivery_message &&
				service.restaurant.delivery_service ){
			delivery = 0;
		}
		delivery = App.ceil(delivery);
		return delivery;
	}

	service.removeDeliveryFee = function(){
		service._removeDeliveryFee = true;
		service._breackDownDelivery();
	}

	service.restoreDeliveryFee = function(){
		service._removeDeliveryFee = false;
		service._breackDownDelivery();
	}

	/**
	 * Crunchbutton service
	 *
	 * @return float
	 */
	service._breackDownFee = function (feeTotal) {
		var fee = 0;

		if (service.restaurant.fee_customer) {
			fee += (feeTotal * (parseFloat(service.restaurant.fee_customer) / 100));
		}

		if( service.form.pay_type == 'campus_cash' && service.campus_cash && service.campus_cash.fee ){
			fee += (feeTotal * (parseFloat(service.campus_cash.fee) / 100));
		}

		fee = App.ceil(fee);
		// Issue - #5671
		if( ( service.form.pay_type == 'card' || service.form.pay_type == 'campus_cash' ) &&
			service && service.account &&
			service.account.user.points &&
			service.account.user.points.free_delivery_message ){
			fee = 0;
		}

		return fee;
	}
	service._breackDownTaxes = function (feeTotal) {
		var taxes = (feeTotal * (service.restaurant.tax / 100));
		// removed App.ceil - see #2613
		// taxes = App.ceil(taxes);
		return taxes;
	}
	service._breakdownTip = function (total) {
		var tip = 0;
		if (service.form.pay_type == 'card' || service.form.pay_type == 'campus_cash') {
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
		if (service.form.pay_type == 'card' || service.form.pay_type == 'campus_cash' && credit) {
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
		var totalWithoutMarkup = this.subtotalWithoutMarkup();
		var feeTotal = total;
		elements['subtotal'] = this.subtotal();
		elements['subtotalWithoutMarkup'] = this.subtotalWithoutMarkup();
		elements['delivery'] = this._breackDownDelivery();
		feeTotal += elements['delivery'];
		elements['fee'] = this._breackDownFee( feeTotal );
		feeTotal += elements['fee'];
		/* 	- taxes should be calculated using the price without markup
				- if restaurant uses 3rd party delivery service remove the delivery_fee
				- see #2236 and #2248 */

		// Check if the restaurant uses 3rd party delivery if it not, add the delivery fee
		if(service.restaurant.delivery_service){
			totalWithoutMarkup += elements[ 'delivery' ];
		}
		// Caculate the tax using the total without the marked up prices
		elements['taxes'] = this._breackDownTaxes( totalWithoutMarkup );
		// The tip will use as base the total price (with the markup)
		elements['tip'] = this._breakdownTip(total);

		return elements;
	}

	service.submit = function( forceAddressOk, geomatchedError ){
		if( forceAddressOk ){
			service._deliveryAddressOk = true;
		} else {
			service._deliveryAddressOk = false;
		}
		if( geomatchedError ){
			service.geomatched = null;
		}
		service.processOrder();
	}

	service.checkout = function() {
		service.scrollToForm();
	}

	service.scrollToForm = function() {
		if( $('.payment-form') && $('.payment-form').offset() ){
			var walkTo = $('.snap-content-inner').scrollTop() + $('.payment-form').offset().top - 60;
			$('html, body, .snap-content-inner').animate({scrollTop: walkTo }, 100, $.easing.easeInOutQuart ? 'easeInOutQuart' : null);
		}
	}

	service.errors = function(errors) {
		console.error('Order posting errors:', errors);
		var error = '';
		for (var x in errors) {
			if ( x == 'set-processor') {
				App.config.processor.type = errors[x];
				if (App.config.processor.type == 'stripe') {
					Stripe.setPublishableKey(App.config.processor.stripe);
				}
				continue;
			}
			if( x != 'debug'){
				error += '<li><i class="icon-li icon-warning-sign"></i>' + errors[x] + '</li>';
			}
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

		// see #3086
		service.local_gid = App.guid();

		App.busy.makeBusy( service.local_gid );

		if( service.form.address && service.form.address != '' ){
			service.form.address = service.location.ordinalReplace( service.form.address );
		}

		var order = {
			address: service.form.address,
			email: service.form.email,
			phone: service.form.phone,
			name: service.form.name,
			cart: service.cart.getCart(),
			pay_type: service.form.pay_type,
			delivery_type: service.form.delivery_type,
			restaurant: service.restaurant.id,
			make_default: service.form.make_default,
			notes: service.form.notes,
			lat: service.location.position.pos().lat(),
			lon: service.location.position.pos().lon(),
			local_gid : service.local_gid,
			geomatched : service.geomatched
		};

		if (order.pay_type == 'card' || order.pay_type == 'applepay' || order.pay_type == 'campus_cash') {
			order.tip = service.form.tip;
			order.autotip_value = service.form.autotip;
		}

		var displayErrors = function(errors) {
			if (!$.isEmptyObject(errors)) {
				service.scrollToForm();
				service.errors(errors);
				App.busy.unBusy();

				App.track('OrderError', errors);
				// Log the error
				App.log.order({
					'errors': errors
				}, 'validation error');
				return true;
			}
			return false;
		}

		var errors = {};
		if (!order.name) {
			errors['name'] = 'Please enter your name.';
		}

		if (!App.phone.validate(order.phone)) {
			errors['phone'] = 'Please enter a valid phone #.';
		}
		if ( order.email && !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( order.email ) ){
			errors['email'] = 'Please enter a valid email.';
		}
		if (order.delivery_type != 'delivery' && order.delivery_type != 'takeout') {
			errors['delivery'] = 'Please select the delivery method.';
		}
		if (order.delivery_type == 'delivery' && !order.address) {
			errors['address'] = 'Please enter an address.';
		}
		if (order.pay_type == 'card' && ((service._cardInfoHasChanged && !service.form.cardNumber) || (!service.account.user.id_user && !service.form.cardNumber) || (!service.form.cardNumber) ) ) {
			errors['card'] = 'Please enter a valid card #.';
		}
		if (order.pay_type == 'card' && ((service._cardInfoHasChanged && !service.form.cardMonth) || (!service.account.user.id_user && !service.form.cardMonth) || (!service.form.cardMonth) || service.form.cardMonth == '-' ) ) {
			errors['card_month'] = 'Please enter the card expiration month.';
		}
		if (order.pay_type == 'card' && ((service._cardInfoHasChanged && !service.form.cardYear) || (!service.account.user.id_user && !service.form.cardYear) || (!service.form.cardYear) || service.form.cardYear == '-' ) ) {
			errors['card_year'] = 'Please enter the card expiration year.';
		}
		if (order.pay_type == 'campus_cash' && !service.form.campusCash ) {
			errors['campus_cash'] = 'Please enter the ' + service.campus_cash.name + '.';
		}
		if (order.pay_type == 'campus_cash' && service.form.campus_cash_delivery_on_campus_confirmation && !service.form.address_campus ) {
			errors['address_campus'] = 'You must certify that the address listed for delivery is located on campus.';
		}
		if (!service.cart.hasItems()) {
			errors['noorder'] = 'Please add something to your order.';
		}

		var _total = service.restaurant.delivery_min_amt == 'subtotal' ? service.subtotal() : service.total();
		if (service.restaurant.meetDeliveryMin(_total) && service.form.delivery_type == 'delivery') {
			errors['delivery_min'] = 'Please meet the delivery minimum of $' + service.restaurant.delivery_min + '.';
		}

		var er = displayErrors(errors);
		if (er) {
			return;
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

				if (service.location.bounding && google && google.maps && google.maps.LatLng ) {
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

					var isTheAddressOk = service.restaurant.id_restaurant == 26 ? true : service.location.validateAddressType(theClosestAddress.result);
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
							App.busy.unBusy();
							App.dialog.show( '.address-out-of-range-warning' );

							App.busy.unBusy();

							$rootScope.$safeApply( function(){
								// Make sure that the form will be visible
								service.showForm = true;
								if (!App.isPhoneGap) {
									$('[name="pay-address"]').focus();
								}
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
						App.dialog.show( '.address-incomplete-warning' );
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
					App.dialog.show( '.address-incomplete-warning' );
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

		var processOrder = function(card) {
			if (card === false) {
				// nada
			} else if (!card.status) {
				var er = displayErrors({
					process: card.error
				});
				return;

			} else {
				order.card = {
					id: card.id,
					uri: card.uri,
					lastfour: card.lastfour,
					card_type: card.card_type,
					month: card.month,
					year: card.year
				};
			}

			// Play the crunch audio just once, when the user clicks at the Place Order button
			if (!service._crunchSoundPlayded) {
				App.playAudio('crunch');
				service._crunchSoundPlayded = true;
			}

			// Clean the phone string
			order.phone = order.phone.replace(/-/g, '');

			// Only use redeemed points if the user knows about them #4851
			order.use_delivery_points = true;

			var processor = ( App.config.processor && App.config.processor.type ) ? App.config.processor.type : false;
			order.processor = processor;


			if (order.pay_type == 'campus_cash' ) {
				order.campusCash = service.form.campusCash;
			}

			var url = App.service + 'order';

			$http( {
				method: 'POST',
				url: url,
				data: $.param( order ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				} ).success( function( json ) {
					try {
						if( json.uuid ){
							var uuid = json.uuid;
						} else {
							console.error('Error',json);
							if (json && json.errors && json.errors[0]) {
								console.error('Credit Card processing Error:', json.errors[0]);
							}
							App.log.order(json, 'processing error');
							if( !json.errors ){
								json = {
									status: 'false',
									errors: ['Sorry! An error has occurred trying to place your order! <br/> Please make sure your credit card info is correct!']
								};
							}
							$rootScope.$broadcast( 'orderProcessingError', true );
						}
					} catch (e) {
						console.error('Exception',e,json);
						App.log.order(json, 'processing error');
						json = {
							status: 'false',
							errors: ['Sorry! An error has occurred trying to place your order! <br/> Please make sure your credit card info is correct!']
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

						// order success
						App.vibrate();

						MainNavigationService.navStack = [];
						MainNavigationService.control();

						if (json.token) {
							$.totalStorage( 'token', json.token );
						}

						// Clean the user entered info
						$.totalStorage( 'userEntered', null );
						service.startStoreEntederInfo = false;

						service.account.updateInfo();

						var orderCached = false;

						var cacheOrder = function(){

							App.cache('Order', json.uuid, function () {

								if (orderCached) {
									return;
								}
								var id_user;
								var user = service.account.user;
								if (user) {
									id_user = user.id_user;
								} else{
									id_user = "Unknown";
								}
								orderCached = true;

								var shouldTrack = true;

								if( service.restaurant.name.search(/test/i) >= 0 ||
									service.form.name.search(/test/i) >= 0 ){
									shouldTrack = false;
								}

								if( shouldTrack ){
									App.track('Ordered', {
										'id': json.uuid,
										'total': service.info.total,
										'subtotal': service.info.subtotal,
										'tip': service.info.tip,
										'tax': service.info.taxes,
										'restaurant': service.restaurant.name,
										'id_restaurant': service.restaurant.id_restaurant,
										'paytype': service.form.pay_type,
										'ordertype': service.form.delivery_type,
										'user': id_user,
										'items': service.cart.totalItems(),
										'cart': service.cart.getItems()
									}, undefined, undefined);

									if( fbq ){ // #7077
										fbq('track', 'Purchase', {value: service.info.total, currency: 'USD'});
									}
								}


								// Clean the cart
								service.cart.clean();
								service.updateTotal();

								// Resets the gift card notes field
								service.giftcard.notes_field.reset();

								$rootScope.$safeApply( function(){
									$rootScope.$broadcast( 'newOrder' );
									OrderViewService.newOrder = true;

									if( service.campus_cash ){
										OrderViewService._campus_cash[ uuid ] = service.form.campusCash.substr( -4 );
									}

									$rootScope.navigation.link('/order/' + uuid + '/confirm', 'push');

									if( App.push && App.push.register ){
										setTimeout(function() {
											App.push.register();
										}, 2000);
									}

								} );

							} );
						}

						var laps = 0;
						var watchDog = function(){
							if( laps >= 30 ){ return; }
							if( !orderCached ){
								cacheOrder();
								setTimeout( function() { watchDog(); }, 500 );
							}
							laps++;
						}

						watchDog();

					}
				});
		}


		if (service._cardInfoHasChanged && order.pay_type == 'card') {
			// need to generate a new tokenized card
			App.tokenizeCard({
				name: service.form.name,
				number: service.form.cardNumber,
				expiration_month: service.form.cardMonth,
				expiration_year: service.form.cardYear,
				security_code: null
			}, processOrder);
		} else if (order.pay_type == 'applepay') {
			ApplePay.getStripeToken(function(response) {
				processOrder({
					id : response.card.id,
					uri: response.id,
					lastfour: response.card.last4,
					card_type: response.card.brand.toLowerCase(),
					month: response.card.exp_month,
					year: response.card.exp_year,
					status : true
				});

			}, function(){
				App.busy.unBusy();
			}, service.info.totalText.replace('$',''), 'Crunchbutton', 'USD');
		} else {
			order.card = {};
			processOrder(false);
		}


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
		var total = service.total();
		var _total = service.restaurant.delivery_min_amt == 'subtotal' ? service.subtotal() : total;
		if (service.restaurant.meetDeliveryMin(_total) && service.form.delivery_type == 'delivery') {
			service.info.deliveryMinDiff = service.restaurant.deliveryDiff(_total);
		} else {
			service.info.deliveryMinDiff = '';
		}
		var breakdown = service.totalbreakdown();
		service.info.totalItems = service.cart.totalItems();
		service.info.extraCharges = service.extraChargesText(breakdown);
		service.info.breakdownDescription = service.info.dollarSign + this.subtotal().toFixed(2);
		service.info.cartSummary = service.cart.summary();
		service.info.taxes = breakdown.taxes.toFixed(2);
		service.info.tip = breakdown.tip.toFixed(2);
		service.info.subtotal = breakdown.subtotal.toFixed(2);
		service.info.fee = breakdown.fee.toFixed(2);
		service.info.delivery = breakdown.delivery.toFixed(2);
		// #5597
		service.info.delivery_service_fee = ( breakdown.delivery + breakdown.fee ).toFixed(2);
		service.info.total = total;

		if (service.form.pay_type == 'card' && credit > 0) {
			service.info.creditLeft = '';
			if (total < credit) {
				service.info.creditLeft = App.ceil((credit - total)).toFixed(2);
				credit = total;
			}
		}
	}
	service._autotip = function () {
		var subtotal = service.totalbreakdown().subtotal;
		if (subtotal === 0) {
			autotipValue = 0;
		} else {
			// autotip formula - see github/#940
			autotipValue = Math.ceil( 2 * (subtotal * 0.11 + 0.95 ) ) / 2;
			if( !isNaN( autotipValue ) ){
				autotipValue = autotipValue.toFixed( 2 );
			}
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
			value: '-',
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
			value: '-',
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

		if( service.form.delivery_type == 'takeout' ){
			tips.push({
				value: 0,
				label: 'Tip with cash'
			});
		}

		var subtotal = service.totalbreakdown().subtotal;
		var _tips = [15, 18, 20, 25, 30, 35, 40, 45, 50];

		for (var x in _tips) {

			if( subtotal ){
				var tip = ( subtotal * _tips[x] / 100 );
				if( !isNaN( tip ) ){
					tip = tip.toFixed( 2 );
				}
			}
			if( tip ){
				tip = ' (' + service.info.dollarSign + tip + ')';
			} else {
				tip = '';
			}

			tips.push({
				value: _tips[x],
				label: 'tip ' + _tips[x] + ' %' + tip
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

	// see #2799
	service.loadUserRestaurantInfo = function ( success, error ) {
		var url = App.service + 'user/restaurant/' + service.restaurant.id_restaurant;
		$http( {
			method: 'POST',
			url: url,
			cache: false,
			data: $.param( { 'words' : service.form.notes, phone: service.form.phone  } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
				if( success ){
					success( data );
				}
			}	)
			.error(function( data, status ) {
				if( error ){
					error( data, status );
				}
			} );
	}

	service._test = function(){
		$rootScope.$safeApply(
			function(){
			service._useRestaurantBoundingBox = true;
			service.form.name = 'MR TEST';
			service.form.phone = '***REMOVED***';
			service.form.address = service.restaurant.address;
			service.form.cardNumber = '4242424242424242';
			service.form.cardMonth = '2';
			service.form.cardYear = '2016';
			service.form.tip = 'autotip';
			service.tooglePayment( 'card' );
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

	service.checkItWasLoaded = function(){
		if( !service.list ){
			service.load();
		}
	}

	// Check if the user has ordered from other device or browser tab and update the list
	service.checkUpdate = function(){
		var url = App.service + 'user/orders/total';
		$http.get( url, {
			cache: false
		} ).success( function ( data ) {
			if( data.total != service.list.length ){
				service.reload = true;
				service.load();
			}
		} );
	}

	service.load = function () {

		if ( service.list && !service.reload ) {
			return service.list;
		}

		OrderViewService.newOrder = false;
		list = false;
		service.list = list;

		var url = App.service + 'user/orders';

		$http.get( url , {
			cache: false
		}).success( function ( json ) {
			service.reload = false;
			if (json) {
				for (var x in json) {
					var arr = json[x].date.split(/[- :]/);
					json[x]._date = new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]);
					json[x].timeFormat = json[x]._date_tz.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i, '$1/$2 $3');
				}
				list = json;
			} else {
				// User has no orders
				list = true;
			}
			service.list = list;
			$rootScope.$broadcast( 'OrdersLoaded', service.list );
		} ).error( function( data, status, headers, config ) {
		 		setTimeout( function(){ service.checkItWasLoaded(); }, 500 );
		 } ).then( function(){
		 		setTimeout( function(){ service.checkItWasLoaded(); }, 1500 );
		 } );
	}

	service.restaurant = function (permalink) {
		$rootScope.navigation.link('/' + restaurants.permalink + '/' + permalink, 'push');
	};

	service.receipt = function (id_order) {
		$rootScope.navigation.link('/order/' + id_order, 'push');
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

	var service = { order : false, reload : true, newOrder : false, _campus_cash: {} };

	service.facebook = FacebookService;

	service.load = function(refresh){
		var url = App.service + 'order/' + $routeParams.id;

		var error = function(){
			$location.path('/');
		}

		$http( {
			method: 'GET',
			url: url,
			cache: !refresh
		}).success( function( data ) {
			service.order = data;

			if( service._campus_cash && service._campus_cash[ service.order.uuid ] ){
				service.order.campus_cash_number = service._campus_cash[ service.order.uuid ];
			}

			if (service.order.uuid) {
				service.order._final_price = parseFloat(service.order.final_price).toFixed(2);

				if (service.order.credit) {
					service.order._credit = parseFloat(service.order.credit).toFixed(2);
				}

				var arr = data.date.split(/[- :]/);
				service.order._date = new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]);

				service.order._time = service.order.date_formated.split(',').shift();

				var order_address = ( service.order.address ) ? service.order.address.replace(/\r|\n/g,' ') : '';
				var restaurant_address = ( service.order._restaurant_address ) ? service.order._restaurant_address.replace(/\r|\n/g,' ') : '';

				if (App.iOS()) {
					service.order.mapLink = 'http://maps.apple.com/?' +(service.order.delivery_type == 'delivery' ? 's' : 'd') + 'addr=' + encodeURIComponent(order_address) + '&' + (service.order.delivery_type == 'delivery' ? 'd' : 's') + 'addr=' + encodeURIComponent(restaurant_address);
				} else {
					service.order.mapLink = 'http://maps.google.com/maps?' +(service.order.delivery_type == 'delivery' ? 's' : 'd') + 'addr=' + encodeURIComponent(order_address) + '&' + (service.order.delivery_type == 'delivery' ? 'd' : 's') + 'addr=' + encodeURIComponent(restaurant_address)+'"';
				}

				service.facebook._order_uuid = service.order.uuid;
				service.facebook.preLoadOrderStatus();

				$rootScope.$broadcast( 'OrderViewLoadedOrder', service.order );

			} else {
				error();
			}
		}).error(function(data) {
			error();
		});
	}
	return service;
});