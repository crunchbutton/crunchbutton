// Restaurant list service
NGApp.factory('RestaurantsService', function ($http, PositionsService) {

	var service = {};
	var restaurants = false;
	var isSorted = false;

	service.reset = function () {
		restaurants = false;
		isSorted = false;
	}

	service.position = PositionsService;

	service.sort = function () {

		if (isSorted) {
			return restaurants;
		}

		var list = restaurants;

		for (var x in list) {

			// recalculate restaurant open status on relist
			list[x].open();

			// determine which tags to display
			if (!list[x]._open) {
				list[x]._tag = 'closed';
			} else {
				if (list[x].delivery != '1') {
					list[x]._tag = 'takeout';
				} else if (list[x].isAboutToClose()) {
					list[x]._tag = 'closing';
				}
			}
			// show short description
			list[x]._short_description = (list[x].short_description || ('Top Order: ' + (list[x].top_name ? (list[x].top_name || list[x].top_name) : '')));
		};
		list.sort(sort_by({
			name: '_open',
			reverse: true
		}, {
			name: 'delivery',
			reverse: true
		}, {
			name: '_weight',
			primer: parseInt,
			reverse: true
		}));
		isSorted = true;
		restaurants = list;
		return restaurants;
	}

	service.list = function (success, error) {
		if (!service.position.pos().valid('restaurants')) {
			if (error) {
				error();
			}
			return false;
		}

		if (restaurants === false || App.restaurants.forceLoad) {

			var url = App.service + 'restaurants?lat=' + service.position.pos().lat() + '&lon=' + service.position.pos().lon() + '&range=' + (service.position.range || App.defaultRange);

			$http.get(url, {
				cache: true
			}).success(function (data) {

				var list = [];
				if (typeof data.restaurants == 'undefined' || data.restaurants.length == 0) {
					if (error) {
						error();
						return false;
					}
				} else {
					for (var x in data.restaurants) {
						list[list.length] = new Restaurant(data.restaurants[x]);
					}
					restaurants = list;
					if (success) {
						success(list);
					}
					return list;
				}
			});
			isSorted = false;
			App.restaurants.forceLoad = false;
		} else {
			if (success) {
				success(restaurants);
			}
			return restaurants;
		}
	}

	return service;
});

//RestaurantService Service
NGApp.factory('RestaurantService', function ($http, $routeParams, $rootScope, AccountService ) {
	
	var service = {
		loaded : false
	};

	service.init = function(){
		service.restaurant = false;
		loaded = false;
		service.load();
	}

service.account = AccountService;

service.load = function(){

	App.cache('Restaurant', $routeParams.id, function () {
		 
		if (service.restaurant && service.restaurant.permalink != $routeParams.id) {
			service.cartService.resetOrder();
		}

		service.restaurant = this;

		service.community = App.getCommunityById( service.restaurant.id_community );
		
		var lastOrderDelivery = false;
		var lastPayCash = false;

		if ( service.account.user && service.account.user.presets && service.account.user.presets[ service.restaurant.id_restaurant ] ) {
			// Check if the last user's order at this restaurant was a delivery type
			lastOrderDelivery = service.account.user.presets[ service.restaurant.id_restaurant ].delivery_type;
			// Check if the last user's order at this restaurant was cash type
			lastPayCash = service.account.user.presets[ service.restaurant.id_restaurant ].pay_type;
			App.order['delivery_type'] = lastOrderDelivery;
			App.order['pay_type'] = lastPayCash;
		}

		//			title: service.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton',

		var complete = function () {

			service.loaded = true;

			service.lastOrderDelivery = lastOrderDelivery;
			
			service.showRestaurantDeliv = ((lastOrderDelivery == 'delivery' || service.restaurant.delivery == '1' || service.restaurant.takeout == '0') && lastOrderDelivery != 'takeout');

		};

		$rootScope.$safeApply( complete );

	});
}
	return service;
});