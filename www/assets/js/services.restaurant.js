// Restaurant list service
NGApp.factory('RestaurantsService', function ($http, $rootScope, PositionsService ) {

	var service = { permalink : 'food-delivery', forceLoad : true, forceGetStatus : false };
	var restaurants = false;

	service.reset = function () {
		restaurants = false;
	}

	service.getRestaurants = function(){
		return restaurants;
	}

	service.position = PositionsService;

	service.sort = function () {
		
		App.profile.log('start sort');
		
		var list = restaurants;

		var areAllTheRestaurantsClosed = true;

		for (var x in list) {
			if( list[x]._open ){
				areAllTheRestaurantsClosed = false;
			}
		}

		// if all are closed sort by that are opening the soonest
		if( areAllTheRestaurantsClosed ){
			list.sort( 
				sort_by( {
					name: '_openIn',
					primer: parseInt,
					reverse: false
				} )
			);
		} else {
			list.sort( 
				sort_by( {
					name: '_open',
					reverse: true
				}, {
					name: 'delivery',
					reverse: true
				}, {
					name: '_weight',
					primer: parseInt,
					reverse: true
				}, {
					name: '_openIn',
					primer: parseInt,
					reverse: false
				} )
			);
		}

		if( areAllTheRestaurantsClosed ){
			// Number of restaurants that will have the opening tag
			var tagRestaurantsAsClosing = 3;
			for (var x in list) {
				if( tagRestaurantsAsClosing <= 0 ){
					break;
				}
				tagRestaurantsAsClosing--;
				list[x]._tag = 'opening';	
			}
		}

		restaurants = list;
		return restaurants;
	}

	service.getStatus = function () {

		App.profile.log('start status');

		var list = restaurants;
		var allClosed = true;
		var totalClosedRestaurantsAfter = 0;
		var totalClosedRestaurantsBefore = 0;

		for (var x in list) {
			if( !list[x]._open ){
				totalClosedRestaurantsBefore++;
			} else {
				allClosed = false;
			}
		}
		
		for (var x in list) {

			list[x].closesIn();

			// determine which tags to display
			if (!list[x]._open || list[x]._closesIn == 0) {
				if( list[x]._force_close ){
					list[x]._tag = 'force_close';
				} else {
					list[x]._tag = 'closed';	
				}
				list[x].openInFormated();
			} else {
				if (list[x].delivery != '1') {
					list[x]._tag = 'takeout';
				} else {
					if( list[x]._closesIn <= list[x]._minimumTime && list[x]._closesIn > 0 ){
						list[x]._tag = 'closing';	
					} 
				}
			}

			if( !list[x]._open ){
				totalClosedRestaurantsAfter++;
			}
		};

		restaurants = list;

		// Reorder the restaurants
		if( allClosed || ( totalClosedRestaurantsAfter != totalClosedRestaurantsBefore ) ){
			service.sort();
		}
		return restaurants;
	}

	service.list = function (success, error) {
		
		if (!service.position.pos().valid('restaurants')) {
			if (error) {
				error();
			}
			return false;
		}
		App.profile.log('start list');

		if (restaurants === false || service.forceLoad) {
			var url = App.service + 'restaurants?lat=' + service.position.pos().lat() + '&lon=' + service.position.pos().lon() + '&range=' + (service.position.range || 2 );

			service.forceGetStatus = false;

			$http.get(url, {
				cache: false
			}).success(function (data) {
				App.profile.log('got list');
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
		restaurants = false;
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
NGApp.factory( 'RestaurantService', function ($http, $routeParams, $rootScope, CommunityService ) {
	var service = { basicInfo : null };
	service.init = function(){
		App.cache('Restaurant', $routeParams.id, function () {
			var restaurant = this;
			var community = CommunityService.getById( restaurant.id_community );
			$rootScope.$broadcast( 'restaurantLoaded',  { restaurant : restaurant, community : community } );
		});
	}
	return service;
});