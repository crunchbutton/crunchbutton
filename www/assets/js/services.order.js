// OrdersService service
NGApp.factory( 'OrdersService', function( $http, $location ){
	
	var service = { list : false };

	service.all = function(){

		$http.get( App.service + 'user/orders', {cache: true}).success(function(json) {
			for (var x in json) {
				json[x].timeFormat = json[x]._date_tz.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i,'$1/$2 $3');
			}
			service.list = json;
		});

	}

	service.restaurant = function( permalink ) {
		$location.path( '/' + App.restaurants.permalink + '/' + permalink );
	};

	service.receipt = function( id_order ) {
		$location.path( '/order/' + id_order );
	};

	return service;

} );

// OrdersService service
NGApp.factory( 'OrderService', function( $routeParams, $location, $rootScope, FacebookService ){
	
	var service = {};

	service.facebook = FacebookService;

	App.cache('Order', $routeParams.id, function() {
		service.order = this;

		var complete = function() {
			$location.path('/');
		};

		if (!service.order.uuid) {
			if (!$rootScope.$$phase) {
				$rootScope.$apply(complete);
			} else {
				complete();
			}
			return;
		}

		service.facebook._order_uuid = service.order.uuid;
		service.facebook.preLoadOrderStatus();

		App.cache('Restaurant', service.order.id_restaurant, function() {
			
			service.restaurant = this;

			var complete = function() {

				if (service.order['new']) {
					setTimeout(function() {
						service.order['new'] = false;
					},500);
				}
			};

			if (!$rootScope.$$phase) {
				$rootScope.$apply(complete);
			} else {
				complete();
			}
		});
	});
	return service;

} );