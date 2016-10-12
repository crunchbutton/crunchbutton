NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/staff', {
			action: 'staff',
			controller: 'StaffCtrl',
			templateUrl: '/assets/view/staff.html',
			reloadOnSearch: false
		})
		.when('/staff/notes', {
			action: 'staff',
			controller: 'StaffNotesCtrl',
			templateUrl: '/assets/view/staff-notes.html'
		})
		.when('/staff/activations', {
			action: 'activations',
			controller: 'StaffActivationsCtrl',
			templateUrl: '/assets/view/staff-activations.html'
		})
		.when('/staff/marketing/:id', {
			action: 'staff',
			controller: 'StaffMarketingFormCtrl',
			templateUrl: '/assets/view/staff-marketing-form.html'
		})
		.when('/staff/community-director/:id', {
			action: 'staff',
			controller: 'StaffCommunityDirectorFormCtrl',
			templateUrl: '/assets/view/staff-community-director-form.html'
		})
		.when('/staff/:id', {
			action: 'staff',
			controller: 'StaffInfoCtrl',
			templateUrl: '/assets/view/staff-staff.html'
		})
		.when('/staff/:id/payinfo', {
			action: 'staff',
			controller: 'StaffPayInfoCtrl',
			templateUrl: '/assets/view/staff-payinfo.html'
		})
		.when('/staff/:id/pexcard', {
			action: 'staff',
			controller: 'StaffPexCardCtrl',
			templateUrl: '/assets/view/staff-pexcard.html'
		})
		.when('/staff/:id/permission', {
			action: 'staff',
			controller: 'StaffPermissionCtrl',
			templateUrl: '/assets/view/staff-permission.html'
		})
		.when('/staff/marketing-rep/faq', {
			action: 'marketing-rep-help',
			controller: 'StaffMarketingFaqCtrl',
			templateUrl: '/assets/view/staff-marketing-rep-help.html'
		})
		.when('/staff/marketing-rep/request-materials', {
			action: 'marketing-rep-request-materials',
			controller: 'StaffMarketingRequestMaterialsCtrl',
			templateUrl: '/assets/view/staff-marketing-rep-request-materials.html'
		})
		.when('/staff/marketing-rep/docs', {
			action: 'marketing-rep-docs',
			controller: 'StaffMarketingDocsCtrl',
			templateUrl: '/assets/view/staff-marketing-docs.html'
		})
		.when('/staff/marketing-rep/docs/payment', {
			action: 'marketing-rep-docs',
			controller: 'DriversPaymentFormCtrl',
			templateUrl: '/assets/view/staff-payment-info-form.html'
		});
}]);


NGApp.controller('StaffMarketingFaqCtrl',function( $scope ){

	$scope.$watch( 'account', function( newValue, oldValue, scope ) {
		if( $scope.account.user ){
			referral_customer_credit = parseInt( $scope.account.user.referral_customer_credit );
			referral_admin_credit = parseInt( $scope.account.user.referral_admin_credit );
			$scope.referral_customer_credit = referral_customer_credit.toFixed(2);
			$scope.referral_admin_credit = referral_admin_credit.toFixed(2);
			$scope.is_campus_manager = $scope.account.user.isCampusManager;
			$scope.profit_percent = $scope.account.user.profit_percent;
		}
	}, true);
});


NGApp.controller( 'StaffMarketingRequestMaterialsCtrl', function(  $scope, StaffService ){});

NGApp.controller('StaffActivationsCtrl',function( $scope, StaffService ){

	$scope.loading = true;

	StaffService.activations( function( json ){
		$scope.loading = false;
		$scope.activations = json;
	} );
});


