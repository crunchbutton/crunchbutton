/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */


if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
}

if (typeof(Number.prototype.toRad) === 'undefined') {
	Number.prototype.toRad = function() {
		return this * Math.PI / 180;
	}
}

var History = window.History;

History.Adapter.bind(window,'statechange',function() {
	var State = History.getState();
	History.log(State.data, State.title, State.url);

	if (!App.config) return;

	if (App._init) {
		App.loadPage();
	}
});
    

var App = {
	service: '/api/',
	cached: {},
	cart: {},
	community: null,
	page: {},
	config: null,
	order: {
		cardChanged: false,
		pay_type: 'card',
		delivery_type: 'delivery',
		tip: '10'
	},
	_init: false,
	_pageInit: false
};

if (typeof(Ti) != 'undefined') {
	App.request = function(url, complete) {
		var client = Ti.Network.createHTTPClient();
		client.open("GET", url, false);
		client.send();
		console.log('[REQUEST]',url);
		console.log('[RESPONSE]',client.responseText);
		complete($.parseJSON(client.responseText),client.responseText);
	};
} else {
	App.request = function(url, complete) {
		$.getJSON(url,function(json) {
			complete(json);
		});
	};
}

// quick and dirty
App.cache = function(type, id) {
	var finalid, args = arguments, complete, partComplete;

	complete = args[2] ? args[2] : function() {};

	partComplete = function() {
		if (this.uuid) {
			App.cached[type][id.uuid] = this;
		}
		if (this.permalink) {
			App.cached[type][id.permalink] = this;
		}
		complete.call(this);
	}

	if (typeof(id) == 'object') {
		//console.log ('storing object',type,id);
		//App.cached[type][id.id] = id;

		eval('App.cached[type][id.id] = new '+type+'(id,partComplete)');
		finalid = id.id;

	} else if (!App.cached[type][id]) {
		//console.log ('creating from network',type,id);
		eval('App.cached[type][id] = new '+type+'(id,partComplete)');

	} else {
		//console.log ('loading from cache',type,id);
		complete.call(App.cached[type][id]);
	}

	// only works sync (Ti)
	return App.cached[type][finalid || id];

};

App.loadRestaurant = function(id) {
	var loc = '/' + App.community.permalink + '/' + id;
	History.pushState({}, loc, loc);
	//App.loadPage();
};

App.loadPaymentinfo = function() {
	var loc = '/' + App.community.permalink + '/' + App.restaurant.permalink + '/order';
	History.pushState({}, loc, loc);
	//App.loadPage();
}

App.loadCommunity = function(id) {
	var onHold = ['santa-monica'];
	$('.community-select').val(id);

	if (onHold.indexOf(id) != -1) {
		$('.main-content-item').show();
		$('.main-content-item').html('<div class="soon">It\'s a pleasure serving you ' + App.communities[id].name + '. We\'ll be back bigger and better for fall semester.</div>');
		return;
	}

	App.cache('Community',id, function() {
		App.community = this;
		console.log(App.community, id);
		if (!App.community.id_community) {
		
			App.cache('Community','yale', function() {
				App.community = this;
				App.loadPage();
			});
			$('.main-content-item').show();
			$('.main-content-item').html('just a sec...');

			return;
		} else {
			App.loadPage();
		}
	});
};

App.page.community = function(id) {

	// probably dont need this since we force community to be loaded
	//App.community = App.cache('Community', id, function() {

		document.title = 'Crunchbutton - ' + App.community.name;

		$('.main-content-item').html(
			'<div class="home-tagline"><h1>streamlined food ordering</h1></div>' + 
			'<div class="content-padder-before"></div><div class="content-padder"><div class="meal-items"></div></div>'
		);

		var rs = App.community.restaurants();

		if (rs.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}

		for (var x in rs) {
			var restaurant = $('<div class="meal-item" data-id_restaurant="' + rs[x]['id_restaurant'] + '" data-permalink="' + rs[x]['permalink'] + '"></div>');
			var restaurantContent = $('<div class="meal-item-content">');

			restaurantContent
				.append('<div class="meal-pic" style="background: url(/assets/images/food/' + rs[x]['image'] + ');"></div>')
				.append('<h2 class="meal-restaurant">' + rs[x]['name'] + '</h2>')
				.append('<h3 class="meal-food">Top Item: ' + rs[x].top().name + '</h3>');

			if (rs[x].delivery != '1') {
				restaurantContent.append('<div class="meal-item-tag">Take out only</div>');
			}

			restaurant
				.append('<div class="meal-item-spacer"></div>')
				.append(restaurantContent);

			$('.meal-items').append(restaurant);
		}
	//});
};

