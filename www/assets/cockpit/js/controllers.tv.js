NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tv', {
			action: 'tv',
			controller: 'TvCtrl',
			templateUrl: 'assets/view/tv.html'

		});
}]);

NGApp.controller('TvCtrl', function ($rootScope, $scope, TvService) {
	$rootScope.title = 'tv';
	$scope.load = function(){
		TvService.get(function(data){
		$scope.Test = data.test;
	});
	};
	$scope.load();

});


