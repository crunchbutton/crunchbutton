
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

App.request = function(url, complete) {
	$.getJSON(url,function(json) {
		complete(json);
	});
};

App.cache = function(type, id) {
	var finalid, args = arguments, complete, partComplete;

	complete = args[2] ? args[2] : function() {};

	partComplete = function() {
		if (this.uuid) {
			App.cached[type][id.uuid] = this;
			App.cached[type][id] = this;
		}
		if (this.permalink) {
			App.cached[type][id.permalink] = this;
			App.cached[type][id] = this;
		}
		complete.call(this);
	}

	if (typeof(id) == 'object') {
		//App.cached[type][id.id] = id;

		eval('App.cached[type][id.id] = new '+type+'(id,partComplete)');
		finalid = id.id;

	} else if (!App.cached[type][id]) {
		eval('App.cached[type][id] = new '+type+'(id,partComplete)');

	} else {
		complete.call(App.cached[type][id]);
	}

	// only works sync (Ti)
	return App.cached[type][finalid || id];

};

$(function() {
	$('.admin-restaurant-link').live('click',function() {
		if (!$(this).attr('data-id_restaurant')) {
			$('.admin-restaurant-form input, admin-restaurant-form select').val('').prop('checked',false);
			return;
		}
		App.cache('Restaurant', $(this).attr('data-id_restaurant'), function() {
			var restaurant = this;
			$('.admin-restaurant-form input, admin-restaurant-form select').each(function() {
				if ($(this).attr('type') == 'checkbox') {
					$(this).prop('checked', restaurant[$(this).attr('name')] == 1 ? true : false);

				} else {
					$(this).val(restaurant[$(this).attr('name')]);
				}
			});
		});
		
	});
});