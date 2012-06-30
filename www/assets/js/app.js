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
		console.log ('storing object',type,id);
		App.cached[type][id.id] = id;
		eval('App.cached[type][id.id] = new '+type+'(id)');
		finalid = id.id;
		$('body').triggerHandler('cache-item-loaded-' + type);

	} else if (!App.cached[type][id]) {
		console.log ('creating from network',type,id);
		eval('App.cached[type][id] = new '+type+'(id)');
		
	} else {
		console.log ('loading from cache',type,id);
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

App.loadCommunity = function(id) {
	App.cache('Community',id, function() {
		App.community = App.cached['Community'][id];

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
			'<div class="content-padder-before"></div><div class="content-padder"><div class="meal-items"></div></div>');
	
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
			'<div class="divider"></div>' + 
			'<div class="cart-total"></div>' + 
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

		var dp = $('<div class="delivery-payment-info"></div>')
			.append('<div class="dp-display-delivery"></div>')
			.append('<div class="dp-display-phone dp-display-item"><label>Your phone number is:</label><br /><a href="javascript:;">' + App.config.user.phone + '</a></div>')
			.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br /><a href="javascript:;">' + App.config.user.address.replace("\n",'<br />') + '</a></div>')
			.append('<div class="dp-display-payment dp-display-item"><label>Your are paying by:</label><br /><a href="javascript:;">credit card</a></div>');
		
		$('.main-content').append(dp);
	});


/*
<a href="" class="delivery-toggle-delivery">delivery</a> | <a href="" class="delivery-toggle-takeout">takeout</a>

<div class="personal-info field-container">
	<label>Name</label>
	<div class="input-item"><input type="text" name="name"></div>
	
	<label>Phone #</label>
	<div class="input-item"><input type="text" name="phone"></div>
	
	<label class="delivery-only">Deliver to</label>
	<div class="input-item delivery-only"><textarea name="deliver"></textarea></div>
</div>

<a href="" class="pay-toggle-credit">credit</a> | <a href="" class="pay-toggle-cash">cash</a>

<div class="payment-info field-container">
	<label>Credit card #</label>
	<div class="input-item"><input type="text" name="card"></div>
	
	<label>Expiration</label>
	<div class="input-item">
		<select><option>Month</option></select>
		<select><option>Year</option></select>
	</div>
	
	<label>Tip</label>
	<div class="input-item">
		<select>
			<option>0%</option>
			<option>5%</option>
			<option>10%</option>
			<option>15%</option>
			<option>20%</option>
		</select>
	</div>
</div>
*/

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
	submit: function() {
		console.log(JSON.stringify(App.cart.items));
		alert(JSON.stringify(App.cart.items));
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
	},
	hasItems: function() {
		if (!$.isEmptyObject(App.cart.items.dishes) || !$.isEmptyObject(App.cart.items.sides) || !$.isEmptyObject(App.cart.items.extras)) {
			return true;
		}
		return false;
	}
};


$(function() {

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
		App.cart.submit();
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