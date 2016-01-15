NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tools', {
			action: 'tools',
			controller: 'ToolsCtrl',
			templateUrl: '/assets/view/tools.html'
		})
		.when('/tools/eta', {
			action: 'tools-eta',
			controller: 'ToolsETACtrl',
			templateUrl: '/assets/view/tools-eta.html'
		});
}]);

NGApp.controller('ToolsCtrl', function () {});

NGApp.controller('ToolsETACtrl', function ( $scope, RestaurantService ) {
	$scope.loading = true;
	RestaurantService.eta( function( json ){
		$scope.restaurants = json;
		$scope.loading = false;
	} );
});
