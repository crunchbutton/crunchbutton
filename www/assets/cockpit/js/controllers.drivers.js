NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/resources', {
			action: 'community-resources',
			controller: 'CommunityResourcesDriverCtrl',
			templateUrl: 'assets/view/drivers-resources.html'

		})
}]);

NGApp.controller( 'CommunityResourcesDriverCtrl', function ($rootScope, $scope, ResourceService ) {
	ResourceService.driver(function(data) {
		$scope.communities = data;
		console.log('$scope.communities',$scope.communities);
	});
} );

NGApp.controller('DriversDashboardCtrl', function ( $scope, MainNavigationService, DriverOrdersService ) {

	//This links to orders page for pending orders
	$scope.showOrderList = function(){
		MainNavigationService.link('/drivers/order/');
	}

	DriverOrdersService.acceptedOrders();
	DriverOrdersService.pickedupOrders();
	DriverOrdersService.revThisShift();
	DriverOrdersService.revLastShift();
	DriverOrdersService.timeLastShift();
	DriverOrdersService.timeThisShift();
	DriverOrdersService.outstandingOrders();
	//Yell at driver if there is an outstanding undelivered order.

});



NGApp.controller('DriversOrderNavCtrl', function ( $scope, $rootScope, DriverOrdersViewService) {
	$scope.oc = DriverOrdersViewService;

	$rootScope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {
		//console.log('ROUTE',arguments);
		//$scope.oc = null;
	});
});

NGApp.controller('DriversOrderCtrl', function ( $scope, $location, $rootScope, $routeParams, DriverOrdersService, DriverOrdersViewService, AccountService) {

	$rootScope.navTitle = '#' + $routeParams.id;
	$scope.ready = false;
	$scope.oc = DriverOrdersViewService;
	DriverOrdersViewService.prep();

	$scope.nextOrder = function() {
		//console.log(arguments);
	};

	$scope.iOS = App.iOS();

	var load = function() {
		DriverOrdersViewService.load();
		watching = null;
	};

	var watching = null;

	if (!AccountService.init) {
		// we got here before the auth service was complete.
		watching = $rootScope.$on('userAuth', load);
	}

	$scope.randomFooter = Math.floor( ( Math.random() * 2 ) + 1 );
	console.log('$scope.randomFooter',$scope.randomFooter);

	load();
	setTimeout(function() {
		DriverOrdersViewService.textLoader = Ladda.create($('#textCustomer5').get(0));
	}, 1000 );

});

NGApp.controller('DriversOrdersCtrl', function ( $scope, $rootScope, DriverOrdersService, MainNavigationService, AccountService, $location ) {

	$scope.showOrders = ( AccountService && AccountService.user && ( ( AccountService.user.permissions && AccountService.user.permissions.GLOBAL ) || AccountService.user.working || ( AccountService.user.hours_since_last_shift !== false && AccountService.user.hours_since_last_shift <= 6 ) ) );
	// #5413
	$scope.showOrders = true;

	var showAll = $.totalStorage('driver-orders-show');
	if (!showAll) {
		showAll = false;
	} else {
		showAll = $.totalStorage('driver-orders-show') == 'all' ? true : false;
	}
	$scope.iOS = App.iOS();
	$scope.show = {
		all: showAll
	};
	$scope.ready = false;

	$scope.$watch('show.all', function() {
		$.totalStorage('driver-orders-show', $scope.show.all ? 'all' : 'mine');
	});

	$scope.filterOrders = function( order ){
		if ($scope.show.all) {
			return true;
		} else {
			if (order.status.status == 'canceled' || !order.status.driver || order.status.driver.id_admin == $scope.account.user.id_admin){
				return true;
			}
		}
		return false;
	}

	$scope.changed = function() {
		$rootScope.$broadcast('updateHeartbeat');
		$scope.update();
	};

	$scope.update = function() {
		console.debug('Updating drivers orders...');
		$scope.unBusy();
		DriverOrdersService.list(function(data) {
			$scope.driverorders = data;
			for (var x in $scope.driverorders) {
				if( $scope.driverorders[x].address ){
					$scope.driverorders[x].addressFirstLine = $scope.driverorders[x].address.split(',').shift();
				}
			}
			$scope.ready = true;
		});
	};

	$scope.accept = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.accept( id_order,
			function( json ){
				if( json.status ) {
					$scope.changed();
				} else {
					$scope.unBusy();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
					$scope.changed();
				}
			}
		);
	};

	$scope.pickedup = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.pickedup( id_order, $scope.changed);
	};

	$scope.delivered = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.delivered( id_order, $scope.changed);
	};

	$scope.showOrder = function( id_order ){
		MainNavigationService.link( '/drivers/order/' + id_order );
	}

	$rootScope.$watch('totalDriverOrders', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			$scope.update();
		}
	});


	$scope.update();
} );

