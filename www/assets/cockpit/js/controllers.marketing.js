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
}]);

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

	$scope.show_more_options = false;

	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options) {

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
	}

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
