NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/reports', {
			action: 'tools',
			controller: 'ReportsCtrl',
			templateUrl: '/assets/view/report.html',
			reloadOnSearch: false
		})
		.when('/report/first-time-users-gift-card', {
			action: 'tools',
			controller: 'ReportFirstTimeUserGiftCodesUsedPerSchoolPerDayCtrl',
			templateUrl: '/assets/view/report-first-time-users-gift-card.html'
		})
}]);

NGApp.controller( 'ReportsCtrl', function(){} );

NGApp.controller( 'ReportFirstTimeUserGiftCodesUsedPerSchoolPerDayCtrl', function ( $scope, $filter, ReportService ) {

	$scope.range = {};

	var start = new Date();
	start.setDate( start.getDate() - 14 );
	$scope.range.start = start;

	var end = new Date();
	end.setDate( end.getDate() - 1 );
	$scope.range.end = end;

	$scope.result = null;

	$scope.report = function(){

		$scope.result = false;

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy') };

		ReportService.first_time_user_gift_codes_used_per_school_per_day( params, function( json ){
			$scope.isProcessing = false;
			$scope.result = json;
		} );
	}

} );