NGApp.controller( 'DriversSummaryCtrl', function ( $scope, DriverService, $routeParams, StaffService, ViewListService ) {

	angular.extend( $scope, ViewListService );

	$scope.isMobile = App.isMobile();

	$scope.id_admin = parseInt( $scope.account.user.id_admin );
	if( $scope.account.isAdmin ){
		if( $routeParams.id ){
			$scope.id_admin = parseInt( $routeParams.id );
		}
	}
	$scope.view( {
		scope: $scope,
		watch: { type: 'all' },
		update: function() {
	// 		var data = {};
	// angular.extend( data, query );
	// data.id_admin = id_admin;
			$scope.query.id_admin = $scope.id_admin;
			DriverService.summary( $scope.query, function( data ){
				$scope.summary = data;
				$scope.complete( data );
			} );
		}
	} );

} );


NGApp.controller( 'DriversPaymentsCtrl', function ( $scope, DriverService, $routeParams, MainNavigationService) {

	MainNavigationService.link('/drivers/summary/' + $routeParams.id);

	/*

	$scope.ready = false;
	$scope.filter = false;

	var drivers = function(){
		DriverService.listSimple( function( data ){
			$scope.drivers = data;
		} );
	}

	$scope.list = function(){
		DriverService.payments( $scope.id_admin, function( json ){
			$scope.result = json;
			$scope.ready = true;
		} );
	}

	$scope.show_payment = function( id_payment ){
		$scope.navigation.link( '/drivers/payment/' + id_payment );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		$scope.id_admin = parseInt( $scope.account.user.id_admin );
		if( $scope.account.isAdmin ){
			drivers();
			if( $routeParams.id ){
				$scope.id_admin = parseInt( $routeParams.id );
			}
		}
		$scope.list();
	}
	*/
});

NGApp.controller( 'DriversPexCardCtrl', function ( $scope, PexCardService ) {

	$scope.submitted = false;
	$scope.isSearching = false;
	$scope.isActivating = false;
	$scope.activateOption = true;

	$scope.status = PexCardService.status;

	$scope.active = function(){
		if( $scope.card.id ){
			$scope.isActivating = true;
			PexCardService.driver_active( $scope.card.id, function( json ){
				if( json.success ){
					$scope.activateOption = false;
					$scope.crunchbutton_card_id = null;
					$scope.last_four_digits = null;
					$scope.card = null;
					App.alert( 'Your PEX Card is Active! :D! Activate another PEX card', 'success' );
				} else {
					App.alert( 'Error activating card!', 'error' );
					$scope.isActivating = false;
				}

			} );
		}
	}

	$scope.search = function() {

		$scope.card = null;
		$scope.activateOption = true;

		if( $scope.isSearching ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSearching = false;
			return;
		}

		$scope.isSearching = true;
		$scope.isActivating = false;

		var data = { 'crunchbutton_card_id': $scope.crunchbutton_card_id, 'last_four_digits' : $scope.last_four_digits };

		PexCardService.driver_search( data,
			function( json ){
				$scope.isSearching = false;
				$scope.submitted = false;
				if( json.id ){
					$scope.card = json;
				} else {
					App.alert( json.error, 'error' );
					$scope.crunchbutton_card_id = '';
					$scope.last_four_digits = '';
				}
			}
		);
	};

} );

