NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/communities', {
			action: 'communities',
			controller: 'CommunitiesCtrl',
			templateUrl: 'assets/view/communities.html',
			reloadOnSearch: false
		})
		.when('/community/:id', {
			action: 'community',
			controller: 'CommunityCtrl',
			templateUrl: 'assets/view/communities-community.html'
		});

}]);

NGApp.controller('CommunitiesCtrl', function ($rootScope, $scope, CommunityService, ViewListService) {
	$rootScope.title = 'Communities';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: ''
		},
		update: function() {
			CommunityService.list($scope.query, function(d) {
				$scope.communities = d.results;
				$scope.complete(d);
			});
		}
	});
});


NGApp.controller('CommunityCtrl', function ($scope, $routeParams, $rootScope, MapService, CommunityService, RestaurantService, OrderService, StaffService) {
	$scope.loading = true;
	$scope.loadingOrders = true;
	$scope.loadingRestaurants = true;
	$scope.loadingStaff = true;
	
	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		update();
	});
	
	var update = function() {
		if (!$scope.map || !$scope.community) {
			return;
		}

		MapService.trackCommunity({
			map: $scope.map,
			community: $scope.community,
			scope: $scope,
			id: 'community-location'
		});
	};

	CommunityService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Community';
		$scope.community = d;
		$scope.loading = false;
		
		update();

		OrderService.list({community: d.id_community, limit: 5}, function(d) {
			$scope.orders = d.results;
			$scope.loadingOrders = false;
		});
		
		RestaurantService.list({community: d.id_community, limit: 50}, function(d) {
			$scope.restaurants = d.results;
			$scope.loadingRestaurants = false;
		});
		
		StaffService.list({community: d.id_community, limit: 50, type: 'driver'}, function(d) {
			$scope.staff = d.results;
			$scope.loadingStaff = false;
		});
	});
});
