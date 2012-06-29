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
		return App.loadPage();
	}
}

var App = {
	service: '/api/',
	cached: {},
	cart: {},
	community: null,
	page: {},
	config: {}
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
	var loc = '/restaurant/' + id;
	history.pushState({}, loc, loc);
	App.loadPage();
};

App.configureCartItem = function(item) {

};

App.page.community = function(id) {

	// probably dont need this since we force community to be loaded
	//App.community = App.cache('Community', id, function() {

		document.title = 'Crunchbutton - ' + App.community.name;

		$('.main-content').html(
			'<div class="home-tagline"><h1>one click food ordering</h1></div>' + 
			'<div class="content-padder"><div class="meal-items"></div></div>');
	
		var rs = App.community.restaurants();
	
		if (rs.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}
	
		for (x in rs) {
			var restaurant = $('<div class="meal-item" data-id_restaurant="' + rs[x]['id_restaurant'] + '"></div>');
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
		App.restaurant = App.cached['Restaurant'][id];

		document.title = App.restaurant.name;
		
		$('.main-content').html(
			'<div class="restaurant-name"><h1>' + App.restaurant.name + '</h1></div>' + 
			'<div class="restaurant-pic-wrapper"><div class="restaurant-pic" style="background: url(/assets/images/food/' + App.restaurant.image + ');"></div></div>' + 
			'<div class="restaurant-items"></div>' + 
			'<div class="cart-items"></div>' + 
			'<div class="cart-total"></div>' + 
			'<button class="button-submitorder"><div>Submit Order</div></button>');
			
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
	
		for (x in dishes) {
			var dish = $('<li><a href="javascript:;" data-id_dish="' + dishes[x].id_dish + '">' + dishes[x].name + '</a> ($' + dishes[x].price + ')</li>');
			$('.resturant-dishes').append(dish);
		}
		
		for (x in sides) {
			var side = $('<li><a href="javascript:;" data-id_side="' + sides[x].id_side + '">' + sides[x].name + '</a> ($' + sides[x].price + ')</li>');
			$('.resturant-sides').append(side);
		}
		for (x in extras) {
			var extra = $('<li><a href="javascript:;" data-id_extra="' + extras[x].id_extra + '">' + extras[x].name + '</a> ($' + extras[x].price + ')</li>');
			$('.resturant-sides').append(extra);
		}
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
	if (!App.community) return;

	var path = location.pathname.split('/');
	switch (true) {
		case /^\/restaurant/.test(location.pathname):
			App.page.restaurant(path[2]);
			break;

		case /^\/pay/.test(location.pathname):
			
			break;
			
		case /^\/(help)|(legal)|(support)/.test(location.pathname):

			break;

		default:
			App.page.community(1);
			break;
	}
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

		switch (type) {
			case 'Dish':
				
				for (x in top) {
					if (top[x]['default'] == 1) {
						toppings[top[x].id_topping] = true;
					}
				}

				for (x in sub) {
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
			.append('<div class="cart-button">-</div>')
			.append('<div class="cart-item-name">' + App.cache(type,item).name + '</div>');
		
		if (type == 'Dish')	{
			el.append('<div class="cart-item-config"><a href="javascript:;">Customize</a></div>');
		}
		el.append('<div class="divider"></div>');
		el.hide();

		$('.cart-items').append(el);
		el.fadeIn();

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
	},
	updateTotal: function() {
		$('.cart-total').html();
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
	
			for (x in top) {
				var check = $('<input type="checkbox" class="cart-customize-check">');
				if (cartitem.toppings[top[x].id_topping]) {
					check.attr('checked','checked');
				}
				var topping = $('<div class="cart-item-customize-item" data-id_topping="' + top[x].id_topping + '"></div>')
					.append(check)
					.append('<label>' + top[x].name + '</label>');
				el.append(topping);
			}
			
			for (x in sub) {
				var check = $('<input type="checkbox" class="cart-customize-check">');
				if (cartitem.substitutions[sub[x].id_substitution]) {
					check.attr('checked','checked');
				}
				var substitution = $('<div class="cart-item-customize-item" data-id_substitution="' + sub[x].id_substitution + '"></div>')
					.append(check)
					.append('<label>' + sub[x].name + '</label>');
				el.append(substitution);
			}
		}
	},
	submit: function() {
		console.log(JSON.stringify(App.cart.items));
		alert(JSON.stringify(App.cart.items));
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
		App.loadRestaurant($(this).attr('data-id_restaurant'));
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
	
	$('.cart-button').live('click',function() {
		App.cart.remove($(this).closest('.cart-item'));
	});
	
	$('.cart-item-config a').live('click',function() {
		App.cart.customize($(this).closest('.cart-item'));
	});
	
	$('.button-submitorder').live('click',function() {
		App.cart.submit();
	});
	
	$('.button-submitorder').live({
		mousedown: function() {
			$(this).addClass('button-submitorder-click');
		},
		touchstart: function() {
			$(this).addClass('button-submitorder-click');
		}
	});
	$('.button-submitorder').live({
		mouseup: function() {
			$(this).removeClass('button-submitorder-click');
		},
		touchend: function() {
			$(this).removeClass('button-submitorder-click');
		}
	});

	// load our config first (not async)
	$.getJSON('/api/config', function(json) {
		App.config = json;
		
		// force load of community reguardless of landing (this contains everything we need)
		App.community = App.cache('Community',1, function() {
			App.loadPage();
		});
	});

	
});

// trash
String.prototype.capitalize = function(){
	return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
};