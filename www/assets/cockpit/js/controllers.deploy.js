

NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/deploy', {
			action: 'deploy',
			controller: 'DeployCtrl',
			templateUrl: 'assets/view/deploy-home.html'

		}).when('/deploy/server/:id', {
			action: 'deploy-server',
			controller: 'DeployServerCtrl',
			templateUrl: 'assets/view/deploy-server.html'

		}).when('/deploy/version/:id', {
			action: 'deploy-version',
			controller: 'DeployVersionCtrl',
			templateUrl: 'assets/view/deploy-version.html'
		});
		
}]);

NGApp.controller('DeployCtrl', function ($scope, $routeParams, DeployService, MainNavigationService, $interval) {
	var update = function() {
		DeployService.server.list({}, function(d) {
			$scope.servers = d;
		});
		DeployService.version.list({}, function(d) {
			$scope.versions = d;
		});
	};
	
	update();
	
	$scope.cancel = function(id) {
		DeployService.version.cancel(id, update);
	};

	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
	});
});

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams, DeployService, $interval, MainNavigationService, DateTimeService) {

	$scope.deploy = {
		date: DateTimeService.local(new Date).format('YYYY-MM-DD HH:mm:ss Z'),
		version: 'master'
	};
	
	$scope.updateCommits = function() {
		$scope.commitLoad = true;
		DeployService.server.commits($routeParams.id, function(d) {
			$scope.commits = d;
			$scope.commitLoad = false;
		});
	};
	
	$scope.updateCommits();
	
	$scope.server = {
		name: $routeParams.id
	};

	var update = function() {
		DeployService.server.get($routeParams.id, function(d) {
			$scope.server = d;
		});
		DeployService.server.versions($routeParams.id, function(d) {
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

			DeployService.version.post(version, function(d) {
				$scope.deploying = false;
				MainNavigationService.link('/deploy/version/' + d.id_deploy_version);
			});
		};
	};
	
	update();
	
	$scope.cancel = function(id) {
		DeployService.version.cancel(id, update);
	};
	
	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
	});
});

NGApp.controller('DeployVersionCtrl', function ($scope, $routeParams, DeployService, $interval, SocketService) {
	var listener = SocketService.listen('deploy.version.' + $routeParams.id, $scope).on('update', function(version) {
		$scope.version = version;
	});

	DeployService.version.get($routeParams.id, function(d) {
		$scope.version = d;
	});
	
	$scope.cancel = function(id) {
		DeployService.version.cancel(id, update);
	};

});
