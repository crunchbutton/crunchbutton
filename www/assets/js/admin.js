
var App = {
	cartHighlightEnabled: false,
	currentPage: null,
	slogans: ['order food in 5 seconds'],
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

/**
 * Populates notifications types with empty fields to be inserted
 *
 * @return void
 */
function _loadEmptyNotifications() {
	var types = ['sms', 'email', 'phone', 'url', 'fax'];
	for(var i in types) {
		var notification = {
				id_notification: '',
				type: types[i],
				value: '',
				active: false,

		}
		_loadNotification(notification);
	}
}

/**
 * Sets the notifications in the restaurant form
 *
 * @param notifications
 */
function _loadNotification(notification) {
		var $wrapper = 'div.check-content.' + notification.type;
			// console.log($wrapper);
			$wrapper = $($wrapper);
		var active   = parseInt(notification.active) ? 'checked="checked"' : '';
		var id       = parseInt(notification.id) ? notification.id : '';

		html = '<div data-id_notification="' + id +'" class="notification-wrap">' +
					'<input type="checkbox" '+ active    +
					' name="notification-active" class="dataset-notification"' +
					' />' +
					'<input value="' + notification.value+ '" '  +
					' name="notification-value" class="dataset-notification notification" ' +
					' />'+
				'</div>';
		$wrapper.append(html);
}

/**
 * Load the restaurant
 *
 * @return void
 */
function _loadRestaurant() {
	var restaurant = this;
	var checkswap = {
		'delivery_fee_check' : 'delivery_fee',
		'delivery_min_check': 'delivery_min',
		'fee_restaurant_check': 'fee_restaurant',
		'fee_customer_check': 'fee_customer',
		'id_community_check': 'id_community'
	};

	$('.admin-restaurant-form input, .admin-restaurant-form select, .admin-restaurant-form textarea').each(function() {
		if ($(this).attr('type') == 'checkbox') {
			if (restaurant[$(this).attr('name')] == 1 && $(this).attr('value') == '1') {
				$(this).click();
			}
			if (restaurant[$(this).attr('name')] == 0 && $(this).attr('value') == '0') {
				$(this).click();
			}

		} else {
			$(this).val(restaurant[$(this).attr('name')]);
		}

		for (var x in checkswap) {
			if ($(this).attr('name') == x) {
				if (restaurant[checkswap[x]] && restaurant[checkswap[x]] != '0') {
					//$('input[name="' + x + '"][value="0"]').prop('checked', false);
					//$('input[name="' + x + '"][value="1"]').prop('checked', true);
					$('input[name="' + x + '"][value="1"]').click();
				} else {
					//$('input[name="' + x + '"][value="0"]').prop('checked', true);
					//$('input[name="' + x + '"][value="1"]').prop('checked', false);
					$('input[name="' + x + '"][value="0"]').click();
				}
			}
		}
	});

	App.restaurant       = restaurant.id_restaurant; // Should be App.id_restaurant IMHO
	App.restaurantObject = restaurant;               // and this one should rellay be App.restaurant

	$('.admin-restaurant-content').html('');

	var notifications = restaurant.notifications();
	for (var i in notifications) {
		_loadNotification(notifications[i]);
	}
	_loadEmptyNotifications();
	_newNotificationFields();

	var categories = restaurant.categories();
	var isDishes = false;

	var $categoriesContainer = $('<div id="categories" class="accordion"></div>');
	$('.admin-restaurant-dishes .admin-restaurant-content').append($categoriesContainer);

	/**
	 * Swip all categories
	 *
	 * @todo Use the App.createCategory
	 */
	for (var i in categories) {
		var dishes       = categories[i].dishes();
		var name         = categories[i].name;
		var sort         = categories[i].sort;
		var $categoryTab = $('<h3 data-id_category="'+ categories[i].id_category +'">'+ name+'</h3>' +
		'<div>' +
			'<div class="labeled-fields category">' +
				'<label><span class="label">Name</span>       <input name="name" value="' + name + '" /></label>' +
				'<label><span class="label">Sort Order</span> <input name="sort" value="' + sort + '" /></label>' +
			'</div>' +
		'</div>');
		$categoriesContainer.append($categoryTab);

		for (var x in dishes) {
			App.showDish(dishes[x]);
			isDishes = true;
		}
	}
	$('.accordion').accordion({
		collapsible: true,
		active:      false,
		heightStyle: "content",
		activate:    function( event, ui ){
			var speed = 100;
			var accordionOptions = $('.accordion').accordion('option');
			setTimeout(function() {
				$('.accordion').accordion('destroy');
				$('.accordion').accordion(accordionOptions);
			}, 1.1 * speed);
		}
	});

	if (!isDishes) {
		$('input[name="dish_check"][value="0"]').prop('checked', true);
		$('input[name="dish_check"][value="1"]').prop('checked', false);
		$('.admin-restaurant-dishes').hide();

	} else {
		$('input[name="dish_check"][value="0"]').prop('checked', false);
		$('input[name="dish_check"][value="1"]').prop('checked', true);
		$('.admin-restaurant-dishes').show();
	}

	var days = {
		'sun': 'Sunday',
		'mon': 'Monday',
		'tue': 'Tuesday',
		'wed': 'Wednesday',
		'thu': 'Thursday',
		'fri': 'Friday',
		'sat': 'Saturday'
	};

	for (var d in days) {

		var day = $('<div class="hours-date"><span class="hours-date-label">' + days[d] + '</span></div>');
		var dayWrap = $('<div class="hours-date-hours"></div>').appendTo(day);
		dayWrap.after('<div class="divider"></div>');

		if (!restaurant._hours) {
			$('input[name="hours_check"][value="0"]').prop('checked', true);
			$('input[name="hours_check"][value="1"]').prop('checked', false);
			$('.admin-restaurant-hours').hide();

		} else {
			$('input[name="hours_check"][value="0"]').prop('checked', false);
			$('input[name="hours_check"][value="1"]').prop('checked', true);
			$('.admin-restaurant-hours').show();

			var dayitem = restaurant._hours[d];

			for (var x in dayitem) {
				var row = $('<div class="hours-date-hour"></div>');
				row.append('<input type="text" value="' + App.formatTime(dayitem[x][0]) + '" name="' + d + '-open[]">' +
						' TO ' +
						' <input type="text" value="' + App.formatTime(dayitem[x][1]) + '" name="' + d + '-close[]">');
				dayWrap.append(row);
			}
		}

		var row = $('<div class="hours-date-hour"></div>');
		row.append('<input type="text" name="' + d + '-open[]"> TO <input type="text" name="' + d + '-close[]">');
		dayWrap.append(row);

		$('.admin-restaurant-hours').append(day);

	}
}

/**
 * Adds a new hours range if they are all filled up
 *
 * @return void
 */
function _newNotificationFields() {
	var inputSelector = '.notification-wrap input[name="notification-value"]';
	$(inputSelector).live('keyup', function() {
		var $container = $(this).closest('.check-content');
		var allfull    = true;
		$container.find(inputSelector).each(function() {
			if ($(this).val() == '') {
				allfull = false;
			}
		});
		if (allfull) {
			var notification = {
				id_notification: '',
				value: '',
				active: false,
			}
			var types = ['sms', 'email', 'phone', 'url', 'fax'];
			for (var i in types) {
				if ($container.hasClass(types[i])) {
					notification.type = types[i];
				}
			}
			_loadNotification(notification);
		}
	});
}

/**
 * Method to be called to save the Dish Categories
 *
 * @param function complete What to trigger after the categories are stored
 *
 * @return void
 *
 * @todo returned elements need to be reloaded
 */
function _saveCategories(complete) {
	var selector  = 'input , select, textarea';
	var container = '#categories .labeled-fields.category';
	var elements  = [];
	$(container).each(function(){
		var inputs = $(selector, this);
		var data   = $(this).parent().prev().data();
		data       = getValues(inputs, data);
		elements[elements.length] = data;
	});

	$.post('/api/restaurant/' + App.restaurant + '/categories', {elements: elements}, function() {
		if (complete) {
			complete();
		}
	});
}

/**
 * Method to be called to save all dishes
 *
 * @param function compelte What to trigger after the dishes are stored
 *
 * @return void
 */
function saveDishes (complete) {
	var selector = 'input.dataset-dish, select.dataset-dish, textarea.dataset-dish';
	var dishes = [];

	$('.admin-food-item-wrap').each(function() {
		var id = $(this).attr('data-id_dish');
		var values = getValues($(this).find(selector), {});
		var dish = {
			name:        values['dish-name'],
			description: values['dish-description'],
			price:       values['dish-price'],
			id_category: values['dish-id_category'],
			active:      values['dish-active'],
			sort:        values['dish-sort']
		};

		if (id) {
			dish.id_dish = id;
		}

		dish.optionGroups = [];
		$(this).find('.admin-dish-options .admin-dish-options-wrapper').each(function() {
			var id = $(this).attr('data-parent');
			var name = $(this).find('.admin-dish-options-title').html();
			name = name.substr(0,name.length-1);

			var optionGroup = {
				name: name,
				'default': values['dish-options-default'],
				type: $(this).attr('data-type'),
				price: $(this).attr('data-modifies_price') == 'true' ? true : false,
				options: []
			};
			if (id) {
				optionGroup.id_option = id;
			}

			$(this).find('.dish-options').each(function() {
				var id = $(this).attr('data-id_option');
				var values = getValues($(this).find('input'), {});

				if (values['dish-options-name']) {
					var option = {
						name: values['dish-options-name'],
						price: values['dish-options-price'] || 0.00,
						'default': $(this).find('input[type="checkbox"], input[type="radio"]').prop('checked'),
					};

					if (id) {
						option.id_option = id;
					}
					optionGroup.options[optionGroup.options.length] = option;
				}
			});
			dish.optionGroups[dish.optionGroups.length] = optionGroup;
		});

		dishes[dishes.length] = dish;

	});
	$.post('/api/restaurant/' + App.restaurant + '/dishes', {dishes: dishes}, function() {
		if (complete) {
			complete();
		}
	});
}

/**
 * Method to be called to save notifications
 *
 * @param function compelte What to trigger after the dishes are stored
 *
 * @return void
 *
 * @todo wasn't able to take the function out becaues of the getValue() method which needs to be refactorized and moved out
 * @todo returned elements need to be reloaded
 */
function _saveNotifications(complete) {
	var selector = 'input.dataset-notification, select.dataset-notification, textarea.dataset-notification';
	var elements = [];

	$('.notification-wrap').each(function() {
		var id      = $(this).attr('data-id_notification');
		var values  = getValues($(this).find(selector), {});
		var element = {
			active: values['notification-active'],
			value:  values['notification-value']
		};

		var types = ['sms', 'email', 'phone', 'url', 'fax'];
		for (var i in types) {
			var $container = $(this).closest('.check-content');
			if ($container.hasClass(types[i])) {
				element.type = types[i];
			}
		}

		if (id) {
			element.id_notification = id;
		}

		elements[elements.length] = element;

	});
	$.post('/api/restaurant/' + App.restaurant + '/notifications', {elements: elements}, function() {
		if (complete) {
			complete();
		}
	});
}

/**
 *
 * @param all
 */
function saveRestaurant (all) {


	var html = '<div id="dialog-add-dish" title="Create new Dish"> ' +
		'<div style="text-align:center; margin-top: 2em;">' +
			'<p>Please wait while saving...</p>' +
			'<img src="/assets/images/admin/ajax-loader-bar.gif" />' +
		'</div>'
	'</div>';
	$(html).dialog({
		resizable:     false,
		height:        160,
		width:         315,
		modal:         true,
		closeOnEscape: false,
		open:          function(event, ui) { $(".ui-dialog-titlebar-close, .ui-icon-closethick", ui.dialog || ui).hide(); },

	});
	$(".ui-dialog-titlebar-close, .ui-icon-closethick").hide();


	var selector = 'input.dataset-restaurant, select.dataset-restaurant, textarea.dataset-restaurant';
	var id = App.restaurant;

	if (id) {
		App.cache('Restaurant', id, function() {
			var restaurant = getValues(selector, this);
			restaurant.save(function() {
				if (all) {
					saveHours(function() {
						_saveCategories(function() {
							saveDishes(function() {
								_saveNotifications(function() {
									location.href = '/admin/restaurants/' + App.restaurant;
								});
							});
						});
					});
				}
			});
		});
	} else {
		var restaurant = getValues(selector, {});
		restaurant = new Restaurant(restaurant);
		restaurant.save(function(r) {

			App.cache('Restaurant', r.id_restaurant, function() {
				App.restaurant = this.id_restaurant;
				if (all) {
					saveHours(function() {
						_saveCategories(function(){
							saveDishes(function() {
								_saveNotifications(function() {
									location.href = '/admin/restaurants/' + App.restaurant;
								});
							});
						});
					});
				} else {
					location.href = '/admin/restaurants/' + App.restaurant;
				}
			});
		});
	}
};

function saveHours (complete) {
	var selector = '.hours-date-hour input';
	var id = App.restaurant;

	if (id) {
		App.cache('Restaurant', id, function() {
			var h = getValues(selector, {});

			var hours = {'sun': [],'mon': [],'tue': [],'wed': [],'thu': [],'fri': [],'sat': []};
			var vals = getValues('input.dataset-restaurant', {});

			if (vals.hours_check) {
				for (var d in hours) {
					for (var x in h[d + '-open']) {
						if (!h[d + '-open'][x]) continue;
						hours[d][hours[d].length] = [App.unFormatTime(h[d + '-open'][x]), App.unFormatTime(h[d + '-close'][x])];
					}
				}
			}
			$.post('/api/restaurant/' + id + '/hours', {hours: hours}, function() {
				if (complete) {
					complete();
				}
			});
		});
	}
};

function getValues(selector, restaurant) {
	$(selector).each(function() {
		var name, value, group = false;

		if ($(this).attr('name').match(/^.*\[\]$/)) {
			group = true;
			name = $(this).attr('name').replace(/^(.*)\[\]$/,'$1');
			if (!restaurant[name]) {
				restaurant[name] = [];
			}
		} else {
			name = $(this).attr('name');
		}

		if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
			value = $(this).prop('checked') ? true : false;
		} else {
			value = $(this).val();
		}

		if (group) {
			restaurant[name][restaurant[name].length] = value;
		} else {
			restaurant[name] = value;
		}
	});

	return restaurant;
}

