NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/marketing', {
			action: 'redirecting',
			redirectTo: '/marketing/outgoing'
		})
		.when('/marketing/outgoing', {
			action: 'tools',
			controller: 'MarketingOutgoingLogCtrl',
			templateUrl: 'assets/view/marketing-outgoing.html'
		});
}]);

NGApp.controller('MarketingOutgoingLogCtrl', function ($scope, MarketingService, ViewListService) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
		},
		update: function() {
			MarketingService.outgoing($scope.query, function(d) {
				$scope.logs = d.results;
				$scope.complete(d);
			});
		}
	});
});
