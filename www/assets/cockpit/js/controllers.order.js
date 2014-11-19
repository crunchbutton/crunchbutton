

NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/orders', {
			action: 'orders',
			controller: 'OrdersCtrl',
			templateUrl: 'assets/view/orders-list.html',
			reloadOnSearch: false

		}).when('/order/:id', {
			action: 'order',
			controller: 'OrderCtrl',
			templateUrl: 'assets/view/orders-order.html'
		});
}]);

NGApp.controller('OrdersCtrl', function ($scope, $routeParams, $location, OrderService) {
	
	var query = $location.search();
	$scope.query = {
		search: query.search,
		restaurant: query.restaurant,
		community: query.community,
		limit: query.limit || 25,
		date: query.date
	};

	var update = function() {
		OrderService.list($scope.query, function(d) {
			$scope.orders = d;
		});
	};
	
	var watch = function() {
		$location.search($scope.query);
		update();
	};
	
	$scope.$watch('query.search', watch);
	$scope.$watch('query.limit', watch);
});

NGApp.controller('OrderCtrl', function ($scope, $routeParams, DeployServices, $interval, MainNavigationService, DateTimeService) {

	$scope.deploy = {
		date: DateTimeService.local(new Date).format('YYYY-MM-DD HH:mm:ss Z'),
		version: 'master'
	};
	
	$scope.updateCommits = function() {
		$scope.commitLoad = true;
		DeployServices.server.commits($routeParams.id, function(d) {
			$scope.commits = d;
			$scope.commitLoad = false;
		});
	};
	
	$scope.updateCommits();
	
	$scope.server = {
		name: $routeParams.id
	};

	var update = function() {
		DeployServices.server.get($routeParams.id, function(d) {
			$scope.server = d;
		});
		DeployServices.server.versions($routeParams.id, function(d) {
			$scope.versions = d;
		});	
		
		$scope.saveDeploy = function() {
			if ($scope.deploying) {
				return;
			}
			$scope.deploying = true;

			var version = {
				date: DateTimeService.server($scope.deploy.date).format('YYYY-MM-DD HH:mm:ss Z'),
				id_deploy_server: $routeParams.id,
				version: $scope.deploy.version
			};

			DeployServices.version.post(version, function(d) {
				$scope.deploying = false;
				MainNavigationService.link('/deploy/version/' + d.id_deploy_version);
			});
		};
	};
	
	update();
	
	$scope.cancel = function(id) {
		DeployServices.version.cancel(id, update);
	};
	
	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
	});
});
