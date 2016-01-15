NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/blockeds', {
			action: 'tools',
			controller: 'BlockedsCtrl',
			templateUrl: '/assets/view/blockeds.html',
			reloadOnSearch: false

		})
}]);
NGApp.controller('BlockedsCtrl', function ($rootScope, $scope, BlockedService, ViewListService) {

	$rootScope.title = 'Blockeds';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			fullcount: false
		},
		update: function() {
			BlockedService.list($scope.query, function(d) {
				$scope.blockeds = d.results;
				$scope.complete(d);
			});
		}
	});
})
