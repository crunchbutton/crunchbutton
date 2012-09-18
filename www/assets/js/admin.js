
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


$(function() {
	$('.admin-restaurant-link').live('click',function() {
		if (!$(this).attr('data-id_restaurant')) {
			$('.admin-restaurant-form input, .admin-restaurant-form select, .admin-restaurant-form textarea').val('').prop('checked',false);
			App.restaurant = null;
			$('.admin-restaurant-content').html('');
			return;
		}
		App.cache('Restaurant', $(this).attr('data-id_restaurant'), function() {
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

			App.restaurant = restaurant.id_restaurant;

			$('.admin-restaurant-content').html('');

			var categories = restaurant.categories();
			var isDishes = false;

			for (var i in categories) {
				var dishes = categories[i].dishes();

				for (var x in dishes) {
					App.showDish(dishes[x]);
					isDishes = true;
				}
			}
			
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
						row.append('<input type="text" value="' + App.formatTime(dayitem[x][0]) + '" name="' + d + '-open[]">&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;<input type="text" value="' + App.formatTime(dayitem[x][1]) + '" name="' + d + '-close[]">');
						dayWrap.append(row);
					}
				}

				var row = $('<div class="hours-date-hour"></div>');
				row.append('<input type="text" name="' + d + '-open[]">&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;<input type="text" name="' + d + '-close[]">');
				dayWrap.append(row);

				$('.admin-restaurant-hours').append(day);

			}			
		});
	});
	
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
			row.append('<input type="text" name="' + day + '-open[]">&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;<input type="text" name="' + day + '-close[]">');
			$(this).closest('.hours-date-hour').append(row);
		}
	});

	var getValues = function(selector, restaurant) {
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

			if ($(this).attr('type') == 'checkbox') {
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

	$('.admin-restaurant-save').live('click', function() {
		saveRestaurant();
		saveHours();
	});

	var saveRestaurant = function() {
		var selector = 'input.dataset-restaurant, select.dataset-restaurant, textarea.dataset-restaurant';
		var id = App.restaurant;

		if (id) {
			App.cache('Restaurant', id, function() {
				var restaurant = getValues(selector, this);
				restaurant.save();
			});
		} else {
			var restaurant = getValues(selector, {});
			restaurant = new Restaurant(restaurant)
			restaurant.save();
		}
	};

	var saveHours = function() {
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
							hours[d][hours[d].length] = [App.unFormatTime(h[d + '-open'][x]), App.unFormatTime(h[d + '-close'][x],true)];
						}
					}
				}

				$.post('/api/restaurant/' + id + '/hours', {hours: hours});
			});
		}
	};
	
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

	$('.bind-a-check').click(function(e) {
		var name = $(this).attr('name');
		var value = $(this).attr('value');
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
	
	$('.date-picker').DatePicker({
		format:'m/d/Y',
		date: $('.date-picker').val(),
		current: $('.date-picker').val(),
		starts: 1,
		mode: 'range',
		calendars: 3,
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
	
	$('.admin-food-item').live('click', function() {
		$(this).closest('.admin-food-item-wrap').find('.admin-food-item-content').slideToggle(100);
		$(this).toggleClass('admin-food-item-collapsed');
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
	
	$('.control-link-add').live('click', function() {
		App.showDish({});
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
	
	$('.admin-food-item-option-padding input[type="text"]').live('keyup', function() {
		var allfull = true;
		$(this).closest('.admin-food-item-option-padding').find('input[type="text"]').each(function() {
			if ($(this).val() == '') {
				allfull = false;
			}
		});
		if (allfull) {
			$(this).closest('.input-faker').append(App.returnOption({price: '',name:'',id_option:''},$(this).closest('.admin-food-item-option-padding').attr('data-type'),$(this).closest('.admin-food-item-option-padding').attr('data-parent')));
		}
	});


});

App.showDish = function(dishItem) {
	if (!dishItem.id_dish) {
		dishItem = {
			'name': '',
			'description': '',
			'id_dish': '',
			'price': ''
		};
	}

	var dish = $('<div class="admin-food-item-wrap" data-id_dish="' + dishItem.id_dish + '"' + (dishItem.id_dish ? '' : ' style="display: none;"') + '></div>');
	dish.append('<div class="admin-food-item ' + (dishItem.id_dish ? 'admin-food-item-collapsed' : '') + '"><span class="food-name">' + dishItem.name + '</span><span class="food-price">($<span class="food-price-num">' + dishItem.price + '</span>)</span><div class="food-drop-down"></div></div>')
	var content = $('<div class="admin-food-item-content" ' + (dishItem.id_dish ? 'style="display: none;"' : '') + '></div>');
	var padding = $('<div class="admin-food-item-content-padding">');
	dish.append(content);
	content.append(padding);
	
	var options = $('<div class="admin-dish-options"></div>');
	var basicOptions = $('<div class="input-faker"></div>');
	var basicWrapper = $('<div class="admin-dish-options-wrapper"><div class="admin-dish-options-title">Basic options:</div></div>')
		.append(basicOptions);

	var optGroups = [];

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
		}
	}
	
	basicOptions.append(App.returnOption({price: '',name:'',id_option:''},'check'));
	
	padding
		.append('<input type="text" placeholder="Name" name="dish-name" class="clean-input dish-name" value="' + dishItem.name + '">')
		.append('<div class="input-faker dish-price"><div class="input-faker-content">$&nbsp;</div><input type="text" placeholder="" name="dish-price" value="' + dishItem.price + '" class="clean-input" data-clean_type="float"><div class="divider"></div></div>')
		.append('<textarea placeholder="Description" name="dish-description" class="clean-input dish-description" value="' + dishItem.description + '"></textarea>')
		.append('<div class="divider"></div><div class="divider dots" style="margin: 10px 0 10px 0;"></div>')
		.append(options);
					
	content
		.append('<div class="divider dots"></div>')
		.append('<div class="admin-food-item-content-padding"><div class="action-button red action-button-small admin-food-item-delete"><span>Delete</span></div><div class="divider"></div></div>')
		.append('<div class="divider"></div>');

	$('.admin-restaurant-dishes .admin-restaurant-content').append(dish);

	
	if (!dishItem.id_dish) {
		dish.find('.dish-name').focus();
		dish.fadeIn(200);
	}
	
	

	
	
};

	
App.returnOption = function(o, type, parent) {
	var defaulted  = '';
	switch (type) {
		case 'select':
			defaulted = '<input type="radio" name="dish-options-default-' + parent + '" value="1" ' + (o['default'] == '1' ? 'checked="checked"' : '') + '>';
			break;

		default:
		case 'check':
			defaulted = '<input type="checkbox" name="dish-options-default" value="1" ' + (o['default'] == '1' ? 'checked="checked"' : '') + '>';
			break;
	}

	return $('<div class="divider"></div>'
		+ '<div class="admin-food-item-option-padding" data-type="' + type + '" data-parent="' + parent + '">'
			+ '<div class="dish-options" data-id_option="' + o.id_option + '">'
				+ defaulted
				+ '<input type="text" placeholder="Name" name="dish-options" value="' + o.name + '">'
				+ '<div class="input-faker-content">$ </div>'
				+ '<input type="text" placeholder="" name="dish-options-price" value="' + o.price + '">'
				+ '<div class="divider"></div>'
			+ '</div>'
		+ '</div>');
}
;


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
