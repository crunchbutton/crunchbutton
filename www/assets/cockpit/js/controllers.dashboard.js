NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/dashboard', {
			action: 'dashboard',
			controller: 'DashboardCtrl',
			templateUrl: 'assets/view/dashboard.html'
		});
}]);

NGApp.controller('DashboardCtrl', function ($rootScope, $scope, DashboardService) {
	$scope.dashboards = null;
	$scope.loading = true;

	DashboardService.get(null, function(dashboards) {
		$scope.dashboards = dashboards;
		$scope.loading = false;
	});
});