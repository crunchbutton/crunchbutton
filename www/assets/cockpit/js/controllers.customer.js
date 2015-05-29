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

NGApp.controller('CustomersCtrl', function ($rootScope, $scope, CustomerService, ViewListService) {
	$rootScope.title = 'Customers';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			sort: '',
			fullcount: false
		},
		update: function() {
			CustomerService.list($scope.query, function(d) {
				$scope.customers = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('CustomerCtrl', function ($scope, $routeParams, $interval, CustomerService, OrderService, CreditService, $rootScope) {

	$scope.loading = true;

	CustomerService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Customers';
		$scope.customer = d;

		if ($scope.customer.address.indexOf(',') > -1) {
			var address = $scope.customer.address.split(',');
			$scope.customer.address = address.shift() + "\n" + address.join(',');
		}

		$scope.loading = false;
	});

	var credits = function(){
		CreditService.history( $routeParams.id, function( d ){
			$scope.credits = d;
		} );
	}

	OrderService.list({user: $routeParams.id}, function(d) {
		$scope.orders = d.results;
		$scope.count = d.count;
		$scope.pages = d.pages;
		$scope.loading = false;
	});

	credits();

	$rootScope.$on( 'creditAdded', function(e, data) {
		credits();
	} );

});
