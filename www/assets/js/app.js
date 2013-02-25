/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */


var App = {
	cartHighlightEnabled: false,
	currentPage: null,
	slogans: ['Push a button. Get Food.'],
	tagline: '',
	service: '/api/',
	cached: {},
	community: null,
	page: {},
	config: null,
	forceHome: false,
	order: {
		cardChanged: false,
		pay_type: 'card',
		delivery_type: 'delivery',
		tip: '15'
	},
	signin : {},
	suggestion : {},
	restaurants: {
		permalink : 'food-delivery'
	},
	modal: {},
	hasBack: false,
	_init: false,
	_pageInit: false,
	_identified: false,
	tips: [0,5,10,15,20,25]
};

App.loadRestaurant = function(id) {

	App.cache('Restaurant', id,function() {

		if (!this.open()) {
			var hours = '';

			for (var x in this._hours) {
				hours += x + ': ';
				for (var xx in this._hours[x]) {
					hours += this._hours[x][xx][0] + ' - ' + this._hours[x][xx][1] + (xx == 0 ? ', ' : '');
				}
				hours += "\n";
			}
			alert("This restaurant is currently closed. It will be open during the following hours (" + this._tzabbr + "):\n\n" + hours);
			App.busy.unBusy();
		} else {
			if (this.redirect) {
				location.href = this.redirect;
				return;
			}
			var loc = '/' + App.restaurants.permalink + '/' + this.permalink;
			History.pushState({}, 'Crunchbutton - ' + this.name, loc);
		}
	});
};


/**
 * Loads up "community" keyword pages
 */
