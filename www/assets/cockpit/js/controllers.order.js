NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/orders', {
			action: 'orders',
			controller: 'OrdersCtrl',
			templateUrl: 'assets/view/orders-list.html',
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
		OrderService.get($routeParams.id, function(d) {
			$rootScope.title = 'Order #' + d.id_order;
			$scope.order = d;
			$scope.ready = true;
			draw();
		});
	};
	
	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
	});
	
	$scope.$on('order-route-' + $routeParams.id, function(event, args) {
		var eta = {
			customer: {},
			restaurant: {},
			total: {}
		};
		if (args.length == 2) {
			eta.restaurant.distance = args[0].distance.value * 0.000621371;
			eta.restaurant.duration = args[0].duration.value/60;
			
			eta.customer.distance = args[1].distance.value;
			eta.customer.duration = args[1].duration.value/60;
			
			eta.total.duration = eta.restaurant.duration + eta.customer.duration;
			eta.total.distance = eta.restaurant.distance + eta.customer.distance;

		} else {
			eta.customer.distance = args[0].distance.value * 0.000621371;
			eta.customer.duration = args[0].duration.value/60;
			
			eta.total.duration = eta.customer.duration;
			eta.total.distance = eta.customer.distance;
		}
		

		$scope.$apply(function() {
			$scope.eta = eta;
		});

		console.debug('Got route update: ', eta);
	});
	
	update();
});
