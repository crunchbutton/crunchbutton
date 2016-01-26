NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/shifts/checkin', {
			action: 'shift-checkin',
			controller: 'ShiftChekinCtrl',
			templateUrl: '/assets/view/shift-checkin.html',
			reloadOnSearch: false
		})
		.when('/shifts/', {
			action: 'shift',
			controller: 'ShiftCtrl',
			templateUrl: '/assets/view/shift.html',
			reloadOnSearch: false
		})
		.when('/shifts/settings', {
			action: 'shift',
			controller: 'ShiftSettingsCtrl',
			templateUrl: '/assets/view/shift-settings.html',
			reloadOnSearch: false
		})
		.when('/shifts/schedule/:permalink?', {
			action: 'shift-schedule',
			controller: 'ShiftScheduleCtrl',
			templateUrl: '/assets/view/shift-schedule.html',
			reloadOnSearch: false
		});

}]);

NGApp.controller( 'ShiftCtrl', function ( $scope ) {} );

NGApp.controller('ShiftSettingsCtrl', function( $scope, ShiftSettingsService ) {

	var load = function(){
		ShiftSettingsService.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.ready = true;
			}
		} )
	}

	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}


		$scope.isSaving = true;
		ShiftSettingsService.save( $scope.config, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.saved = true;
				App.alert( ( 'Information saved!' ) )
				setTimeout( function() { $scope.saved = false; }, 1000 );
			}
		} );
	}

	load();

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