App.loadRestaurant = function(id_restaurant) {

	App.cache('Restaurant', id_restaurant , _loadRestaurant);
};

/**
 * Generates HTML to show dish and it's items
 *
 * @todo Not sure if hide purges HTML or what.
 */
App.showDish = function(dishItem) {
	dishItem = $.extend({
		id_dish:     '',
		// id_category: '',
		name:        '',
		description: '',
		price:       ''
	}, dishItem);

	var dish = $('<div class="admin-food-item-wrap" data-id_dish="' + dishItem.id_dish + '"' + (dishItem.id_dish ? '' : ' style="display: none;"') + '></div>');
	dish.append('<div class="admin-food-item ' + (dishItem.id_dish ? 'admin-food-item-collapsed' : '') + '"> ' +
			'<span class="food-name">' + dishItem.name + '</span>' +
			'<span class="food-price">($<span class="food-price-num">' + dishItem.price + '</span>)</span><div class="food-drop-down"></div></div>')
	var content = $('<div class="admin-food-item-content" ' + (dishItem.id_dish ? 'style="display: none;"' : '') + '></div>');
	var padding = $('<div class="admin-food-item-content-padding labeled-fields">');
	dish.append(content);
	content.append(padding);

	var options = $('<div class="admin-dish-options"></div>');
	var basicOptions = $('<div class="input-faker"></div>');
	var basicWrapper = $('<div class="admin-dish-options-wrapper" data-parent="BASIC"><div class="admin-dish-options-title">Basic options:</div></div>')
		.append(basicOptions);

	var optGroups = [];

	if (dishItem.options) {
		var opts = dishItem.options();

		options.append(basicWrapper);

		for (var x in opts) {
			var option = opts[x];
			if (option.id_option_parent) {
				continue;
			}

			if (option.type == 'check') {
				basicOptions.append(App.returnOption(option,option.type));

			} else if (option.type == 'select') {

				var optionAdder = $('<div class="input-faker"></div>');
				var optionWrapper = $('<div class="admin-dish-options-wrapper" data-type="' + option.type + '" data-parent="' + option.id + '"><div class="admin-dish-options-title">' + option.name + ':</div></div>')
					.append(optionAdder);

				var select = $('<select class="cart-customize-select">');
				for (var i in opts) {
					if (opts[i].id_option_parent == option.id_option) {
						optionAdder.append(App.returnOption(opts[i],option.type,option.id_option));
					}
				}
				optionAdder.append(App.returnOption({price: '',name:'',id_option:''},option.type,option.id_option));
				options.append(optionWrapper);
			}
		}

		basicOptions.append(App.returnOption({price: '',name:'',id_option:''},'check'));

		options.append('<div class="admin-restaurant-options-controls">'
			+ '<div class="control-link">'
				+ '<a href="javascript:;" class="control-link-add-option">'
					+ '<div class="control-icon-plus control-icon"></div>'
					+ '<label class="control-label">Add another option group?</label>'
				+ '</a>'
			+ '</div>'
		+ '</div><div class="divider"></div>');
	}

	var dishDescription = dishItem.description ? dishItem.description : '';
	var categories      = App.restaurantObject.categories();
	var categoryOptions = '';
	if (!dishItem.id_category) {
		console.log('ERROR, no category for this dish');
	}
	for (var i in categories) {
		var selected     = (categories[i].id_category == dishItem.id_category) ? ' selected="selected" ' : '';
		categoryOptions += '<option value="' + categories[i].id_category+ '" ' + selected + '>' + categories[i].name+ '</option>';
	}

	var active = (parseInt(dishItem.active)) ? 'checked="checked"' : '';

	padding
		.append('<input type="text" placeholder="Name" name="dish-name" class="dataset-dish clean-input dish-name" value="' + dishItem.name + '">')
		.append('<div class="input-faker dish-price"><div class="input-faker-content">$&nbsp;</div><input type="text" placeholder="" name="dish-price" value="' + dishItem.price + '" class="dataset-dish clean-input" data-clean_type="float"><div class="divider"></div></div>')
		.append('<div class="clear"></div>')
		.append('<label><span>Move to category</span><select name="dish-id_category" class="dataset-dish clean-input">' + categoryOptions + '</select></label')
		.append('<label><span>Active</span><input type="checkbox" name="dish-active" class="dataset-dish clean-input" ' + active + ' /></label')
		.append('<label><span>Sort order</span><input name="dish-sort" class="dataset-dish clean-input" value="' + dishItem.sort + '" /></label')
		.append('<textarea placeholder="Description" name="dish-description" class="dataset-dish clean-input dish-description">' + dishDescription + '</textarea>')
		.append('<div class="divider"></div><div class="divider dots" style="margin: 10px 0 10px 0;"></div>')
		.append(options);

	content
		.append('<div class="divider dots"></div>')
		.append('<div class="admin-food-item-content-padding"><div class="action-button red action-button-small admin-food-item-delete"><span>Delete</span></div><div class="divider"></div></div>')
		.append('<div class="divider"></div>');

	$('[data-id_category="'+ dishItem.id_category +'"] + div').append(dish);

	if (!dishItem.id_dish) {
		dish.find('.dish-name').focus();
		dish.fadeIn(200);
	}
};