App.routeAlias = function(id) {
	// Get the alias
	alias = App.aliases[ id ] || false;
	if( alias ){
		// Get the location of the alias
		var loc = App.locations[ alias.id_community ];
		if( loc.loc_lat && loc.loc_lon ){
			App.loc.lat = loc.loc_lat;
			App.loc.lon = loc.loc_lon;
			App.loc.prep = alias.prep;
			App.loc.name_alt = alias.name_alt;
			$.cookie( 'location_prep', alias.prep, { expires: new Date(3000,01,01), path: '/'});
			$.cookie( 'location_name_alt', alias.name_alt, { expires: new Date(3000,01,01), path: '/'});
			$.cookie( 'location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
			$.cookie( 'location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});	
			App.foodDelivery.preProcess();
			return;
		}
	}
	// If the alias doesn't exist show the home with the error location message.
	App.forceHome = true;
	App.showErrorLocation = true;
	App.loadHome();
	return;
};

App.loadHome = function() {
	App.currentPage = 'home';
	History.pushState({}, 'Crunchbutton', '/');
	if( App.forceHome ){
		App.page.home();	
	}

};

App.page.resetPassword = function( path ){
	if( !App.signin.passwordHelp.reset.hasStarted ){
		App.signin.passwordHelp.reset.hasStarted = true;
		$( '.wrapper' ).append( App.signin.passwordHelp.reset.html( path ) );
		App.showReset = true;
		App.page.home();
	}
}

App.render = function(template, data) {
	var compiled = _.template($('.template-' + template).html());
	return compiled(data);
};

App.showPage = function(params) {
	// switch here for AB testing

	App.currentPage = params.page;
	if (params.title) {
		document.title = params.title;
	}

	// track different AB pages
	if (params.tracking) {
		App.track(params.tracking.title, params.tracking.data);
	}
	$('.main-content').html(App.render(params.page, params.data));
};

/**
 * Router init
 * @todo replace with router
 */
App.loadPage = function() {
	
	// If the user is using Chrome for iOS show the message:	
	if (App.isChromeForIOS() ){
		App.message.chrome();
	}

	App.signin.checkUser();
	var
		url = History.getState().url.replace(/http(s)?:\/\/.*?\/(.*)/,'$2').replace('//','/'),
		path = url.split('/');

	if (!path[path.length-1]) {
		delete path[path.length-1];
	}

	if (!App.config) {
		return;
	}

	// hide whatever we have
	if (App._pageInit) {
		$('.main-content').css('visibility','0');
	} else {
		App._pageInit = true;
	}

	// check if the user clicked at the back button
	if( !url && App.hasBack ){
		App.forceHome = false;
		App.page.home();
		return;
	}

	// force to a specific community
	if (!url) {
		App.loc.process();
		return;
	}

	var restaurantRegex = new RegExp('^\/(restaurant)|(' + App.restaurants.permalink + ')/', 'i');
	var cleaned_url = $.trim( url.replace( '/', '' ) );

	App.hasBack = true;

	switch (true) {
		case /^legal/i.test(url):
			App.page.legal();
			break;
		case /^help/i.test(url):
			App.page.help();
			break;
		case /^orders/i.test(url):
			App.page.orders();
			break;
		case /^order/i.test(url):
			App.page.order(path[1]);
			break;
		case /^reset/i.test(url):
			App.page.resetPassword( path );
			break;
		case new RegExp( App.restaurants.permalink + '$', 'i' ).test( cleaned_url ):
			App.page.foodDelivery();
			break;
		case restaurantRegex.test(url):
			App.page.restaurant(path[1]);
			break;
		default:
			App.routeAlias( path[ 0 ] );
			$('.footer').removeClass('footer-hide');
			setTimeout(scrollTo, 80, 0, 1);
			setTimeout( function(){ App.signin.checkUser(); }, 100 );
			break;
	}

	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
	App.refreshLayout();
	$('.main-content').css('visibility','1');
	setTimeout(scrollTo, 80, 0, 1);
	setTimeout( function(){ App.signin.checkUser(); }, 300 );
};


/**
 * Refresh the pages layout for a blank page
 */
App.refreshLayout = function() {
	setTimeout(function() {
		scrollTo(0, 1);
	}, 80);
};


/**
 * Sends a tracking item to mixpanel, or to google ads if its an order
 */
App.track = function() {
	if (App.config.env != 'live') {
		return;
	}
	if (arguments[0] == 'Ordered') {
		$('img.conversion').remove();
		mixpanel.people.track_charge(arguments[1].total);
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + History.getState().url + '">').appendTo($('body'));
	}
	if (arguments[1]) {
		mixpanel.track(arguments[0],arguments[1]);
	} else {
		mixpanel.track(arguments[0]);
	}
};


/**
 * Tracks a property to mixpanel
 */
App.trackProperty = function(prop, value) {
	if (!App.config || App.config.env != 'live') {
		return;
	}
	
	var params = {};
	params[prop] = value;
	
	mixpanel.register_once(params);
};


/**
 * Itendity the user to mixpanel
 */
App.identify = function() {
	if (App.config.env != 'live') {
		return;
	}
	if (!App._identified && App.config.user.uuid) {
		mixpanel.identify(App.config.user.uuid);
		App._identified = true;
	}
};

/**
 * generate ab formulas
 */
App.AB = function() {
	// random taglines
	App.taglines = [
		{
			name: 'tagline-for-free',
			tagline: 'Order the top food %s. For free. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'
		},
		{ name: 'tagline-no-free',
			tagline: 'Order the top food %s. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'		
		}
	];
	
	App.slogan = App.slogans[Math.floor(Math.random()*App.slogans.length)];
	App.tagline = App.taglines[Math.floor(Math.random()*App.taglines.length)];
	App.trackProperty('restaurant-tagline', App.tagline.name);
};

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

		$('.cart-items-content').append(el);
		//el.fadeIn();
		el.show();

		App.cart.updateTotal();

		App.track('Dish added');
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
			totalText  = '$' + this.total(),
			tipText	= '',
			feesText   = '',
			totalItems = 0,
			hasFees	= ((App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') || App.restaurant.fee_customer) ? true : false;

		for (var x in App.cart.items) {
			totalItems++;
		}

		/* If the user changed the delivery method to takeout and the payment is card
		 * the default tip will be 0%. If the delivery method is delivery and the payment is card
		 * the default tip will be 15% (variable App.order.tip).
		 * If the user had changed the tip value the default value will be chosed one.
		 */
		var wasTipChanged = false;
		if( App.order.delivery_type == 'takeout' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = 0;
				wasTipChanged = true;
			}
		} else if( App.order.delivery_type == 'delivery' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = ( App.config.user.last_tip ) ? App.config.user.last_tip : 15; // Default value is 15
				wasTipChanged = true;
			}
		}

		if( wasTipChanged ){
			$('[name="pay-tip"]').val( App.order.tip );
			// Forces the recalculation of total because the tip was changed.
			totalText  = '$' + this.total();
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
			$('.cart-breakdownDescription').html('$' + this.subtotal().toFixed(2) + ' (+'+ extraCharges +')' );
		} else {
			$('.cart-breakdownDescription').html('$' + this.subtotal().toFixed(2));
		}

		$('.cart-total').html(totalText);


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
			text += x;
			if (totalItems[x] > 1) {
				text += '&nbsp;(' + totalItems[x] + ')';
			}
			text += ',&nbsp;&nbsp;';
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

	customizeItemPrice: function(price) {
		return price != '0.00' ? '&nbsp;($' + price.toFixed(2) + ')' : '';
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
							App.cart.customizeItemPrice(price) + '</label>'
						);
					el.append(option);

				} else if (opt[x].type == 'select') {

					var select = $('<select class="cart-customize-select">');
					for (var i in opt) {

						if (opt[i].id_option_parent == opt[x].id_option) {

							var option = $('<option value="' + opt[i].id_option + '">' + opt[i].name + (opt[i].description || '') + (opt[i].price != '0.00' || opt[x].price_linked == '1' ? (' ($' + (parseFloat(opt[i].price) + parseFloat(obj.price)).toFixed(2) + ')') : '') + '</option>');
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
		console.log(cartitem.options);

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
			elements.push('$' + breakdown.delivery.toFixed(2) + ' delivery');
		}

		if (breakdown.fee) {
			elements.push('$' + breakdown.fee.toFixed(2) + ' fee');
		}
		if (breakdown.taxes) {
			elements.push('$' + breakdown.taxes.toFixed(2) + ' taxes');
		}
		if (breakdown.tip) {
			elements.push('$' + breakdown.tip.toFixed(2) + ' tip');
		}

		if (elements.length) {
			if (elements.length > 2) {
				var lastOne  = elements.pop();
				var elements = [elements.join(', ')];
				elements.push(lastOne);
			}
			var text 	=  elements.join(' and ');
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
			lat: 		   App.loc.lat,
			lon: 		   App.loc.lon
		};

		if (order.pay_type == 'card') {
			order.tip = App.order.tip ? App.order.tip : '15';
		}

		if (read) {
			order.address  = App.config.user.address;
			order.phone	= App.config.user.phone;
			order.name 	= App.config.user.name;
/*
Issue 13: Removed the password for while
			order.password = $( 'input[name=pay-password]' ).val( );
*/
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
			alert(error);
			App.busy.unBusy();
			App.track('OrderError', errors);
			return;
		}

		$.ajax({
			url: App.service + 'order',
			data: order,
			dataType: 'json',
			type: 'POST',
			complete: function(json) {

				json = $.parseJSON(json.responseText);

				if (json.status == 'false') {
					var error = '';
					for (x in json.errors) {
						error += json.errors[x] + "\n";
					}
					App.track('OrderError', json.errors);
					alert(error);

				} else {
					if (json.token) {
						$.cookie('token', json.token, { expires: new Date(3000,01,01), path: '/'});
					}

					$('.link-orders').show();

					order.cardChanged = false;
					App.justCompleted = true;

					var totalItems = 0;

					for (var x in App.cart.items) {
						totalItems++;
					}

					$.getJSON('/api/config', App.processConfig);
					App.cache('Order',json.uuid,function() {
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

						var loc = '/order/' + this.uuid;
						History.pushState({},loc,loc);
					});
				}
				App.busy.unBusy();
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
		elements['tip']  	= this._breakdownTip(total);
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
	}
};


App.busy = {
	isBusy: function() {
		return $('.app-busy').length ? true : false;
	},
	makeBusy: function() {
		//el.addClass('button-bottom-disabled');
		var busy = $('<div class="app-busy"></div>')
				.append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>'))


		$('body').append(busy);
	},
	unBusy: function() {
		$('.app-busy').remove();
		//el.removeClass('button-bottom-disabled');
	}
};


App.test = {
	card: function() {
		$('[name="pay-card-number"]').val('4242424242424242');
		$('[name="pay-card-month"]').val('1');
		$('[name="pay-card-year"]').val('2020');

		$('[name="pay-name"]').val('MR TEST');
		$('[name="pay-phone"]').val('***REMOVED***');
		$('[name="pay-address"]').val("123 main\nsanta monica ca");

		App.order.cardChanged = true;
	},
	logout: function() {
		$.getJSON('/api/logout',function(){ location.reload()});
	},
	cart: function() {
		alert(JSON.stringify(App.cart.items));
	},
	clearloc: function() {
		$.cookie('community', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lat', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lon', '', { expires: new Date(3000,01,01), path: '/'});
		location.href = '/';
	}
};

App.processConfig = function(json) {
	App.config = json;
	App.AB();
	if (App.config.user) {
		App.identify();
		App.order['pay_type'] = App.config.user['pay_type'];
		App.order['delivery_type'] = App.config.user['delivery_type'];
		App.order['tip'] = App.config.user['tip'] || 15;
	}
};

App.loc = {
	distance: function(params) {
		try{
			var R = 6371; // Radius of the earth in km
			var dLat = (params.to.lat - params.from.lat).toRad();

			var dLon = (params.to.lon - params.from.lon).toRad();
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
				Math.cos(params.from.lat.toRad()) * Math.cos(params.to.lat.toRad()) *
				Math.sin(dLon/2) * Math.sin(dLon/2);
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
			var d = R * c; // Distance in km

			return d;
		} catch( e ) {
			App.track('Location Error', {
				lat: App.loc.lat,
				lon: App.loc.lon,
				address: $('.location-address').val()
			});
		}
	},
	getClosest: function() {
		var closest;
		if (App.loc.lat) {
			for (x in App.communities) {
				App.communities[x].distance = App.loc.distance({
					from: {lat: App.loc.lat, lon: App.loc.lon},
					to: {lat: parseFloat(App.communities[x].loc_lat), lon: parseFloat(App.communities[x].loc_lon)}
				});
				if (!closest || App.communities[x].distance < closest.distance) {
					closest = App.communities[x];
				}
			}
		}
		return closest;
	},
	closest: function(complete) {
		if (google && google.loader && google.loader.ClientLocation) {
			App.loc.lat = google.loader.ClientLocation.latitude;
			App.loc.lon = google.loader.ClientLocation.longitude;

			complete();
		}
	},
	getLocation: function() {
		$('.location-detect-loader').show();
		$('.location-detect-icon').hide();

		var complete = function() {
			App.track('Locations Shared', {
				lat: App.loc.lat,
				lon: App.loc.lon
			});
			App.loc.reverseGeocode(function() {
				$('.location-detect-loader').hide();
				$('.location-detect-icon').show();
				$('.button-letseat-form').click();
			});
		};

		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position){
				App.loc.lat = position.coords.latitude;
				App.loc.lon = position.coords.longitude;
				complete();
			}, complete, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});
		}
	},
	setFormattedLoc: function(results, raw) {


		if (raw) {
			App.loc.reverseGeocodeCity = raw;
		} else {
			if (!results) {
				return;
			}
			switch (results[0].types[0]) {
				default:
				case 'administrative_area_level_1':
					App.loc.reverseGeocodeCity = results[0].address_components[0].long_name;
					break;
				case 'locality':
					App.loc.reverseGeocodeCity = results[0].address_components[0].long_name + ', ' + results[0].address_components[2].short_name;
					break;
				case 'street_address':
					App.loc.reverseGeocodeCity = results[0].address_components[2].long_name + ', ' + results[0].address_components[4].short_name;
					break;
				case 'postal_code':
				case 'route':
					App.loc.reverseGeocodeCity = results[0].address_components[1].long_name + ', ' + results[0].address_components[3].short_name;
					break;
			}
		}
		$('.loc-your-area').html(App.loc.reverseGeocodeCity || 'your area');
		
		// Get the city's name
		App.loc.city_name = null;
		
		if (!results) {
			return;
		}
		for (var x = 0; x < results.length; x++) {
			for (var i = 0; i < results[x].address_components.length; i++) {
				for (var j = 0; j < results[x].address_components[i].types.length; j++) {
					if(results[x].address_components[i].types[j] == 'locality') {
						App.loc.city_name = results[x].address_components[i].long_name;
					}
				}
			}
		}
	},
	preProcess: function() {

		if (google.loader.ClientLocation) {
			if (!$.cookie('location_lat')) {
				App.loc.lat = google.loader.ClientLocation.latitude;
				App.loc.lon = google.loader.ClientLocation.longitude;

				if (google.loader.ClientLocation.address.country_code == 'US' && google.loader.ClientLocation.address.region) {
					App.loc.setFormattedLoc(null, google.loader.ClientLocation.address.city + ', ' + google.loader.ClientLocation.address.region.toUpperCase());
				} else {
					App.loc.setFormattedLoc(null, google.loader.ClientLocation.address.city + ', ' + google.loader.ClientLocation.address.country_code);
				}
			}
		}
	},
	process: function() {

		var did = false;
		if (App.config.user && !App.forceHome) {

			if ($.cookie('location_lat')) {
				App.loc.lat = parseFloat($.cookie('location_lat'));
				App.loc.lon = parseFloat($.cookie('location_lon'));
			} else if (App.config.user.location_lat) {
				App.loc.lat = parseFloat(App.config.user.location_lat);
				App.loc.lon = parseFloat(App.config.user.location_lon);
			}
			App.foodDelivery.preProcess();
			return;
		}

		if (!did && !App.forceHome && navigator.geolocation) {
			var complete = function() {
				$('.button-letseat-form').click();
			};
			navigator.geolocation.getCurrentPosition(function(position){
				App.loc.lat = position.coords.latitude;
				App.loc.lon = position.coords.longitude;

				App.track('Locations Shared', {
					lat: App.loc.lat,
					lon: App.loc.lon
				});

				complete();
			}, complete, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});
		}

		if (!did) {
			App.forceHome = false;
			App.page.home();
		}

	},
	geocode: function(complete) {

		var geocoder = new google.maps.Geocoder();
		var forceLoc = null;
		var address = $('.location-address').val().toLowerCase();

		App.track('Location Entered', {
			address: address
		});

		// Check if the typed address has an alias
		if( App.aliases[ address ] ){
			return App.routeAlias( address );
		}

		geocoder.geocode({'address': $('.location-address').val()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				App.loc.lat = results[0].geometry.location.lat();
				App.loc.lon = results[0].geometry.location.lng();					
				App.loc.name_alt = null;
				App.loc.prep = null;
				$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
				$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});
				$.cookie('location_name_alt', App.loc.name_alt, { expires: new Date(3000,01,01), path: '/'});
				$.cookie('location_prep', App.loc.prep, { expires: new Date(3000,01,01), path: '/'});
				App.loc.setFormattedLoc( results );
				setTimeout( function(){
					App.foodDelivery.preProcess();	
				}, 50 );
				
			} else {
				$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
			}
			complete();
		});
	},
	reverseGeocode: function(complete) {
		App.track('Location Reverse Geocode', {
			lat: App.loc.lat,
			lon: App.loc.lon
		});

		if (App.loc.reverseGeocodeResults) {
			$('.location-address').val(App.loc.reverseGeocodeResults);
			complete();
			return;
		}

		var geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(App.loc.lat, App.loc.lon);

		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[1]) {
					$('.location-address').val(results[0].formatted_address);
				} else {
					$('.location-address').val('Where are you?!');
				}

				App.loc.reverseGeocodeResults = results[0].formatted_address;
				App.loc.setFormattedLoc(results);
				complete();
				setTimeout(function() {
					App.loc.reverseGeocodeResults = null;
				}, 1000 * 60 * 2);

			} else {
				$('.location-address').val('Oh no! We couldn\'t locate you');
				$('.location-detect-loader').hide();
				$('.location-detect-icon').show();
			}

		});
	}
}