App.page.restaurant = function(id) {
	$('.content').addClass('short-meal-list');

	App.cache('Restaurant', id, function() {
		if (App.restaurant && App.restaurant.permalink != id) {
			App.cart.resetOrder();
		}

		App.restaurant = this;
		document.title = App.restaurant.name;
		
		$('.main-content-item').html(
			'<div class="cart-summary"><div class="cart-summary-icon"></div><div class="cart-summary-items"></div></div>' +
			'<div class="restaurant-name"><h1>' + App.restaurant.name + '</h1></div>' + 
			(App.restaurant.image ? '<div class="restaurant-pic-wrapper"><div class="restaurant-pic" style="background: url(/assets/images/food/' + App.restaurant.image + ');"></div></div>' : '') + 
			'<div class="main-content-readable">' + 
				'<div class="restaurant-items"></div>' + 
				'<div class="cart-items"><div class="restaurant-item-title">your order</div><div class="cart-items-content"></div></div>' + 
				'<div class="divider"></div>' + 
			'</div>' + 
			'<div class="divider dots restaurant-payment-div"></div>'
		);

		if (!App.config.user.id_user) {
			//$('.main-content-item').append('<button class="button-deliver-payment button-bottom"><div>Next</div></button>');
		} else {
//			$('.main-content-item').append('<button class="button-submitorder button-bottom"><div>Submit Order</div></button>');
		}

		$('.restaurant-items').append(
			'<div class="restaurant-item-title">top items</div>' + 
			'<ul class="resturant-dishes resturant-dish-container"></ul>' + 
			'<div class="restaurant-item-title">top sides</div>' + 
			'<ul class="resturant-sides resturant-dish-container">'
		);

		var
			dishes = App.restaurant.dishes(),
			sides = App.restaurant.sides(),
			extras = App.restaurant.extras();
	
		for (var x in dishes) {
			var dish = $('<li><a href="javascript:;" data-id_dish="' + dishes[x].id_dish + '"><span class="dish-name">' + dishes[x].name + '</span><span class="dish-price">($' + dishes[x].price + ')</span></a></li>');
			$('.resturant-dishes').append(dish);
		}
		
		for (var x in sides) {
			var side = $('<li><a href="javascript:;" data-id_side="' + sides[x].id_side + '"><span class="dish-name">' + sides[x].name + '</span><span class="dish-price">($' + sides[x].price + ')</span></a></li>');
			$('.resturant-sides').append(side);
		}
		for (var x in extras) {
			var extra = $('<li><a href="javascript:;" data-id_extra="' + extras[x].id_extra + '"><span class="dish-name">' + extras[x].name + '</span><span class="dish-price">($' + extras[x].price + ')</span></a></li>');
			$('.resturant-sides').append(extra);
		}

		if (App.cart.hasItems()) {
			App.cart.reloadOrder();
		} else if (App.config.user && App.config.user.defaults && App.config.user.defaults[App.restaurant.id_restaurant]) {
			try {
				App.cart.loadOrder(JSON.parse(App.config.user.defaults[App.restaurant.id_restaurant].config));
			} catch (e) {
				App.cart.loadOrder(App.restaurant.defaultOrder());
			}
		} else {
			App.cart.loadOrder(App.restaurant.defaultOrder());
		}

		if (App.config.user.id_user) {

			var dp = $('<div class="delivery-payment-info content-padder main-content-readable"></div>')
				.append('<div class="dp-display-phone dp-display-item"><label>Your phone number is:</label><br /><a href="javascript:;">' + (App.config.user.phone ? App.config.user.phone : '<i>no phone # provied</i>') + '</a></div>');
			var paying = $('<div class="dp-display-payment dp-display-item"><label>Your are paying:</label><br /><span class="cart-total">$0.00</span></div>');
			if (App.config.user.pay_type == 'card') {
				paying.append('&nbsp;and <a href="javascript:;">10% tip</a> by <a href="javascript:;">card</a>');
			} else {
				paying.append('&nbsp;using <a href="javascript:;">cash</a>');			
			}
			dp.append(paying);
			if (App.config.user.delivery_type == 'delivery') {
				dp.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br /><a href="javascript:;">' + (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>') + '</a></div>');
			} else {
				dp.append('<div class="dp-display-address dp-display-item"><label>Delivered to:</label><br /><a href="javascript:;"><i>takeout</i></a></div>');			
			}
			dp.append('<div class="divider"></div>');
	
			$('.main-content-item').append(dp);
			$('<div class="content-padder-before"></div>').insertBefore(dp);

			App.drawPay();
			$('.payment-form').hide();
		} else {
			App.drawPay();
		}

		var total = App.cart.updateTotal();
	});

};

