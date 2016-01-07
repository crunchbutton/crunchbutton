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

NGApp.controller('ShiftScheduleCtrl', function ( $scope, $rootScope, ShiftScheduleService, CommunityService ) {

	// @remove -- remove it before commit
	$scope.options = { communities: [ 92 ] };

	$scope.selectNoneCommunity = function(){
		$scope.options.communities = [];
	}

	$scope.loadShifts = function(){
		ShiftScheduleService.loadShifts( $scope.options, function( json ){
			if( json.communities ){
				$scope.shifts = { communities: json.communities };
				$scope.days = json.days;
			}
		} );
	}

	$scope.showPSTtz = false;

	$scope.toggleShowHideShift = function( shift ){
		shift.hidden = !shift.hidden;
		ShiftScheduleService.showHideShift( { id_community_shift: shift.id_community_shift }, function( json ){
			if( json.error ){
				App.alert( 'Ops, error!' );
			}
		} );
	}

	$scope.toggleTz = function(){
		$scope.showPSTtz = !$scope.showPSTtz;
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
			$scope.loadShifts();
		} );

		if( !$scope.communities ){
			CommunityService.listSimple( function( json ){
				$scope.communities = json;
			} );
		}
	}

	$scope.addShift = function( id_community, name, date ){
		var params = { community: { id_community: id_community, name: name }, date: date }
		$rootScope.$broadcast( 'openAddShiftContainer', params );
	}

	$scope.scheduleShift = function( id_community_shift ){
		var params = { id_community_shift: id_community_shift }
		$rootScope.$broadcast( 'openScheduleShiftContainer', params );
	}

	$scope.editShift = function( id_community_shift ){
		var params = { id_community_shift: id_community_shift }
		$rootScope.$broadcast( 'openEditShiftContainer', params );
	}

	$rootScope.$on( 'shiftsChanged', function(e, data) {
		$scope.loadShifts();
	});

	start();

});

NGApp.controller('ShiftScheduleEditShiftCtrl', function ( $scope, $rootScope, ShiftScheduleService ) {
	$rootScope.$on( 'openEditShiftContainer', function( e, data ) {
		$scope.loading = true;
		$scope.shift = null;
		data.ignore_log = false;
		$scope.shift = null;
		ShiftScheduleService.loadShift( data, function( json ){
			$scope.loading = false;
			$scope.shift = json;
			$scope.shift.change = 'only-this-shift';
		} )
		App.dialog.show( '.edit-shift-dialog-container' );
	});

	$scope.formEditShiftSave = function(){

		if( $scope.formEditShift.$invalid ){
			$scope.formEditShiftSubmitted = true;
			return;
		}

		$scope.isSavingEditShift = true;
		ShiftScheduleService.editShift( $scope.shift, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
				$scope.isSavingEditShift = false;
			} else {
				$rootScope.$broadcast( 'shiftsChanged', json.id_community );
				setTimeout( function(){ $rootScope.closePopup(); $scope.isSavingEditShift = false; }, 200 );
			}
		} );
	}

} );

NGApp.controller('ShiftScheduleScheduleShiftCtrl', function ( $scope, $rootScope, ShiftScheduleService ) {

	$rootScope.$on( 'openScheduleShiftContainer', function( e, data ) {
		$scope.loading = true;
		$scope.shift = null;
		ShiftScheduleService.loadShift( data, function( json ){
			$scope.loading = false;
			$scope.shift = json;
		} )
		App.dialog.show( '.schedule-shift-dialog-container' );
	});

	loadShiftLog = function(){
		var params = { id_community_shift: $scope.shift.id_community_shift };
		ShiftScheduleService.loadShiftLog( params, function( json ){
			$scope.shift.log = json;
		} );
	}

	$scope.assignDriver = function( driver ){
		if( driver.assigned_permanently ){
			driver.assigned_permanently = false;
		}
		updateShiftAssignment( driver );
	}

	$scope.assignDriverPermanently = function( driver ){
		if( driver.assigned_permanently ){
			driver.assigned = true;
		}
		if( !driver.assigned_permanently && $scope.shift.shift_remove_permanency ){
			driver.assigned = false;
		}
		updateShiftAssignment( driver );
	}

	$scope.saveDriverNote = function( driver ){
		var params = { id_admin: driver.id_admin, notes: driver.notes_text };
		ShiftScheduleService.saveDriverNote( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				driver.notes_saved = true;
				setTimeout( function() {
					$rootScope.$apply( function() {
						driver.notes_saved = false;
					} );
				}, 500 );
			}
		} );
	}

	var updateShiftAssignment = function( driver ){
		var params = { id_admin: driver.id_admin, id_community_shift: $scope.shift.id_community_shift, assigned: driver.assigned, permanent: driver.assigned_permanently };
		ShiftScheduleService.assignDriver( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.saved = true;
				loadShiftLog();
				setTimeout( function() {
					$rootScope.$apply( function() {
						$scope.saved = false;
					} );
				}, 500 );
				$rootScope.$broadcast( 'shiftsChanged', json.id_community );
			}
		} );
	}

} );

NGApp.controller('ShiftScheduleAddShiftCtrl', function ( $scope, $rootScope, ShiftScheduleService ) {

	$rootScope.$on( 'openAddShiftContainer', function( e, data ) {

		$scope.formAddShiftSubmitted = false;

		$scope.loading = true;
		$scope.shift = { type: 'one-time-shift', id_community: data.community.id_community, date: data.date.day, community: data.community.name, date_formatted: data.date.formatted };
		App.dialog.show( '.add-shift-dialog-container' );

		$scope.loading = false;
	});

	$scope.formAddShiftSave = function(){

		if( $scope.formAddShift.$invalid ){
			$scope.formAddShiftSubmitted = true;
			return;
		}

		$scope.isSavingAddShift = true;
		ShiftScheduleService.addShift( $scope.shift, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
				$scope.isSavingAddShift = false;
			} else {
				$rootScope.$broadcast( 'shiftsChanged', json.id_community );
				setTimeout( function(){ $rootScope.closePopup(); $scope.isSavingAddShift = false; }, 200 );
			}
		} );
	}

} );