NGApp.controller( 'DriversPaymentCtrl', function ( $scope, DriverService, $routeParams ) {

	$scope.ready = false;
	$scope.schedule = true;

	load = function(){
		DriverService.payment( function( json ){
			$scope.result = json;
			if( json.pay_type == DriverService.PAY_TYPE_REIMBURSEMENT ){
				$scope.pay_type_reimbursement = true;
			} else {
				$scope.pay_type_payment = true;
			}
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	load();
});



NGApp.controller( 'DriversShiftsCtrl', function ( $scope, DriverShiftsService ) {

	$scope.show = { all : true };
	$scope.ready = false;

	$scope.filterShifts = function( shift ){
		if( $scope.show.all ){
			return true;
		} else {
			if( shift.mine ){
				return true;
			}
		}
		return false;
	}

	$scope.list = function(){
		DriverShiftsService.list( function( data ){
			DriverShiftsService.groupByDay( data, function( data ){
				$scope.drivershifts = data;
				$scope.ready = true;
			} );
		} );
	}

	$scope.schedules = function(){
		$scope.navigation.link( '/drivers/shifts/schedule' );
	}


	$scope.list();

} );

NGApp.controller( 'DriversShiftsScheduleCtrl', function ( $scope, DriverShiftScheduleService ) {

	var isSaving = false;

	$scope.ready = false;

	var list = function(){
		DriverShiftScheduleService.list( function( data ){
			process( data );
			$scope.ready = true;
		} );
	}

	$scope.shiftsAvailableToWork = 0;
	$scope.availableToWork = [ 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1 ];

	var process = function( data ){
		$scope.available = 0;
		$scope.yes = 0;
		$scope.not = 0;
		$scope.period = data.info.period;
		$scope.shiftsAvailableToWork = parseInt( data.shifts );
		$scope.shifts = data.results;
		count();
	}

	var count = function(){
		$scope.available = 0;
		$scope.yes = 0;
		$scope.not = 0;
		var list = [];
		var ranking = 1;
		var selecteds = [];
		if( $scope.shifts && $scope.shifts.length ){
			$scope.shifts.ranking_next = null;
			$scope.shifts.ranking_prev = null;
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				if( shift.ranking > 0 ){
					ranking++;
					selecteds.push( i );
				}
				if( !shift.ranking && shift.ranking != 0 ){
					$scope.available++;
				}
				if( shift.ranking > 0 ){
					$scope.yes++;
				}
				if( shift.ranking == 0 ){
					$scope.not++;
				}
			}
		}

		$scope.nextRanking = ranking;
		if( selecteds && selecteds.length ){

			for( var i = 0; i < selecteds.length; i++ ){
				var shift_index = selecteds[ i ];
				var next_index = selecteds[ i + 1 ];
				var prev_index = selecteds[ i - 1 ];
				$scope.shifts[ shift_index ].ranking_next = null;
				$scope.shifts[ shift_index ].ranking_prev = null;

				if( $scope.shifts[ prev_index ] ){
					$scope.shifts[ shift_index ].ranking_prev = $scope.shifts[ prev_index ].id_community_shift;
				} else {
					$scope.shifts[ shift_index ].ranking_prev = 0;
				}

				if( $scope.shifts[ next_index ] ){
					$scope.shifts[ shift_index ].ranking_next = $scope.shifts[ next_index ].id_community_shift;
				} else {
					$scope.shifts[ shift_index ].ranking_next = 0;
				}
			}
		}
	}

	$scope.save = function(){
		$scope.makeBusy();
		$scope.isSaving = true;
		var shifts = {};
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				shifts[ shift.id_community_shift ] = shift.ranking;
			}
		}
		DriverShiftScheduleService.save( shifts, function( data ){
			$scope.isSaving = false;
			process( data );
			$scope.unBusy();
		} );
	}

	$scope.updateShiftsAvailable = function( shifts ){

		$scope.shiftsAvailableToWork = shifts;
		DriverShiftScheduleService.shiftsAvailableToWork( shifts, function( data ){
			process( data );
		} );
	}

	$scope.rankingChange = function( id_community_shift, id_community_shift_change ){
		var shift = null;
		var change = null;
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					shift = i;
				}
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift_change ) {
					change = i;
				}
			}
			if( $scope.shifts[ shift ] && $scope.shifts[ change ] ){
				var actual = $scope.shifts[ shift ].ranking;
				$scope.shifts[ shift ].ranking = $scope.shifts[ change ].ranking;
				$scope.shifts[ change ].ranking = actual;
				actual = $scope.shifts[ shift ];
				$scope.shifts[ shift ] = $scope.shifts[ change ];
				$scope.shifts[ change ] = actual;
				// console.log('$scope.shifts[ shift ]',$scope.shifts[ shift ]);
				// console.log('$scope.shifts[ change ]',$scope.shifts[ change ]);
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.rankingChange( id_community_shift, id_community_shift_change, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.dontWantToWork = function( id_community_shift ){
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					$scope.shifts[ i ].ranking = 0;
				}
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.dontWantToWork( id_community_shift, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.wantToWork = function( id_community_shift ){
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					$scope.shifts[ i ].ranking = $scope.nextRanking;
				}
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.wantToWork( id_community_shift, $scope.nextRanking, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	list();

} );

NGApp.controller( 'DriversOnboardingDocsCtrl', function ( $scope, $timeout, DriverOnboardingService ) {

	$scope.ready = false;
	var waiting = false;
	$scope.page = 1;

	var list = function(){
		DriverOnboardingService.docs.listDocs( $scope.page, function( data ){
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.documents = data.results;
			$scope.count = data.count;
			$scope.ready = true;
		} );
	}

	$scope.approve = function( doc ){
		var approve = ( doc.approved ) ? false : true;
		DriverOnboardingService.docs.approve( doc.id_driver_document_status, approve, function( data ){
			list();
		} );
	}

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	$scope.edit = function( id_admin ){
		$scope.navigation.link( '/drivers/onboarding/' + id_admin );
	}

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	list();

} );

NGApp.controller('DriversOnboardingCtrl', function ($scope, $timeout, $location, StaffService, ViewListService, CommunityService) {

	angular.extend( $scope, ViewListService );

	var load = function(){
		StaffService.list($scope.query, function(d) {
			$scope.drivers = d.results;
			$scope.complete(d);
		});
	}

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'driver',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			community: '',
			send_text: 'all',
			fullcount: true
		},
		update: function() {
			load();
		}
	});

	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options) {

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
	}

	var limits = [];
	limits.push( { value: '20', label: '20' } );
	limits.push( { value: '50', label: '50' } );
	limits.push( { value: '100', label: '100' } );
	limits.push( { value: '200', label: '200' } );
	$scope.limits = limits;

	var statuses = [];
	statuses.push( { value: 'active', label: 'Active' } );
	statuses.push( { value: 'inactive', label: 'Inactive' } );
	$scope.statuses = statuses;

	var send_text_options = [];
	send_text_options.push( { value: 'all', label: 'All' } );
	send_text_options.push( { value: '1', label: 'Yes' } );
	send_text_options.push( { value: '0', label: 'No' } );
	$scope.send_text_options = send_text_options;

	$scope.send_text_about_schedule = function( id_admin, value ){
		params = { 'id_admin': id_admin, 'value': value };
		StaffService.send_text_about_schedule( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error , 'error' );
			}
		} );
	}

	$scope.last_note = function( id_admin ){

		$scope.note = {};
		$scope.formNoteSubmitted = false;
		$scope.isSavingNote = false;
		App.dialog.show( '.admin-note-container' );
		StaffService.note( id_admin, function( json ){
			if( json.id_admin ){
				$scope.note = json;
			} else {
				App.alert( 'Error loading note' , 'error' );
			}
		} );
	}

	$scope.save_note = function( id_admin, text ){
		if( $scope.formNote.$invalid ){
			$scope.formNoteSubmitted = true;
			$scope.isSavingNote = false;
			return;
		}
		StaffService.save_note( $scope.note, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error , 'error' );
			} else {
				load();
				App.dialog.close();
			}
			$scope.formNoteSubmitted = false;
			$scope.isSavingNote = false;
		} );
	}

});

