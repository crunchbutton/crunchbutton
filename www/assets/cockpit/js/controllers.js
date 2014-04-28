NGApp.controller('DefaultCtrl', function ($scope, $http, $location, MainNavigationService, AccountService) {
	if (AccountService.user.id_admin) {
		MainNavigationService.link('/drivers/orders');
	}
});

NGApp.controller('MainHeaderCtrl', function ( $scope, $rootScope) {

});

NGApp.controller('SideMenuCtrl', function ($scope) {
	$scope.setupPermissions = function() {
		
	}
});

NGApp.controller('LoginCtrl', function($scope, AccountService) {
	$scope.login = function() {
		AccountService.login($scope.username, $scope.password, function(status) {
//			$scope.$apply(function() {
				$scope.error = !status;
//			})
		});
	}
});

NGApp.controller('DriversOrderCtrl', function ($http, $scope, $rootScope) {

});


NGApp.controller('DriversOrdersCtrl', function ($http, $scope, $rootScope, DriverOrdersService) {

	DriverOrdersService.loadOrders();

	$scope.accept = function(id_order) {
		$rootScope.makebusy();
		$http.post(App.service + 'order/' + id_order + '/delivery-accept').success(function(json) { 
			if (json.status) {
				$scope.loadOrders();
			} else {
	 			var name = json['delivery-status'].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
	 			App.alert('Ops, error!\n It seems this order was already accepted' + name + '!'  );
	 			$scope.loadOrders();
	 		}
	 	}); 
	};

	$scope.pickedup = function(id_order) {
		$rootScope.makebusy();
			$http.post(App.service + 'order/' + id_order + '/delivery-pickedup').success(function() {
			$scope.loadOrders();
		});
	};
	
	$scope.delivered = function(id_order) {
		$rootScope.makebusy();
		$http.post(App.service + 'order/' + id_order + '/delivery-delivered').success(function() {
			$scope.loadOrders();
		});
	};
});

NGApp.controller('DriversShiftsCtrl', function ($http, $scope, $rootScope) {

});