/**
 * Adds the new Dish Category to the DOM
 *
 * @returns void
 */
App.createCategory = function(dialog) {
	var name                 = $('[name="admin-category-name"]',dialog).val();
	var $categoriesContainer = $('.accordion');
	var options              = $categoriesContainer.accordion('option');
	var $categoryTab         = $('<h3 data-id_category="">' + name + '</h3><div>' +
		'<label><span class="label">Name</span>       <input name="name" value="' + name + '" /></label>' +
		'<label><span class="label">Sort Order</span> <input name="sort" /></label>' +
	'</div>');

	$categoriesContainer.accordion('destroy');
	$categoriesContainer.append($categoryTab);
	$categoriesContainer.accordion(options);

};

App.createOptionGroup = function(el, source) {
	el = $(el);
	var parent = source.closest('.admin-food-item-wrap');

	var option = {
		name: el.find('[name="admin-option-name"]').val(),
		price: el.find('[name="admin-option-price"]').attr('checked') ? true : false,
		type: el.find('[name="admin-option-type"]').val(),
		id_option: '',
		id: ''
	};

	var optionAdder = $('<div class="input-faker"></div>');
	var optionWrapper = $('<div class="admin-dish-options-wrapper" data-modifies_price="' + option.price + '" data-type="' + option.type + '" data-parent="' + option.id + '"><div class="admin-dish-options-title">' + option.name + ':</div></div>')
		.append(optionAdder);

	optionAdder.append(App.returnOption({price: '',name:'',id_option:''}, option.type, option.id_option));
	parent.find('.admin-dish-options .admin-restaurant-options-controls').before(optionWrapper);
};