NGApp.controller( 'DriversOnboardingFormCtrl', function ( $scope, $routeParams, $filter, FileUploader, DriverOnboardingService, CommunityService, StaffPayInfoService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.isSaving = false;

	$scope.payment_types = StaffPayInfoService.typesPayment();

	var vehicle_default = null;

	$scope._yesNo = DriverOnboardingService.yesNo();
	$scope.timezones = CommunityService.timezones();
	DriverOnboardingService.vehicles( function( json ){
		if( !$scope.vehicles ){
			$scope.vehicles = json.options;
			vehicle_default = json.default;
		}
	} );

	$scope.$watch( 'driver.phone', function( newValue, oldValue, scope ) {
		referral();
	} );


	$scope.$watch( 'driver.name', function( newValue, oldValue, scope ) {
		referral();
	} );

	var referral = function(){
		if( $scope.driver && !$scope.driver.id_admin ){
			var name = $scope.driver.name;
			var phone = $scope.driver.phone;
			if( name && phone ){
				DriverOnboardingService.referral( phone, name, function( data ){
					if( data.code ){
						$scope.driver.invite_code = data.code;
					};
				} );
			}
		}
	}

	CommunityService.listSimple( function( data ){
		if( !$scope.communities ){
			$scope.communities = data;
		}
		$scope.ready = true;
	} );

	var docs = function(){
		// Load the docs
		DriverOnboardingService.docs.list( $routeParams.id, function( data ){
			$scope.documents = data;
		} );
		docsPendency();
	}

	$scope.approve = function( doc ){
		var approve = ( doc.approved ) ? false : true;
		DriverOnboardingService.docs.approve( doc.id_driver_document_status, approve, function( data ){
			docs();
		} );
	}

	$scope.remove = function( id_driver_document_status ){
		if( confirm( 'Confirm remove document?' ) ){
			DriverOnboardingService.docs.remove( id_driver_document_status, function( data ){
				docs();
			} );
		}
	}

	var docsPendency = function(){
		DriverOnboardingService.docs.pendency( $routeParams.id, function( data ){  } );
	}

	var logs = function(){
		DriverOnboardingService.logs( $routeParams.id, function( data ){
			$scope.logs = data;
		} );
	}

	var start = function(){

		DriverOnboardingService.get( $routeParams.id, function( driver ){

			$scope.driver = driver;

			if( driver.pexcard_date ){
				$scope.driver.pexcard_date = new Date( driver.pexcard_date );
			} else {
				$scope.driver.pexcard_date = new Date();
			}

			if( !$scope.driver.id_admin ){
				$scope.driver.notify = true;
			}

			if( !$scope.driver.vehicle && vehicle_default ){
				$scope.driver.vehicle = vehicle_default
			}

			docs();
		} );

		DriverOnboardingService.phone_types( function( json ){
			$scope.phone_types = json.options;
			$scope.phones_default = json.default;
			$scope.iphone_options = json.iphone_options;
			$scope.android_options = json.android_options;
			$scope.android_versions = json.android_versions;

			$scope.iphone_type = json.default;
			$scope.android_type = json.default;
			//$scope.android_type_other = json.other;//michal
			$scope.android_version = json.default;
		} );

		DriverOnboardingService.carrier_types( function( json ){
			$scope.carrier_types = json.options;
			$scope.carrier_type_other = json.other;
		} );

    DriverOnboardingService.tshirt_sizes( function( json ){
			$scope.tshirt_sizes = json.tshirt_options;
		} );

	}

	$scope.notify = function(){
		DriverOnboardingService.notifySetup( $scope.driver.id_admin, function( json ){
			if( json.success ){
				App.alert( 'Notification sent!' );
				// logs();
			} else {
				App.alert( 'Notification not sent: ' + json.error , 'error' );
			}
		} );
	}

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// method save that saves the driver
	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		if( $scope.driver.pexcard_date ){
			$scope.driver.pexcard_date = $filter( 'date' )( $scope.driver.pexcard_date, 'yyyy-MM-dd' );
		}

		$scope.isSaving = true;
		DriverOnboardingService.save( $scope.driver, function( json ){
			if( json.success ){
				var url = '/drivers/onboarding/' + json.success.id_admin;
				if( $scope.driver.id_admin ){
					$scope.reload();
				} else {
					$scope.navigation.link( url );
				}
				setTimeout( function(){
					App.alert( 'Driver saved!' );
				}, 50 );
				$scope.isSaving = false;
			} else {
				App.alert( 'Driver not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}

	$scope.cancel = function(){
		$scope.navigation.link( '/drivers/onboarding/' );
	}

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// this is a listener to upload error
	$scope.$on( 'driverDocsUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );

	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		var id_driver_document = data.id_driver_document;
		var response = data.response;
		if( response.success ){
			var doc = { id_admin : $scope.driver.id_admin, id_driver_document : id_driver_document, file : response.success };
			DriverOnboardingService.docs.save( doc, function( json ){
				if( json.success ){
					App.alert( 'File saved!' );
					docs();
				} else {
					App.alert( 'File not saved: ' + json.error );
				}
			} );
		} else {
			App.alert( 'File not saved! ');
		}
	});


	// Upload control stuff
	$scope.doc_uploaded = 0;
	var uploader = $scope.uploader = new FileUploader({
		url: '/api/driver/documents/upload/'
	});

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	$scope.$watch( 'ready', function( newValue, oldValue, scope ) {
		start();
	});

} );

NGApp.controller( 'DriversOnboardingSetupCtrl', function( $scope, DriverOnboardingService ) {

	$scope.ready = false;
	$scope.finished = false;
	$scope.sending = false;

	$scope.driver = { password: '', email : '', confirm: '' };

	$scope.access = function(){
		$scope.navigation.link( '/login' );
	}

	$scope.check_password = false;

	$scope.send = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}
		$scope.sending = true;
		DriverOnboardingService.setupSave( $scope.driver, function( json ){
			if( json.success ){
				$scope.driver	= json.success;
				$scope.sending = false;
				$scope.finished = true;
			} else {
				$scope.sending = false;
				$scope.error = json.error;
			}
		} );
	}

	DriverOnboardingService.setupValidate( function( json ){
		if( json.success ){
			$scope.driver.id_admin = json.success.id_admin;
			$scope.driver.hasEmail = json.success.hasEmail;
			$scope.ready = true;
		} else {
			$scope.error = json.error;
		}
	} );

} );

NGApp.controller( 'DriversDocsFormCtrl', function( $scope, $rootScope, DriverOnboardingService, StaffService) {

	$scope.ready = false;
	$scope.status = {};
	$scope.pexcard = false;

	var docs = function(){

		// Load the docs
		DriverOnboardingService.docs.list( $scope.account.user.id_admin, function( data ){

			$scope.documents = data;
			$scope.ready = true;

			$scope.status.docs = true;
			$scope.status.identification = true;

			angular.forEach($scope.documents, function(doc, x) {
				if ($scope.documents[x].url && (!$scope.documents[x].status || $scope.documents[x].status.file.substring(9, 14) == 'blank')) {
					$scope.status.docs = false;
				} else if (!$scope.documents[x].url && !$scope.documents[x].status) {
					$scope.status.identification = false;
				}
			});
		} );
	}

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// this is a listener to upload error
	$scope.$on( 'driverDocsUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );

	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		if( data.success ){
			App.alert( 'File saved!' );
			docs();
		} else {
			App.alert( 'Upload error, please try again or send us a message.' );
		}
	});

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	var loadInfo = function(){
		if( $scope.account && $scope.account.user && $scope.account.user.id_admin ){
			StaffService.status( $scope.account.user.id_admin, function(data) {
				if (data.payment == true) {
					$scope.status.paymentinfo = true;
				}
			});
			StaffService.has_pexcard( $scope.account.user.id_admin, function( json ) {
				if( json.success ){
					$scope.pexcard = json.success;
				}
			});
			docs();
		} else {
			setTimeout( function(){ loadInfo(); }, 500 );
		}
	}

	loadInfo();

} );

