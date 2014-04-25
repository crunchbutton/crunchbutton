NGApp.controller('DefaultCtrl', function ($scope, $http, $location, MainNavigationService) {
	MainNavigationService.link('/drivers/orders');
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


NGApp.controller('DriversOrdersCtrl', function ($scope, $rootScope) {

});

NGApp.controller('DriversShiftsCtrl', function ($scope, $rootScope) {

});