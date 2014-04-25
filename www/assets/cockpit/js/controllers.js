NGApp.controller('DefaultCtrl', function ($scope, $http, $location) {

});

NGApp.controller( 'MainHeaderCtrl', function ( $scope, $rootScope) {

});

NGApp.controller( 'SideMenuCtrl', function () {

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