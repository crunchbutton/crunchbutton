NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/customers', {
			action: 'customers',
			controller: 'CustomersCtrl',
			templateUrl: 'assets/view/customers.html',
			reloadOnSearch: false

		}).when('/customer/:id', {
			action: 'customer',
			controller: 'CustomerCtrl',
			templateUrl: 'assets/view/customers-customer.html'
		});
}]);

NGApp.controller('CustomersCtrl', function ($scope, $routeParams, $location, CustomerService) {
	
	var query = $location.search();
	$scope.query = {
		search: query.search,
		limit: query.limit || 25,
		page: query.page || 1
	};
	
	$scope.query.page = parseInt($scope.query.page);

	var update = function() {
		$scope.loading = true;
		CustomerService.list($scope.query, function(d) {
			$scope.customers = d.results;
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

NGApp.controller('CustomerCtrl', function ($scope, $routeParams, $interval, CustomerService, OrderService) {
	$scope.loading = true;

	CustomerService.get($routeParams.id, function(d) {
		$scope.customer = d;
		$scope.loading = false;
	});
	
	OrderService.list({user: $routeParams.id}, function(d) {
		$scope.orders = d.results;
		$scope.count = d.count;
		$scope.pages = d.pages;
		$scope.loading = false;
	});
});
