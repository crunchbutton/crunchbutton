
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
			$('.admin-restaurant-form').attr('data-id_restaurant', '');
			return;
		}
		App.cache('Restaurant', $(this).attr('data-id_restaurant'), function() {
			var restaurant = this;
			console.log(restaurant);
			$('.admin-restaurant-form input, admin-restaurant-form select').each(function() {
				if ($(this).attr('type') == 'checkbox') {
					$(this).prop('checked', restaurant[$(this).attr('name')] == 1 ? true : false);
				} else {
					$(this).val(restaurant[$(this).attr('name')]);
				}
			});
			$('.admin-restaurant-panel').show();
			$('.admin-restaurant-form').attr('data-id_restaurant', restaurant.id_restaurant);
			
		});
		
	});
	
	$('.admin-restaurant-save').live('click',function() {
		var id = $(this).closest('.admin-restaurant-form').attr('data-id_restaurant');

		var saveRestaurant = function(restaurant) {
			$('.admin-restaurant-form input, admin-restaurant-form select').each(function() {
				if ($(this).attr('type') == 'checkbox') {
					restaurant[$(this).attr('name')] = $(this).prop('checked') ? true : false;
				} else {
					restaurant[$(this).attr('name')] = $(this).val();
				}
			});
			return restaurant;
		}

		if (id) {
			App.cache('Restaurant', id, function() {
				var restaurant = saveRestaurant(this);
				restaurant.save();
			});
		} else {
			var restaurant = saveRestaurant({});
			restaurant = new Restaurant(restaurant)
			restaurant.save();
		}
	});
});