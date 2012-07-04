if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
}

if (typeof(history.pushState) === 'undefined') {
	history.pushState = function() { return null; }
	history.replaceState = function() { return null; }
} else {
	window.onpopstate = function (e) {
		if (App._init) {
			return App.loadPage();
		}
	}
}

var App = {
	service: '/api/',
	cached: {},
	cart: {},
	community: null,
	page: {},
	config: {},
	order: {cardChanged: false},
	_init: false
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
		var triggerCache = arguments[2] ? true : false;
		$.getJSON(url,function(json) {
			if (triggerCache) {
				$('body').triggerHandler('cache-item-loaded-' + arguments[2]);
			}
			complete(json);
		});
	};
}

App.itemLoaded = function(type) {
	$('body').triggerHandler('cache-item-loaded-' + type);
};


// quick and dirty
App.cache = function(type, id) {
	var finalid, args = arguments;
	
	if (arguments[2]) {
		$('body').one('cache-item-loaded-' + type, function() {
			args[2]();
		});
	}

	if (typeof(id) == 'object') {
		///console.log ('storing object',type,id);
		App.cached[type][id.id] = id;
		eval('App.cached[type][id.id] = new '+type+'(id)');
		finalid = id.id;
		$('body').triggerHandler('cache-item-loaded-' + type);

	} else if (!App.cached[type][id]) {
		//console.log ('creating from network',type,id);
		eval('App.cached[type][id] = new '+type+'(id)');
		
	} else {
		//console.log ('loading from cache',type,id);
		$('body').triggerHandler('cache-item-loaded-' + type);
	}

	// only works sync (Ti)
	return App.cached[type][finalid || id];

};

App.loadRestaurant = function(id) {
	var loc = '/' + App.community.permalink + '/' + id;
	history.pushState({}, loc, loc);
	App.loadPage();
};

App.loadPaymentinfo = function() {
	var loc = '/' + App.community.permalink + '/' + App.restaurant.permalink + '/order';
	history.pushState({}, loc, loc);
	App.loadPage();
}

App.loadCommunity = function(id) {
	App.cache('Community',id, function() {
		App.community = App.cached['Community'][id];
		console.log(App.community, id);
		if (!App.community.id_community) {
			$('.main-content').show();
			$('.main-content').html('invalid community');
			return;
		}
		for (var x in App.cached['Community']) {
			App.cached['Community'][App.cached['Community'][x].permalink] = App.cached['Community'][x];
			App.cached['Community'][App.cached['Community'][x].id_community] = App.cached['Community'][x];
		}
		for (var x in App.cached['Restaurant']) {
			App.cached['Restaurant'][App.cached['Restaurant'][x].permalink] = App.cached['Restaurant'][x];
		}
		App.loadPage();
	});
};

