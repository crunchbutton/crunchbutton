

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

NGApp.controller('OrdersCtrl', function ($scope, $routeParams, $location, OrderService) {
	
	var query = $location.search();
	$scope.query = {
		search: query.search,
		restaurant: query.restaurant,
		community: query.community,
		limit: query.limit || 25,
		date: query.date,
		page: query.page || 1
	};
	
	$scope.query.page = parseInt($scope.query.page);

	var update = function() {
		$scope.loading = true;
		OrderService.list($scope.query, function(d) {
			$scope.orders = d.results;
			$scope.count = d.count;
			$scope.pages = d.pages;
			$scope.loading = false;
		});
	};
	
	var watch = function() {
		$location.search($scope.query);
		update();
	};
	
	// @todo: this breaks linking to pages
	var inputWatch = function() {
		if ($scope.query.page != 1) {
			$scope.query.page = 1;
		} else {
			watch();
		}
	};
	
	$scope.$watch('query.search', inputWatch);
	$scope.$watch('query.limit', inputWatch);
	$scope.$watch('query.page', watch);
	
	$scope.setPage = function(page) {
		$scope.query.page = page;
		App.scrollTop(0);
	};
});

NGApp.controller('OrderCtrl', function ($scope, $routeParams, $interval, OrderService) {
	OrderService.get($routeParams.id, function(d) {
		$scope.order = d;
		$scope.ready = true;
	});
});
