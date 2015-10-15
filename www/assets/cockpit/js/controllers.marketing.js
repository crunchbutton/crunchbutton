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
		})
		.when('/marketing/brand-reps', {
			action: 'brand-reps',
			controller: 'MarketingBrandRepsCtrl',
			templateUrl: 'assets/view/marketing-brand-representative.html',
			reloadOnSearch: false
		})
		.when('/marketing/drivers', {
			action: 'drivers',
			controller: 'MarketingDriversCtrl',
			templateUrl: 'assets/view/marketing-drivers.html',
			reloadOnSearch: false
		})
}]);


NGApp.controller('MarketingDriversCtrl', function ($scope, StaffService, ViewListService ) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			community: '',
			drivers: true,
			fullcount: true
		},
		update: function() {
			StaffService.list($scope.query, function(d) {
				$scope.community = d.community;
				$scope.staff = d.results;
				$scope.complete(d);
			});
		}
	});

});

NGApp.controller('MarketingBrandRepsCtrl', function ($scope, StaffService, ViewListService ) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			community: '',
			brandreps: true,
			fullcount: true
		},
		update: function() {
			StaffService.list($scope.query, function(d) {
				$scope.community = d.community;
				$scope.staff = d.results;
				$scope.complete(d);
			});
		}
	});

});

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