App.page.community = function(id) {

	// probably dont need this since we force community to be loaded
	//App.community = App.cache('Community', id, function() {

		document.title = 'Crunchbutton - ' + App.community.name;

		$('.main-content').html(
			'<div class="home-tagline"><h1>one click food ordering</h1></div>' + 
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
		App.restaurant = App.cached['Restaurant'][id];

		document.title = App.restaurant.name;
		
		$('.main-content').html(
			'<div class="restaurant-name"><h1>' + App.restaurant.name + '</h1></div>' + 
			'<div class="restaurant-pic-wrapper"><div class="restaurant-pic" style="background: url(/assets/images/food/' + App.restaurant.image + ');"></div></div>' + 
			'<div class="restaurant-items"></div>' + 
			'<div class="cart-items"></div>' + 
			'<div class="divider"></div>'
		);

		if (!App.config.user.id_user) {
			$('.main-content').append('<button class="button-deliver-payment button-bottom"><div>Next</div></button>');
		} else {
			$('.main-content').append('<button class="button-submitorder button-bottom"><div>Submit Order</div></button>');
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
			var dish = $('<li><a href="javascript:;" data-id_dish="' + dishes[x].id_dish + '">' + dishes[x].name + '</a> ($' + dishes[x].price + ')</li>');
			$('.resturant-dishes').append(dish);
		}
		
		for (var x in sides) {
			var side = $('<li><a href="javascript:;" data-id_side="' + sides[x].id_side + '">' + sides[x].name + '</a> ($' + sides[x].price + ')</li>');
			$('.resturant-sides').append(side);
		}
		for (var x in extras) {
			var extra = $('<li><a href="javascript:;" data-id_extra="' + extras[x].id_extra + '">' + extras[x].name + '</a> ($' + extras[x].price + ')</li>');
			$('.resturant-sides').append(extra);
		}

		if (App.cart.hasItems()) {
			App.cart.reloadOrder();
		} else {
			App.cart.loadOrder(App.restaurant.defaultOrder());
		}

		if (App.config.user.id_user) {
			var dp = $('<div class="delivery-payment-info content-padder"></div>')
				.append('<div class="dp-display-phone dp-display-item"><label>Your phone number is:</label><br /><a href="javascript:;">' + App.config.user.phone + '</a></div>')
				.append('<div class="dp-display-payment dp-display-item"><label>Your are paying:</label><br /><span class="cart-total">$0.00</span> and <a href="javascript:;">10% tip</a> by <a href="javascript:;">card</a></div>')
				.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br /><a href="javascript:;">' + (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>') + '</a></div>')
				.append('<div class="divider"></div>');
	
			$('.main-content').append(dp);
			$('<div class="content-padder-before"></div>').insertBefore(dp);
		}
	});
};


