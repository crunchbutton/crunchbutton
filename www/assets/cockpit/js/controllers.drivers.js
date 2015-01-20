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



NGApp.controller('DriversOrderCtrl', function ( $scope, DriverOrdersService, MainNavigationService) {

	$scope.ready = false;

	// private method
	var load = function(){
		DriverOrdersService.get( function( json ){
			$scope.order = json;
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.accept = function() {
		$scope.makeBusy();
		DriverOrdersService.accept( $scope.order.id_order,
			function( json ){
				if( json.status ) {
					load();
				} else {
					load();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
				}
			}
		);
	};

	$scope.undo = function() {
		$scope.makeBusy();
		DriverOrdersService.undo( $scope.order.id_order, function(){ load(); } );
	};

	$scope.pickedup = function() {
		$scope.makeBusy();
		DriverOrdersService.pickedup( $scope.order.id_order, function(){ load(); } );
	};

	$scope.delivered = function() {
		$scope.makeBusy();
		DriverOrdersService.delivered( $scope.order.id_order, function(){
			load();
			MainNavigationService.link('/drivers/orders');
		} );
	};

	$scope.reject = function() {
		$scope.makeBusy();
		DriverOrdersService.reject( $scope.order.id_order, function(){ load();	} );
	};

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
	}

	DriverOrdersService.driver_take();

});

NGApp.controller('DriversOrdersCtrl', function ( $scope, $rootScope, DriverOrdersService, MainNavigationService ) {
	var showAll = $.totalStorage('driver-orders-show');
	if (!showAll) {
		showAll = false;
	} else {
		showAll = $.totalStorage('driver-orders-show') == 'all' ? true : false;
	}

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
			if (!order.status.driver || order.status.driver.id_admin == $scope.account.user.id_admin){
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

	if( $scope.account.isLoggedIn() ){
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
	}

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
					$scope.flash.setMessage( 'Your PEX Card is Active! :D! Activate another PEX card', 'success' );
				} else {
					$scope.flash.setMessage( 'Error activating card!', 'error' );
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
					$scope.flash.setMessage( json.error, 'error' );
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

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
	}
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

	if( $scope.account.isLoggedIn() ){
		$scope.list();
	}

} );

NGApp.controller( 'DriversShiftsScheduleCtrl', function ( $scope, DriverShiftScheduleService ) {

	$scope.ready = false;

	var list = function(){
		DriverShiftScheduleService.list( function( data ){
			process( data );
			$scope.ready = true;
		} );
	}

	$scope.shiftsAvailableToWork = 0;
	$scope.availableToWork = [12,11,10,9,8,7,6,5,4,3,2,1];

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
		var list = [];
		var ranking = 1;
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				if( shift.ranking > 0 ){
					ranking++;
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
		$scope.unBusy();
		$scope.nextRanking = ranking;
	}

	$scope.updateShiftsAvailable = function( shifts ){
		$scope.makeBusy();
		$scope.shiftsAvailableToWork = shifts;
		DriverShiftScheduleService.shiftsAvailableToWork( shifts, function( data ){
			process( data );
		} );
	}

	$scope.rankingChange = function( id_community_shift, id_community_shift_change ){
		$scope.makeBusy();
		DriverShiftScheduleService.rankingChange( id_community_shift, id_community_shift_change, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.dontWantToWork = function( id_community_shift ){
		$scope.makeBusy();
		DriverShiftScheduleService.dontWantToWork( id_community_shift, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.wantToWork = function( id_community_shift ){
		$scope.makeBusy();
		DriverShiftScheduleService.wantToWork( id_community_shift, $scope.nextRanking, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	if( $scope.account.isLoggedIn() ){
		list();
	}

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
NGApp.controller('DriversOnboardingCtrl', function ($scope, $timeout, $location, DriverOnboardingService) {

	var query = $location.search();
	$scope.query = {
		search: query.search,
		limit: query.limit || 25,
		page: query.page || 1
	};

	$scope.query.page = parseInt($scope.query.page);

	var update = function() {
		$scope.loading = true;
		DriverOnboardingService.list($scope.query.page, $scope.query.search, function(data) {
			$scope.pages = data.pages;
			$scope.drivers = data.results;
			$scope.count = data.count;
			$scope.loading = false;
		});
	}

	var watch = function() {
		$location.search($scope.query);
		update();
	};

	// @todo: this breaks linking to pages
	var inputWatch = function() {
		if ($scope.query.page != 1) {
			$scope.query.page = 1;
		} else {
			watch();
		}
	};

	$scope.$watch('query.search', inputWatch);
	$scope.$watch('query.limit', inputWatch);
	$scope.$watch('query.page', watch);

	$scope.setPage = function(page) {
		$scope.query.page = page;
		App.scrollTop(0);
	};

	$scope.focus('#search');
});

NGApp.controller( 'DriversOnboardingFormCtrl', function ( $scope, $routeParams, $filter, FileUploader, DriverOnboardingService, CommunityService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.isSaving = false;

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

			$scope._yesNo = DriverOnboardingService.yesNo();

			$scope.driver = driver;

			if( driver.pexcard_date ){
				$scope.driver.pexcard_date = new Date( driver.pexcard_date );
			} else {
				$scope.driver.pexcard_date = new Date();
			}

			if( !$scope.driver.id_admin ){
				$scope.driver.notify = true;
			}
			// logs();
			docs();
			DriverOnboardingService.vehicles( function( json ){
				$scope.vehicles = json.options;
				if( !$scope.driver.vehicle ){
					$scope.driver.vehicle = json.default;
				}
			} );

			CommunityService.listSimple( function( data ){
				$scope.communities = data;
				$scope.ready = true;
			} );

		} );

		DriverOnboardingService.phone_types( function( json ){

			$scope.phone_types = json.options;
			$scope.phones_default = json.default;
			$scope.iphone_options = json.iphone_options;
			$scope.android_options = json.android_options;
			$scope.android_versions = json.android_versions;

			$scope.iphone_type = json.default;
			$scope.android_type = json.default;
			$scope.android_version = json.default;
		} );

		DriverOnboardingService.carrier_types( function( json ){
			$scope.carrier_types = json.options;
			$scope.carrier_type_other = json.other;
		} );

	}

	$scope.notify = function(){
		DriverOnboardingService.notifySetup( $scope.driver.id_admin, function( json ){
			if( json.success ){
				$scope.flash.setMessage( 'Notification sent!' );
				// logs();
			} else {
				$scope.flash.setMessage( 'Notification not sent: ' + json.error , 'error' );
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
					$scope.flash.setMessage( 'Driver saved!' );
				}, 500 );

				$scope.isSaving = false;

			} else {
				$scope.flash.setMessage( 'Driver not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}

	$scope.cancel = function(){
		$scope.navigation.link( '/drivers/onboarding/' );
	}

	// Upload control stuff
	$scope.doc_uploaded = 0;
	var uploader = $scope.uploader = new FileUploader({
		url: '/api/driver/documents/upload/'
	});


	uploader.onSuccessItem = function(fileItem, response, status, headers) {
		$scope.$apply();

		if( response.success ){
			var doc = { id_admin : $scope.driver.id_admin, id_driver_document : $scope.doc_uploaded, file : response.success };
			DriverOnboardingService.docs.save( doc, function( json ){
				if( json.success ){
					docs();
					logs();
					$scope.flash.setMessage( 'File saved!' );
				} else {
					$scope.flash.setMessage( 'File not saved: ' + json.error );
				}
			} );
			uploader.clearQueue();
		} else {
			$scope.flash.setMessage( 'File not saved: ' + json.error );
		}
	};

	uploader.onErrorItem = function (fileItem, response, status, headers) {
		App.alert( 'Upload error, please try again or send us a message.' );
	};

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	start();

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

	var docs = function(){
		// Load the docs
		DriverOnboardingService.docs.list( $scope.account.user.id_admin, function( data ){
			$scope.documents = data;
			$scope.ready = true;

			$scope.status.docs = true;
			$scope.status.identification = true;

			angular.forEach($scope.documents, function(doc, x) {
				if ($scope.documents[x].url && (!$scope.documents[x].status || $scope.documents[x].status.file.substring(9, 14) == 'blank')) {
					console.log($scope.documents[x]);
					$scope.status.docs = false;
				} else if (!$scope.documents[x].url && !$scope.documents[x].status) {
					console.log($scope.documents[x]);
					$scope.status.identification = false;
				}
			});
		} );
	}

	docs();

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
			var doc = { id_admin : $scope.account.user.id_admin, id_driver_document : id_driver_document, file : response.success };
			DriverOnboardingService.docs.save( doc, function( json ){
				if( json.success ){
					$scope.flash.setMessage( 'File saved!' );
					docs();
				} else {
					$scope.flash.setMessage( 'File not saved: ' + json.error );
				}
			} );
		} else {
			$scope.flash.setMessage( 'File not saved! ');
		}
	});

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}




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

NGApp.controller('DriversPaymentFormCtrl', function( $scope, StaffPayInfoService ) {

	$scope.bank = { 'showForm': true };
	$scope.basicInfo = {};

	var load = function(){
		StaffPayInfoService.loadById( $scope.account.user.id_admin, function( json ){
			if( json.id_admin ){
				$scope.basicInfo = json;
				if( json.balanced_bank ){
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
				$scope.flash.setMessage( 'Information saved!' );
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		StaffPayInfoService.bankInfoTest( function( json ){
			$scope.bank.routing_number = json.routing_number; ;
			$scope.bank.account_number = json.account_number;;
		} )
	}

	$scope.tokenize = function(){

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
		var payload = { name: $scope.basicInfo.legal_name_payment,
										account_number: $scope.bank.account_number,
										routing_number: $scope.bank.routing_number };
		StaffPayInfoService.bankAccount( payload, function( json ){
			console.debug('Saving bank info response: ', arguments);
			if( json.href ){
				json.id_admin = $scope.basicInfo.id_admin;
				json.legal_name_payment = $scope.basicInfo.legal_name_payment;
				StaffPayInfoService.save_bank( json, function( data ){
					if( data.error ){
						App.alert( data.error);
						return;
					} else {
						document.activeElement.blur();
						load();
						$scope.isTokenizing = false;
						$scope.saved = true;
						$scope.bank.account_number = '';
						$scope.bank.routing_number = '';
						$scope.bank.showForm = false;
						$scope.flash.setMessage( 'Bank information saved!' );
						setTimeout( function() { $scope.saved = false; }, 1500 );
					}
				} );

			} else {
				App.alert( 'Error saving account! Please make sure you typed your account information correctly.' );
				$scope.isTokenizing = false;
			}
		} );
	}

	$scope.list = function(){
		$scope.navigation.link( '/staff/list' );
	}

	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller('DriversHelpCtrl', function() {});
NGApp.controller('DriversHelpCreditCardCtrl', function() {});

NGApp.controller('DriversWelcomeHomeCtrl', function() {});
NGApp.controller('DriversWelcomeInfoCtrl', function() {});
NGApp.controller('DriversWelcomeLocationCtrl', function() {});
NGApp.controller('DriversWelcomePushCtrl', function() {});
NGApp.controller('DriversWelcomeWahooCtrl', function() {});
