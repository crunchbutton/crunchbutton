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
		})
		.when('/shifts/schedule', {
			action: 'shift',
			controller: 'ShiftScheduleCtrl',
			templateUrl: 'assets/view/shift-schedule.html',
			reloadOnSearch: false
		});

}]);

NGApp.controller( 'ShiftCtrl', function ( $scope ) {} );


NGApp.controller('ShiftScheduleCtrl', function ( $scope, ShiftScheduleService, CommunityService ) {

	$scope.options = { communities: [ 92 ] };

	$scope.selectNoneCommunity = function(){
		$scope.options.communities = [];
	}

	$scope.loadShifts = function(){
		ShiftScheduleService.loadShifts( $scope.options, function( json ){
			if( json.communities ){
				$scope.shifts = { communities: json.communities };
			}
		} );
	}

	$scope.selectAllCommunities = function(){
		$scope.selectNoneCommunity();
		for( x in $scope.communities ){
			if( $scope.communities[ x ].id_community ){
				$scope.options.communities.push( $scope.communities[ x ].id_community );
			}
		}
	}
	var start = function(){
		ShiftScheduleService.weekStart( function( json ){
			if( json.start ){
				$scope.options.start = new Date( json.start );
			} else {
				$scope.options.start = new Date();
			}
		} );

		if( !$scope.communities ){
			CommunityService.listSimple( function( json ){
				$scope.communities = json;
			} );
		}
		$scope.loadShifts();
	}

	start();

});

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