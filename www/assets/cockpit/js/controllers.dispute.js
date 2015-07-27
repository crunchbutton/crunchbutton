NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/dispute/:id', {
			action: 'dispute',
			controller: 'DisputeCtrl',
			templateUrl: 'assets/view/disputes-dispute.html'
		})
}]);


NGApp.controller('DisputeCtrl', function( $scope, $rootScope, $routeParams, DisputeService ) {

	$scope.loading = true;

	var load = function(){
		DisputeService.get( $routeParams.id, function( json ){
			$scope.dispute = json;
			$scope.loading = false;
		} );
	}
	load();
} );
