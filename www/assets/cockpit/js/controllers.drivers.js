NGApp.controller('DriversOrderCtrl', function ( $scope, DriverOrdersService ) {

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
					App.alert( 'Ops, error!\n It seems this order was already accepted ' + name + '!'  );
				}
			}
		);
	};

	$scope.pickedup = function() {
		$scope.makeBusy();
		DriverOrdersService.pickedup( $scope.order.id_order, function(){ load(); } );
	};

	$scope.delivered = function() {
		$scope.makeBusy();
		DriverOrdersService.delivered( $scope.order.id_order, function(){ load();	} );
	};

	$scope.reject = function() {
		$scope.makeBusy();
		DriverOrdersService.reject( $scope.order.id_order, function(){ load();	} );
	};

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller('DriversOrdersCtrl', function ( $scope, DriverOrdersService, MainNavigationService ) {

	$scope.show = { all : true };
	$scope.ready = false;

	$scope.filterOrders = function( order ){
		if( $scope.show.all ){
			return true;
		} else {
			if( order.lastStatus.id_admin == $scope.account.user.id_admin ){
				return true;
			}
		}
		return false;
	}

	$scope.list = function(){
		$scope.unBusy();
		DriverOrdersService.list( function( data ){
			$scope.driverorders = data;
			$scope.ready = true;
		} );
	}

	$scope.accept = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.accept( id_order,
			function( json ){
				if( json.status ) {
					$scope.list();
				} else {
					$scope.unBusy();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Ops, error!\n It seems this order was already accepted ' + name + '!'  );
					$scope.list();
				}
			}
		);
	};

	$scope.pickedup = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.pickedup( id_order, function(){ $scope.list(); } );
	};

	$scope.delivered = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.delivered( id_order, function(){ $scope.list();	} );
	};

	$scope.showOrder = function( id_order ){
		MainNavigationService.link( '/drivers/order/' + id_order );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		$scope.list();
	}
} );

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
		// $scope.makeBusy();
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

NGApp.controller( 'DriversOnboardingCtrl', function ( $scope, $timeout, DriverOnboardingService ) {

	$scope.ready = false;
	$scope.search = '';

	var list = function(){
		DriverOnboardingService.list( $scope.page, $scope.search, function( data ){
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.drivers = data.results;
			$scope.count = data.count;
			$scope.ready = true;
			$scope.focus( '#search' );
		} );
	}

	var waiting = false;

	$scope.$watch( 'search', function( newValue, oldValue, scope ) {
		if( newValue == oldValue || waiting ){
			return;
		}
		waiting = true;
		$timeout( function() {
			list();
			$scope.ready = false;
			waiting = false;
		}, 1 * 1000 );
	} );

	$scope.page = 1;

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	$scope.add = function( id_admin ){
		$scope.navigation.link( '/drivers/onboarding/new' );
	}

	$scope.edit = function( id_admin ){
		$scope.navigation.link( '/drivers/onboarding/' + id_admin );
	}

	list();

} );

NGApp.controller( 'DriversOnboardingFormCtrl', function ( $scope, $routeParams, $fileUploader, DriverOnboardingService, CommunityService ) {

	$scope.ready = false;
	$scope.submitted = false;

	var docs = function(){
		// Load the docs
		DriverOnboardingService.docs.list( $routeParams.id, function( data ){
			$scope.documents = data;
		} );
		docsPendency();
	}

	var docsPendency = function(){
		DriverOnboardingService.docs.pendency( $routeParams.id, function( data ){
			console.log('data',data);
		} );
	}

	var logs = function(){
		DriverOnboardingService.logs( $routeParams.id, function( data ){
			$scope.logs = data;
		} );
	}

	DriverOnboardingService.get( $routeParams.id, function( driver ){
		$scope.driver = driver;
		if( !$scope.driver.id_admin ){
			$scope.driver.notify = true;
		}
		// logs();
		docs();
		CommunityService.listSimple( function( data ){
			$scope.communities = data;
			$scope.ready = true;
		} );
	} );

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

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		DriverOnboardingService.save( $scope.driver, function( json ){
			if( json.success ){
				$scope.navigation.link( '/drivers/onboarding/' + json.success.id_admin );
				$scope.flash.setMessage( 'Driver saved!' );
				$scope.driver.pass = '';
			} else {
				$scope.flash.setMessage( 'Driver not saved: ' + json.error , 'error' );
			}
		} );
	}

	$scope.cancel = function(){
		$scope.navigation.link( '/drivers/onboarding/' );
	}

	// Upload control stuff
	$scope.doc_uploaded = 0;

	var uploader = $scope.uploader = $fileUploader.create({
									scope: $scope,
									url: '/api/driver/documents/upload/',
									filters: [ function( item ) { return true; } ]
								} );

	uploader.bind( 'success', function( event, xhr, item, response ) {
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
	});

	uploader.bind('error', function (event, xhr, item, response) {
		App.alert( 'Upload error, please try again or send us a message.' );
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

NGApp.controller( 'DriversDocsFormCtrl', function( $scope, $fileUploader, DriverOnboardingService ) {

	$scope.ready = false;

	var docs = function(){
		// Load the docs
		DriverOnboardingService.docs.list( $scope.account.user.id_admin, function( data ){
			$scope.documents = data;
			$scope.ready = true;
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

} );

NGApp.controller( 'PreOnboardingCtrl', function( $scope, PreOnboardingService, CommunityService ) {

	$scope.ready = false;
	$scope.submitted = false;

	CommunityService.listSimple( function( data ){
		$scope.communities = data;
		$scope.ready = true;
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

NGApp.controller('DriversHelpCtrl', function() {

});