App.trigger = {
	delivery: function() {
		$('.delivery-toggle-takeout').removeClass('toggle-active');
		$('.delivery-toggle-delivery').addClass('toggle-active');
		$('.delivery-only').show();
		App.order['delivery_type'] = 'delivery';
		App.cart.updateTotal();
	},
	takeout: function() {
		$('.delivery-toggle-delivery').removeClass('toggle-active');
		$('.delivery-toggle-takeout').addClass('toggle-active');
		$('.delivery-only, .field-error-zip').hide();
		App.order['delivery_type'] = 'takeout';
		App.cart.updateTotal();
	},
	credit: function() {
		$('.pay-toggle-cash').removeClass('toggle-active');
		$('.pay-toggle-credit').addClass('toggle-active');
		$('.card-only').show();
		App.order['pay_type'] = 'card';
		App.cart.updateTotal();
	},
	cash: function() {
		$('.pay-toggle-credit').removeClass('toggle-active');
		$('.pay-toggle-cash').addClass('toggle-active');
		$('.card-only').hide();
		App.order['pay_type'] = 'cash';
		App.cart.updateTotal();
	}
}


/**
 * global event binding and init
 */
$(function() {

	$(document).on('click', '.signout-button', function() {
		App.signin.signOut();
	});

	$(document).on('click', '.signup-add-facebook-button', function() {
		App.signin.facebook.login();
	});

	$(document).on('click', '.change-location-inline', function() {
		App.forceHome = true;
		App.showErrorLocation = false;
		App.loadHome();
		$('input').blur();
	});

	$(document).on('submit', '.button-letseat-formform', function() {
		$('.button-letseat-form').click();
		return false;
	});

	$(document).on('click', '.button-letseat-form', function() {
		
		var complete = function() {
			var closest = App.loc.getClosest();
			if (closest) {
				if (closest.distance < 25) {

					App.routeAlias( closest.permalink );

					App.track('Location Success', {
						lat: App.loc.lat,
						lon: App.loc.lon,
						address: $('.location-address').val(),
						community: closest.permalink
					});

				} else {

					App.track('Location Error', {
						lat: App.loc.lat,
						lon: App.loc.lon,
						address: $('.location-address').val()
					});
					return;
				}
			}
		};

		if ($('.location-address').val() && $('.location-address').val() != App.loc.reverseGeocodeResults) {
			App.loc.enteredLoc = $('.location-address').val();
			$.cookie('entered_address', App.loc.enteredLoc, { expires: new Date(3000,01,01), path: '/'});
			App.loc.geocode(complete);
		} else if (App.loc.lat) {
			complete();
		} else {
//			$('.location-address').val('').attr('placeholder','Hey! Enter your address here');
		}
	});

	$(document).on('click', '.delivery-toggle-delivery', function() {
		App.trigger.delivery();
		App.track('Switch to delivery');
	});

	$(document).on('click', '.delivery-toggle-takeout', function() {
		App.trigger.takeout();
		App.track('Switch to takeout');
	});

	$(document).on('click', '.pay-toggle-credit', function() {
		App.trigger.credit();
		App.track('Switch to card');
	});

	$(document).on('click', '.pay-toggle-cash', function() {
		App.trigger.cash();
		App.track('Switch to cash');
	});

	$(document).on('click', '.location-detect', function() {
		App.loc.getLocation();
	});
	
	$(document).on({
		mousedown: function() {
			$(this).addClass('location-detect-click');
		},
		touchstart: function() {
			$(this).addClass('location-detect-click');
		},
		mouseup: function() {
			$(this).removeClass('location-detect-click');
		},
		touchend: function() {
			$(this).removeClass('location-detect-click');
		}
	}, '.location-detect');
	$(document).on({
		mousedown: function() {
			if (App.busy.isBusy()) {
				return;
			}

			if (navigator.userAgent.toLowerCase().indexOf('ios') > -1) {
				return;
			}
			$(this).addClass('meal-item-down');
			var self = $(this);
			var r = self.closest('.meal-item').attr('data-permalink');
			var c = self.closest('.meal-item').attr('data-permalink-community');

			setTimeout(function() {
				if (r) {
					App.loadRestaurant(r);
				} else if (c) {
					App.routeAlias(c);
				}
			},100);
		},
		mouseup: function() {
			$(this).removeClass('meal-item-down');
		},
		touchstart: function(e) {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				//return;
			}
			App.startX = event.touches[0].pageX;
			App.startY = event.touches[0].pageY;

			$(this).addClass('meal-item-down');
		},
		touchmove: function(e) {
			App.touchX = event.touches[0].pageX;
			App.touchY = event.touches[0].pageY;
		},
		touchend: function(e) {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				//return;
			}
			if (App.busy.isBusy()) {
				return;
			}

			var maxDistance = 10;
			var r = $(this).closest('.meal-item').attr('data-permalink');
			var c = $(this).closest('.meal-item').attr('data-permalink-community');

			if (Math.abs(App.startX-App.touchX) < maxDistance && Math.abs(App.startY-App.touchY) < maxDistance) {
				if (r) {
					App.loadRestaurant(r);
				} else if (c) {
					History.pushState({},c,c);
					App.routeAlias(c);
				}
			}
			$(this).removeClass('meal-item-down');
		}
	}, '.meal-item-content');


	$(document).on('click', '.resturant-dish-container a', function() {
		if ($(this).attr('data-id_dish')) {
			App.cart.add($(this).attr('data-id_dish'));

		} else if ($(this).hasClass('restaurant-menu')) {
			return;
		}
	});

	$(document).on('click', '.your-orders a', function() {
		if ($(this).attr('data-id_order')) {
			History.pushState({},'Crunchbutton - Your Order', '/order/' + $(this).attr('data-id_order'));
		}
	});

	$(document).on('click', '.cart-button-remove', function() {
		App.cart.remove($(this).closest('.cart-item'));
	});

	$(document).on('click', '.cart-button-add', function() {
		App.cart.clone($(this).closest('.cart-item'));
	});

	$(document).on('click', '.cart-item-config a', function() {
		App.cart.customize($(this).closest('.cart-item'));
	});

	$(document).on('click', '.button-submitorder', function() {
		App.cart.submit($(this));
	});
	
	$(document).on('click', '.button-submitorder-form', function() {
		App.cart.submit($(this),true);
	});

	$(document).on('click', '.button-deliver-payment, .dp-display-item a', function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});

	$(document).on({
		mousedown: function() {
			$(this).addClass('button-bottom-click');
		},
		touchstart: function() {
			$(this).addClass('button-bottom-click');
		},
		mouseup: function() {
			$(this).removeClass('button-bottom-click');
		},
		touchend: function() {
			$(this).removeClass('button-bottom-click');
		}
	}, '.button-bottom');

	$(document).on('click', '.cart-customize-check, .cart-customize-select', function() {
		App.cart.customizeItem($(this));
	});

	$(document).on('click', '.cart-item-customize-item label', function() {
		$(this).prev('input').click();
	});

	$(document).on('change', '[name="pay-tip"]', function() {
		App.order.tip = $(this).val();
		App.order.tipHasChanged = true;
		var total = App.cart.total();
		App.cart.updateTotal();
	});

	$(document).on('click', '.nav-back', function() {
		History.back();
	});

	$(document).on('click', '.link-home', function() {
		if (App.hasLocation) {
			App.foodDelivery.preProcess();
		} else {
			App.forceHome = true;
			App.loadHome();
			$('input').blur();
		}
	});

	$(document).on('change', '[name="pay-card-number"], [name="pay-card-month"], [name="pay-card-year"]', function() {
		App.order.cardChanged = true;
	});

	$(document).on('click', '.link-help', function() {
		History.pushState({}, 'Crunchbutton - About', '/help');
	});

	$(document).on('click', '.link-legal', function() {
		History.pushState({}, 'Crunchbutton - Legal', '/legal');
	});

	$(document).on('click', '.link-orders', function() {
		History.pushState({}, 'Crunchbutton - Orders', '/orders');
	});

	$(document).on('keyup', '[name="pay-phone"]', function() {
		$(this).val( App.phone.format($(this).val()) );
	});

	// make sure we have our config loaded
	var haveConfig = function(json) {
		App.processConfig(json);
		App._init = true;
		App.loadPage();
	};

	if (App.config) {
		haveConfig(App.config)
	} else {
		$.getJSON('/api/config', haveConfig);
	}

	$(document).on('click', '.cart-summary', function() {
		$('body').scrollTop($('.cart-items').position().top-80);
	});

	var unHideBars = function() {
		$('[data-position="fixed"]').show();
	}
	$(document).on('focus', 'select, input, textarea', function() {

		if ($(window).width() >= 768 || navigator.userAgent.toLowerCase().indexOf('android') > -1 || $(this).hasClass('location-address')) {
			return;
		}
		clearTimeout(App.unHideBars);
		$('[data-position="fixed"]').hide();
	});
	$(document).on('blur', 'select, input, textarea', function() {
		if ($(window).width() >= 768) {
			return;
		}
		clearTimeout(App.unHideBars);
		setTimeout(unHideBars, 100);
	});

	var checkForDistance = function() {
		if (App.order['delivery_type'] == 'takeout') {
			return;
		}
	};

	$(document).on('blur', '[name="pay-address"]', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 100);
	});

	$(document).on('change', '[name="pay-address"]', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 1000);
	});
	
	$(document).on('click', '.config-icon', function() {
		App.forceHome = true;
		App.loadHome();
		$('input').blur();
	});

	$(document).on('change', '[name="pay-address"], [name="pay-name"], [name="pay-phone"], [name="pay-card-number"], [name="notes"]', function() {
		App.config.user.name = $('[name="pay-name"]').val();
		App.config.user.phone = App.phone.format($('[name="pay-phone"]').val());
		App.config.user.address = $('[name="pay-address"]').val();
		App.config.user.card = $('[name="pay-card-number"]').val();
		App.config.user.notes = $('[name="notes"]').val();
		App.config.user.card_exp_month = $('[name="pay-card-month"]').val();
		App.config.user.card_exp_year = $('[name="pay-card-year"]').val();
	});

	App.signin.init();
	App.signup.init();
	App.suggestion.init();

});