NGApp.controller( 'PreOnboardingCtrl', function( $scope, PreOnboardingService, CommunityService, DriverOnboardingService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.driver = {};

	CommunityService.listSimple( function( data ){
		$scope.communities = data;
		$scope.ready = true;
	} );

	DriverOnboardingService.vehicles( function( json ){
		$scope.vehicles = json.options;
		$scope.driver.vehicle = json.default;
	} );

	$scope.sending = false;

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}
		$scope.sending = true;
		PreOnboardingService.save( $scope.driver, function( json ){
			if( json.success ){
				$scope.login = json.success.login;
				$scope.finished = true;
			} else {
				$scope.sending = false;
				$scope.error = json.error;
			}
		} );
	}
} );

NGApp.controller('DriversPaymentFormCtrl', function( $scope, StaffPayInfoService, ConfigService ) {

	$scope.bank = { 'showForm': true };
	$scope.basicInfo = {};

	var load = function(){
		StaffPayInfoService.loadById( $scope.account.user.id_admin, function( json ){
			if( json.id_admin ){
				$scope.basicInfo = json;
				if(json.stripe_id && json.stripe_account_id ){
					$scope.bank.showForm = false;
				}
				$scope.ready = true;
				$scope.payment = {};
			} else {
				App.alert( json.error );
			}
		} )
	}

	$scope.save_basic_info = function(){
		if( $scope.formBasic.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.basicSubmitted = true;
			return;
		}
		$scope.isSaving = true;
		StaffPayInfoService.save( $scope.basicInfo, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.basicInfo = data;
				$scope.saved = true;
				App.alert( 'Information saved!' );
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		StaffPayInfoService.bankInfoTest( function( json ){
			$scope.bank.routing_number = json.routing_number;
			$scope.bank.account_number = json.account_number;
		} );
	}

	$scope.createBankAccount = function(){

		if( !$scope.basicInfo.id_admin_payment_type ){
			App.alert( 'You must save the "Basic Information" form before save the Bank Account Information.' );
			return;
		}

		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}

		$scope.isTokenizing = true;

		stripe();
	}

	var stripe = function(){
		Stripe.bankAccount.createToken( {
			country: 'US',
			currency: 'USD',
			routing_number: $scope.bank.routing_number,
			account_number: $scope.bank.account_number
		}, function( header, response ){

			if( response.id ){
				var params = {
					'token': response.id,
					'id_admin': $scope.account.user.id_admin
				};
				StaffPayInfoService.save_stripe_bank( params, function( d ){
					$scope.isTokenizing = false;
					if( d.id_admin ){
						bank_info_saved();
					} else {
						var error = d.error ? d.error : '';
						App.alert( 'Error: ' + error );
					}
				} );
			} else {
				$scope.isTokenizing = false;
				App.alert( 'Error creating a Stripe token' );
			}
		} );
	}

	var bank_info_saved = function(){
		document.activeElement.blur();
		load();
		$scope.isTokenizing = false;
		$scope.saved = true;
		$scope.bank.account_number = '';
		$scope.bank.routing_number = '';
		$scope.bank.showForm = false;
		App.alert( 'Bank information saved!' );
		setTimeout( function() { $scope.saved = false; }, 1500 );
	}

	$scope.list = function(){
		$scope.navigation.link( '/staff/list' );
	}


	// just to cache the config process stuff
	ConfigService.getProcessor( function( json ){
		$scope.processor = json.processor.type;
		$scope.isStripe = true;
		load();
	} );

});

