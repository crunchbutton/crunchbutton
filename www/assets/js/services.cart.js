// CartService service
NGApp.factory('CartService', function () {
	
	var service = {
		restaurants : {},
		id_restaurant : null
	}

	service.uuid = function () {
		var id = 'c-' + service.restaurants[ service.id_restaurant ].uuidInc;
		service.restaurants[ service.id_restaurant ].uuidInc++;
		console.log('service.id_restaurant',service.id_restaurant);
		console.log('id',id);
		return id;
	}

	service.setRestaurant = function( id_restaurant ){
		service.id_restaurant = id_restaurant;
		if( !service.restaurants[ service.id_restaurant ] ){
			service.restaurants[ service.id_restaurant ] = {
				uuidInc: 0,
				items: {}
			}
		}
		console.log('service.id_restaurant',service.id_restaurant);
		console.log('service.restaurants',service.restaurants[ service.id_restaurant ] );
	}

	service.add = function (item) {
		var id = service.uuid(),
			dish = App.cache('Dish', item);
		dish_options = dish.options(),
		options = [];
		if (arguments[1]) {
			options = arguments[1].options;
			// This lines above will verify is there are any 'select' option without a selected value
			for (var i in dish_options) {
				if (dish_options[i].type == 'select') {
					var hasSelectedOption = false;
					var defaultValue = false;
					for (var j in dish_options) {
						if (dish_options[j].id_option_parent == dish_options[i].id_option) {
							if (dish_options[j]['default'] == 1) {
								defaultValue = dish_options[j]['id_option'];
							}
							for (var k in options) {
								if (options[k] == dish_options[j].id_option) {
									hasSelectedOption = true;
								}
							}
						}
					}
					if (defaultValue && !hasSelectedOption) {
						options[options.length] = defaultValue;
					}
				}
			}
		} else {
			for (var x in dish_options) {
				if (dish_options[x]['default'] == 1) {
					options[options.length] = dish_options[x].id_option;
				}
			}
		}
		service.restaurants[ service.id_restaurant ].items[id] = {};
		service.restaurants[ service.id_restaurant ].items[id].id = item;
		service.restaurants[ service.id_restaurant ].items[id].options = options;
		/* Template viewer stuff */
		service.restaurants[ service.id_restaurant ].items[id].details = {};
		service.restaurants[ service.id_restaurant ].items[id].details.id = id;
		service.restaurants[ service.id_restaurant ].items[id].details.name = dish.name;
		service.restaurants[ service.id_restaurant ].items[id].details.description = dish.description != null ? dish.description : '';
		/* Customization stuff */
		service.restaurants[ service.id_restaurant ].items[id].details.customization = {};
		service.restaurants[ service.id_restaurant ].items[id].details.customization.customizable = (dish.options().length > 0);
		service.restaurants[ service.id_restaurant ].items[id].details.customization.expanded = (parseInt(dish.expand_view) > 0);
		service.restaurants[ service.id_restaurant ].items[id].details.customization.options = service._parseCustomOptions(dish_options, options);
		service.restaurants[ service.id_restaurant ].items[id].details.customization.rawOptions = dish_options;
		//TODO:: If it is a mobile add the items at the top #1035
		App.track('Dish added', {
			id_dish: dish.id_dish,
			name: dish.name
		});
	}
	service.clone = function (item) {
		var
		cart = service.restaurants[ service.id_restaurant ].items[item],
			newoptions = [];
		for (var x in cart.options) {
			newoptions[newoptions.length] = cart.options[x];
		}
		service.add(cart.id, {
			options: newoptions
		});
		App.track('Dish cloned');
	}

	service.getItems = function(){
		return service.restaurants[ service.id_restaurant ].items;
	}

	service.reset = function(){
		console.log('reset');
		service.restaurants[ service.id_restaurant ].uuidInc = 0;
		service.restaurants[ service.id_restaurant ].items =  {};
		console.log('service.id_restaurant',service.id_restaurant);
		console.log('service.restaurants[ service.id_restaurant ].items ',service.restaurants[ service.id_restaurant ].items );
	}

	service.remove = function (item) {
		App.track('Dish removed');
		delete service.restaurants[ service.id_restaurant ].items[item];
	}
	service.customizeItem = function (option, item) {
		var cartitem = service.restaurants[ service.id_restaurant ].items[item.details.id];
		if (option) {
			if (option.type == 'select') {
				var options = item.details.customization.rawOptions;
				for (var i in options) {
					if (options[i].id_option_parent != option.id_option) {
						continue;
					}
					for (var x in cartitem.options) {
						if (cartitem.options[x] == options[i].id_option && options[i].id_option_parent == option.id_option) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}
				cartitem.options[cartitem.options.length] = option.selected;
			} else if (option.type == 'check') {
				if (option.checked) {
					cartitem.options[cartitem.options.length] = option.id_option;
				} else {
					for (var x in cartitem.options) {
						if (cartitem.options[x] == option.id_option) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}
			}
		}
		service.restaurants[ service.id_restaurant ].items[item.details.id] = cartitem;
	}
	service.customizeItemPrice = function (price, force) {
		if (price != '0.00' || force) {
			return ' (' + ((price < 0) ? 'minus $' : '+ $') + parseFloat(Math.abs(price)).toFixed(2) + ')';
		}
		return '';
	}
	service.getCart = function () {
		var cart = [];
		for (x in service.restaurants[ service.id_restaurant ].items) {
			var index = cart.length;
			cart[index] = { id: service.restaurants[ service.id_restaurant ].items[x].id, options: service.restaurants[ service.id_restaurant ].items[x].options };
		}
		return cart;
	}
	service.hasItems = function () {
		if (!$.isEmptyObject(service.restaurants[ service.id_restaurant ].items)) {
			return true;
		}
		return false;
	}
	service.summary = function () {
		var items = {};
		for (var x in service.restaurants[ service.id_restaurant ].items) {
			if (items[service.restaurants[ service.id_restaurant ].items[x].details.name]) {
				items[service.restaurants[ service.id_restaurant ].items[x].details.name]++;
			} else {
				items[service.restaurants[ service.id_restaurant ].items[x].details.name] = 1;
			}
		}
		var text = '';
		for (x in items) {
			text = ',\u00A0\u00A0' + text;
			if (items[x] > 1) {
				text = x + '\u00A0(' + items[x] + ')' + text;
			} else {
				text = x + text;
			}
		}
		return text.substr(0, text.length - 3);
	}
	service.subtotal = function () {
		var
		total = 0,
			options;
		for (var x in service.restaurants[ service.id_restaurant ].items) {
			total += parseFloat(App.cached['Dish'][service.restaurants[ service.id_restaurant ].items[x].id].price);
			options = service.restaurants[ service.id_restaurant ].items[x].options;
			for (var xx in options) {
				var option = App.cached['Option'][options[xx]];
				if (option === undefined) continue; // option does not exist anymore
				total += parseFloat(option.optionPrice(options));
			}
		}
		total = App.ceil(total);
		return total;
	}
	service.totalItems = function () {
		var size = 0;
		for (var x in service.restaurants[ service.id_restaurant ].items) {
			if (service.restaurants[ service.id_restaurant ].items.hasOwnProperty(x)) {
				size++;
			}
		}
		return size;
	}

	service._parseCustomOptions = function (options, selectedOptions) {
		var parsedOptions = [];
		for (var x in options) {
			var newOption = {};
			var rawOption = options[x];
			if (rawOption.id_option_parent) {
				continue;
			}
			newOption.type = rawOption.type;
			newOption.id_option = rawOption.id_option;
			newOption.name = rawOption.name + (rawOption.description || '');
			if (rawOption.type == 'check') {
				newOption.id_option = rawOption.id_option;
				newOption.price = rawOption.optionPrice(options);
				newOption.priceFormated = service.customizeItemPrice(newOption.price);
				newOption.checked = ($.inArray(rawOption.id_option, selectedOptions) !== -1);
			}
			if (rawOption.type == 'select') {
				newOption.options = [];
				newOption.selected = false;
				for (var i in options) {
					if (options[i].id_option_parent == rawOption.id_option) {
						var newSubOption = {};
						newSubOption.id_option = options[i].id_option;
						newSubOption.id_option_parent = options[i].id_option_parent;
						newSubOption.price = options[i].price;
						newSubOption.priceFormated = service.customizeItemPrice(newSubOption.price);
						newSubOption.selected = ($.inArray(options[i].id_option, selectedOptions) !== -1);
						newSubOption.name = options[i].name + (options[i].description || '') + service.customizeItemPrice(newSubOption.price, (rawOption.price_linked == '1'));
						newOption.options.push(newSubOption);
						if (newSubOption.selected) {
							newOption.selected = options[i].id_option;
						}
					}
				}
			}
			parsedOptions.push(newOption);
		}
		return parsedOptions;
	}
	return service;
});