App.drawPay = function() {
	var total = App.cart.total();

	$('.main-content-item').append(
		'<form class="payment-form main-content-readable">' + 
		'<div class="delivery-info-container"></div><div class="divider"></div>' + 
		'<div class="payment-info-container"></div><div class="divider"></div>' + 
		'<div class="payment-total">Your total is <span class="cart-total">$' + total + '</span> (incl tax<span class="includes-tip"> and tip</span>)</div>' +
		'</form>' + 

		'<button class="button-submitorder-form button-bottom"><div>Submit Order</div></button>'
	);

	$('.delivery-info-container').append(

		'<div class="personal-info field-container">' + 
		
			'<label class="pay-title-label">Delivery Info</label>' + 
			'<div class="input-item toggle-wrapper clearfix">' +
				'<a href="javascript:;" class="delivery-toggle-delivery toggle-item">delivery</a> <span class="toggle-spacer">or</span> <a href="javascript:;" class="delivery-toggle-takeout toggle-item">takeout</a>' + 
			'</div><div class="divider"></div>' + 

			'<label>Name</label>' + 
			'<div class="input-item"><input type="text" name="pay-name"></div><div class="divider"></div>' + 
	
			'<label>Phone #</label>' + 
			'<div class="input-item"><input type="text" name="pay-phone"></div><div class="divider"></div>' + 
	
			'<label class="delivery-only">Deliver to</label>' + 
			'<div class="input-item delivery-only"><textarea name="pay-address"></textarea></div><div class="divider"></div>' + 
		'</div>'
	);

	$('.payment-info-container').append(

		'<div class="payment-info field-container">' + 

			'<label class="pay-title-label">Payment Info</label>' + 
			'<div class="input-item toggle-wrapper">' +
				'<a href="javascript:;" class="pay-toggle-credit toggle-item"><span>card</span></a> <span class="toggle-spacer">or</span>  <a href="javascript:;" class="pay-toggle-cash toggle-item"><span>cash</span></a>' + 
			'</div><div class="divider"></div>' + 

			'<div class="payment-card-info card-only"><p>Your credit card information is <br />super secure and encrypted.<br /><br /></p>' + 
				'<div class="card-icons">' +
					'<img src="/assets/images/payment/Visa-40.png" alt="visa">' + 
					'<img src="/assets/images/payment/Mastercard-40.png" alt="master card">' + 
					'<img src="/assets/images/payment/Amex-40.png" alt="american express">' + 
					'<img src="/assets/images/payment/Discover-40.png" alt="discover card">' + 
				'</div>' + 
			'</div>' + 

			'<label class="card-only">Credit card #</label>' + 
			'<div class="input-item card-only"><input type="text" name="pay-card-number"></div><div class="divider"></div>' + 

			'<label class="card-only">Expiration</label>' + 
			'<div class="input-item card-only">' + 
				'<select name="pay-card-month"><option>Month</option></select>' + 
				'<select name="pay-card-year"><option>Year</option></select><div class="divider"></div>' + 
			'</div>' + 

			'<div class="divider"></div><label class="card-only">Tip</label>' + 
			'<div class="input-item card-only">' + 
				'<select name="pay-tip"></select>' + 
				'<div class="divider"></div>' + 
			'</div>' + 
		'</div>'
	);

	var tips = [0,5,10,15,20];
	for (var x in tips) {
		$('[name="pay-tip"]').append('<option value="' + tips[x] + '">' + tips[x] + '%</option>');
	}	
	for (var x=1; x<=12; x++) {
		$('[name="pay-card-month"]').append('<option>' + x + '</option>');
	}
	var date = new Date().getFullYear();
	for (var x=date; x<=date+20; x++) {
		$('[name="pay-card-year"]').append('<option>' + x + '</option>');
	}
	
	if (App.order['pay_type'] == 'cash') {
		$('.pay-toggle-cash').click();
	} else {
		$('.pay-toggle-credit').click();
	}

	if (App.order['delivery_type'] == 'takeout') {
		$('.delivery-toggle-takeout').click();
	} else {
		$('.delivery-toggle-delivery').click();
	}
	$('[name="pay-tip"]').val(App.order.tip);
	
	//$('[name="pay-card-number"]').val('4242424242424242');
	//$('[name="pay-card-month"]').val('1');
	//$('[name="pay-card-year"]').val('2020');
		
	$('[name="pay-name"]').val(App.config.user.name);
	$('[name="pay-phone"]').val(App.config.user.phone);
	$('[name="pay-address"]').val(App.config.user.address);
	$('[name="pay-card-number"]').val(App.config.user.card);

};