NGApp.controller('DriversHelpCtrl', function( $scope, AccountService ) {
	$scope.account = AccountService;
});

NGApp.controller('InviteCtrl', function() {});
NGApp.controller('DriversFeedbackCtrl', function($scope, FeedbackService) {
	$scope.feedback = {};
	$scope.errors = {};
    $scope.post = function(){

    	$scope.errors = {};
    	if (!$scope.feedback.message) {
    		$scope.errors.message = true;
    	}
    	if (jQuery.isEmptyObject($scope.errors)) {
    		console.log($scope.feedback);
    			FeedbackService.post($scope.feedback, function(data){
					$scope.finished = true;
        		//console.log(data);
    			})
    	}
    }
});
NGApp.controller('DriversHelpCreditCardCtrl', function() {});
NGApp.controller('DriversLocationsCtrl', function($rootScope, $scope, $routeParams, $location, StaffService, MapService) {


		$scope.staff = null;
		$scope.map = null;
		$scope.loading = true;
		var marker;

		StaffService.get($rootScope.account.user.id_admin, function(staff) {
			$rootScope.title = staff.name + ' | Staff';
			$scope.staff = staff;
			$scope.loading = false;
		});

		StaffService.locations($rootScope.account.user.id_admin, function(d) {
			$scope.locations = d;
			update();
		});

		$scope.$watch('staff', function() {
			console.log('staff');
			update();
		});

		$scope.$watch('map', function() {
			console.log('map');
			//update();
		});

		var update = function() {
			if (!$scope.map || !$scope.staff || !$scope.locations) {
				return;
			}

			MapService.trackStaff({
				map: $scope.map,
				staff: $scope.staff,
				locations: $scope.locations,
				scope: $scope,
				id: 'staff-locations'
			});

		};

		$scope.$on('mapInitialized', function(event, map) {
			$scope.map = map;
			MapService.style(map);
			//update();
		});
});