NGApp.controller('StaffInfoCtrl', function ($rootScope, $scope, $routeParams, $location, StaffService, MapService, DriverShiftsService) {

	$scope.staff = null;
	$scope.map = null;
	$scope.loading = true;
	var marker;

	$rootScope.$on( 'AssignmentgFinished', function(e, data) {
		load();
	});


	$scope.openStaffNoteContainer = function( id_admin ){
		$rootScope.$broadcast( 'openStaffNoteContainer', id_admin );
	}

	$rootScope.$on( 'staffNoteSaved', function(e, data) {
		$scope.staff.note = data;
	});

	$scope.change_status = function(){
		var params = { id_admin: $scope.staff.id_admin, active: $scope.staff.active };
		StaffService.change_status( params, function(){
			load();
		} );
	}

	$scope.makeCommunityDirector = function(){
		var params = { id_admin: $scope.staff.id_admin, active: $scope.staff.active };
		StaffService.makeCommunityDirector( params, function(){
			load();
		} );
	}

	$scope.change_down_to_help_notifications = function(){
		var params = { id_admin: $scope.staff.id_admin };
		StaffService.change_down_to_help_notifications( params, function(){
			load();
		} );
	}

	var load = function(){
		StaffService.get($routeParams.id, function(staff) {
			$rootScope.title = staff.name + ' | Staff';
			$scope.staff = staff;
			$scope.isCommunityDirector = staff.isCommunityDirector;
			$scope.loading = false;
		});
	}

	load();

	$scope.assignGroups = function(){
		$(':focus').blur();
		$rootScope.$broadcast( 'GroupsAssign', { 'groups': $scope.staff.groups } );
	}

	$scope.assignCommunities = function(){
		$(':focus').blur();
		$rootScope.$broadcast( 'CommunitiesAssign', { 'communities': $scope.staff.communities } );
	}

	$scope.sendDriverLicenceToStripe = function(callback, e){
		StaffService.sendDriverLicenceToStripe( $scope.staff.id_admin, function( data ) {
			if(data && data.file_id){
				App.alert("Driver's licence sent to Stripe!<br>");
			} else {
				App.alert("Error sending driver's licence to Stripe!<br>");
			}
			if( callback ){ callback(); }
			// load();
		});

	}

	$scope.reverify = function( callback, e) {
		StaffService.reverify( $scope.staff.id_admin, e.altKey ? true : false, function( data ) {
			if (data.status.status == 'unverified') {
				var error = 'Could not finish verification. ';
				if (!data.status.ssn) {
					error += 'Missing SSN. ';
				}
				if (data.status.contacted) {
					error += 'Contacted. Requires manual fix. Check email. ';
				}
				if (data.status.fields) {
					error += 'Missing fields: ' + data.status.fields.join(',');
				}
				App.alert(error);
			} else if (!data.status.status || data.status.status == 'pending'){
				App.alert('Could not finish verification. Check bank account information.');
			} else {
				App.alert('Looks like it might have reverified successfully.');
				load();
			}
			if( callback ){ callback(); }
		});
	};

	StaffService.locations($routeParams.id, function(d) {
		$scope.locations = d;
		update();
	});

	$scope.$watch('staff', function() {
		update();
	});

	$scope.$watch('map', function() {
		//update();
	});

	$scope.chat = function(){
		StaffService.chat( $routeParams.id, function( json ){
			if( json.id_support ){
				$scope.navigation.link( '/ticket/' + json.id_support );
			} else {
				App.alert( 'Ops, an error has occurred!' );
			}
		} )
	}

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

	$scope.shift_checkin = function( id_admin_shift_assign ){
		var success = function(){
			DriverShiftsService.shift_checkin( id_admin_shift_assign, function( json ){
				if( json.success ){
					load();
				} else {
					App.alert( 'Error!' );
				}
			} );
		}
		var fail = function(){}
		App.confirm( 'Confirm checkin?' , 'Checking', success, fail, null, true);
	}

});

