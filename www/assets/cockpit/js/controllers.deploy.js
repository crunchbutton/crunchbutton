

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

NGApp.controller('DeployCtrl', function ($scope, $routeParams, DeployService, MainNavigationService, $interval, SocketService) {

	$scope.loadingServers = true;
	$scope.loadingVersions = true;

	var updateServers = function() {
		DeployService.server.list({}, function(d) {
			$scope.servers = d;
			$scope.loadingServers = false;
		});
	};
	var updateVersions = function() {
		DeployService.version.list({}, function(d) {
			$scope.versions = d;
			$scope.loadingVersions = false;
		});
	};

	SocketService.listen('deploy.versions', $scope)
		.on('update', function(d) {
			for (var x in $scope.versions) {
				if ($scope.versions[x].id_deploy_version == d.id_deploy_version) {
					$scope.versions[x] = d;
				}
			}

		}).on('create', function(d) {
			updateVersions();
		});

	SocketService.listen('deploy.servers', $scope)
		.on('update', function(d) {
			for (var x in $scope.servers) {
				if ($scope.servers[x].id_deploy_server == d.id_deploy_server) {
					$scope.servers[x] = d;
				}
			}

		}).on('create', function(d) {
			updateServers();
		});

	updateServers();
	updateVersions();

	$scope.cancel = function(id) {
		DeployService.version.cancel(id, update);
	};
});

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams, SocketService, DeployService, $interval, MainNavigationService, DateTimeService) {

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

	var updateVersions = function() {
		DeployService.server.versions($routeParams.id, function(d) {
			$scope.versions = d;
		});
	};

	updateVersions();

	DeployService.server.get($routeParams.id, function(d) {
		$scope.server = d;


		SocketService.listen('travisci.builds', $scope)
			.on('update', function(d) {
				$scope.updateCommits();
			});

		SocketService.listen('deploy.server.' + d.id_deploy_server + '.versions', $scope)
			.on('update', function(d) {
				for (var x in $scope.versions) {
					if ($scope.versions[x].id_deploy_version == d.id_deploy_version) {
						$scope.versions[x] = d;
					}
				}

			}).on('create', function(d) {
				updateVersions();
			});

		SocketService.listen('deploy.server.' + d.id_deploy_server, $scope).on('update', function(d) {
			$scope.server = d;
		});
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

	$scope.cancel = function(id) {
		DeployService.version.cancel(id, update);
	};
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
