NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/calls', {
			action: 'calls',
			controller: 'CallsCtrl',
			templateUrl: 'assets/view/calls.html',
			reloadOnSearch: false

		}).when('/call/:id', {
			action: 'call',
			controller: 'CallCtrl',
			templateUrl: 'assets/view/calls-call.html'
		});
}]);

NGApp.controller('CallsCtrl', function ($rootScope, $scope, CallService, ViewListService) {
	$rootScope.title = 'Calls';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			status: 'all',
			fullcount: false
		},
		update: function() {
			CallService.list($scope.query, function(d) {
				$scope.calls = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('CallCtrl', function ($scope, $routeParams, CallService, $rootScope) {
	$scope.loading = true;

	CallService.get($routeParams.id, function(d) {
		$rootScope.title = d.phone + ' | Call';
		$scope.call = d;
		$scope.loading = false;
	});

});
