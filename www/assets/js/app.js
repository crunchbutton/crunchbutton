if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
}

var App = {
	service: '/api/',
	cached: {},
	cart: {},
	community: null,
	page: {}
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
	location.href = '/restaurant/' + id;
};

App.page.community = function(id) {
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

};

App.loadPage = function() {
	switch (true) {
		case /^\/restaurant/.test(location.pathname):
			
			break;

		case /^\/pay/.test(location.pathname):
			
			break;

		default:
			App.page.community(1);
			break;
	}
};

App.cart = {
	items: {
		dishes: [],
		sides: [],
		extras: [],
	},
	add: function(type, item) {

		switch (type) {
			case 'Dish':
				App.cart.items.dishes[App.cart.items.dishes.length] = {
					id: item,
					substitutions: [],
					toppings: []
				};
				break;
			case 'Side':
				App.cart.items.sides[App.cart.items.sides.length] = {
					id: item
				};
				break;
			case 'Extra':
				App.cart.items.extras[App.cart.items.extras.length] = {
					id: item
				};
				break;
		}

		var el = $('<div class="cart-item cart-item-dish"></div>');
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
		item.remove();
	},
	updateTotal: function() {
		$('.cart-total').html();
	},
	customize: function(item) {
		alert('nope.');
	},
	submit: function() {
		console.log(JSON.stringify(App.cart.items));
		alert(JSON.stringify(App.cart.items));
	}
};


$(function() {

	$('.meal-item-content').live('mousedown',function() {
		$(this).addClass('meal-item-down');
	});
	$('.meal-item-content').live('mouseup',function() {
		$(this).removeClass('meal-item-down');
	});

	$('.meal-item').live('click',function() {
		App.loadRestaurant($(this).attr('data-id_restaurant'));
	});
	
	$('.resturant-dishes a').live('click',function() {
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
	
	$('.button-submitorder').live('mousedown',function() {
		$(this).addClass('button-submitorder-click');
	});
	
	$('.button-submitorder').live('mouseup',function() {
		$(this).removeClass('button-submitorder-click');
	});
	
	App.community = App.cache('Community',1, function() {
		App.loadPage();
	});
	
});

String.prototype.capitalize = function(){
	return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
};