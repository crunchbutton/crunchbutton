// Restaurant list service
NGApp.factory('RestaurantsService', function ($http, $rootScope, PositionsService ) {

	var service = { permalink : 'food-delivery', forceLoad : true };
	var restaurants = false;

	service.reset = function () {
		restaurants = false;
	}

	service.getRestaurants = function(){
		return restaurants;
	}

	service.position = PositionsService;

	service.sort = function () {
		// dont not sort. if a restaurant closes we want to make sure it apears so

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

		if (restaurants === false || service.forceLoad) {
			var url = App.service + 'restaurants?lat=' + service.position.pos().lat() + '&lon=' + service.position.pos().lon() + '&range=' + (service.position.range || 2 );

			$http.get(url, {
				cache: false
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

			service.forceLoad = false;
		} else {
			if (success) {
				success(restaurants);
			}
			return restaurants;
		}
	}

	$rootScope.$on( 'NewLocationAdded', function(e, data) {
		service.forceLoad = true;
	});

	return service;
});


//CommunityService Service
NGApp.factory( 'CommunityService', function( $http ){
	var service = {};
	service.getById = function( id ){
		for (x in App.communities) {
			if( App.communities[x].id_community == id ){
				return App.communities[x];
			}
		}
		return false;
	}
	return service;
} );

//RestaurantService Service
NGApp.factory('RestaurantService', function ($http, $routeParams, $rootScope, CommunityService ) {
	var service = {};
	service.init = function(){
		App.cache('Restaurant', $routeParams.id, function () {
			var restaurant = this;
			var community = CommunityService.getById( restaurant.id_community );
			$rootScope.$broadcast( 'restaurantLoaded',  { restaurant : restaurant, community : community } );
		});
	}
	return service;
});