App.page.paymentinfo = function() {
	var total = App.cart.total();
	if (total == '0.00') {
		App.loadRestaurant(App.restaurant.permalink);
		return;
	}
	$('.content').addClass('short-meal-list');

	$('.main-content').html(
		'<div class="payment-total">Your total is $' + total + '</div>' +
		'<form class="payment-form">' + 
		'<div class="delivery-info-container"></div><div class="divider"></div>' + 
		'<div class="payment-info-container"></div><div class="divider"></div>' + 
		'</form>' + 
		'<button class="button-submitorder-form button-bottom"><div>Submit Order</div></button>'
	);

	$('.delivery-info-container').append(

		'<div class="personal-info field-container">' + 
		
			'<label>Delivery Info</label>' + 
			'<div class="input-item">' +
				'<a href="javascript:;" class="delivery-toggle-delivery toggle-item">delivery</a> <span class="toggle-spacer">or</span> <a href="javascript:;" class="delivery-toggle-takeout toggle-item">takeout</a>' + 
			'</div><div class="divider"></div>' + 

			'<label>Name</label>' + 
			'<div class="input-item"><input type="text" name="pay-name"></div><div class="divider"></div>' + 
	
			'<label>Phone #</label>' + 
			'<div class="input-item"><input type="text" name="pay-phone"></div><div class="divider"></div>' + 
	
			'<label class="delivery-only">Deliver to</label>' + 
			'<div class="input-item delivery-only"><textarea name="pay-deliver"></textarea></div><div class="divider"></div>' + 
		'</div>'
	);

	$('.payment-info-container').append(

		'<div class="payment-info field-container">' + 

			'<label>Payment Info</label>' + 
			'<div class="input-item">' +
				'<a href="javascript:;" class="pay-toggle-credit toggle-item">card</a> <span class="toggle-spacer">or</span>  <a href="javascript:;" class="pay-toggle-cash toggle-item">cash</a>' + 
			'</div><div class="divider"></div>' + 

			'<div class="payment-card-info card-only"><p>Your credit card information is secure and encrypted.<br /><br /></p>' + 
				'<img src="/assets/images/payment/Visa-40.png" alt="visa">' + 
				'<img src="/assets/images/payment/Mastercard-40.png" alt="master card">' + 
				'<img src="/assets/images/payment/Amex-40.png" alt="american express">' + 
				'<img src="/assets/images/payment/Discover-40.png" alt="discover card">' + 
			'</div>' + 

			'<label class="card-only">Credit card #</label>' + 
			'<div class="input-item card-only"><input type="text" name="pay-card-number"></div><div class="divider"></div>' + 

			'<label class="card-only">Expiration</label>' + 
			'<div class="input-item card-only">' + 
				'<select name="pay-card-month"><option>Month</option></select>' + 
				'<select name="pay-card-year"><option>Year</option></select><div class="divider"></div>' + 
			'</div>' + 

			'<label>Tip</label>' + 
			'<div class="input-item">' + 
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
	
	$('.delivery-toggle-delivery, .pay-toggle-credit').click();
	$('[name="pay-tip"]').val('10');

};

App.loadPage = function() {

	// only fires on chrome
	if (!App.config) return;
	
	// non app methods
	switch (true) {
		case /^\/help|legal|support|pay/.test(location.pathname):
			return;
			break;
	}

	// hide whatever we have
	$('.main-content').hide();
	
	// page path handler
	var path = location.pathname.split('/');
	
	// force to yale if there isnt a place yet
	// @todo: detect community
	if (location.pathname == '/') {
		loc = '/yale';
		history.replaceState({},loc,loc);
		path = ['','yale'];
	}

	if (!App.community) {
		// force load of community reguardless of landing (this contains everything we need)
		App.loadCommunity(path[1]);
		
		return;
	}

	var communityRegex = new RegExp('^\/' + App.community.permalink + '$', 'i');
	var restaurantRegex = new RegExp('^\/(restaurant|)(' + App.community.permalink + ')/', 'i');
	
	// retaurant only

	if (restaurantRegex.test(location.pathname)) {

		var restaurant = App.cached['Restaurant'][path[2]];
		var orderRegex = new RegExp('^\/' + App.community.permalink + '\/' + restaurant.permalink + '\/order$', 'i');

		switch (true) {
			case orderRegex.test(location.pathname):
				App.restaurant = restaurant;
				App.page.paymentinfo();
				setTimeout(scrollTo, 80, 0, 1);
				$('.main-content').fadeIn();
				return;
				break;
		}
	}

	switch (true) {
		case communityRegex.test(location.pathname):
			setTimeout(scrollTo, 80, 0, 1);
			$('.main-content').show();
			App.page.community(App.community.id);
			return;
			break;

		case restaurantRegex.test(location.pathname):
			App.page.restaurant(path[2]);
			break;

		default:
			App.page.community(App.community.id);
			break;
	}
	setTimeout(scrollTo, 80, 0, 1);
	$('.main-content').fadeIn();
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
		el
			.append('<div class="cart-button cart-button-remove">-</div>')
			.append('<div class="cart-button cart-button-add">+</div>')
			.append('<div class="cart-item-name">' + App.cache(type,item).name + '</div>');
		
		if (type == 'Dish' && (App.cached['Dish'][item].toppings().length || App.cached['Dish'][item].substitutions().length)) {
			el.append('<div class="cart-item-config"><a href="javascript:;">Customize</a></div>');
		}
		el.append('<div class="divider"></div>');
		el.hide();

		$('.cart-items').append(el);
		el.fadeIn();
		
		App.cart.updateTotal();
	},
	clone: function(item) {
		var
			cartid = item.attr('data-cart_id'),
			cart = App.cart.items.dishes[cartid];

		App.cart.add('Dish',cart.id, {
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
		if (el.hasClass('button-bottom-disabled')) {
			return;
		}
		el.addClass('button-bottom-disabled');

		var read = arguments[1] ? true : false;

		if (read) {
			App.config.user.name = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val();
			if (App.order['delivery_type'] == 'deliver') {
				App.config.user.address = $('[name="pay-deliver"]').val();
			}
			App.order.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart: App.cart.items,
			tip: App.order.tip ? App.order.tip : '10',
			pay_type: App.order['pay_type'],
			deliver_type: App.order['deliver_type'],
			restaurant: App.restaurant.id
		};

		if (read) {
			order.address = App.config.user.address;
			order.phone = App.config.user.phone;
			order.name = App.config.user.name;

			if (order.cardChanged || !App.config.user.id_user) {
				order.card = {
					number: $('[name="pay-card-number"]').val(),
					month: $('[name="pay-card-month"]').val(),
					year: $('[name="pay-card-year"]').val()
				};
			}
		}

		console.log(order);

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
					order.cardChanged = false;
					alert('Success!! ... or something...');
				}
				el.removeClass('button-bottom-disabled');

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
		return total.toFixed(2);
	},
	resetOrder: function() {
		App.cart.items = {
			dishes: {},
			sides: {},
			extras: {},
		};
		$('.cart-items, .cart-total').html('');
	},
	reloadOrder: function() {
		var cart = App.cart.items;
		App.cart.resetOrder();
		App.cart.loadOrder(cart);
	},
	loadOrder: function(order) {
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


App.test = {
	card: function() {
		$('[name="pay-card-number"]').val('4242424242424242');
		$('[name="pay-card-month"]').val('1');
		$('[name="pay-card-year"]').val('2020');
		
		$('[name="pay-name"]').val('MR TEST');
		$('[name="pay-phone"]').val('***REMOVED***');
		$('[name="pay-deliver"]').val("123 main\nsanta monica ca");
	}
};

$(function() {

	$('.pay-card-number, .pay-card-month, .pay-card-year').live('change',function() {
		order.cardChanged = true;
	});

	$('.delivery-toggle-delivery').live('click',function() {
		$('.delivery-toggle-takeout').removeClass('toggle-active');
		$('.delivery-toggle-delivery').addClass('toggle-active');
		$('.delivery-only').show();
		App.order['deliver_type'] = 'deliver';

	});
	
	$('.delivery-toggle-takeout').live('click',function() {
		$('.delivery-toggle-delivery').removeClass('toggle-active');
		$('.delivery-toggle-takeout').addClass('toggle-active');
		$('.delivery-only').hide();
		App.order['deliver_type'] = 'takeout';
	});
	
	$('.pay-toggle-credit').live('click',function() {
		$('.pay-toggle-cash').removeClass('toggle-active');
		$('.pay-toggle-credit').addClass('toggle-active');
		$('.card-only').show();
		App.order['pay_type'] = 'card';
	});
	
	$('.pay-toggle-cash').live('click',function() {
		$('.pay-toggle-credit').removeClass('toggle-active');
		$('.pay-toggle-cash').addClass('toggle-active');
		$('.card-only').hide();
		App.order['pay_type'] = 'credit';
	});

	$('.meal-item-content').live({
		mousedown: function() {
			$(this).addClass('meal-item-down');
		},
		touchstart: function() {
			$(this).addClass('meal-item-down');
		}
	});
	$('.meal-item-content').live({
		mouseup: function() {
			$(this).removeClass('meal-item-down');
		},
		touchend: function() {
			$(this).removeClass('meal-item-down');
		}
	});

	$('.meal-item').live('click',function() {
		App.loadRestaurant($(this).attr('data-permalink'));
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
	
	$('.button-deliver-payment').live('click',function() {
		App.loadPaymentinfo();
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
	
	$('.cart-customize-check').live('change',function() {
		App.cart.customizeItem($(this));
	});
	
	$('.cart-item-customize-item label').live('click', function() {
		$(this).prev('input').click();
	});

	// load our config first (not async)
	// @todo: encode this data into the initial request
	$.getJSON('/api/config', function(json) {
		App.config = json;
		App._init = true;
		App.loadPage();

	});
});

// trash
String.prototype.capitalize = function(){
	return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
};