App.page.order = function(id) {
	App.cache('Order', id, function() {
		var message, order = this;

		if (App.justCompleted) {
			App.justCompleted = false;
			message = 'Thanks ' + this.name + '!';
		} else {
			message = 'Your order';
		}
		$('.content').addClass('short-meal-list');
		$('.main-content-item').css('width','auto');
		$('.main-content-item').html(
			'<div class="content-padder-before"></div>' +
			'<div class="delivery-payment-info content-padder main-content-readable"></div>'
		);
		$('.delivery-payment-info').html(
			'<span class="order-thanks-message">'+ message +'</span>' + 
			'<br /><br />'
		);

		if (this.delivery_type == 'delivery') {
			$('.delivery-payment-info').append('<b>Your delivery address is:</b><br />' + this.address + '<br /><br />');
		} else {
			$('.delivery-payment-info').append('<b>For pickup</b><br />');
		}
		
		$('.delivery-payment-info').append('<b>Your phone # is:</b><br />' + this.phone + '<br /><br />');
		
		
		$('.delivery-payment-info').append('<b>Your ordered:</b>' + order._message + '<br /><br />');
		
		if (this.pay_type == 'card') {
			$('.delivery-payment-info').append('<b>Your total is:</b><br />$' + parseInt(this.price).toFixed(2) + '<br /><br />');
		} else {
			$('.delivery-payment-info').append('<b>Total approximate total is</b>:<br />$' + parseInt(this.price).toFixed(2) + '<br /><br />');
		}
		
		App.cache('Restaurant',order.id_restaurant, function() {
			$('.delivery-payment-info').append('For updates on your order, please call<br />' + this.name + ': <b>' + this.phone + '</b><br /><br />');
			
			$('.delivery-payment-info').append('To reach Crunchbutton, <a href="javascript:;" onclick="$(\'.habla_button\').click();">message us</a><br />or call <b>(213) 2 WENZEL</b><br /><br />');
			
			
		});

	});

};

App.page.paymentinfo = function() {
	var total = App.cart.total();
	if (total == '0.00') {
		App.loadRestaurant(App.restaurant.permalink);
		return;
	}
	$('.content').addClass('short-meal-list');
	App.drawCart();
	

};

App.loadPage = function() {

	var
		url = History.getState().url.replace(/http:\/\/.*?\/(.*)/,'$1'), //location.pathname
		path = url.split('/');

	console.log(url,path);

	// only fires on chrome
	if (!App.config) {
		return;
	}
	
	// non app methods. just display the page.
	switch (true) {
		case /^help|legal|support|pay/.test(url):
			return;
			break;
	}

	// hide whatever we have
	if (App._pageInit) {
		$('.main-content-item').hide();
	} else {
		App._pageInit = true;
	}

	
	// force to a specific community
	if (!url) {
		App.loc.process();
		return;
	}

	if (!App.community) {
		// force load of community reguardless of landing (this contains everything we need)
		console.log('loading',path[0])
		App.loadCommunity(path[0]);
		
		return;
	}

	var communityRegex = new RegExp('^\/' + App.community.permalink + '$', 'i');
	var restaurantRegex = new RegExp('^\/(restaurant)|(' + App.community.permalink + ')/', 'i');
	var orderRegex = new RegExp('^order\/', 'i');

	// retaurant only
	/*
	if (restaurantRegex.test(url)) {
		var restaurant = App.cached['Restaurant'][path[1]];
//		var orderRegex = new RegExp('^\/' + App.community.permalink + '\/' + restaurant.permalink + '\/order$', 'i');

		switch (true) {
			case orderRegex.test(url):
				App.restaurant = restaurant;
				App.page.paymentinfo();
				setTimeout(scrollTo, 80, 0, 1);
				$('.main-content-item').fadeIn();
				return;
				break;
		}
	}
	*/

	switch (true) {
		case communityRegex.test(url):
			setTimeout(scrollTo, 80, 0, 1);
			$('.main-content-item').show();
			App.page.community(App.community.id);
			return;
			break;

		case restaurantRegex.test(url):
			App.page.restaurant(path[1]);
			break;

		case orderRegex.test(url):
			App.page.order(path[1]);
			break;

		default:
			App.page.community(App.community.id);
			break;
	}
	setTimeout(scrollTo, 80, 0, 1);
	$('.main-content-item').fadeIn();
};

