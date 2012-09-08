
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
			$('.admin-restaurant-panel').hide();
			App.restaurant = null;
			$('.admin-restaurant-content').html('');
			return;
		}
		App.cache('Restaurant', $(this).attr('data-id_restaurant'), function() {
			var restaurant = this;

			$('.admin-restaurant-form input, .admin-restaurant-form select').each(function() {
				if ($(this).attr('type') == 'checkbox') {
					$(this).prop('checked', restaurant[$(this).attr('name')] == 1 ? true : false);
				} else {
					$(this).val(restaurant[$(this).attr('name')]);
				}
			});
			$('.admin-restaurant-panel').show();
			App.restaurant = restaurant.id_restaurant;

			$('.admin-restaurant-content').html('');
			
			var days = ['sun','mon','tue','wed','thu','fri','sat'];

			for (var d in days) {

				var day = $('<div class="hours-date"><span class="hours-date-label">' + days[d] + '</span></div>');
				var dayWrap = $('<div class="hours-date-hours"></div>').appendTo(day);

				if (!restaurant._hours) {
					continue;
				}

				var dayitem = restaurant._hours[days[d]];

				for (var x in dayitem) {
					var row = $('<div class="hours-date-hour"></div>');
					row.append('<input type="text" value="' + dayitem[x][0] + '" name="' + days[d] + '-open"> - <input type="text" value="' + dayitem[x][1] + '" name="' + days[d] + '-close">');
					dayWrap.append(row);
				}
				
				var row = $('<div class="hours-date-hour"></div>');
				row.append('<input type="text" name="' + days[d] + '-open"> - <input type="text" name="' + days[d] + '-close">');
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
			row.append('<input type="text" name="' + day + '-open"> - <input type="text" name="' + day + '-close">');
			$(this).closest('.hours-date-hour').append(row);
		}
	});

	var saveRestaurant = function(selector, restaurant) {
		$(selector + ' input, ' + selector + ' select').each(function() {
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

	$('.admin-restaurant-save').live('click',function() {
		var selector = '.admin-restaurant-form';
		var id = App.restaurant;

		if (id) {
			App.cache('Restaurant', id, function() {
				var restaurant = saveRestaurant(selector, this);
				restaurant.save();
			});
		} else {
			var restaurant = saveRestaurant(selector, {});
			restaurant = new Restaurant(restaurant)
			restaurant.save();
		}
	});

	$('.admin-restaurant-hours-save').live('click',function() {
		var selector = '.admin-restaurant-hours';
		var id = App.restaurant;

		if (id) {
			App.cache('Restaurant', id, function() {
				var h = saveRestaurant(selector, {});
				var hours = {'sun': [],'mon': [],'tue': [],'wed': [],'thu': [],'fri': [],'sat': []};

				for (var d in hours) {

					$(selector).find('[name="' + d + '-open"]').each(function() {

						if (!$(this).val()) {
							return;
						}
						var close = $($(selector).find('[name="' + d + '-close"]').get(0)).val();
						if (!close) {
							return;
						}

						var hour = [$(this).val(), close];
						hours[d][hours[d].length] = hour;
					});
				}
				console.log(hours);
				$.post('/api/restaurant/' + id + '/hours', {hours: hours}, function() {
				
				});
			});
		}
	});
	
	$('.admin-restaurant-hours-save-all').live('click',function() {
		$('.admin-restaurant-hours-save-link').click();
	});
	
	
	$('.check label').live('click',function() {
		var checked = $(this).closest('.check').find('input').prop('checked');
		$(this).closest('.check').find('input').prop('checked', checked ? false : true);
	});

});