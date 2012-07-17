if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
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
	config: {},
	order: {
		cardChanged: false,
		pay_type: 'card',
		deliver_type: 'deliver',
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
	var finalid, args = arguments, complete;

	if (args[2]) {
		complete = args[2];
	} else {
		complete = function() {};
	}

	if (typeof(id) == 'object') {
		//console.log ('storing object',type,id);
		App.cached[type][id.id] = id;
		eval('App.cached[type][id.id] = new '+type+'(id,complete)');
		finalid = id.id;

	} else if (!App.cached[type][id]) {
		//console.log ('creating from network',type,id);
		eval('App.cached[type][id] = new '+type+'(id,complete)');

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
			$('.main-content-item').show();
			$('.main-content-item').html('just a sec...');

			setTimeout(function() {
				// @todo: fix this so it works
				//History.replaceState({},'/yale','/yale');
			},500);
			return;
		}
		
		// force build permalink ids
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

App.page.order = function(data) {
	$('.main-content-item').html(JSON.stringify(data));
};

App.page.community = function(id) {

	// probably dont need this since we force community to be loaded
	//App.community = App.cache('Community', id, function() {

		document.title = 'Crunchbutton - ' + App.community.name;

		$('.main-content-item').html(
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
		
		// @todo: fix it so it just pulls from this
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
			$('.main-content-item').append('<button class="button-submitorder button-bottom"><div>Submit Order</div></button>');
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
		} else {
			App.cart.loadOrder(App.restaurant.defaultOrder());
		}

		if (App.config.user.id_user) {
			var dp = $('<div class="delivery-payment-info content-padder"></div>')
				.append('<div class="dp-display-phone dp-display-item"><label>Your phone number is:</label><br /><a href="javascript:;">' + (App.config.user.phone ? App.config.user.phone : '<i>no phone # provied</i>') + '</a></div>')
				.append('<div class="dp-display-payment dp-display-item"><label>Your are paying:</label><br /><span class="cart-total">$0.00</span> and <a href="javascript:;">10% tip</a> by <a href="javascript:;">card</a></div>')
				.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br /><a href="javascript:;">' + (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>') + '</a></div>')
				.append('<div class="divider"></div>');
	
			$('.main-content-item').append(dp);
			$('<div class="content-padder-before"></div>').insertBefore(dp);
		}
	});
	
	App.drawPay();
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
			'<div class="input-item delivery-only"><textarea name="pay-deliver"></textarea></div><div class="divider"></div>' + 
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
	
	if (App.order['pay_type'] == 'takeout') {
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
	$('[name="pay-deliver"]').val(App.config.user.address);

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

	
	// force to yale if there isnt a place yet
	// @todo: detect community
	if (!url) {
		var closest = App.loc.closest();
		if (closest.permalink) {
			loc = '/' + closest.permalink;
			path = [closest.permalink];
		} else {
			loc = '/yale';
			path = ['yale'];
		}
		History.replaceState({},loc,loc);
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
	// retaurant only

	if (restaurantRegex.test(url)) {

		var restaurant = App.cached['Restaurant'][path[1]];
		var orderRegex = new RegExp('^\/' + App.community.permalink + '\/' + restaurant.permalink + '\/order$', 'i');

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
					App.page.order(json);
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
		$('[name="pay-deliver"]').val("123 main\nsanta monica ca");
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
			$(this).addClass('meal-item-down');
		},
		touchstart: function() {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				return;
			}
			$(this).addClass('meal-item-down');
		}
	});
	$('.meal-item-content').live({
		mouseup: function() {
			$(this).removeClass('meal-item-down');
		},
		touchend: function() {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				return;
			}
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
	
	$('.button-deliver-payment, .dp-display-item a').live('click',function() {
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
		App.community = null;
		$('.main-content-item').hide();
		History.pushState({}, loc, loc);
		//App.loadPage();
	});
	
	if (screen.width <= 480) {
		
	}
	
	var select = $('<select class="community-select">');
	for (x in App.communities) {
		select.append('<option value="' + x + '"' + (x == 'yale' ? ' selected' : '') + '>' + App.communities[x].name + '</option>');
	}
	$('.community-selector').append(select);

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
		// {from: {lat: 1, lon: 1}, to: {lat: 2, lon: 2}}
		var R = 6371; // Radius of the earth in km
		var dLat = (params.to.lat - params.from.lat).toRad();  // Javascript functions in radians

		var dLon = (params.to.lon - params.from.lon).toRad(); 
		var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(params.from.lat.toRad()) * Math.cos(params.to.lat.toRad()) * 
			Math.sin(dLon/2) * Math.sin(dLon/2); 
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
		var d = R * c; // Distance in km

		return d;
	},
	closest: function() {
		try {
			App.loc.lat = google.loader.ClientLocation.latitude;
			App.loc.lon = google.loader.ClientLocation.longitude;
	
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
			return closest;
		} catch (e) {
			return App.communities['yale'];
		}
	}
}


/*
if (navigator.geolocation) {

} else {

}

navigator.geolocation.getCurrentPosition(function(position){
    var lat = position.coords.latitude;
    var lon = position.coords.longitude;
    var marker = new GMarker(new GLatLng(lat, lon));
    
    var jsMap = new GMap2(document.getElementById("jsMap"));
    jsMap.addOverlay(marker);
},function(error){

});
*/

if (typeof(Number.prototype.toRad) === "undefined") {
	Number.prototype.toRad = function() {
		return this * Math.PI / 180;
	}
}


