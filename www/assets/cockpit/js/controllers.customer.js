NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/customers', {
			action: 'customers',
			controller: 'CustomersCtrl',
			templateUrl: 'assets/view/customers.html',
			reloadOnSearch: false

		})
		.when('/customer/credit/:id', {
			action: 'customer',
			controller: 'CustomerCreditCtrl',
			templateUrl: 'assets/view/customers-credit.html'
		})
		.when('/customer/:id', {
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

NGApp.controller('CustomerCreditCtrl', function ($rootScope, $routeParams, $scope, CreditService, CustomerService, ViewListService) {

	CustomerService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Credits';
		$scope.customer = d;
	});

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {},
		update: function() {
			CreditService.history( $routeParams.id, function(d) {
				$scope.credits = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('CustomerCtrl', function ($scope, $routeParams, $interval, CustomerService, OrderService, CreditService, BlockedService, $rootScope) {

	$scope.loading = true;

	CustomerService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Customers';
		$scope.customer = d;

		if ($scope.customer && $scope.customer.address && $scope.customer.address.indexOf(',') > -1) {
			var address = $scope.customer.address.split(',');
			$scope.customer.address = address.shift() + "\n" + address.join(',');
		}

		$scope.loading = false;
	});

	$scope.blockUser = function( id_user ){
		BlockedService.user( id_user, function( status ){
			console.log(status);
		} );
	}

	$scope.blockPhone = function( id_phone ){
		BlockedService.phone( id_phone, function( status ){
			console.log(status);
		} );
	}

	var credits = function(){
		CreditService.status( $routeParams.id, function( d ){
			$scope.credits = d;
			console.log('$scope.credits',$scope.credits);
		} );
	}

	$scope.orders = function(){
		OrderService.list({user: $routeParams.id}, function(d) {
			$scope.orders = d.results;
			$scope.count = d.count;
			$scope.pages = d.pages;
			$scope.loading = false;
		});
	}

	credits();

	$rootScope.$on( 'creditAdded', function(e, data) {
		credits();
	} );

});
