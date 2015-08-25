NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/shifts/checkin', {
			action: 'shift',
			controller: 'ShiftChekinCtrl',
			templateUrl: 'assets/view/shift-checkin.html',
			reloadOnSearch: false
		})
		.when('/shifts/', {
			action: 'shift',
			controller: 'ShiftCtrl',
			templateUrl: 'assets/view/shift.html',
			reloadOnSearch: false
		});

}]);

NGApp.controller( 'ShiftCtrl', function ( $scope ) {} );

NGApp.controller('ShiftChekinCtrl', function ( $scope, ShiftService, ViewListService, DriverShiftsService ) {
	angular.extend( $scope, ViewListService );
	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			status: 'all',
			fullcount: true
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		ShiftService.checking.get( $scope.query, function(d) {
				$scope.shifts = d.results;
				$scope.complete(d);
			});
	}

	$scope.shift_checkin = function( id_admin_shift_assign ){
		var success = function(){
			DriverShiftsService.shift_checkin( id_admin_shift_assign, function( json ){
				if( json.success ){
					update();
				} else {
					App.alert( 'Error!' );
				}
			} );
		}
		var fail = function(){}
		App.confirm( 'Confirm checkin?' , 'Checking', success, fail, null, true);
	}
});