NGApp.controller('DriversWelcomeHomeCtrl', function() {

});
NGApp.controller('DriversWelcomeInfoCtrl', function() {});

NGApp.controller('DriversWelcomeLocationCtrl', function($location, LocationService, $scope, $rootScope) {
	var complete = function() {
		$location.path('/drivers/welcome/push');
		//history.pushState({}, 'next', '/drivers/welcome/push');
		$rootScope.$safeApply();
	};
	var l;
	$scope.locateit = function() {
		if (App.isPhoneGap) {
			LocationService.register(complete);
		} else {
			complete();
		}
		l.start();
	};
	setTimeout(function(){
		l = Ladda.create($('.welcome-button .ladda-button').get(0));
	},700);
});

NGApp.controller('DriversWelcomePushCtrl', function($rootScope, $location, PushService, $scope) {
	var complete = function() {
		$location.path('/drivers/welcome/wahoo');
		//history.pushState({}, 'next', '/drivers/welcome/wahoo');
		$rootScope.$safeApply();
	};
	$scope.pushit = function() {
		if (App.isPhoneGap) {
			PushService.register(complete);
		} else {
			complete();
		}
	};;
});
NGApp.controller('DriversWelcomeWahooCtrl', function() {
	$.totalStorage('isDriverWelcomeSetup', '1');
});