NGApp.controller('StaffCtrl', function ($rootScope, $scope, StaffService, ViewListService, CommunityService) {

	angular.extend( $scope, ViewListService );

	$scope.openStaffNoteContainer = function( id_admin ){
		$rootScope.$broadcast( 'openStaffNoteContainer', id_admin );
	}

	$rootScope.$on( 'staffNoteSaved', function(e, data) {
		$scope.staff.note = data;
	});


	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			community: '',
			fullcount: true
		},
		update: function() {
			StaffService.list($scope.query, function(d) {
				$scope.staff = d.results;
				$scope.complete(d);
			});
		}
	});

	$scope.show_more_options = false;

	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options) {

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
		$rootScope.$broadcast('search-toggle');
	}

	$scope.$watch( 'query.community', function( newValue, oldValue, scope ) {
		_staffType();
	});

	var _staffType = function(){
		var staffType = [];
		staffType.push( { 'value': 'all', 'label': 'All' } );
		staffType.push( { 'value': 'driver', 'label': 'Drivers' } );
		staffType.push( { 'value': 'brand-representative', 'label': 'Brand Representative' } );
		staffType.push( { 'value': 'comm-director', 'label': 'Community Director' } );
		if( !$scope.query.community ){
			staffType.push( { 'value': 'community-manager', 'label': 'Community Manager' } );
		} else {
			if( $scope.query.type == 'community-manager' ){
				$scope.query.type = 'marketing-rep';
			}
		}
		$scope.staffType = staffType;
	}

	_staffType();

});


NGApp.controller('StaffPexCardCtrl', function( $scope, StaffPayInfoService, PexCardService ) {

	$scope.status = PexCardService.status;

	$scope.open_card = function( id_card ){
		change_card_status( id_card, PexCardService.status.OPEN );
	}

	$scope.block_card = function( id_card ){
		change_card_status( id_card, PexCardService.status.BLOCKED );
	}

	var change_card_status = function( id_card, status ){
		if( confirm( 'Confirm change card status to ' + status + '?' ) ){
			PexCardService.pex_change_card_status( { id_card: id_card, status: status },
				function( json ){
					if( json.id ){
						for( x in $scope.payInfo.cards ){
							if( $scope.payInfo.cards[ x ].id == json.id ){
								$scope.payInfo.cards[ x ] = json;
							}
						}
						$scope.flash.setMessage( 'Card status changed to ' + status, 'success' );
					} else {
						$scope.flash.setMessage( json.error, 'error' );
					}
				}
			);
		}
	}

	$scope.pexcard = {};

	$scope.remove_assignment = function( pexcard_id ){
		if( confirm( 'Confirm remove assignment?' ) ){
			PexCardService.admin_pexcard_remove( pexcard_id, function( json ){
				if( json.success ){
					load();
					App.alert( 'Driver assigned removed!', 'success' );
				} else {
					App.alert( 'Error removing assignment!', 'error' );
				}
			} );
		}
	}

	$scope.add_funds = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isAdding = true;
		PexCardService.add_funds( $scope.pexcard, function( data ){
			$scope.isAdding = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				App.alert( 'Funds Added!' );
				$scope.submitted = false;
				$scope.pexcard = {};
				setTimeout( function(){ load(); $scope.isAdding = false; }, 1000 );
			}
		} );
	}

	var load = function(){
		StaffPayInfoService.pexcard( function( json ){
			if( json.id_admin ){
				$scope.payInfo = json;
				$scope.ready = true;
			} else {
				App.alert( json.error );
			}
		} )
	}

	load();

} );

NGApp.controller( 'StaffMarketingDocsCtrl', function ( $scope, $routeParams, $filter, FileUploader, StaffService, CommunityService ) {

	$scope.status = {};

	var docs = function(){

		if( $scope.account && $scope.account.user && $scope.account.user.id_admin ){
			StaffService.status( $scope.account.user.id_admin, function(data) {
				if (data.payment == true) {
					$scope.status.paymentinfo = true;
				}
			});
			StaffService.marketing.docs.list( $scope.account.user.id_admin, function( data ){
				$scope.documents = data;
				$scope.ready = true;
				$scope.status.docs = true;
				angular.forEach($scope.documents, function(doc, x) {
					if ($scope.documents[x].url && (!$scope.documents[x].status || $scope.documents[x].status.file.substring(9, 14) == 'blank')) {
						$scope.status.docs = false;
					}
				});
			} );

		}
		else {
			setTimeout( function(){ docs() }, 100 );
		}
	}

	// this is a listener to upload error
	$scope.$on( 'driverDocsUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );
// aqui
	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		if( data.success ){
			App.alert( 'File saved!' );
			docs();
		} else {
			App.alert( 'Upload error, please try again or send us a message.' );
		}
	});


	// Upload control stuff
	$scope.doc_uploaded = 0;
	var uploader = $scope.uploader = new FileUploader({
		url: '/api/driver/documents/upload/'
	});

	$scope.download = function( id_driver_document_status ){
		StaffService.marketing.docs.download( id_driver_document_status );
	}

	docs();

} );

