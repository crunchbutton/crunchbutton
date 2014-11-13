

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

NGApp.controller('DeployCtrl', function ($scope, $routeParams, DeployServices) {
	DeployServices.server.list({}, function(d) {
		$scope.servers = d;
	});
	DeployServices.version.list({}, function(d) {
		$scope.versions = d;
	});
});

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams, DeployServices) {
	DeployServices.server.get($routeParams.id, function(d) {
		$scope.server = d;
	});
	DeployServices.server.versions($routeParams.id, function(d) {
		console.log(d);
		$scope.versions = d;
	});
});

NGApp.controller('DeployVersionCtrl', function ($scope, $routeParams, DeployServices) {
	DeployServices.version.get($routeParams.id, function(d) {
		$scope.version = d;
	});
});
