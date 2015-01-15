NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tools', {
			action: 'tools',
			controller: 'ToolsCtrl',
			templateUrl: 'assets/view/tools.html'
		});
}]);

NGApp.controller('ToolsCtrl', function () {});