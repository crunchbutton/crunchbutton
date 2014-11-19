

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

NGApp.controller('DeployCtrl', function ($scope, $routeParams, DeployServices, MainNavigationService, $interval) {
	var update = function() {
		DeployServices.server.list({}, function(d) {
			$scope.servers = d;
		});
		DeployServices.version.list({}, function(d) {
			$scope.versions = d;
		});
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

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams, DeployServices, $interval, MainNavigationService, DateTimeService) {

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
			var version = {
				date: DateTimeService.server($scope.deploy.date).format('YYYY-MM-DD HH:mm:ss Z'),
				id_deploy_server: $routeParams.id,
				version: $scope.deploy.version
			};

			DeployServices.version.post(version, function(d) {
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

NGApp.controller('DeployVersionCtrl', function ($scope, $routeParams, DeployServices, $interval) {
	var update = function() {
		DeployServices.version.get($routeParams.id, function(d) {
			$scope.version = d;
		});
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