App.cart = {
	items: {
		dishes: {},
		sides: {},
		extras: {},
	},
	add: function(type, item) {
		var
			id = _.uniqueId('cart-'),
			top = App.cached['Dish'][item].toppings(),
			sub = App.cached['Dish'][item].substitutions(),
			toppings = {},
			substitutions = {};
			
		if (arguments[2]) {
			toppings = arguments[2]['toppings'];
			substitutions = arguments[2]['substitutions'];
		}

		switch (type) {
			case 'Dish':
				
				for (var x in top) {
					if (top[x]['default'] == 1) {
						toppings[top[x].id_topping] = true;
					}
				}

				for (var x in sub) {
					if (sub[x]['default'] == 1) {
						substitutions[sub[x].id_substitution] = true;
					}
				}

				App.cart.items.dishes[id] = {
					id: item,
					substitutions: substitutions,
					toppings: toppings
				};

				break;

			case 'Side':
				App.cart.items.sides[id] = {
					id: item
				};
				break;

			case 'Extra':
				App.cart.items.extras[id] = {
					id: item
				};
				break;
		}

		var el = $('<div class="cart-item cart-item-dish" data-cart_id="' + id + '" data-cart_type="' + type + '"></div>');
		el.append('<div class="cart-button cart-button-remove"></div>');
		//if (type == 'Dish') {
			el.append('<div class="cart-button cart-button-add"></div>');
		//}
		el.append('<div class="cart-item-name">' + App.cache(type,item).name + '</div>');
		
		if (type == 'Dish' && (App.cached['Dish'][item].toppings().length || App.cached['Dish'][item].substitutions().length)) {
			el.append('<div class="cart-item-config"><a href="javascript:;">Customize</a></div>');
		}
		el.append('<div class="divider"></div>');
		el.hide();

		$('.cart-items-content').append(el);
		el.fadeIn();
		
		App.cart.updateTotal();
	},
	clone: function(item) {
	console.log(item);
		var
			cartid = item.attr('data-cart_id'),
			type = item.attr('data-cart_type'),
			cart;
			
		switch (type) {
			case 'Dish':
				cart = App.cart.items.dishes[cartid];
				break;
			case 'Side':
				cart = App.cart.items.sides[cartid];
				break;
			case 'Extra':
				cart = App.cart.items.extras[cartid];
				break;
		}

		App.cart.add(type,cart.id, {
			toppings: cart.toppings,
			substitutions: cart.substitutions
		});
	},
	remove: function(item) {
		var
			name,
			cart = item.attr('data-cart_id');

		switch (item.attr('data-cart_type')) {
			case 'Dish':
				name = 'dishes';
				break;
			case 'Side':
				name = 'sides';
				break;
			case 'Extra':
				name = 'extras';
				break;
		}
		delete App.cart.items[name][cart];

		item.remove();
		$('.cart-item-customize[data-id_cart_item="' + cart + '"]').remove();

		App.cart.updateTotal();
	},
	updateTotal: function() {
		$('.cart-total').html('$' + App.cart.total());
		if (App.order['pay_type'] == 'card') {
			$('.includes-tip').show();
		} else {
			$('.includes-tip').hide();		
		}

		var
			totalItems = {},
			key, name, text = '';
		$('.cart-summary-items').html('');

		for (var x in App.cart.items) {
			for (var xx in App.cart.items[x]) {
				switch (x) {
					case 'dishes':
						key = 'Dish';
						break;

					case 'sides':
						key = 'Side';
						break;
						
					case 'extras':
						key = 'Extra';
						break;
				}

				name = App.cached[key][App.cart.items[x][xx].id].name;
				if (totalItems[name]) {
					totalItems[name]++;
				} else {
					totalItems[name] = 1;
				}
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
				cartitem = App.cart.items.dishes[cart],
				obj = App.cached['Dish'][cartitem.id],
				top = obj.toppings(),
				sub = obj.substitutions();
	
			for (var x in top) {
				var check = $('<input type="checkbox" class="cart-customize-check">');
				if (cartitem.toppings[top[x].id_topping]) {
					check.attr('checked','checked');
				}
				var topping = $('<div class="cart-item-customize-item" data-id_topping="' + top[x].id_topping + '"></div>')
					.append(check)
					.append('<label class="cart-item-customize-name">' + top[x].name + '</label><label class="cart-item-customize-price">' + (top[x].price != '0.00' ? '&nbsp;($' + top[x].price + ')' : '') + '</label></label>');
				el.append(topping);
			}
			
			for (var x in sub) {
				var check = $('<input type="checkbox" class="cart-customize-check">');
				if (cartitem.substitutions[sub[x].id_substitution]) {
					check.attr('checked','checked');
				}
				var substitution = $('<div class="cart-item-customize-item" data-id_substitution="' + sub[x].id_substitution + '"></div>')
					.append(check)
					.append('<label class="cart-item-customize-name">' + sub[x].name + '</label><label class="cart-item-customize-price">' + (sub[x].price != '0.00' ? '&nbsp;($' + sub[x].price + ')' : '') + '</label></label>');
				el.append(substitution);
			}
		}
	},
	customizeItem: function(item) {

		var
			cart = item.closest('.cart-item-customize').attr('data-id_cart_item'),
			cartitem = App.cart.items.dishes[cart],
			customitem = item.closest('.cart-item-customize-item'),
			top = customitem.attr('data-id_topping')
			sub = customitem.attr('data-id_substitution');

		if (top) {
			if (item.is(':checked')) {
				cartitem.toppings[top] = true;
			} else {
				delete cartitem.toppings[top];
			}
		}
		if (sub) {
			if (item.is(':checked')) {
				cartitem.substitutions[sub] = true;
			} else {
				delete cartitem.substitutions[sub];
			}
		}
		
		App.cart.updateTotal();
			
	},
	submit: function(el) {
		if (App.busy.isBusy()) {
			return;
		}

		App.busy.makeBusy();

		var read = $('.payment-form').length ? true : false;
		console.log(read);

		if (read) {
			App.config.user.name = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val();
			if (App.order['delivery_type'] == 'delivery') {
				App.config.user.address = $('[name="pay-address"]').val();
			}
			App.order.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart: App.cart.items,
			pay_type: App.order['pay_type'],
			delivery_type: App.order['delivery_type'],
			restaurant: App.restaurant.id
		};
		
		if (order.pay_type == 'card') {
			order.tip = App.order.tip ? App.order.tip : '10';
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

		console.log('ORDER:',order);
		
		var errors = [];
		
		if (!order.name) {
			errors[errors.length] = 'Please enter your name.';
		}
		
		if (!order.phone) {
			errors[errors.length] = 'Please enter a valid phone #.';
		}
		
		if (order.delivery_type == 'delivery' && !order.address) {
			errors[errors.length] = 'Please enter an address.';
		}
		
		if (order.pay_type == 'card' && ((App.order.cardChanged && !order.card.number) || (!App.config.user.id_user && !order.card.number))) {
			errors[errors.length] = 'Please enter a valid card #.';
		}
		
		if (!App.cart.hasItems()) {
			errors[errors.length] = 'Please add something to your order.';
		}
		
		if (errors.length) {
			var error = '';
			for (x in errors) {
				error += errors[x] + "\n";
			}
			alert(error);
			App.busy.unBusy();
			return;
		}

		$.ajax({
			url: App.service + 'order',
			data: order,
			dataType: 'json',
			type: 'POST',
			complete: function(json) {
				
				json = $.parseJSON(json.responseText);
				console.log(json);
				
				if (json.status == 'false') {
					var error = '';
					for (x in json.errors) {
						error += json.errors[x] + "\n";
					}
					alert(error);

				} else {
					if (json.token) {
						$.cookie('token', json.token, { expires: new Date(3000,01,01), path: '/', });
					}

					order.cardChanged = false;
					App.justCompleted = true;
					
					$.getJSON('/api/config', App.processConfig);
					App.cache('Order',json.uuid,function() {
						var loc = '/order/' + this.uuid;
						History.pushState({},loc,loc);
					});

					

					
				}
				App.busy.unBusy();
			}
		});
	},
	total: function() {
		var
			total = 0,
			dish;
		for (var x in App.cart.items) {
			for (var xx in App.cart.items[x]) {
				switch (x) {
					case 'dishes':
						total += parseFloat(App.cached['Dish'][App.cart.items[x][xx].id].price);
						for (var xxx in App.cart.items[x][xx].toppings) {
							total += parseFloat(App.cached['Topping'][xxx].price);
						}
						for (var xxx in App.cart.items[x][xx].substitutions) {
							total += parseFloat(App.cached['Substitution'][xxx].price);
						}
						break;

					case 'sides':
						total += parseFloat(App.cached['Side'][App.cart.items[x][xx].id].price);
						break;
						
					case 'extras':
						total += parseFloat(App.cached['Extra'][App.cart.items[x][xx].id].price);
						break;
				}
			}
		}
		var final = total + (total * (App.restaurant.tax/100));

		if (App.order['pay_type'] == 'card') {
			final += (total * (App.order.tip/100));
		}

		return final.toFixed(2);
	},
	resetOrder: function() {
		App.cart.items = {
			dishes: {},
			sides: {},
			extras: {},
		};
		$('.cart-items-content, .cart-total').html('');
	},
	reloadOrder: function() {
		var cart = App.cart.items;
		App.cart.resetOrder();
		App.cart.loadOrder(cart);
	},
	loadOrder: function(order) {
	console.log('LOAD',order);
		try {
			if (order) {
				for (var x in order) {
					switch (x) {
						case 'dishes':
							for (var xx in order[x]) {
								App.cart.add('Dish',order[x][xx].id,{
									toppings: order[x][xx].toppings,
									substitutions: order[x][xx].substitutions
								});
							}
							break;
		
						case 'sides':
							for (var xx in order[x]) {
								App.cart.add('Side',order[x][xx].id);
							}
							break;
		
						case 'extras':
							for (var xx in order[x]) {
								App.cart.add('Extra',order[x][xx].id);
							}
							break;
					}
				}
			}
		} catch (e) { console.log(e.stack); }
		App.cart.updateTotal();
	},
	hasItems: function() {
		if (!$.isEmptyObject(App.cart.items.dishes) || !$.isEmptyObject(App.cart.items.sides) || !$.isEmptyObject(App.cart.items.extras)) {
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
	orders: function() {
		$.getJSON('/api/user/orders',function(json) {
			var c = '';
			for (var x in json) {
				for (var xx in json[x]) {
					c += xx + ' - ' + json[x][xx] + '<br />';
				}
			}
			$('.main-content-item').html(c);
		});
	},
	cart: function() {
		alert(JSON.stringify(App.cart.items));
	}
};

App.processConfig = function(json) {
	App.config = json;
	if (App.config.user) {
		App.order['pay_type'] = App.config.user['pay_type'];
		App.order['delivery_type'] = App.config.user['delivery_type'];
	}
};

$(function() {

	$('.delivery-toggle-delivery').live('click',function() {
		$('.delivery-toggle-takeout').removeClass('toggle-active');
		$('.delivery-toggle-delivery').addClass('toggle-active');
		$('.delivery-only').show();
		App.order['delivery_type'] = 'delivery';
	});
	
	$('.delivery-toggle-takeout').live('click',function() {
		$('.delivery-toggle-delivery').removeClass('toggle-active');
		$('.delivery-toggle-takeout').addClass('toggle-active');
		$('.delivery-only').hide();
		App.order['delivery_type'] = 'takeout';
	});
	
	$('.pay-toggle-credit').live('click',function() {
		$('.pay-toggle-cash').removeClass('toggle-active');
		$('.pay-toggle-credit').addClass('toggle-active');
		$('.card-only').show();
		App.order['pay_type'] = 'card';
		App.cart.updateTotal();
	});
	
	$('.pay-toggle-cash').live('click',function() {
		$('.pay-toggle-credit').removeClass('toggle-active');
		$('.pay-toggle-cash').addClass('toggle-active');
		$('.card-only').hide();
		App.order['pay_type'] = 'cash';
		App.cart.updateTotal();
	});

	$('.meal-item-content').live({
		mousedown: function() {
			if (navigator.userAgent.toLowerCase().indexOf('ios') > -1 || navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				return;
			}
			$(this).addClass('meal-item-down');
			var self = $(this);
			setTimeout(function() {
				App.loadRestaurant(self.closest('.meal-item').attr('data-permalink'));
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
			
			var maxDistance = 10;
			if (Math.abs(App.startX-App.touchX) < maxDistance && Math.abs(App.startY-App.touchY) < maxDistance) {
				App.loadRestaurant($(this).closest('.meal-item').attr('data-permalink'));
			}
			$(this).removeClass('meal-item-down');
		}
	});

	$('.meal-item').live('click',function() {
		//App.loadRestaurant($(this).attr('data-permalink'));
	});
	
	$('.resturant-dish-container a').live('click',function() {
		if ($(this).attr('data-id_dish')) {
			App.cart.add('Dish',$(this).attr('data-id_dish'));

		} else if ($(this).attr('data-id_side')) {
			App.cart.add('Side',$(this).attr('data-id_side'));

		} else if ($(this).attr('data-id_extra')) {
			App.cart.add('Extra',$(this).attr('data-id_extra'));

		} else if ($(this).hasClass('restaurant-menu')) {
			return;
		}
	});
	
	$('.cart-button-remove').live('click',function() {
		App.cart.remove($(this).closest('.cart-item'));
	});
	
	$('.cart-button-add').live('click',function() {
		App.cart.clone($(this).closest('.cart-item'));
	});
	
	$('.cart-item-config a').live('click',function() {
		App.cart.customize($(this).closest('.cart-item'));
	});
	
	$('.button-submitorder').live('click',function() {
		App.cart.submit($(this));
	});
	$('.button-submitorder-form').live('click',function() {
		App.cart.submit($(this),true);
	});
	
	$('.button-deliver-payment, .dp-display-item a').live('click',function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});
	
	$('.button-bottom').live({
		mousedown: function() {
			$(this).addClass('button-bottom-click');
		},
		touchstart: function() {
			$(this).addClass('button-bottom-click');
		}
	});
	$('.button-bottom').live({
		mouseup: function() {
			$(this).removeClass('button-bottom-click');
		},
		touchend: function() {
			$(this).removeClass('button-bottom-click');
		}
	});
	
	$('.cart-summary').live('click', function() {
		
	});
	
	$('.cart-customize-check').live('change',function() {
		App.cart.customizeItem($(this));
	});
	
	$('.cart-item-customize-item label').live('click', function() {
		$(this).prev('input').click();
	});
	
	$('[name="pay-tip"]').live('change',function() {
		App.order.tip = $(this).val();
		console.log(App.order.tip);
		var total = App.cart.total();
		console.log(total);
		App.cart.updateTotal();
	});
	
	$('.community-select').live('change',function() {
		var loc = '/' + $(this).val();
		
		$.cookie('community', $(this).val(), { expires: new Date(3000,01,01), path: '/', });
		
		App.community = null;
		$('.main-content-item').hide();
		History.pushState({}, loc, loc);
		//App.loadPage();
	});
	
	$('[name="pay-card-number"], [name="pay-card-month"], [name="pay-card-year"]').live('change', function() {
		App.order.cardChanged = true;
	});
	
	if (screen.width <= 480) {
		
	}
	
	var select = $('<select class="community-select">');
	var selected = $.cookie('community') ? $.cookie('community') : 'yale';
	for (x in App.communities) {
		select.append('<option value="' + x + '"' + (x == selected ? ' selected' : '') + '>' + App.communities[x].name + '</option>');
	}
	$('.community-selector').append(select);

	// make sure we have our config loaded
	// @todo: encode this data into the initial request and update as needed
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

	$('.cart-summary').live('click', function() {
		$('body').scrollTop($('.cart-items').position().top-80);
	});
});

// trash
String.prototype.capitalize = function(){
	return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
};

/*
(function(){  
var init = function() {  
  var updateOrientation = function() {
  	var html = $('.wrapper, .content, body').css('width',document.body.clientWidth + 'px');
  	alert('asd');
    var orientation = window.orientation;  
  
    switch(orientation) {  
      case 90: case -90:  
        orientation = 'landscape';  
      break;  
      default:  
        orientation = 'portrait';  
    }  
  
    // set the class on the HTML element (i.e. )  
    document.body.parentNode.setAttribute('class', orientation);  
  };  
  
  // event triggered every 90 degrees of rotation  
  window.addEventListener('orientationchange', updateOrientation, false);  
  
  // initialize the orientation  
  updateOrientation();  
}  
  
window.addEventListener('DOMContentLoaded', init, false);  
  
})();  
*/

App.loc = {
	distance: function(params) {

		var R = 6371; // Radius of the earth in km
		var dLat = (params.to.lat - params.from.lat).toRad();

		var dLon = (params.to.lon - params.from.lon).toRad();
		var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(params.from.lat.toRad()) * Math.cos(params.to.lat.toRad()) * 
			Math.sin(dLon/2) * Math.sin(dLon/2); 
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
		var d = R * c; // Distance in km

		return d;
	},
	getClosest: function() {
		var closest;
		for (x in App.communities) {
			App.communities[x].distance = App.loc.distance({
				from: {lat: App.loc.lat, lon: App.loc.lon},
				to: {lat: parseFloat(App.communities[x].loc_lat), lon: parseFloat(App.communities[x].loc_lon)}
			});
			if (!closest || App.communities[x].distance < closest.distance) {
				closest = App.communities[x];
			}
		}
		return closest || App.communities['yale'];
	},
	closest: function(complete) {

		if (google.loader.ClientLocation) {
			App.loc.lat = google.loader.ClientLocation.latitude;
			App.loc.lon = google.loader.ClientLocation.longitude;

			complete();

		} else {

			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position){
				    App.loc.lat = position.coords.latitude;
				    App.loc.lon = position.coords.longitude;

				    complete();
				});
			}
		}
	},
	process: function() {
		App.loc.closest(function() {
			var
				closest = App.loc.getClosest(),
				loc;

			if ($.cookie('community') && !App.community) {
				loc = '/' + $.cookie('community');
	
			} else if (closest.permalink) {
				if (closest.distance > 25) {
					location.href = '/hello';
					return;
				}

			    if (closest.permalink != App.community.permalink) {
			    	loc = '/'.closest.permalink;
				}
	
			} else if (!App.community) {
				loc = '/yale';
			}

			if (loc) {
				History.replaceState({},loc,loc);
			}
		});

	}
}