NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/staff', {
			action: 'staff',
			controller: 'StaffCtrl',
			templateUrl: 'assets/view/staff.html',
			reloadOnSearch: false
		})
		.when('/staff/marketing/:id', {
			action: 'staff',
			controller: 'StaffMarketingFormCtrl',
			templateUrl: 'assets/view/staff-marketing-form.html'
		})
		.when('/staff/:id', {
			action: 'staff',
			controller: 'StaffInfoCtrl',
			templateUrl: 'assets/view/staff-staff.html'
		})
		.when('/staff/:id/payinfo', {
			action: 'staff',
			controller: 'StaffPayInfoCtrl',
			templateUrl: 'assets/view/staff-payinfo.html'
		})
		.when('/staff/:id/pexcard', {
			action: 'staff',
			controller: 'StaffPexCardCtrl',
			templateUrl: 'assets/view/staff-pexcard.html'
		})
		.when('/staff/:id/permission', {
			action: 'staff',
			controller: 'StaffPermissionCtrl',
			templateUrl: 'assets/view/staff-permission.html'
		})
		.when('/staff/marketing-rep/faq', {
			action: 'marketing-rep-help',
			controller: 'StaffMarketingFaqCtrl',
			templateUrl: 'assets/view/staff-marketing-rep-help.html'
		})
		.when('/staff/marketing-rep/request-materials', {
			action: 'marketing-rep-request-materials',
			controller: 'StaffMarketingRequestMaterialsCtrl',
			templateUrl: 'assets/view/staff-marketing-rep-request-materials.html'
		})
		.when('/staff/marketing-rep/activations', {
			action: 'marketing-rep-activations',
			controller: 'StaffMarketingActivationsCtrl',
			templateUrl: 'assets/view/staff-marketing-rep-activations.html'
		})
		.when('/staff/marketing-rep/docs', {
			action: 'marketing-rep-docs',
			controller: 'StaffMarketingDocsCtrl',
			templateUrl: 'assets/view/staff-marketing-docs.html'
		})
		.when('/staff/marketing-rep/docs/payment', {
			action: 'marketing-rep-docs',
			controller: 'DriversPaymentFormCtrl',
			templateUrl: 'assets/view/staff-payment-info-form.html'
		});
}]);


NGApp.controller('StaffMarketingFaqCtrl',function( $scope ){

	$scope.$watch( 'account', function( newValue, oldValue, scope ) {
		if( $scope.account.user ){
			$scope.referral_customer_credit = $scope.account.user.referral_customer_credit;
			$scope.referral_admin_credit = $scope.account.user.referral_admin_credit;
		}
	}, true);
});


NGApp.controller( 'StaffMarketingRequestMaterialsCtrl', function(  $scope, StaffService ){});

NGApp.controller('StaffMarketingActivationsCtrl',function( $scope, StaffService ){

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

	$scope.change_status = function(){
		var params = { id_admin: $scope.staff.id_admin, active: $scope.staff.active };
		StaffService.change_status( params, function(){
			load();
		} );
	}

	var load = function(){
		StaffService.get($routeParams.id, function(staff) {
			$rootScope.title = staff.name + ' | Staff';
			$scope.staff = staff;
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

	$scope.reverify = function( callback ) {
		StaffService.reverify( $scope.staff.id_admin, function( data ) {
			if (data.status.status == 'unverified') {
				App.alert('Could not finish verification. Missing fields: ' + data.status.fields.join(','));
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

NGApp.controller('StaffCtrl', function ($scope, StaffService, ViewListService) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			fullcount: true
		},
		update: function() {
			StaffService.list($scope.query, function(d) {
				$scope.staff = d.results;
				$scope.complete(d);
			});
		}
	});
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
					$scope.flash.setMessage( 'Driver assigned removed!', 'success' );
				} else {
					$scope.flash.setMessage( 'Error removing assignment!', 'error' );
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
			if( data.error ){
				App.alert( data.error);
				$scope.isAdding = false;
				return;
			} else {
				$scope.flash.setMessage( 'Funds Added!' );
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

	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		var id_driver_document = data.id_driver_document;
		var response = data.response;
		if( response.success ){
			var doc = { id_admin : $scope.account.user.id_admin, id_driver_document : id_driver_document, file : response.success };
			StaffService.marketing.docs.save( doc, function( json ){
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
		StaffService.marketing.docs.download( id_driver_document_status );
	}

	docs();

} );


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
		var id_driver_document = data.id_driver_document;
		var response = data.response;
		if( response.success ){
			var doc = { id_admin : $scope.staff.id_admin, id_driver_document : id_driver_document, file : response.success };
			StaffService.marketing.docs.save( doc, function( json ){
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

		GroupService.list( { 'limit': 'none' }, function( data ) {
			$scope.groups = data.results;
			$scope.ready = true;
		} );

		$scope.assigned.groups = [];

		if( data.groups ){
			angular.forEach( data.groups, function(name, id_group) {
				$scope.assigned.groups.push( id_group );
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
				$scope.payInfo.using_pex = parseInt( $scope.payInfo.using_pex );
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
		if( parseInt( $scope.payInfo.using_pex ) == 0 ){
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
