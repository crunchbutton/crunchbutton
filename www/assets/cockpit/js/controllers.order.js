NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/orders', {
			action: 'orders',
			controller: 'OrdersCtrl',
			templateUrl: 'assets/view/orders.html',
			reloadOnSearch: false

		}).when('/order/:id', {
			action: 'order',
			controller: 'OrderCtrl',
			templateUrl: 'assets/view/orders-order.html'
		});
}]);

NGApp.controller('OrdersCtrl', function ($scope, $location, OrderService, ViewListService, SocketService, MapService, TicketService, RestaurantService, CommunityService) {
	angular.extend($scope, ViewListService);

//	var query = $location.search();
//	$scope.query.view = 'list';

	SocketService.listen('orders', $scope)
		.on('update', function(d) {
			for (var x in $scope.orders) {
				if ($scope.orders[x].id_order == d.id_order) {
					$scope.orders[x] = d;
				}
			}

		}).on('create', function(d) {
			$scope.update();
		});

	var draw = function() {
		if (!$scope.map || !$scope.orders) {
			return;
		}

		MapService.trackOrders({
			map: $scope.map,
			orders: $scope.orders,
			id: 'orders-location',
			scope: $scope
		});
	};

	$scope.ticket = function(id_order) {
		OrderService.ticket(id_order, function(json) {
			if (json.id_support) {
				ticket(json.id_support);
			} else {
				App.alert('Fail retrieving support ticket!' );
			}
		});
	}

	var ticket = function( id_support ){
		var url = '/ticket/' + id_support;
		$scope.navigation.link( url );
	}

	$scope.$on('mapInitialized', function(event, map) {
		console.log('INIT');
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			restaurant: '',
			community: '',
			date: '',
			view: 'list',
			datestart: '',
			dateend: '',
			user: '',
			phone: '',
			fullcount: false
		},
		update: function() {
			OrderService.list($scope.query, function(d) {
				$scope.orders = d.results;
				$scope.complete(d);
				draw();
			});
		}
	});

	$scope.show_more_options = false;

	$scope.exports = function(){
		var params = {};
		angular.copy( $scope.query, params );
		console.log('params',params);
		OrderService.exports( params, function(){
		} );
	}


	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options) {

			if( !$scope.restaurants ){
				$scope.restaurants = [];
				RestaurantService.shortlist( function( json ){
					$scope.restaurants = json;
				} );
			}

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
	}


	var options = [];
	options.push( { value: '20', label: '20' } );
	options.push( { value: '50', label: '50' } );
	options.push( { value: '100', label: '100' } );
	options.push( { value: '200', label: '200' } );
	$scope.limits = options;


});

NGApp.controller('OrderCtrl', function ($scope, $rootScope, $routeParams, $interval, OrderService, MapService, SocketService) {

	SocketService.listen('order.' + $routeParams.id, $scope)
		.on('update', function(d) {
			$scope.order = d;
		});

	$scope.loading = true;

	var draw = function() {
		if (!$scope.map || !$scope.order) {
			return;
		}

		MapService.trackOrder({
			map: $scope.map,
			order: $scope.order,
			restaurant: $scope.order.restaurant,
			driver: $scope.order.driver,
			id: 'order-driver-location',
			scope: $scope
		});
	};

	var update = function() {
		var loading = true;
		OrderService.get($routeParams.id, function(d) {
			$rootScope.title = 'Order #' + d.id_order;
			$scope.order = d;
			$scope.loading = false;
			draw();
		});
	};

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	var cleanup = $rootScope.$on('order-route-' + $routeParams.id, function(event, args) {
		$scope.$apply(function() {
			$scope.eta = args;
		});
		console.debug('Got route update: ', args);
	});

	$scope.updater = $interval(update, 30000); // update every 30 seconds
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
		cleanup();
	});

	$scope.isRefunding = false;

	$scope.refund = function(){

		if( $scope.isRefunding ){
			return;
		}

		var question = 'Are you sure you want to refund this order?';
		if( parseFloat( $scope.order.credit ) > 0 ){
			question += "\n";
			question += 'A gift card was used at this order the refund value will be $' + $scope.order.charged + ' + $' + $scope.order.credit + ' as gift card.' ;
		}

		if ( confirm( question ) ){

			$scope.isRefunding = true;
			OrderService.refund( $scope.order.id_order, function( result ){

				$scope.isRefunding = false;

				if( result.success ){
					$rootScope.reload();
				} else {
					console.log( result.responseText );
					var er = result.errors ? "<br>" + result.errors : 'See the console.log!';
					App.alert('Refunding fail! ' + er);
				}
			} );
		}
	}

	$scope.do_not_pay_driver = function(){
		OrderService.do_not_pay_driver( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_pay_driver = ( $scope.order.do_not_pay_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_pay_restaurant = function(){
		OrderService.do_not_pay_restaurant( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_pay_restaurant = ( $scope.order.do_not_pay_restaurant ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_reimburse_driver = function(){
		OrderService.do_not_reimburse_driver( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_reimburse_driver = ( $scope.order.do_not_reimburse_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}


	$scope.resend_notification_drivers = function(){
		$scope.isDriverNotifying = true;
		OrderService.resend_notification_drivers( $scope.order, function(result) {
			$scope.isDriverNotifying = false;
		});
	}

	$scope.resend_notification = function(){
		$scope.isRestaurantNotifying = true;
		OrderService.resend_notification( $scope.order, function(result) {
			$scope.isRestaurantNotifying = false;
		});
	}

	update();
});
