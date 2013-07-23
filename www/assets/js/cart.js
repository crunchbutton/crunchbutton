
App.cart = {
	uuidInc: 0,

	items:   {},

	uuid: function() {
		var id = 'c-' + App.cart.uuidInc;
		App.cart.uuidInc++;
		return id;
	},

	add: function(item) {
		var
			id = App.cart.uuid(),
			opt = App.cached['Dish'][item].options(),
			options = [];

		if (arguments[1]) {
			options = arguments[1].options;
		} else {
			for (var x in opt) {
				if (opt[x]['default'] == 1) {
					options[options.length] = opt[x].id_option;
				}
			}
		}

		App.cart.items[id] = {
			id: item,
			options: options
		};

		var el = $('<div class="cart-item cart-item-dish" data-cart_id="' + id + '"></div>');
		el.append('<div class="cart-button cart-button-remove"><span></span></div>');

		el.append('<div class="cart-item-name">' + App.cache('Dish',item).name + ' <span class="cart-item-description">' + (App.cache('Dish',item).description != null ? App.cache('Dish',item).description : '') + '</span></div>');

		if (App.cached['Dish'][item].options().length) {
			el.append('<div class="cart-item-config"><a href="javascript:;">Customize</a></div>');
		}

		el.hide();

		// If it is a mobile add the items at the top #1035
		if( $( window ).width() > 769 ){
			$('.cart-items-content').append(el);	
		} else {
			$('.cart-items-content').prepend(el);	
		}
		
		//el.fadeIn();
		el.show();

		if( parseInt( App.cache( 'Dish', item ).expand_view ) > 0 ){
			App.cart.customize( el );
		}

		App.cart.updateTotal();

		App.track('Dish added', {
			id_dish: App.cache('Dish',item).id_dish,
			name: App.cache('Dish',item).name
		});
	},

	clone: function(item) {
		var
			cartid = item.attr('data-cart_id'),
			cart = App.cart.items[cartid],
			newoptions = [];

		for (var x in cart.options) {
			newoptions[newoptions.length] = cart.options[x];
		}
		App.cart.add(cart.id, {
			options: newoptions
		});

		App.track('Dish cloned');
	},

	remove: function(item) {
		var
			cart = item.attr('data-cart_id');

		App.track('Dish removed');

		delete App.cart.items[cart];

		item.remove();
		$('.cart-item-customize[data-id_cart_item="' + cart + '"]').remove();

		App.cart.updateTotal();
	},

	/**
	 * Gets called after the cart is updarted to refresh the total
	 *
	 * @todo Gets called many times before the cart is updated, on load, and shouldn't
	 *
	 * @return void
	 */
	updateTotal: function() {
		var
			totalText  = ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + this.charged(),
			tipText	= '',
			feesText   = '',
			totalItems = 0,
			credit = 0,
			hasFees	= ((App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') || App.restaurant.fee_customer) ? true : false;

		if( App.credit.restaurant[ App.restaurant.id ] ){
			credit = parseFloat( App.credit.restaurant[ App.restaurant.id ] );
		}

		for (var x in App.cart.items) {
			totalItems++;
		}
		App.cart.autotip();

		/* If the user changed the delivery method to takeout and the payment is card
		 * the default tip will be 0%. If the delivery method is delivery and the payment is 
		 * card the default tip will be autotip.
		 * If the user had changed the tip value the default value will be the chosen one.
		 */
		var wasTipChanged = false;
		if( App.order.delivery_type == 'takeout' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = 0;
				wasTipChanged = true;
			}
		} else if( App.order.delivery_type == 'delivery' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = ( App.config.user.last_tip ) ? App.config.user.last_tip : 'autotip';
				App.order.tip = App.lastTipNormalize( App.order.tip );
				wasTipChanged = true;
			}
		}

		if( wasTipChanged ){
			$('[name="pay-tip"]').val( App.order.tip );
			// Forces the recalculation of total because the tip was changed.
			totalText  = ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + this.charged();
		}

		if (App.restaurant.meetDeliveryMin() && App.order.delivery_type == 'delivery') {
			$('.delivery-minimum-error').show();
			$('.delivery-min-diff').html(App.restaurant.deliveryDiff());

		} else {
			$('.delivery-minimum-error').hide();
		}

		$('.cart-summary-item-count span').html(totalItems);

		/* If no items, hide payment line
		 * .payment-total  	line for new customers
		 * .dp-display-payment is for stored customers
		 */
		if (!this.subtotal()) {
			$('.payment-total, .dp-display-payment').hide();
		} else {
			$('.payment-total, .dp-display-payment').show();
		}

		var breakdown	= App.cart.totalbreakdown();

		var extraCharges = App.cart.extraChargesText(breakdown);
		if (extraCharges) {
			$('.cart-breakdownDescription').html( ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + this.subtotal().toFixed(2) + ' (+'+ extraCharges +')' );
		} else {
			$('.cart-breakdownDescription').html( ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + this.subtotal().toFixed(2));
		}

		if( App.order.pay_type == 'card' && credit > 0 ){
			var creditLeft = '';
			if( this.total() < credit ){
				var creditLeft = '<span class="gift-left"> - You\'ll still have ' +  ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + App.ceil( ( credit - this.total() ) ).toFixed( 2 ) + ' gift card left </span>';
				credit = this.total();
			} 
			$('.cart-gift').html( '&nbsp;(- ' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + App.ceil( credit ).toFixed( 2 ) + ' credit ' + creditLeft + ') ' );
		} else {
			$('.cart-gift').html( '' );
		}

		setTimeout( function(){
			if( App.order.pay_type == 'cash' && credit > 0 && App.giftcard.showGiftCardCashMessage ){
				$( '.cart-giftcard-message' ).html( '<span class="giftcard-payment-message">Pay with a card, NOT CASH, to use your  ' +  ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + App.ceil( credit ).toFixed( 2 ) + ' gift card!</span>' );
			} else {
			  $( '.cart-giftcard-message' ).html( '' );
			}
		}, 1000 );

		$('.cart-total').html( totalText );

		/**
		 * Crunchbutton doesnt collect the cash, the restaurant will ring up
		 * the order in their register, which may have different prices. The
		 * restaurant collects the cash, so its posible things may be
		 * different. This differs from when its card, crunchbutton collects
		 * the money directly so the price cant vary
		 */
		if (App.order['pay_type'] == 'card') {
			$('.cash-order-aprox').html('');
			$('.cart-paymentType').html('by card');
		} else {
			$('.cash-order-aprox').html('approximately');
			$('.cart-paymentType').html('');
		}

		if (App.cartHighlightEnabled && $('.cart-summary').css('display') != 'none') {
			$('.cart-summary').removeClass('cart-summary-detail');
			$('.cart-summary').effect('highlight', {}, 500, function() {
				$('.cart-summary').addClass('cart-summary-detail');
			});
		}

		if ($('.cart-total').html() == totalText) {
			//return;
		}

		if (!totalItems) {
			$('.default-order-check').hide();
		} else {
			$('.default-order-check').show();
		}

		var
			totalItems = {},
			name,
			text = '';
		$('.cart-summary-items').html('');

		for (var x in App.cart.items) {
			name = App.cached['Dish'][App.cart.items[x].id].name;
			if (totalItems[name]) {
				totalItems[name]++;
			} else {
				totalItems[name] = 1;
			}
		}

		for (x in totalItems) {
			text = ',&nbsp;&nbsp;' + text;
			if (totalItems[x] > 1) {
				text = x + '&nbsp;(' + totalItems[x] + ')' + text;
			} else {
				text = x + text;
			}
		}

		$('.cart-summary-items').html(text.substr(0,text.length-13));

		$('.cart-item-customize-price').each(function() {
			var dish = $(this).closest('.cart-item-customize').attr('data-id_cart_item'),
				option = $(this).closest('.cart-item-customize-item').attr('data-id_option'),
				cartitem = App.cart.items[dish],
				opt = App.cached['Option'][option],
				price = opt.optionPrice(cartitem.options);

			$(this).html(App.cart.customizeItemPrice(price));
		});

	},

	customizeItemPrice: function(price, force) {
		var priceText = '';
		if( price != '0.00' || force ){
			priceText = '&nbsp;(';
			priceText += ( price < 0 ) ? 'minus $' : '+ $';
			priceText += parseFloat( Math.abs( price ) ).toFixed(2);
			priceText += ')';
		} 
		return priceText;
	},

	customize: function(item) {
		var
			cart = item.attr('data-cart_id'),
			old = $('.cart-item-customize[data-id_cart_item="' + cart + '"]');

		if (old.length) {
			old.remove();
		} else {
			var
				el = $('<div class="cart-item-customize" data-id_cart_item="' + cart + '"></div>').insertAfter(item),
				cartitem = App.cart.items[cart],
				obj = App.cached['Dish'][cartitem.id],
				opt = obj.options();

			// First the basic options
			for (var x in opt) {
				if (opt[x].id_option_parent) {
					continue;
				}

				if (opt[x].type == 'check') {

					var price = opt[x].optionPrice(cartitem.options);
					var check = $('<input type="checkbox" class="cart-customize-check">');

					if ($.inArray(opt[x].id_option, cartitem.options) !== -1) {
						check.attr('checked','checked');
					}

					var option = $('<div class="cart-item-customize-item" data-id_option="' + opt[x].id_option + '"></div>')
						.append(check)
						.append('<label class="cart-item-customize-name">' +
							opt[x].name + (opt[x].description || '') +
							'</label><label class="cart-item-customize-price">' +
							App.cart.customizeItemPrice( price ) + '</label>'
						);
					el.append(option);

				}
			}

			// Second the customizable options 
			for (var x in opt) {
				if (opt[x].id_option_parent) {
					continue;
				}

				if (opt[x].type == 'select') {

					var select = $('<select class="cart-customize-select">');
					for (var i in opt) {

						if (opt[i].id_option_parent == opt[x].id_option) {
							var price = opt[i].price;
							var option = $('<option value="' + opt[i].id_option + '">' + opt[i].name + (opt[i].description || '') + App.cart.customizeItemPrice( price, ( opt[x].price_linked == '1' ) ) + '</option>');
							if ($.inArray(opt[i].id_option, cartitem.options) !== -1) {
								option.attr('selected','selected');
							}
							select.append(option);
						}
					}
					var option = $('<div class="cart-item-customize-item" data-id_option="' + opt[x].id_option + '"></div>')
						.append('<label class="cart-item-customize-select-name">' + opt[x].name + (opt[x].description || '') + '</label>')
						.append(select);

					el.append(option);

				}
			}
		}

		App.track('Dish customized');
	},

	customizeItem: function(item) {

		var
			cart = item.closest('.cart-item-customize').attr('data-id_cart_item'),
			cartitem = App.cart.items[cart],
			customitem = item.closest('.cart-item-customize-item'),
			opt = customitem.attr('data-id_option');

		if (opt) {
			if (item.hasClass('cart-customize-select')) {

				var obj = App.cached['Dish'][cartitem.id],
					opts = obj.options();

				for (var i in opts) {
					if (opts[i].id_option_parent != opt) {
						continue;
					}
					for (var x in cartitem.options) {
						if (cartitem.options[x] == opts[i].id) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}

				cartitem.options[cartitem.options.length] = item.val();

			} else if(item.hasClass('cart-customize-check')) {
				if (item.is(':checked')) {
					cartitem.options[cartitem.options.length] = opt;
				} else {
					for (var x in cartitem.options) {
						if (cartitem.options[x] == opt) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}
			}
		}
		App.cart.updateTotal();
	},

	/**
	 * subtotal, delivery, fee, taxes and tip
	 *
	 * @category view
	 */
	extraChargesText: function(breakdown) {
		var elements = [];
		var text 	= '';
		if (breakdown.delivery) {
			elements.push(( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + breakdown.delivery.toFixed(2) + ' delivery');
		}

		if (breakdown.fee) {
			elements.push(( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + breakdown.fee.toFixed(2) + ' fee');
		}
		if (breakdown.taxes) {
			elements.push(( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + breakdown.taxes.toFixed(2) + ' taxes');
		}
		if (breakdown.tip && breakdown.tip > 0) {
			elements.push(( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + breakdown.tip + ' tip');
		}

		if (elements.length) {
			if (elements.length > 2) {
				var lastOne  = elements.pop();
				var elements = [elements.join(', ')];
				elements.push(lastOne);
			}
			var text 	=  elements.join(' & ');
		}
		return text;
	},

	getCart: function() {
		var cart = [];
		for (x in App.cart.items) {
			cart[cart.length] = App.cart.items[x];
		}
		return cart;
	},

	/**
	 * Submits the cart order
	 *
	 * @returns void
	 */
	submit: function() {

		if (App.busy.isBusy()) {
			return;
		}
		
		App.busy.makeBusy();

		var read = $('.payment-form').length ? true : false;

		if (read) {
			App.config.user.name  = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val().replace(/[^\d]*/gi,'');
			if (App.order['delivery_type'] == 'delivery') {
				App.config.user.address = $('[name="pay-address"]').val();
			}
			App.order.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart:  		App.cart.getCart(),
			pay_type:  	App.order['pay_type'],
			delivery_type: App.order['delivery_type'],
			restaurant:	App.restaurant.id,
			make_default:  $('#default-order-check').is(':checked'),
			notes: 		$('[name="notes"]').val(),
			lat: ( App.loc.pos() ) ? App.loc.pos().lat : null,
			lon: ( App.loc.pos() ) ? App.loc.pos().lon : null
		};

		if (order.pay_type == 'card') {
			order.tip = App.order.tip || '3';
			order.autotip_value = $('[name=pay-autotip-value]').val();
		}

		if (read) {
			order.address  = App.config.user.address;
			order.phone	= App.config.user.phone;
			order.name 	= App.config.user.name;
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

		console.log('ORDER:',order);

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

		if (!App.cart.hasItems()) {
			errors['noorder'] = 'Please add something to your order.';
		}

		if (!$.isEmptyObject(errors)) {
			var error = '';
			for (var x in errors) {
				error += errors[x] + "\n";
			}
			$('body').scrollTop($('.payment-form').position().top-80);
			App.alert(error);
			App.busy.unBusy();
			App.track('OrderError', errors);
			// Log the error
			App.log.order( { 'errors' : errors } , 'validation error' );
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if( App.iOS() && !App.crunchSoundAlreadyPlayed ){
			App.playAudio( 'get-food-audio' );
			App.crunchSoundAlreadyPlayed = true;
		}

		// if it is a delivery order we need to check the address
		if( order.delivery_type == 'delivery' ){

			// Correct Legacy Addresses in Database to Avoid Screwing Users #1284
			// If the user has already ordered food 
			if( App.config && App.config.user && App.config.user.last_order ){

				// Check if the order was made at this community
				if( App.config.user.last_order.communities.indexOf( App.restaurant.id_community ) > -1 ){

					// Get the last address the user used at this community
					var lastAddress = App.config.user.last_order.address;
					var currentAdress = $( '[name=pay-address]' ).val();

					// Make sure the the user address is the same of his last order
					if( $.trim( lastAddress ) != '' && $.trim( lastAddress ) == $.trim( currentAdress ) ){
						App.isDeliveryAddressOk = true;
						
						// Log the legacy address
						App.log.order( { 'address' : lastAddress, 'restaurant' : App.restaurant.name } , 'legacy address' );
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
					var latLong = new google.maps.LatLng( App.restaurant.loc_lat, App.restaurant.loc_long );
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

						if (!App.restaurant.deliveryHere({ lat: lat, lon: lon})) {
							App.alert( 'Sorry, you are out of delivery range or have an invalid address. \nPlease check your address, or order takeout.' );
							

							// Write the found address at the address field, so the user can check it.
							$( '[name=pay-address]' ).val( App.loc.formatedAddress( theClosestAddress ) );

							// Log the error
							App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : App.restaurant.name } , 'address out of delivery range' );
						
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
							App.cart.submit();
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
						App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : App.restaurant.name } , 'address not found or invalid' );
					}
				}

				// Address not found!
				var error = function() {
					App.alert( 'Oops, it looks like your address is incomplete. \nPlease enter a street name, number and zip code.' );
					App.busy.unBusy();
					// Log the error
					App.log.order( { 'address' : $( '[name=pay-address]' ).val(), 'restaurant' : App.restaurant.name } , 'address not found' );
				};

				// Call the geo method
				App.loc.doGeocodeWithBound(order.address, latLong, success, error);
				return;
			} 
			*/
		}

		if( order.delivery_type == 'takeout' ){
			App.isDeliveryAddressOk = true;
		}
App.isDeliveryAddressOk = true;
		if( !App.isDeliveryAddressOk ){
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if( !App.crunchSoundAlreadyPlayed ){
			App.playAudio( 'get-food-audio' );
			App.crunchSoundAlreadyPlayed = true;
		}

		$.ajax({
			url: App.service + 'order',
			data: order,
			dataType: 'html',
			type: 'POST',
			complete: function(json) {
				try {
					json = $.parseJSON(json.responseText);
				} catch (e) {
					// Log the error
					App.log.order( json.responseText, 'processing error' );
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
					App.log.order( { 'errors' : json.errors } , 'validation error - php' );
				} else {

					if (json.token) {
						$.totalStorage('token', json.token);
					}

					$('.link-orders').show();

					order.cardChanged = false;
					App.justCompleted = true;
					App.giftcard.notesCode = false;

					var totalItems = 0;

					for (var x in App.cart.items) {
						totalItems++;
					}

					$.getJSON('/api/config', App.processConfig);
					
					App.cache('Order',json.uuid, function() {
						App.track('Ordered', {
							'total':this.final_price,
							'subtotal':this.price,
							'tip':this.tip,
							'restaurant': App.restaurant.name,
							'paytype': this.pay_type,
							'ordertype': this.order_type,
							'user': this.user,
							'items': totalItems
						});

						App.order.cardChanged = false;
						App.loc.changeLocationAddressHasChanged = false;
						delete App.order.tipHasChanged;
						App.go('/order/' + this.uuid);

					});
				}
				setTimeout( function(){
					App.busy.unBusy();
				}, 400 );
			}
		});

	}, // end App.cart.submit()

	subtotal: function() {
		var
			total = 0,
			options;

		for (var x in App.cart.items) {
			total += parseFloat(App.cached['Dish'][App.cart.items[x].id].price);
			options = App.cart.items[x].options;

			for (var xx in options) {
				var option = App.cached['Option'][options[xx]];
				if (option === undefined) continue; // option does not exist anymore
				total += parseFloat(option.optionPrice(options));
			}
		}
		total = App.ceil(total);
		return total;
	},

	/**
	 * delivery cost
	 *
	 * @return float
	 */
	_breackDownDelivery: function() {
		var delivery = 0;
		if (App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') {
			delivery = parseFloat(App.restaurant.delivery_fee);
		}
		delivery = App.ceil(delivery);
		return delivery;
	},

	/**
	 * Crunchbutton service
	 *
	 * @return float
	 */
	_breackDownFee: function(feeTotal) {
		var fee = 0;
		if (App.restaurant.fee_customer) {
			fee = (feeTotal * (parseFloat(App.restaurant.fee_customer)/100));
		}
		fee = App.ceil(fee);
		return fee;
	},

	_breackDownTaxes: function(feeTotal) {
		var taxes = (feeTotal * (App.restaurant.tax/100));
		taxes = App.ceil(taxes);
		return taxes;
	},

	_breakdownTip: function(total) {
		var tip = 0;
		if (App.order['pay_type'] == 'card') {
			if (App.order.tip === 'autotip') {
				return parseFloat($('[name=pay-autotip-value]').val());
			}
			tip = (total * (App.order.tip/100));
		}
		tip = App.ceil(tip);
		return tip;
	},

	total: function() {
		var
			total = 0,
			dish,
			options,
			feeTotal	= 0,
			totalItems  = 0,
			finalAmount = 0
		;

		var breakdown = this.totalbreakdown();
		total		= breakdown.subtotal;
		feeTotal 	= total;
		feeTotal	+= breakdown.delivery;
		feeTotal	+= breakdown.fee;
		finalAmount  = feeTotal + breakdown.taxes;
		finalAmount += this._breakdownTip(total);
		return App.ceil(finalAmount).toFixed(2);
	},

	charged : function(){

		var finalAmount = this.total();

		if( App.order.pay_type == 'card' && App.credit.restaurant[ App.restaurant.id ] ){
			finalAmount = finalAmount - App.ceil( App.credit.restaurant[ App.restaurant.id ] ).toFixed(2);
			if( finalAmount < 0 ){
				finalAmount = 0;
			}
		}
		return App.ceil(finalAmount).toFixed(2);
	},

	/**
	 * Returns the elements that calculates the total
	 *
	 * breakdown elements are: subtotal, delivery, fee, taxes and tip
	 *
	 * @return array
	 */
	totalbreakdown: function() {
		var elements = {};
		var total	= this.subtotal();
		var feeTotal = total;

		elements['subtotal'] = this.subtotal();
		elements['delivery'] = this._breackDownDelivery();
		feeTotal			+= elements['delivery'];
		elements['fee']  	= this._breackDownFee(feeTotal);
		feeTotal			+= elements['fee'];
		elements['taxes']	= this._breackDownTaxes(feeTotal);
		elements['tip']  	= this._breakdownTip( total );
		return elements;
	},

	resetOrder: function() {
		App.cart.items = {};
		$('.cart-items-content, .cart-total').html('');
	},

	reloadOrder: function() {
		var cart = App.cart.items;
		App.cart.resetOrder();
		App.cart.loadFlatOrder(cart);
	},

	loadFlatOrder: function(cart) {
		for (var x in cart) {
			App.cart.add(cart[x].id,{
				options: cart[x].options ? cart[x].options : []
			});
		}
	},

	loadOrder: function(order) {
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
						App.cart.add(dishes[x].id_dish,{
							options: options
						});
					}

				}
			}
		} catch (e) {
			console.log(e.stack);
			// throw e;
		}
		App.cart.updateTotal();
	},

	hasItems: function() {
		if (!$.isEmptyObject(App.cart.items)) {
			return true;
		}
		return false;
	},
	
	autotip: function() {
		var subtotal = App.cart.totalbreakdown().subtotal;
		var autotipValue
		if (subtotal === 0) {
			autotipValue = 0;
		} else {
			// autotip formula - see github/#940
			autotipValue = Math.ceil(4*(subtotal * 0.107 + 0.85)) / 4;
		}
		$('[name="pay-autotip-value"]').val(autotipValue);
		var autotipText = autotipValue ? ' (' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + autotipValue + ')' : '';
		$('[name=pay-tip] [value=autotip]').html('Autotip' + autotipText);
	}
};

