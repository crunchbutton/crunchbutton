if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
}

var App = {
	service: '/api/',
	cached: {},
	cart: {}
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
		$('body').bind('cache-item-loaded-' + type,function() {
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

App.cart = {
	items: {
		dishes: [],
		sides: [],
		extras: [],
		custom: []
	},
	add: function(type, item) {
		var el = $('<div class="cart-item cart-item-dish">TEST</div>');
		$('.cart-items').append(el);
	},
	remove: function(type, item) {
	
	},
	updateTotal: function() {
		$('.cart-total').html();
	}
};



$(function() {
	$('.meal-item-content').mousedown(function() {
		$(this).addClass('meal-item-down');
	});
	$('.meal-item-content').mouseup(function() {
		$(this).removeClass('meal-item-down');
	});

	community = App.cache('Community','1',function() {
		console.log(community.__restaurants);
	});


	$('.meal-item').live('click',function() {
		App.loadRestaurant($(this).attr('data-id_restaurant'));
	});
	
	$('.resturant-dishes a').live('click',function() {
		if ($(this).attr('data-id_dish')) {
		
		} else if ($(this).attr('data-id_side')) {
		
		} else if ($(this).attr('data-id_extra')) {
		
		} else if ($(this).hasClass('restaurant-menu')) {
			return;
		}
		
		App.cart.add();
	});
	
	


});