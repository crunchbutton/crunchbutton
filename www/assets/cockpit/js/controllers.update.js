NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/update', {
			action: 'drivers-welcome-update',
			controller: 'UpdateCtrl',
			templateUrl: 'assets/view/general-update.html'
		});

}]);


NGApp.controller('UpdateCtrl', function( $scope) {

	$scope.version = App.version;
	//$scope.minVersion = App.config.site['cockpit-min-app-version'];
	$scope.ios = App.iOS();

		if (App.iOS()) {
			$scope.url = 'https://itunes.apple.com/us/app/crunchbutton-cockpit/id926523210';
		} else {
			$scope.url = 'https://play.google.com/store/apps/details?id=com.crunchbutton.cockpit';
		}

	$scope.update = function() {

		window.open($scope.url,'_system');
	};
});