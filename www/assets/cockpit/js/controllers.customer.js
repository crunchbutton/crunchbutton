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
		.when('/customer/edit/:id', {
			action: 'customer',
			controller: 'CustomerEditCtrl',
			templateUrl: 'assets/view/customers-form.html'
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


NGApp.controller('CustomerEditCtrl', function ($scope, $routeParams, $rootScope, CustomerService ) {

	$scope.loading = true;

	var load = function(){
		CustomerService.get($routeParams.id, function(d) {
			$rootScope.title = d.name + ' | Edit';
			$scope.customer = d;
			$scope.loading = false;
			CustomerService.active_orders( $scope.customer.id_user, function( json ){
				$scope.orders = json;
			} );
		});
	}

	$scope.$watch( 'customer.name', function( newValue, oldValue, scope ) {
		if( newValue != oldValue ){
			$scope.customer.notify_driver = true;
		}
	});

	$scope.$watch( 'customer.address', function( newValue, oldValue, scope ) {
		if( newValue != oldValue ){
			$scope.customer.notify_driver = true;
		}
	});

	$scope.$watch( 'customer.phone', function( newValue, oldValue, scope ) {
		if( newValue != oldValue ){
			$scope.customer.notify_driver = true;
		}
	});

	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		$scope.isSaving = true;
		CustomerService.post( $scope.customer, function( json ){
			if( json.success ){
				App.alert( 'Customer saved!' );
				load();
				$scope.isSaving = false;
			} else {
				App.alert( 'Customer not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}

	load();

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