NGApp.controller( 'StaffCommunityDirectorFormCtrl', function ( $scope, $routeParams, $filter, StaffService, CommunityService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.isSaving = false;
	$scope.action = null;

	var start = function(){

		$scope.yesNo = CommunityService.yesNo();

		$scope.action = ( $routeParams.id == 'new' ) ? 'new' : 'edit';

		CommunityService.listSimple( function( data ){
			$scope.communities = data;
			$scope.ready = true;
		} );

		if($routeParams.id && $routeParams.id != 'new'){
			StaffService.communityDirector.load( $routeParams.id, function( staff ){
				if( !staff.id_admin ){
					$scope.navigation.link( '/staff/marketing/new' );
					return;
				}
				$scope.staff = staff;
			} );
		}

		$scope._yesNo = StaffService.yesNo();

	}

	start();

	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid || !$scope.staff.id_community ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		$scope.isSaving = true;

		StaffService.communityDirector.save( $scope.staff, function( json ){

			if( json.success ){

				var url = '/staff/community-director/' + json.success.id_admin;

				if( $scope.staff.login ){
					$scope.navigation.link( '/staff/' + json.success.login );
				} else {
					$scope.navigation.link( url );
				}

				$scope.isSaving = false;

			} else {
				App.alert( 'Brand Representative not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}
});


NGApp.controller( 'StaffMarketingFormCtrl', function ( $scope, $routeParams, $filter, FileUploader, StaffService, CommunityService, CustomerRewardService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.isSaving = false;
	$scope.action = null;

	var docs = function(){
		StaffService.marketing.docs.list( $scope.staff.id_admin, function( data ){
			$scope.documents = data;
		} );
	}

	$scope.approve = function( doc ){
		var approve = ( doc.approved ) ? false : true;
		StaffService.marketing.docs.approve( doc.id_driver_document_status, approve, function( data ){
			docs();
		} );
	}

	$scope.remove = function( id_driver_document_status ){
		if( confirm( 'Confirm remove document?' ) ){
			StaffService.marketing.docs.remove( id_driver_document_status, function( data ){
				docs();
			} );
		}
	}

	var logs = function(){
		DriverOnboardingService.logs( $routeParams.id, function( data ){
			$scope.logs = data;
		} );
	}

	var start = function(){

		$scope.yesNo = CommunityService.yesNo();

		$scope.action = ( $routeParams.id == 'new' ) ? 'new' : 'edit';

		if( $scope.action == 'edit' ){
			StaffService.marketing.load( $routeParams.id, function( staff ){
				if( !staff.id_admin ){
					$scope.navigation.link( '/staff/marketing/new' );
					return;
				}
				$scope.staff = staff;
				docs();
			} );
		} else {

			CustomerRewardService.reward.config.load( function( d ){
				$scope.staff = { 	referral_admin_credit: d[ CustomerRewardService.constants.key_admin_refer_user_amt ],
													referral_customer_credit: d[ CustomerRewardService.constants.key_customer_get_referred_amt ] };
			} );

		}

		CommunityService.listSimple( function( data ){
			$scope.communities = data;
			$scope.ready = true;
		} );

		$scope._yesNo = StaffService.yesNo();

	}

	// method save that saves the driver
	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid || !$scope.staff.id_community ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		$scope.isSaving = true;

		StaffService.marketing.save( $scope.staff, function( json ){

			if( json.success ){

				var url = '/staff/marketing/' + json.success.id_admin;

				if( $scope.staff.login ){
					$scope.navigation.link( '/staff/' + json.success.login );
				} else {
					$scope.navigation.link( url );
				}

				$scope.isSaving = false;

			} else {
				App.alert( 'Brand Representative not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}

	$scope.cancel = function(){
		$scope.navigation.link( '/staff/' );
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

	// Upload control stuff
	$scope.doc_uploaded = 0;
	var uploader = $scope.uploader = new FileUploader({
		url: '/api/driver/documents/upload/'
	});

	$scope.download = function( id_driver_document_status ){
		StaffService.marketing.docs.download( id_driver_document_status );
	}

	start();

} );


NGApp.controller('StaffCommunityCtrl', function( $scope, $routeParams, $rootScope, CommunityService, StaffService ) {

	$scope.assigned = {};

	$rootScope.$on( 'CommunitiesAssign', function(e, data ) {

		App.dialog.show( '.assign-communities-container' );

		CommunityService.listSimple( function( data ) {
			$scope.ready = true;
			$scope.communities = data;
		} );

		$scope.assigned.communities = [];

		if( data.communities ){
			angular.forEach( data.communities, function(name, id_community) {
				$scope.assigned.communities.push( parseInt( id_community ) );
			} );
		}

	} );

	$scope.save = function(){
		var params = {};
		params.communities = $scope.assigned.communities;
		params.id_admin = $routeParams.id;
		StaffService.community( params, function( json ){
			if( json.success ){
				App.alert( 'Communities assigned!' );
				$rootScope.$broadcast( 'AssignmentgFinished' );
			} else {
				App.alert( 'Error assigning communities!' );
			}
		} );
	}

} );

NGApp.controller('StaffGroupCtrl', function( $scope, $routeParams, $rootScope, GroupService, StaffService ) {

	$scope.assigned = {};

	$rootScope.$on( 'GroupsAssign', function(e, data) {


		App.dialog.show( '.assign-groups-container' );

		GroupService.list( { 'limit': 'none', 'active-only': true }, function( data ) {
			$scope.groups = data.results;
			$scope.ready = true;
		} );

		$scope.assigned.groups = [];

		if( data.groups ){
			angular.forEach( data.groups, function(name, id_group) {
				$scope.assigned.groups.push( parseInt(id_group) );
			} );
		}

	} );

	$scope.save = function(){
		var params = {};
		params.groups = $scope.assigned.groups;
		params.id_admin = $routeParams.id;
		StaffService.group( params, function( json ){
			if( json.success ){
				App.alert( 'Groups assigned!' );
				$rootScope.$broadcast( 'AssignmentgFinished' );
			} else {
				App.alert( 'Error assigning groups!' );
			}
		} );
	}

} );

NGApp.controller('StaffPayInfoCtrl', function( $scope, $filter, StaffPayInfoService, ConfigService ) {

	$scope.bank = { 'showForm': true };
	$scope.payInfo = {};

	$scope.yesNo = StaffPayInfoService.typesUsingPex();

	var load = function(){
		StaffPayInfoService.load( function( json ){
			if( json.id_admin ){
				$scope.payInfo = json;
				$scope.payInfo.using_pex = $scope.payInfo.using_pex;
				$scope.ready = true;
				$scope.payment = {};
				if( json.using_pex_date ){
					$scope.payInfo.using_pex_date = new Date( json.using_pex_date );
				}
				if( json.date_terminated ){
					$scope.payInfo.date_terminated = new Date( json.date_terminated );
				}

				if(json.stripe_id && json.stripe_account_id ){
					$scope.bank.showForm = false;
				}
				$scope.payment._methods = StaffPayInfoService.methodsPayment();
				$scope.payment._using_pex = StaffPayInfoService.typesUsingPex();
				$scope.payment._types = StaffPayInfoService.typesPayment();
			} else {
				App.alert( json.error );
			}
		} )
	}

	var using_pex_date = null;

	$scope.$watch( 'payInfo.using_pex', function( newValue, oldValue, scope ) {
		if(!$scope.payInfo.using_pex){
			using_pex_date = $scope.payInfo.using_pex_date;
			//
			$scope.payInfo.using_pex_date = '';
		} else {
			if( !$scope.payInfo.using_pex_date ){
				$scope.payInfo.using_pex_date = new Date();
			}
		}

	});

	$scope.save = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		if( $scope.payInfo.using_pex_date && !isNaN( $scope.payInfo.using_pex_date.getTime() ) ){
			$scope.payInfo.using_pex_date_formatted = $filter( 'date' )( $scope.payInfo.using_pex_date, 'yyyy-MM-dd' )
		} else {
			$scope.payInfo.using_pex_date_formatted = null;
		}

		if( $scope.payInfo.date_terminated && !isNaN( $scope.payInfo.date_terminated.getTime() ) ){
			$scope.payInfo.date_terminated_formatted = $filter( 'date' )( $scope.payInfo.date_terminated, 'yyyy-MM-dd' )
		} else {
			$scope.payInfo.date_terminated_formatted = null;
		}

		$scope.isSaving = true;
		StaffPayInfoService.save( $scope.payInfo, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				$scope.isSaving = false;
				return;
			} else {
				load();
				$scope.saved = true;
				setTimeout( function() { $scope.saved = false; }, 500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		StaffPayInfoService.bankInfoTest( function( json ){
			$scope.bank.routing_number = json.routing_number; ;
			$scope.bank.account_number = json.account_number;;
		} )
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
					'id_admin': $scope.payInfo.id_admin
				};
				StaffPayInfoService.save_stripe_bank( params, function( d ){
					if( d.id_admin ){
						bank_info_saved();
					} else {
						var error = d.error ? d.error : '';
						App.alert( 'Error: ' + error );
					}
				} );
			} else {
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

	$scope.tokenize = function(){

		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}

		$scope.isTokenizing = true;
		stripe();
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

NGApp.controller('StaffAddNoteCtrl', function ($scope, $routeParams, $rootScope, StaffService ) {

	$rootScope.$on( 'openStaffNoteContainer', function(e, data) {
		$scope.note = {};
		$scope.note.id_admin = data;
		App.dialog.show('.admin-note-container');
		$scope.isSavingNote = false;
		$scope.formStaffAddNoteSubmitted = false;

		if( !$scope.note.id_admin ){
			if( !$scope.staff ){
				StaffService.active( function( json ){
					$scope.staff = json;
				} );
			}
		} else {
			// StaffService.note( $scope.note.id_admin, function( json ){
			// $scope.note.text = json.text;
			// } );
		}
	});

	$scope.formStaffAddNoteSave = function(){

		if( $scope.formStaffAddNote.$invalid ){
			$scope.formStaffAddNoteSubmitted = true;
			return;
		}

		if( !$scope.note.id_admin ){
			App.alert( 'Please select an admin!' );
			return;
		}

		StaffService.save_note( $scope.note, function( json ){
			$scope.isSavingNote = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$rootScope.closePopup();
				$rootScope.$broadcast( 'staffNoteSaved', json );
			}
			$scope.formStaffAddNoteSubmitted = false;
		} );
	}

} );


NGApp.controller('StaffNotesCtrl', function ($scope, $rootScope, ViewListService, StaffService) {

	$rootScope.title = 'Staff Notes';

	$scope.show_more_options = false;

	$scope.openStaffNoteContainer = function(){
		$rootScope.$broadcast( 'openStaffNoteContainer', null );
	}

	$rootScope.$on( 'staffNoteSaved', function(e, data) {
		update();
	});

	$scope.moreOptions = function(){

		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options ){
			if( !$scope.staff ){
				StaffService.notes_list( function( json ){
					$scope.staff = json;
				} );
			}
		}
	}

	angular.extend($scope, ViewListService);

	var update = function() {
			StaffService.notes( $scope.query, function(d) {
				$scope.notes = d.results;
				$scope.complete(d);
					if( ( $scope.query.admin ) && !$scope.show_more_options ){
						$scope.moreOptions();
					}
			});
		}

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			admin: '',
			added_by: '',
			fullcount: false
		},
		update: update
	});
});