/*
App.addOptionGroup = function(option) {
	var optionAdder = $('<div class="input-faker"></div>');
	var optionWrapper = $('<div class="admin-dish-options-wrapper"><div class="admin-dish-options-title">' + option.name + ':</div></div>')
		.append(optionAdder);

	var select = $('<select class="cart-customize-select">');
	for (var i in opts) {
		if (opts[i].id_option_parent == option.id_option) {
			optionAdder.append(App.returnOption(opts[i],option.type,option.id_option));
		}
	}
	optionAdder.append(App.returnOption({price: '',name:'',id_option:''},option.type,option.id_option));
	options.append(optionWrapper);
};
*/

App.returnOption = function(o, type, parent) {
	var defaulted  = '';
	switch (type) {
		case 'select':
			defaulted = '<input type="radio" class="dataset-dish" name="dish-options-default-' + parent + '" value="1" ' + (o['default'] == '1' ? 'checked="checked"' : '') + '>';
			break;

		default:
		case 'check':
			defaulted = '<input type="checkbox" class="dataset-dish" name="dish-options-default" value="1" ' + (o['default'] == '1' ? 'checked="checked"' : '') + '>';
			break;
	}

	return $('<div class="divider"></div>'
		+ '<div class="admin-food-item-option-padding" data-type="' + type + '" data-parent="' + parent + '">'
			+ '<div class="dish-options ' + (o.id_option ? 'blue' : '') + '" data-id_option="' + o.id_option + '">'
				// + '<input type="text" placeholder="0" name="dish-options-sort" value="' + o.sort + '" />'
				+ defaulted
				+ '<input type="text" placeholder="Name" name="dish-options-name" value="' + o.name + '">'
				+ '<div class="input-faker-content">$ </div>'
				+ '<input type="text" placeholder="" name="dish-options-price" value="' + o.price + '">'
				+ '<a class="dish-options-delete" href="javascript:;"></a>'
				+ '<div class="divider"></div>'
			+ '</div>'
		+ '</div>');
};

