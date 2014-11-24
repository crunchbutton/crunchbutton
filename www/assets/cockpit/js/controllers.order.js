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

NGApp.controller('OrdersCtrl', function ($scope, OrderService, ViewListService) {
	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			restaurant: '',
			community: '',
			date: '',
		},
		update: function() {
			OrderService.list($scope.query, function(d) {
				$scope.orders = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('OrderCtrl', function ($scope, $rootScope, $routeParams, $interval, OrderService, MapService) {
	
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
