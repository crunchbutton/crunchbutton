NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/staff', {
			action: 'staff',
			controller: 'StaffCtrl',
			templateUrl: 'assets/view/staff.html',
			reloadOnSearch: false
		})
		.when('/staff/marketing/new', {
			action: 'staff',
			controller: 'StaffMarketingFormCtrl',
			templateUrl: 'assets/view/staff-marketing-form.html'
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
		.when('/staff/marketing-rep/faq', {
			action: 'marketing-rep-help',
			controller: 'StaffMarketingFaqCtrl',
			templateUrl: 'assets/view/staff-marketing-rep-help.html'
		})
		.when('/staff/marketing-rep/docs', {
			action: 'marketing-rep-docs',
			controller: 'StaffMarketingDocsCtrl',
			templateUrl: 'assets/view/staff-marketing-docs.html'
		})
		.when('/staff/marketing-rep/docs/payment', {
			action: 'marketing-rep-docs',
			controller: 'DriversPaymentFormCtrl',
			templateUrl: 'assets/view/drivers-payment-info-form.html'
		});
}]);


NGApp.controller('StaffMarketingFaqCtrl',function(){});

NGApp.controller('StaffInfoCtrl', function ($rootScope, $scope, $routeParams, $location, StaffService, MapService) {
	$scope.staff = null;
	$scope.map = null;
	$scope.loading = true;
	var marker;

	StaffService.get($routeParams.id, function(staff) {
		$rootScope.title = staff.name + ' | Staff';
		$scope.staff = staff;
		$scope.loading = false;
	});

	StaffService.locations($routeParams.id, function(d) {
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
			fullcount: false
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

	if( $scope.account.isLoggedIn() ){
		load();
	}

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


NGApp.controller( 'StaffMarketingFormCtrl', function ( $scope, $routeParams, $filter, FileUploader, StaffService, CommunityService ) {

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

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		$scope.isSaving = true;

		StaffService.marketing.save( $scope.staff, function( json ){

			if( json.success ){

				var url = '/staff/marketing/' + json.success.id_admin;

				if( $scope.staff.id_admin ){
					$scope.reload();
				} else {
					$scope.navigation.link( url );
				}

				setTimeout( function(){
					App.alert( 'Marketing rep saved!' );
				}, 500 );

				$scope.isSaving = false;

			} else {
				App.alert( 'Marketing rep not saved: ' + json.error , 'error' );
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

NGApp.controller('StaffPayInfoCtrl', function( $scope, $filter, StaffPayInfoService ) {

	$scope.bank = { 'showForm': true };
	$scope.payInfo = {};

	$scope.yesNo = StaffPayInfoService.typesUsingPex();

	var load = function(){
		StaffPayInfoService.load( function( json ){
			if( json.id_admin ){
				$scope.payInfo = json;
				if( json.balanced_bank ){
					$scope.bank.showForm = false;
				}
				$scope.payInfo.using_pex = parseInt( $scope.payInfo.using_pex );
				$scope.ready = true;
				$scope.payment = {};
				if( json.using_pex_date ){
					$scope.payInfo.using_pex_date = new Date( json.using_pex_date );
				}
				if( json.date_terminated ){
					$scope.payInfo.date_terminated = new Date( json.date_terminated );
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
				return;
			} else {
				load();
				$scope.saved = true;
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
		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}
		$scope.isTokenizing = true;
		var payload = { name: $scope.payInfo.legal_name_payment,
										account_number: $scope.bank.account_number,
										routing_number: $scope.bank.routing_number };
		StaffPayInfoService.bankAccount( payload, function( json ){
			if( json.href ){
				json.legal_name_payment = $scope.payInfo.legal_name_payment;
				StaffPayInfoService.save_bank( json, function( data ){
					if( data.error ){
						App.alert( data.error);
						return;
					} else {
						load();
						$scope.isTokenizing = false;
						$scope.saved = true;
						setTimeout( function() { $scope.saved = false; }, 1500 );
					}
				} );

			} else {
				App.alert( 'Error!' );
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