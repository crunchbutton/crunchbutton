

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
	
	$interval(update, 5000);
});

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams, DeployServices, $interval) {
	$scope.deploy = {
		date: moment().format('YYYY-MM-DD HH:mm:ss'),
		version: 'master',
		id_deploy_server: $routeParams.id
	};

	var update = function() {
		DeployServices.server.get($routeParams.id, function(d) {
			$scope.server = d;
		});
		DeployServices.server.versions($routeParams.id, function(d) {
			$scope.versions = d;
		});
		DeployServices.git.list({}, function(d) {
			$scope.gitversions = d;
		});
		
		$scope.saveDeploy = function() {
			DeployServices.version.post($scope.deploy, function(d) {
				MainNavigationService.link('/deploy');
			});
		};
	};
	
	update();
	
	$interval(update, 5000);
});

NGApp.controller('DeployVersionCtrl', function ($scope, $routeParams, DeployServices, $interval) {
	var update = function() {
		DeployServices.version.get($routeParams.id, function(d) {
			$scope.version = d;
		});
	};
	
	update();
	
	$interval(update, 5000);
});
