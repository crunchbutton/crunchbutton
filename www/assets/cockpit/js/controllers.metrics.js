NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/metrics', {
			action: 'metrics',
			controller: 'MetricsCtrl',
			templateUrl: 'assets/view/metrics.html',
			reloadOnSearch: false

		}).when('/metrics/:id', {
			action: 'metrics',
			controller: 'MetricsViewCtrl',
			templateUrl: 'assets/view/metrics-view.html'
		});
}]);

NGApp.controller('MetricsCtrl', function ($rootScope, $scope, $timeout, MetricsService) {
	
	var initVars = function() {
		$scope.data = [];
		$scope.labels = [];
	}
	
	// doesnt seem to work. not sure why
	$scope.colours = {
		fillColor: "rgba(70,191,189,0.2)",
		strokeColor: "rgba(70,191,189,1)",
		pointColor: "rgba(70,191,189,1)",
		pointStrokeColor: "#fff",
		pointHighlightFill: "#fff",
		pointHighlightStroke: "rgba(70,191,189,0.8)"
	};
	
	initVars();

	MetricsService.get({id_metrics: 'example', 'days': 4000}, function(response) {
		initVars();
		var keys = [];
		for (var i in response.data) {
			for (var x in response.data[i]) {
				keys.push(x);
			}
			break;
		}
		console.log(keys);
		for (var i in response.data) {
			$scope.data.push(response.data[i][keys[0]]);
			$scope.labels.push(response.data[i][keys[1]]);
		}
		$scope.data = [$scope.data];
		console.log($scope.data);
	});

});

NGApp.controller('MetricsViewCtrl', function () {
	// get a specific metric view
});
