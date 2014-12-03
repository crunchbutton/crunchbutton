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

NGApp.controller('OrdersCtrl', function ($scope, $location, OrderService, ViewListService, SocketService, MapService) {
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
			view: 'list'
		},
		update: function() {
			OrderService.list($scope.query, function(d) {
				$scope.orders = d.results;
				$scope.complete(d);
				draw();
			});
		}
	});
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

	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
		cleanup();
	});
	
	update();
});