App.orders = {
	params: function() {
		return {
			search: $('input[name="order-search"]').val(),
			env: $('select[name="env"]').val(),
			processor: $('select[name="processor"]').val(),
			limit: $('input[name="limit"]').val(),
			dates: $('input[name="date-range"]').val(),
			restaurant: $('select[name="restaurant"]').val(),
			community: $('select[name="community"]').val()
		};
	},
	load: function() {
		//admin-orders-filter
		$('.orders-loader').show();
		$('.orders-content').html('');
		$.ajax({
			url: '/admin/orders/content',
			data: App.orders.params(),
			complete: function(content) {
				$('.orders-content').html(content.responseText);
				$('.orders-loader').hide();
			}
		});
	},
	export: function() {
		var params = App.orders.params();
		params.export = 'csv';
		location.href = '/admin/orders/content?' + jQuery.param(params);
	}
};

App.suggestions = {
	params: function() {
		return {
			search: $('input[name="suggestion-search"]').val(),
			type: $('select[name="type"]').val(),
			status: $('select[name="status"]').val(),
			limit: $('input[name="limit"]').val(),
			dates: $('input[name="date-range"]').val(),
			restaurant: $('select[name="restaurant"]').val(),
			community: $('select[name="community"]').val()
		};
	},
	load: function() {
		$('.suggestions-loader').show();
		$('.suggestions-content').html('');
		$.ajax({
			url: '/admin/suggestions/content',
			data: App.suggestions.params(),
			complete: function(content) {
				$('.suggestions-content').html(content.responseText);
				$('.suggestions-loader').hide();
			}
		});
	},
	prepareForm: function( id_suggetion ){
		$( '.admin-suggestion-save' ).live( 'click', function(){
			$( '#suggestion-status' ).html( '' );
			var status = $( '#status' ).val();
			var data = { 'status' : status };
			var url = App.service + 'suggestion/' + id_suggetion;
			$.ajax({
				type: "POST",
				dataType: 'json',
				data: data,
				url: url,
				success: function(content) {
					$( '#suggestion-status' ).html( 'Status saved!' );
				},
				error: function( ){
					$( '#suggestion-status' ).html( 'Error, please try it again.' );
				}
			});
		} );
	}
};


