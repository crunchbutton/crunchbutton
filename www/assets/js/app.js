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
	tagline: '',
	service: '/api/',
	cached: {},
	community: null,
	page: {},
	config: null,
	forceHome: false,
	cookieExpire: new Date(3000,01,01),
	order: {
		cardChanged: false,
		pay_type: 'card',
		delivery_type: 'delivery',
		tip: 'autotip'
	},
	signin : {},
	suggestion : {},
	restaurants: {
		permalink : 'food-delivery',
		list: false
	},
	defaultTip: 'autotip',
	defaultRange : 2,
	modal: {},
	hasBack: false,
	_init: false,
	_pageInit: false,
	_identified: false,
	isDeliveryAddressOk : false,
	tips: [0,10,15,18,20,25,30],
	touchX: null,
	touchY: null,
	touchOffset: null,
	crunchSoundAlreadyPlayed : false,
	useCompleteAddress : true, /* if true it means the address field will be fill with the address found by google api */
	boundingBoxMeters : 8000,
	useRestaurantBoundingBox : false
};

App.alert = function(txt) {
	setTimeout(function() {
		alert(txt);
	});
};

App.loadRestaurant = function(id) {

	App.cache('Restaurant', id,function() {

		if (!this.open()) {
			App.alert("This restaurant is currently closed. It will be open during the following hours (" + this._tzabbr + "):\n\n" + this.closedMessage());
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
App.routeAlias = function(id, success, error) {
	id = id.toLowerCase();
	alias = App.aliases[id] || false;
	success = success || function(){};
	error = error || function(){};

	if (alias) {
		// Get the location of the alias
		var loc = App.locations[alias.id_community];

		if (loc.loc_lat && loc.loc_lon) {
			var res = {
				lat: loc.loc_lat,
				lon: loc.loc_lon,
				prep: alias.prep,
				city: alias.name_alt,
				address: alias.name_alt
			};
			App.loc.range = loc.range || App.defaultRange;

			success({alias: res});
			return;
		}
	}
	
	error();
};

App.loadHome = function(force) {
	$('input').blur();

	App.currentPage = 'home';
	History.pushState({}, 'Crunchbutton', '/');
	
	App.page.home(force);

};

App.render = function(template, data) {
	var compiled = _.template($('.template-' + template).html());
	return compiled(data);
};

App.showPage = function(params) {

	// Hides the gift card message
	App.credit.hide();

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
	// Force it!
	setTimeout( function(){App.signin.checkUser()}, 500 );

	var url = History.getState().url.replace(/http(s)?:\/\/.*?\/(.*)/,'$2').replace('//','/');
	// check if there are any query string vars and ignore it. See #939
	url = url.split( '?' )[0];

	var path = url.split('/');

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
		App.loadHome();
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
		case /^giftcard/i.test(url):
			App.page.giftCard( path );
			break;
		case new RegExp( App.restaurants.permalink + '$', 'i' ).test( cleaned_url ):
			App.page.foodDelivery();
			break;
		case restaurantRegex.test(url):
			App.page.restaurant(path[1]);
			break;
		default:
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
		// return;
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
	//  || App.config.env != 'live'
	if (!App.config) {
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
		//return;
	}
	if (App.config.user.uuid) {
		mixpanel.identify(App.config.user.uuid);
		mixpanel.people.set({
			$name: App.config.user.name,
			$ip: App.config.user.ip,
			$email: App.config.user.email
		});
	}
};


/**
 * generate ab formulas
 */
App.AB = {
	options: {
		tagline: [
			{
				name: 'tagline-for-free',
				tagline: 'Order the top food %s. For free. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'
			},
			{
				name: 'tagline-no-free',
				tagline: 'Order the top food %s. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'		
			}
		],
		slogan: [
			{
				name: 'slogan-push-food',
				slogan: 'Push a button. Get Food.'
			}
		],
		restaurantPage: [
			{
				name: 'restaurant-page-noimage'
			},
			{
				name: 'restaurant-page-image',
				disabled: true
			}
		],
		dollarSign: [
			{
				name : 'show'
			},
			{
				name : 'hide'
			}
		],
	},
	init: function() {
		if (!App.config.ab) {
			// we dont have ab variables. generate them
			App.AB.create(true);
		}
		App.AB.load();
	},
	create: function(clear) {
		if (clear) {
			App.config.ab = {};
		}
		
		_.each(App.AB.options, function(option, key) {
			if (App.config.ab[key]) {
				return;
			}
			var opts = _.filter(App.AB.options[key], function(o) { return o.disabled ? false : true; });
			var opt = opts[Math.floor(Math.random()*opts.length)];
			App.config.ab[key] = opt.name
			App.trackProperty('AB-' + key, opt.name);
		});
		
		App.AB.save();
		console.log(App.config.ab);
		
	},
	load: function() {
		App.slogan = _.findWhere(App.AB.options.slogan, {name: App.config.ab.slogan});
		App.tagline = _.findWhere(App.AB.options.tagline, {name: App.config.ab.tagline});

		if (!App.slogan || !App.tagline) {
			App.AB.create(true);
			App.AB.load(true);
		}
	},
	save: function() {
		$.ajax({
			url: App.service + 'config',
			data: {ab: App.config.ab},
			dataType: 'json',
			type: 'POST',
			complete: function(json) {

			}
		});
	}
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
		App.updateAutotipValue();

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

		if( App.order.pay_type == 'cash' && credit > 0 ){
			totalText += '<span class="giftcard-message">Hey! Pay with a card to make use of your ' +  ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + App.ceil( credit ).toFixed( 2 ) + ' gift card!</span>';
		}

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

	customizeItemPrice: function(price) {
		return price != '0.00' ? '&nbsp;(' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + price.toFixed(2) + ')' : '';
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
							App.cart.customizeItemPrice(price) + '</label>'
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

							var option = $('<option value="' + opt[i].id_option + '">' + opt[i].name + (opt[i].description || '') + (opt[i].price != '0.00' || opt[x].price_linked == '1' ? (' (' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + (parseFloat(opt[i].price) + parseFloat(obj.price)).toFixed(2) + ')') : '') + '</option>');
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
			return;
		}

		// Play the crunch audio just once, when the user clicks at the Get Food button
		if( App.iOS() && !App.crunchSoundAlreadyPlayed ){
			App.playAudio( 'get-food-audio' );
			App.crunchSoundAlreadyPlayed = true;
		}

		// if it is a delivery order we need to check the address
		if( order.delivery_type == 'delivery' ){

			// Check if the user address was already validated
			if ( !App.isDeliveryAddressOk	) {

				// Use the aproxLoc to create the bounding box
				if( App.loc.aproxLoc ){
					var latLong = new google.maps.LatLng( App.loc.aproxLoc.lat, App.loc.aproxLoc.lon );	
				}

				// Use the restautant's position to create the bounding box - just for tests
				if( App.useRestaurantBoundingBox ){
					var latLong = new google.maps.LatLng( App.restaurant.loc_lat, App.restaurant.loc_long );
				}

				if( !latLong ){
					App.alert( 'An error occurred!' );
					App.busy.unBusy();
					return;
				}

				var success = function( results ) {

					// Get the closest address from that lat/lng
					var theClosestAddress = App.loc.theClosestAddress( results, latLong );

					var isTheAddressOk = App.loc.validateAddressType( theClosestAddress );

					if( isTheAddressOk ){
						// Now lets check if the restaurant deliveries at the given address
						var lat = theClosestAddress.geometry.location.lat();
						var lon = theClosestAddress.geometry.location.lng();

						if (!App.restaurant.deliveryHere({ lat: lat, lon: lon})) {
							App.alert( 'Sorry, you are out of delivery range or have an invalid address. \nTry again, or order takeout.' );
							App.busy.unBusy();
						} else {

							if( App.useCompleteAddress ){
								$( '[name=pay-address]' ).val( App.loc.formatedAddress( theClosestAddress ) );
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
					}
				}

				// Address not found!
				var error = function() {
					App.alert( 'Oops, it looks like your address is incomplete. \nPlease enter a street name, number and zip code.' );
					App.busy.unBusy();
				};

				// Call the geo method
				App.loc.doGeocodeWithBound( order.address, latLong, success, error);
				return;
			} 
		}

		if( order.delivery_type == 'takeout' ){
			App.isDeliveryAddressOk = true;
		}

		if( !App.isDeliveryAddressOk ){
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
					App.alert(error);

				} else {

					// Play the crunch audio just once, when the user clicks at the Get Food button
					if( !App.crunchSoundAlreadyPlayed ){
						App.playAudio( 'get-food-audio' );
						App.crunchSoundAlreadyPlayed = true;
					}

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
						delete App.order.tipHasChanged;
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
		$('[name="pay-address"]').val( App.restaurant.address || "123 main\nsanta monica ca" );

		App.order.cardChanged = true;
		App.creditCard.changeIcons( $( '[name="pay-card-number"]' ).val() );
	},
	logout: function() {
		$.getJSON('/api/logout',function(){ location.reload()});
	},
	cart: function() {
		App.alert(JSON.stringify(App.cart.items));
	},
	clearloc: function() {
		$.cookie('community', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lat', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lon', '', { expires: new Date(3000,01,01), path: '/'});
		location.href = '/';
	},
	init: function() {
		$$('.test-card').tap(function() {
			App.test.card();
		});
		$$('.test-logout').tap(function() {
			App.test.logout();
		});
		$$('.test-cart').tap(function() {
			App.test.cart();
		});
		$$('.test-clearloc').tap(function() {
			App.test.clearloc();
		});
	}
};

App.processConfig = function(json, user) {
	if (user && !json) {
		App.config.user = user;
	} else {
		App.config = json;
	}
	App.AB.init();
	if (App.config.user) {
		App.identify();
		App.order['pay_type'] = App.config.user['pay_type'];
		App.order['delivery_type'] = App.config.user['delivery_type'];
		var lastTip = App.config.user['last_tip'] || 'autotip';
		lastTip = App.lastTipNormalize( lastTip );
		App.order['tip'] = lastTip;
	}
};

App.updateAutotipValue = function() {
	var subtotal = App.cart.totalbreakdown().subtotal;
	var autotipValue
	if(subtotal === 0) {
		autotipValue = 0;
	}
	else {
		// the holy formula - see github/#940
		autotipValue = Math.ceil(4*(subtotal * 0.107 + 0.85)) / 4;
	}
	$('[name="pay-autotip-value"]').val(autotipValue);
	var autotipText = autotipValue ? ' (' + ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + autotipValue + ')' : '';
	$('[name=pay-tip] [value=autotip]').html('Autotip' + autotipText);
};

App.lastTipNormalize = function( lastTip ){

	if( lastTip === 'autotip' ) {
		return lastTip;
	}

	lastTip = parseInt( lastTip );
	if( App.config.user && App.config.user.last_tip_type && App.config.user.last_tip_type == 'number' ){
		return App.defaultTip;
	}
	// it means the last tipped value is not at the permitted value, return default.
	if( App.tips.indexOf( lastTip ) > 0 ){
		lastTip = lastTip;
	} else {
		lastTip = App.defaultTip;
	}
	return lastTip;
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

	App.test.init();

	$(document).on('touchclick', '.signout-button', function() {
		App.signin.signOut();
	});

	$(document).on('touchclick', '.signup-add-facebook-button', function() {
		App.signin.facebook.login();
	});

	$(document).on('touchclick', '.change-location-inline', function() {
		App.loadHome(true);
	});

	$(document).on('submit', '.button-letseat-formform', function() {
		$('.button-letseat-form').trigger('touchclick');
		return false;
	});

	$(document).on('touchclick', '.button-letseat-form', function() {

		var success = function() {
			App.page.foodDelivery(true);
		};

		var error = function() {
			$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
		};

		var address = $.trim($('.location-address').val());

		if (!address) {
			// the user didnt enter any address
			$('.location-address').val('').attr('placeholder','Please enter your address here');

		} else if (address && address == App.loc.address()) {
			// we already have a geocode result of that address. dont do it again
			success();

		} else {
			// we need a new geocode result set
			if(App.loc.aproxLoc && App.loc.aproxLoc.lat && App.loc.aproxLoc.lon ){
				App.loc.geocodeLocationPage(address, success, error);
			} else {
				App.loc.geocode(address, success, error);
			}
			
		}
	});

	$(document).on('touchclick', '.delivery-toggle-delivery', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.delivery();
		App.track('Switch to delivery');
	});

	$(document).on('touchclick', '.delivery-toggle-takeout', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.takeout();
		App.track('Switch to takeout');
	});

	$(document).on('touchclick', '.pay-toggle-credit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.credit();
		App.track('Switch to card');
	});

	$(document).on('touchclick', '.pay-toggle-cash', function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.trigger.cash();
		App.track('Switch to cash');
	});

	$(document).on('touchclick', '.location-detect', function() {
		// detect location from the browser
		$('.location-detect-loader').show();
		$('.location-detect-icon').hide();
		
		var error = function() {
			$('.location-address').val('Oh no! We couldn\'t locate you');
			$('.location-detect-loader').hide();
			$('.location-detect-icon').show();
		};

		var success = function() {
			App.page.foodDelivery();
//			$('.location-detect-loader').hide();
//			$('.location-detect-icon').show();
//			$('.button-letseat-form').click();
		};
		
		App.loc.getLocationByBrowser(success, error);
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
	

	$$('.link-help').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - About', '/help');
	});

	$$('.link-legal').tap(function(e) {
	console.log('LEGAL', e)
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - Legal', '/legal');
	});

	$$('.link-orders').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		History.pushState({}, 'Crunchbutton - Orders', '/orders');
	});


	
	if (App.isMobile()) {
	

		// prevent double trigger
		$(document).on('touchclick','input[type="checkbox"]', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		// manually rebind checkbox events
		$$('input[type="checkbox"]').tap(function(e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).checkToggle();
		});
		
		// manually rebind labels
		$$('label[for]').tap(function(e) {
			e.stopPropagation();
			e.preventDefault();
			var target = document.getElementById($(this).attr('for'));
			if (target && target.tagName == 'INPUT') {
				switch ($(target).attr('type')) {
					case 'text':
					case 'password':
					case 'number':
					case 'phone':
					case 'tel':
						$(target).focus();
						break;
					case 'checkbox':
						$(target).checkToggle();
						break;
				}
			}
			$(this).checkToggle();
		});

		// manually bind links
		// @todo: intercept for native app
		$$('a[href]').tap(function(e) {
			var el = $(this);
			var href = el.attr('href');

			if (!href || e.defaultPrevented) {
				return;
			}
			
			if ($(this).attr('target')) {
				window.open($(this).attr('href'), $(this).attr('target'));
			} else {
				location.href = $(this).attr('href');
			}
		});


		// ignore all click events from acidently triggering on mobile. only use touchclick
		$(document).on('click', function(e, force) {
			e.stopPropagation();
			e.preventDefault();
		});
	
		// touch events for restaurant list
		$(document).on({
			touchstart: function(e) {
				if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
					//return;
				}
				App.startX = event.touches[0].pageX;
				App.startY = event.touches[0].pageY;
				App.startOffset = document.all? iebody.scrollLeft : pageYOffset;
	
				$(this).addClass('meal-item-down');
			},
			touchmove: function(e) {
				App.touchX = event.touches[0].pageX;
				App.touchY = event.touches[0].pageY;
				App.touchOffset = document.all? iebody.scrollLeft : pageYOffset;
				
				var maxDistance = 25;
				if (Math.abs(App.startX-App.touchX) > maxDistance || Math.abs(App.startY-App.touchY) > maxDistance || Math.abs(App.startOffset-App.touchOffset) > maxDistance) {
					$(this).removeClass('meal-item-down');
				}
			},
			touchend: function(e) {

				if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
					//return;
				}
				if (App.busy.isBusy()) {
					return;
				}

				var maxDistance = 25;
				var r = $(this).closest('.meal-item').attr('data-permalink');
				var c = $(this).closest('.meal-item').attr('data-permalink-community');

				if ((App.touchX == null && App.touchY == null) || (Math.abs(App.startX-App.touchX) < maxDistance && Math.abs(App.startY-App.touchY) < maxDistance && Math.abs(App.startOffset-App.touchOffset) < maxDistance)) {
					if (r) {
						App.loadRestaurant(r);
					} else if (c) {
						History.pushState({},c,c);
						App.routeAlias(c);
					}
				}
				App.touchX = null;
				App.touchY = null;
				App.touchOffset = null;
				$(this).removeClass('meal-item-down');
			}
		}, '.meal-item-content');
	} else {
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
			}
		}, '.meal-item-content');
	}
	
	$$('.dish-item').tap(function(e) {
		if ($(this).attr('data-id_dish')) {
			App.cart.add($(this).attr('data-id_dish'));
		} else if ($(this).hasClass('restaurant-menu')) {
			return;
		}
	});

	$$('.your-orders a').tap(function() {
		if ($(this).attr('data-id_order')) {
			History.pushState({},'Crunchbutton - Your Order', '/order/' + $(this).attr('data-id_order'));
		}
	});

	$$('.cart-button-remove').tap(function() {
		App.cart.remove($(this).closest('.cart-item'));
	});

	$$('.cart-button-add').tap(function() {
		App.cart.clone($(this).closest('.cart-item'));
	});

	$$('.cart-item-config a').tap(function() {
		App.cart.customize($(this).closest('.cart-item'));
	});

	$$('.button-submitorder-form').tap(function(e) {
		e.preventDefault();
		e.stopPropagation();
		App.crunchSoundAlreadyPlayed = false;
		App.isDeliveryAddressOk = false;
		App.cart.submit($(this),true);
	});


	$(document).on('touchclick', '.button-deliver-payment, .dp-display-item a', function() {
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

	$(document).on('change', '.cart-customize-select', function() {
		App.cart.customizeItem($(this));
	});

	$$('.cart-customize-check').tap( function() {
		// For some reason this tap event have to wait a little time before runs the customizeItem method
		// if we ignore this time it will not read attr checked of the checkbox correctly
		var checkbox = $(this);
		setTimeout( function(){
			App.cart.customizeItem( checkbox );
		}, 1 );
	});

	$$('.cart-item-customize-item label').tap(function() {
		$(this).prev('input').checkToggle();
		App.cart.customizeItem( $(this).prev('input') );
	});

	$(document).on('change', '[name="pay-tip"]', function() {
		App.order.tip = $(this).val();
		App.order.tipHasChanged = true;
		var total = App.cart.total();
		App.cart.updateTotal();
	});

	$$('.nav-back').tap(function() {
		History.back();
	});

	$$('.link-home').tap(function() {
		if( App.restaurants.list && App.restaurants.list.length > 0 ){
			App.page.foodDelivery();
		} else {
			App.loadHome(true);
		}
	});

	$(document).on('change', '[name="pay-card-number"], [name="pay-card-month"], [name="pay-card-year"]', function() {
		if( !App.order.cardChanged ){
			var self = $( this );
			var cardInfo = [ '[name="pay-card-number"]', '[name="pay-card-month"]', '[name="pay-card-year"]' ];
			$( cardInfo ).each( function( index, value ){
				var input = $( value );
				if( self.attr( 'name' ) != input.attr( 'name' ) ){
					input.val( '' );
				}
			} )
		}
		App.order.cardChanged = true;
	});

	// Listener to verify if the user typed a gift card at the notes field
	$(document).on('blur', '[name=notes]', function(){
		App.giftcard.notesField.listener();
	} );
	

	$(document).on('change, keyup', '[name="pay-card-number"]', function() {
		App.creditCard.changeIcons( $(this).val() );
	} );

	$(document).on('keyup', '[name="pay-phone"]', function() {
		$(this).val( App.phone.format($(this).val()) );
	});

	// make sure we have our config loaded
	var haveConfig = function(json) {
		$(document).trigger('have-config');
		App.processConfig(json);
		App._init = true;
		App.loadPage();
	};

	if (App.config) {
		haveConfig(App.config)
	} else {
		$.getJSON('/api/config', haveConfig);
	}

	$$('.cart-summary').tap(function(e) {
		e.stopPropagation();
		e.preventDefault();
		$('html, body').animate({
			scrollTop: $('.cart-items').position().top-80
		}, {
			duration: 500,
			specialEasing: {
				scrollTop: 'easeInOutQuart'
			}
		});
	});
	
	if (App.isMobile()) {
		setInterval(function() {
			var focused = $(':focus');
			if (!focused.length) {
				$('[data-position="fixed"]').show();
				return;
			}

			focused = focused.get(0);

			if (focused.tagName == 'SELECT' || focused.tagName == 'INPUT' || focused.tagName == 'TEXTAREA') {
				$('[data-position="fixed"]').hide();
			} else {
				$('[data-position="fixed"]').show();
			}
		}, 100);
	}
/*
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
	*/

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
	
	$(document).on('touchclick', '.config-icon', function() {
		App.loadHome(true);
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


	$(document).on('touchclick', '.content-item-locations-city', function() {
		$( '.main-content' ).html( '' );
		var permalink = $( this ).attr( 'permalink' );
		App.routeAlias( permalink, function( result ){
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
	App.signin.init();
	App.signup.init();
	App.suggestion.init();
	App.recommend.init();
	App.loc.init();
	App.credit.tooltip.init();
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

App.playAudio = function( audio, callback ){
	var audio = $( '#' + audio ).get(0);
	try{
		audio.addEventListener( 'ended', function() {
		if( callback ){
			callback();
			}
		});
		audio.play();	
	} catch( e ){}
}

App.registerLocationsCookies = function() {
	$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
	$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});
	$.cookie('location_range', ( App.loc.range || App.defaultRange ), { expires: new Date(3000,01,01), path: '/'});
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

