
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
			$('.admin-restaurant-form input, admin-restaurant-form select').val('').prop('checked',false);
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

			$('.admin-restaurant-form input, .admin-restaurant-form select').each(function() {
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

							$('input[name="' + x + '"][value="0"]').prop('checked', false);
							$('input[name="' + x + '"][value="1"]').prop('checked', true);
						} else {

							$('input[name="' + x + '"][value="0"]').prop('checked', true);
							$('input[name="' + x + '"][value="1"]').prop('checked', false);						
						}
					}
				}
			});

			App.restaurant = restaurant.id_restaurant;

			$('.admin-restaurant-content').html('');

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
		var selector = 'input.dataset-restaurant, select.dataset-restaurant';
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
				console.log('hours',h);

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
console.log(hours); return;
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
});