$(function() {
	$('.admin-restaurant-link').live('click',function() {
		App.loadRestaurant($(this).attr('data-id_restaurant'));
	});

	/**
	 * Adds a new hours range if they are all filled up
	 *
	 * @return void
	 */
	$('.hours-date-hour input').live('keyup', function() {
		var allfull = true;
		$(this).closest('.hours-date-hours').find('input').each(function() {
			if ($(this).val() == '') {
				allfull = false;
			}
		});
		if (allfull) {
			var day = $(this).attr('name').replace(/-open|-close/,'');
			var row = $('<div class="hours-date-hour"></div>');
			row.append('<input type="text" name="' + day + '-open[]"> TO <input type="text" name="' + day + '-close[]">');
			$(this).closest('.hours-date-hour').append(row);
		}
	});


	$('.admin-restaurant-save').live('click', function() {
		saveRestaurant(true);
	});

	$('.admin-restaurant-save-details').live('click', function() {
		saveRestaurant(false);
	});

	$('.admin-restaurant-save-hours').live('click', function() {
		saveHours();
	});

	$('.admin-restaurant-save-dishes').live('click', function() {
		_saveCategories(function(){
			saveDishes();
		});
	});

	$('.admin-restaurant-hours-save-all').live('click',function() {
		$('.admin-restaurant-hours-save-link').click();
	});

	$('.check label').live('click',function() {
		$(this).closest('.check').find('input').click();
	});

	$('.order-range-all label').live('click', function() {
		$(this).parent().find('input').click();
	});

	$('[name="phone"]').live('keyup', function(e) {
		$(this).val( App.phone.format($(this).val()) );
	});

	var changeACheck = function() {
		var name = $(this).attr('name');
		var parent = $(this).closest('.content-sub').length ? $(this).closest('.content-sub') : $(this).closest('.content-primary');
		parent.find('input[name="' + name + '_check"][value="1"]').prop('checked', true);
		parent.find('input[name="' + name + '_check"][value="0"]').prop('checked', false);
	};

	$('.change-a-check').live('change', changeACheck).live('keyup', changeACheck);

	/**
	 * Folds/unfolds the checkbox options
	 *
	 * @todo There is a strange bug in the checkbox, If the label is selected, it does work, if you click on the checkbox, it doesnt
	 */
	$('.bind-a-check').click(function(e) {
		var name   = $(this).attr('name');
		var value  = $(this).attr('value');
		var parent = $(this).closest('.content-sub').length ? $(this).closest('.content-sub') : $(this).closest('.content-primary');

		$(this).prop('checked', true);
		parent.find('input[name="' + name + '"][value="' + (value == '1' ? '0' : '1') + '"]').prop('checked', false);

		if (value == '1') {
			parent.find('.check-content').fadeIn();
		} else {
			parent.find('.check-content').fadeOut(100);
		}

		e.stopPropagation();
		return false;
	});

	if ($('.date-picker').length) {
		var d = $('.date-picker').val();
		d = d.split(',');


		$('.date-picker').DatePicker({
			format: 'm/d/Y',
			date: d,
			current: d[0],
			starts: 1,
			mode: 'range',
			calendars: 2,
			position: 'r',
			onBeforeShow: function(){
				//$('.date-picker').DatePickerSetDate($('.date-picker').val(), true);
			},
			onChange: function(formated, dates){
				$('.date-picker').val(formated);
				if ($('#closeOnSelect input').attr('checked')) {
					$('.date-picker').DatePickerHide();
				}
			}
		});
	}

	$('input[name="order-range-all"]').live('change', function() {
		if ($(this).prop('checked')) {
			$('.date-picker').attr('disabled', 'disabled');
			$('.date-picker').val('');
		} else {
			$('.date-picker').removeAttr('disabled');
		}
	});

	$('.hours-date-hour input').live('change', function() {
		$(this).val(App.formatTime($(this).val()));
	});

	/**
	 * What to do when clicking a dish
	 *
	 * @todo refactorize how the accordion should be redrawn in a private method
	 */
	$('.admin-food-item').live('click', function() {
		var speed = 100;
		$(this).closest('.admin-food-item-wrap').find('.admin-food-item-content').slideToggle(speed);
		$(this).toggleClass('admin-food-item-collapsed');

		// re-draws the dishes accordion after expanding/collapsing dish
		var accordionOptions = $('.accordion').accordion('option');
		setTimeout(function() {
			$('.accordion').accordion('destroy');
			$('.accordion').accordion(accordionOptions);
		}, 1.1 * speed);

	});

	var ignoreKeys = [37,38,39,40,16,9]; //,17,18,91,13,16

	var cleanInput = function(e) {
		if (ignoreKeys.indexOf(e.which) !== -1) {
			return;
		}
		var cleaned = App.cleanInput($(this).val(), $(this).attr('data-clean_type') || 'text');
		var caret = $(this).getCursorPosition();
		$(this).val(cleaned);
		if (e.type == 'keyup') {
			$(this).setCursorPosition(caret);
		}
	};

	$('.clean-input').live('keyup', cleanInput).live('change', cleanInput);

	var changeDish = function(e) {
		$(this).closest('.admin-food-item-wrap').find('.food-name').html($(this).val());
	};

	$('.dish-name').live('keyup', changeDish).live('change', changeDish);

	var changePrice = function(e) {
		$(this).closest('.admin-food-item-wrap').find('.food-price-num').html($(this).val());
	};

	$('.dish-price input').live('keyup', changePrice).live('change', changePrice);

	/**
	 * Show the new dish dialog to set a category for it
	 *
	 * @todo Make the categories select a function to be reused
	 *
	 * @return boolean
	 */
	$('.control-link-add-dish').live('click', function() {

		var categories      = App.restaurantObject.categories();
		var categoryOptions = '';
		for (var i in categories) {
			// var selected     = (categories[i].id_category == dishItem.id_category) ? ' selected="selected" ' : '';
			var selected     = '';
			categoryOptions += '<option value="' + categories[i].id_category + '" ' + selected + '>' + categories[i].name + '</option>';
		}

		var html = '<div id="dialog-add-dish" title="Create new Dish"> ' +
			'<select name="dish-id_category">' + categoryOptions + '</select>' +
		'</div>';
		$(html).dialog({
			resizable: false,
			height:    160,
			width:     315,
			modal:     true,
			buttons: {
				'Create': function() {
					var category = $('[name="dish-id_category"]', this).val();
					App.showDish({
						id_category:category
					});
					$(this).dialog('close');
					$(this).remove();
				},
				Cancel: function() {
					$(this).dialog('close');
					$(this).remove();
				}
			}
		});

		return false;
	});

	$('.admin-food-item-delete').live('click', function() {

		var parent = $(this).closest('.admin-food-item-wrap');
		var id_dish = parent.attr('data-id_dish');
		var name = parent.find('.dish-name').val();

		var remove = function() {
			parent.fadeOut(100,function() {
				$(this).remove();
			});
		};

		if (!id_dish) {
			remove();
		} else {
			if (confirm('Are you sure you want to delete "' + name + '"')) {
				remove();
			}
		}
	});

	$('.dish-options-delete').live('click', function() {
		var parent = $(this).closest('.dish-options');
		var id_option = parent.attr('data-id_option');
		var name = parent.find('input[name="dish-options-name"]').val();

		var remove = function() {
			parent.fadeOut(100,function() {
				$(this).remove();
			});
		};

		if (!id_option) {
			remove();
		} else {
			if (confirm('Are you sure you want to delete "' + name + '"')) {
				remove();
			}
		}
	});

	$('.admin-dish-options-wrapper input[type="text"]').live('keyup', function() {
		var allfull = true;

		$(this).closest('.admin-dish-options-wrapper').find('.dish-options').each(function() {

			var selfComplete = true;
			$(this).find('input[type="text"]').each(function() {
				if ($(this).val() == '' || !$(this).val()) {
					allfull = selfComplete = false;
				}
			});
			if (selfComplete) {
				$(this).addClass('blue');
			}
		});

		if (allfull) {
			$(this).closest('.input-faker').append(App.returnOption({price: '', name: '', id_option: ''},$(this).closest('.admin-dish-options-wrapper').attr('data-type'),$(this).closest('.admin-dish-options-wrapper').attr('data-parent')));
		}
	});

	$('.control-link-add-option').live('click', function() {
		var self = $(this);
		$('#dialog-option-group').dialog({
			resizable: false,
			height: 250,
			width: 400,
			modal: true,
			buttons: {
				'Create': function() {
					if ($(this).find('[name="admin-option-name"]').val()) {
						App.createOptionGroup(this, self);
						$(this).dialog('close');
						$(this).find('[name="admin-option-name"]').val('');
						$(this).find('[name="admin-option-price"]').removeAttr('checked');
						$(this).find('[name="admin-option-type"]').val('check');
					}
				},
				Cancel: function() {
					$(this).dialog('close');
					$(this).find('[name="admin-option-name"]').val('');
					$(this).find('[name="admin-option-price"]').removeAttr('checked');
					$(this).find('[name="admin-option-type"]').val('check');
				}
			}
		});
	});

	/**
	 * Opens new Dish Category dialog
	 *
	 * @returns boolean
	 */
	$('.control-link-add-category').live('click', function() {
		$('#dialog-add-menu').dialog({
			resizable: false,
			height: 160,
			width: 315,
			modal: true,
			buttons: {
				'Create': function() {
					if ($(this).find('[name="admin-category-name"]').val()) {
						App.createCategory(this);
						$(this).dialog('close');
						$(this).find('[name="admin-category-name"]').val('');
					}
				},
				Cancel: function() {
					$(this).dialog('close');
					$(this).find('[admin-category-name"]').val('');
				}
			}
		});
		return false;
	});


});


(function($) {
	$.fn.getCursorPosition = function() {
		var input = this.get(0);
		if (!input) return; // No (input) element found
		if ('selectionStart' in input) {
			// Standard-compliant browsers
			return input.selectionStart;
		} else if (document.selection) {
			// IE
			input.focus();
			var sel = document.selection.createRange();
			var selLen = document.selection.createRange().text.length;
			sel.moveStart('character', -input.value.length);
			return sel.text.length - selLen;
		}
	}

	$.fn.setCursorPosition = function(position){
		if(this.length == 0) return this;
		return $(this).setSelection(position, position);
	}

	$.fn.setSelection = function(selectionStart, selectionEnd) {
		if(this.length == 0) return this;
		input = this[0];

		if (input.createTextRange) {
			var range = input.createTextRange();
			range.collapse(true);
			range.moveEnd('character', selectionEnd);
			range.moveStart('character', selectionStart);
			range.select();
		} else if (input.setSelectionRange) {
			input.focus();
			input.setSelectionRange(selectionStart, selectionEnd);
		}

		return this;
	}

})(jQuery);