App.modal.contentWidth = function(){
	if( $( window ).width() > 700 ){
		return 280;
	}
	if( $( window ).width() <= 700 ){
		return $( window ).width() - 50;
	}
}

App.getCommunityById = function( id ){
	for (x in App.communities) {	
		if( App.communities[x].id_community == id ){
			return App.communities[x];
		}
	}
	return false;
}

App.message = {};
App.message.show = function( title, message ) {
	if( $( '.message-container' ).length > 0 ){
		$( '.message-container' ).html( '<h1>' + title + '</h1><div class="message-container-content">' +   message + '</div>' );
	} else {
		var html = '<div class="message-container">' +
			'<h1>' + title + '</h1>' +
			'<div class="message-container-content">' + 
			message +
			'</div>' +
			'</div>';
		$('.wrapper').append(html);
	}

	$('.message-container')
		.dialog({
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			close: function( event, ui ) { App.modal.shield.close(); },
		});

}

App.message.chrome = function( ){
	var title = 'How to use Chrome',
		message = '<p>' +
		'Just tap "Request Desktop Site.' +
		'</p>' +
		'<p align="center">' +
		'<img style="border:1px solid #000" src="/assets/images/chrome-options.png" />' + 
		'</p>';
	App.message.show(title, message);
}

google.load('maps', '3',  {callback: App.loc.preProcess, other_params: 'sensor=false'});
