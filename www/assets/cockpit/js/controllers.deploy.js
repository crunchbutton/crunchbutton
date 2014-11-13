

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
	DeployServices.server.get(1, function(d) {
		console.log(d);
	});
	DeployServices.version.list({}, function(d) {
		$scope.versions = d;
	});
	DeployServices.version.get(1, function(d) {
		console.log(d);
	});
});

NGApp.controller('DeployServerCtrl', function ($scope, $routeParams) {

});

NGApp.controller('DeployVersionCtrl', function ($scope, $routeParams) {

});