NGApp.controller('ShiftScheduleCtrl', function ( $scope, $rootScope, $routeParams, $location, $filter, ShiftScheduleService, CommunityService ) {

	$scope.options = { communities: [] };

	if( $routeParams.permalink ){
		$scope.options.communities.push( $routeParams.permalink );
	}

	$scope.selectNoneCommunity = function(){
		$scope.options.communities = [];
	}

	$scope.openCallTextModal = function( phone ){
		$rootScope.closePopup();
		setTimeout( function(){
			$scope.callText( phone )
		}, 500 );
	}

	$scope.loaded = false;

	$scope.loadShifts = function(){
		$scope.loaded = false;
		ShiftScheduleService.loadShifts( $scope.options, function( json ){
			if( json.communities ){
				$scope.shifts = { communities: json.communities };
				$scope.days = json.days;
				$scope.current_week = json.current_week;
				$scope.loaded = true;
				if( openShift ){
					$scope.scheduleShift( openShift );
				}
			}
		} );
		changeQuery();
	}

	$scope.$on( 'modalClosed', function(e, data) {
		changeQuery();
	});

	var changeQuery = function(){
		var query = { date: null, communities: '' };
		if( $scope.options.start ){
			query.date = $filter( 'date' )( $scope.options.start, 'MM/dd/yyyy' );
		}
		var commas = '';
		for( x in $scope.communities ){
			if( $scope.options.communities.indexOf( $scope.communities[ x ].permalink ) >= 0 ){
				query.communities += commas + $scope.communities[ x ].id_community;
				commas = ',';
			}
		}
		if( $scope.options.id_community_shift ){
			query.id_community_shift = $scope.options.id_community_shift;
		}
		$location.search( query );
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

	$scope.previousWeek = function(){
		var prev = new Date( $scope.options.start );
		prev.setDate( prev.getDate() - 7 );
		$scope.options.start = prev;
		$scope.loadShifts();
	}

	$scope.nextWeek = function(){
		var next = new Date( $scope.options.start );
		next.setDate( next.getDate() + 7 );
		$scope.options.start = next;
		$scope.loadShifts();
	}

	$scope.toggleTz = function(){
		$scope.showPSTtz = !$scope.showPSTtz;
	}

	$scope.selectAllCommunities = function(){
		$scope.selectNoneCommunity();
		for( x in $scope.communities ){
			if( $scope.communities[ x ].permalink ){
				$scope.options.communities.push( $scope.communities[ x ].permalink );
			}
		}
	}

	var openShift = null;

	var start = function(){

		var query = $location.search();
		var startDate = null;
		var communities = new Array();

		if( query.date ){
			startDate = new Date( query.date );
		}

		if( query.id_community_shift ){
			$scope.options.id_community_shift = query.id_community_shift;
			openShift = $scope.options.id_community_shift;
		}

		if( query.communities ){
			communities = query.communities.split( ',' );
			for( x in communities ){
				communities[ x ] = parseInt( communities[ x ] );
			}
		}

		ShiftScheduleService.weekStart( function( json ){

			if( startDate ){
				$scope.options.start = startDate;
			} else {
				if( json.start ){
					$scope.options.start = new Date( json.start );
				} else {
					$scope.options.start = new Date();
				}
			}

			if( $scope.options.communities.length ){
				$scope.loadShifts();
			}

		} );

		if( !$scope.communities ){
			CommunityService.listPermalink( function( json ){
				$scope.communities = json;
				if( communities.length ){
					parseCommunities( communities );
				} else {
					ShiftScheduleService.communitiesWithShift( function( json ){
						if( json.length ){
							parseCommunities( json );
						}
					} );
				}
			} );
		}
	}

	var parseCommunities =  function( communities ){
		for( x in $scope.communities ){
			if( communities.indexOf( $scope.communities[ x ].id_community ) >= 0 ){
				$scope.options.communities.push( $scope.communities[ x ].permalink );
			}
		}
		$scope.loadShifts();
	}

	$scope.addShift = function( id_community, name, date ){
		var params = { community: { id_community: id_community, name: name }, date: date }
		$rootScope.$broadcast( 'openAddShiftContainer', params );
	}

	$scope.scheduleShift = function( id_community_shift ){
		var query = $location.search();
		query.id_community_shift = id_community_shift;
		$location.search( query );
		var params = { id_community_shift: id_community_shift }
		$rootScope.$broadcast( 'openScheduleShiftContainer', params );
		openShift = null;
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
		data.ignore_log = true;
		$scope.shift = null;
		ShiftScheduleService.loadShift( data, function( json ){

			$scope.loading = false;
			$scope.shift = json;
			$scope.shift.ok_to_change = 'yes';
			if( $scope.shift.drivers_assigned ){
				$scope.shift.ok_to_change = 'no';
			}
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

	var confirmFail = function(){
		setTimeout( function() {
			if( $scope.shift.id_community_shift ){
				var params = { id_community_shift: $scope.shift.id_community_shift }
				$rootScope.$broadcast( 'openEditShiftContainer', params );
			}
		}, 400 );
	}

	$scope.removeShift = function(){
		if( $scope.shift.id_community_shift ){
			var success = function(){
				var params = { id_community_shift: $scope.shift.id_community_shift }
				ShiftScheduleService.removeShift( params, function( json ){
					if( json.error ){
						App.alert( 'Error deleting: ' + json.error );
					} else {
						$rootScope.$broadcast( 'shiftsChanged', json.id_community );
						setTimeout( function(){ $rootScope.closePopup(); }, 200 );
					}
				} );
			}
			App.confirm( 'Confirm delete this shift?', 'Confirm', success, confirmFail, 'Yes,No', true );
		}
	}

	$scope.removeRecurringShift = function(){
		if( $scope.shift.id_community_shift ){
			var success = function(){
				var params = { id_community_shift: $scope.shift.id_community_shift }
				ShiftScheduleService.removeRecurringShift( params, function( json ){
					if( json.error ){
						App.alert( 'Error deleting: ' + json.error );
					} else {
						$rootScope.$broadcast( 'shiftsChanged', json.id_community );
						setTimeout( function(){ $rootScope.closePopup(); }, 200 );
					}
				} );
			}
			App.confirm( 'Confirm delete all recurring shifts?', 'Confirm', success, confirmFail, 'Yes,No', true );
		}
	}

} );

NGApp.controller('ShiftScheduleScheduleShiftCtrl', function ( $scope, $rootScope, ShiftScheduleService ) {

	var info = null;

	$rootScope.$on( 'openScheduleShiftContainer', function( e, data ) {
		info = data;
		$scope.loading = true;
		$scope.shift = null;
		loadShiftInfo();
		App.dialog.show( '.schedule-shift-dialog-container' );
	});

	var loadShiftInfo = function(){
		ShiftScheduleService.loadShift( info, function( json ){
			$scope.loading = false;
			$scope.shift = json;
		} )
	}

	loadShiftLog = function(){
		var params = { id_community_shift: $scope.shift.id_community_shift };
		ShiftScheduleService.loadShiftLog( params, function( json ){
			$scope.shift.log = json;
		} );
	}

	$scope.assignDriver = function( driver ){
		if( driver.assigned_permanently ){
			driver.keep_permanency = true;
		}
		processShiftAssignmentUpdate( driver );
	}

	$scope.assignDriverPermanently = function( driver ){
		if( driver.assigned_permanently ){
			driver.assigned = true;
			driver.keep_permanency = true;
		} else {
			driver.keep_permanency = false;
		}
		processShiftAssignmentUpdate( driver );
	}

 	logDriver = function( driver ){
 		console.log('driver.assigned',driver.assigned);
 		console.log('driver.keep_permanency',driver.keep_permanency);
 		console.log('driver.assigned_permanently',driver.assigned_permanently);
	}

	$scope.saveDriverNote = function( driver ){
		var params = { id_admin: driver.id_admin, notes: driver.notes_text };
		ShiftScheduleService.saveDriverNote( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				driver.notes_saved = true;
				loadShiftInfo();
				setTimeout( function() {
					$rootScope.$apply( function() {
						driver.notes_saved = false;
					} );
				}, 500 );
			}
		} );
	}

	var updateShiftAssignment = function( driver, callback ){
		var params = { id_admin: driver.id_admin, id_community_shift: $scope.shift.id_community_shift, assigned: driver.assigned, permanent: driver.assigned_permanently, keep_permanency: driver.keep_permanency };
		if( !driver.assigned ){
			params.reason = driver.reason;
			params.reason_other = driver.reason_other;
			params.find_replacement = driver.find_replacement;
		}
		ShiftScheduleService.assignDriver( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				loadShiftInfo();
				$scope.saved = true;
				if( callback ){
					callback();
				}
				setTimeout( function() {
					$rootScope.$apply( function() {
						$scope.saved = false;
					} );
				}, 500 );
				$rootScope.$broadcast( 'shiftsChanged', json.id_community );
			}
		} );
	}

	var processShiftAssignmentUpdate = function( driver ){
		logDriver( driver );
		if( driver.assigned ){
			updateShiftAssignment( driver );
		} else {
			if( !$scope.shift.ask_reason ){
				updateShiftAssignment( driver );
			} else {
				driver.assigned = true;
				driver.removal_info = true;
			}
		}
	}

	$scope.updateRemovalInfo = function( driver ){
		ShiftScheduleService.updateRemovalInfo( driver, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.saved = true;
				loadShiftInfo();
				setTimeout( function() {
					$rootScope.$apply( function() {
						$scope.saved = false;
					} );
				}, 500 );
				$rootScope.$broadcast( 'shiftsChanged', json.id_community );
			}
		} );
	}

	$scope.removeShiftAssigment = function( driver ){
		if( !driver.reason ){
			// as it is modal we need to use default alert
			alert( 'Please select the reason!' );
			return;
		}
		if( ( driver.reason == 'Unacceptable: Other' || driver.reason == 'Acceptable: Other' ) && !driver.reason_other ){
			alert( 'Please type the reason!' );
			return;
		}
		if( driver.reason != 'Our decision' && !driver.find_replacement ){
			alert( 'Please select one option for "Did they find a replacement"!' );
			return;
		}
		driver.assigned = false;
		updateShiftAssignment( driver, function(){ driver.removal_info = false; } );
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
