// Restaurant list service
NGApp.factory('RestaurantsService', function ($http, $rootScope, PositionsService ) {

	var service = { permalink : 'food-delivery', forceLoad : true, forceGetStatus : false, cachedAt: false };
	var restaurants = false;

	service.reset = function () {
		restaurants = false;
	}

	service.getRestaurants = function(){
		return restaurants;
	}

	service.position = PositionsService;

	service.sort = function () {

		var list = restaurants;

		var areAllTheRestaurantsMinimized = true;

		// call here that way this method does not have to be called inside each restaurant object
		var now = dateTime.getNow();

		var totalMaximized = 0;

		for ( var x in list ) {
			list[ x ]._divider = false;
			list[ x ].open( now );
		}

		// call the method open to check it status and tagfy - 2662
		for ( var x in list ) {
			if( list[ x ].openRestaurantPage( now, true ) ){
				list[ x ]._maximized = true;
				if( !list[ x ].driver_restaurant ){
					areAllTheRestaurantsMinimized = false;
				}
				if( !list[ x ]._open ){
					list[ x ].tagfy( 'opening' );
				} else {
					list[x].tagfy();
				}
				totalMaximized++;
			} else {
				list[ x ]._maximized = false;
				list[x].tagfy();
			}
		}

		// if all are closed sort by that are opening the soonest
		if( areAllTheRestaurantsMinimized ){

			if( list && list.sort ){

				list.sort(
					sort_by( {
						name: 'driver_restaurant',
						reverse: true
					}, {
						name: '_hasHours',
						reverse: true
					}, {
						name: '_weight',
						primer: parseFloat,
						reverse: true
					}, {
						name: '_opensIn',
						primer: function( n ){
							if( !isNaN( parseInt( n ) ) ){
								return parseInt( n );
							} else {
								// the restaurant does not have open hours for next 24 hours, fake opens in to next week just to make it the last of the list
								return ( 16800 * 60 );
							}
						},
						reverse: false
					}, {
						name: 'distance',
						primer: parseFloat,
						reverse: false
					}, {
						name: 'name',
						reverse: false
					} )
				);
			}
		} else {

			list.sort(
				sort_by( {
					name: '_maximized',
					reverse: true
				},  {
					name: '_open',
					reverse: true
				}, {
					name: '_opensIn',
					primer: function( n ){
						if( !isNaN( parseInt( n ) ) ){
							return parseInt( n );
						} else {
							// the restaurant does not have open hours for next 24 hours, fake opens in to next week
							return ( 16800 * 60 );
						}
					},
					reverse: false
				}, {
					name: 'delivery',
					reverse: true
				}, {
					name: '_weight',
					primer: parseFloat,
					reverse: true
				}, {
					name: 'distance',
					primer: parseFloat,
					reverse: false
				} )
			);
		}

		if( areAllTheRestaurantsMinimized ){
			var tagRestaurantsAsClosing = 5;
			var divider = false;
			for ( var x in list ) {
				if( divider ){
					list[ x ]._divider = true;
					divider = false;
				}
				if( tagRestaurantsAsClosing <= 0 ){
					continue;
					break;
				}
				list[x].tagfy( 'opening', list[x].driver_restaurant );
				list[x]._maximized = true;
				tagRestaurantsAsClosing--;
				if( tagRestaurantsAsClosing === 0 ){
					divider = true;
				}
			}
		} else {
			for ( var x in list ) {
				if( !list[x]._maximized ){
					var prev = x - 1;
					if( list[ prev ] && list[ prev ]._maximized ){
						list[ x ]._divider = true;
						break;
					}
				}
			}
		}

		restaurants = list;
		return restaurants;
	}

	service.getStatus = function ( force, callback ) {

		var now = dateTime.getNow();
		var list = restaurants;
		var areAllTheRestaurantsMinimized = true;
		var totalClosedRestaurantsAfter = 0;
		var totalClosedRestaurantsBefore = 0;

		for (var x in list) {
			if( !list[x]._open ){
				totalClosedRestaurantsBefore++;
			} else {
				areAllTheRestaurantsMinimized = false;
			}
		}

		// call the method open to check it status and tagfy - 2662
		for ( var x in list ) {
			if( list[ x ].openRestaurantPage( now, true ) ){
				areAllTheRestaurantsMinimized = false;
				totalClosedRestaurantsBefore++;
				if( !list[ x ]._open ){
					list[ x ].tagfy( 'opening' );
				} else {
					list[x].tagfy();
				}
			} else {
				list[ x ]._maximized = false;
				list[x].tagfy();
			}
		}

		restaurants = list;

		// Reorder the restaurants
		if( areAllTheRestaurantsMinimized || ( totalClosedRestaurantsAfter != totalClosedRestaurantsBefore ) ){
			service.sort();
		}
		// check if it is necessary to reload the hours
		service.reloadHours( force );

		if( callback ){
			if( typeof callback === 'function' ){
				callback( restaurants );
			}
		}
		return restaurants;
	}

	service.reloadHours = function( force ){
		var now = ( Math.floor( new Date().getTime() / 1000 ) );
		var age = Math.floor( now - service.cachedAt );
		if( force || age >= 60 ){
			service.cachedAt = now;
			var count = 1;
			for ( var x in restaurants ) {
				var callback = null;
				if( count == restaurants.length ){
					var callback = service._callback;
				}
				count++;
				restaurants[ x ].reloadHours( true, callback );
			}
		}
		service._callback = null;
	}

	service.removeInactive = function( id_restaurant ){
		var list = [];
		for ( var x in restaurants ) {
			if( restaurants[ x ].id_restaurant != id_restaurant ){
				list.push( restaurants[ x ] );
			}
		}
		restaurants = list;
	}

	service.list = function ( success, error ) {

		if (!service.position.pos().valid('restaurants')) {
			if (error) {
				error();
			}
			return false;
		}

		if (restaurants === false || service.forceLoad) {

			var url = App.service + 'restaurants?lat=' + service.position.pos().lat() + '&lon=' + service.position.pos().lon() + '&range=' + (service.position.range || 2 );

			service.forceGetStatus = false;

			$http.get( url, {
				cache: false
			}).success(function (data) {
				// property to control the cache expiration of the list
				service.cachedAt = ( Math.floor( new Date().getTime() / 1000 ) );
				service.community_closed = data.community_closed;
				service.community = data.community;
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
					var community = getMostCommonCommunity(restaurants);
					if(community != null) {
						// overwrite user's community with last set community
						App.trackCommunity(community);
					} else {
						console.log('no community found in restaurants: ', restaurants);
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
	function mostCommonElement (arr) {
		var counts, count, elem, maxCount, maxValue;
		counts = {};
		for(var i = 0; i < arr.length; i++) {
			if(!counts[arr[i]]) {
				counts[arr[i]] = 0;
			}
			counts[arr[i]]++;
		}
		for(elem in counts) {
			if(counts.hasOwnProperty(elem)) {
				count = counts[elem];
				if(count > maxCount || !maxCount) {
					maxCount = count;
					maxValue = elem;
				}
			}
		}
		return maxValue;
	}

	// pulls out the most common community from all the passed in restaurants
	function getMostCommonCommunity (restaurants) {
		var restaurant, id_community, count;
		var communities = {};
		var maxCount = 0;
		var mostFrequentCommunity = undefined;
		try {
			communities = restaurants.map(function (restaurant) { return restaurant.id_community;});
			return mostCommonElement(communities);

		} catch (e) {
			console.log('error with getting community from restaurant', e)
		}
	}

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

	var service = { basicInfo : null, loadedList: {}, initied : {}, id_community: null };

	service.alreadyLoaded = function(){
		return ( service.loadedList[ $routeParams.id ] ? true : false );
	}

	service.init = function(){
		if( service.initied[ $routeParams.id ] ){
			return;
		}
		service.initied[ $routeParams.id ] = true;
		App.cache( 'Restaurant', $routeParams.id, function () {
			service.initied[ $routeParams.id ] = false;
			var restaurant = this;
			service.loadedList[ $routeParams.id ] = true;
			service.id_community = restaurant.id_community;
			var community = CommunityService.getById( restaurant.id_community );
			$rootScope.$broadcast( 'restaurantLoaded',  { restaurant : restaurant, community : community } );

		});
	}
	return service;
});