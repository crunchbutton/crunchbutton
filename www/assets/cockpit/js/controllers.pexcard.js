NGApp.controller( 'PexCardCtrl', function(){} );

NGApp.controller( 'PexCardReportCtrl', function ( $scope, $filter, PexCardService ) {

	$scope.range = {};

	$scope.newReport = true;

	var start = new Date();
	start.setDate( start.getDate() - 2 );
	$scope.range.start = start;

	var end = new Date();
	end.setDate( end.getDate() - 1 );
	$scope.range.end = end;

	$scope.result = null;

	$scope.report = function(){
		report( false );
	}


	$scope.isProcessing = true;

	PexCardService.report_dates( function( json ){
		$scope.isProcessing = false;
		$scope.dates = json;
		console.log('$scope.dates',$scope.dates);
	} );

	var report = function(){

		$scope.result = false;

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;

		var params = {  'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy') };

		PexCardService.report( params, function( json ){
			$scope.isProcessing = false;
			$scope.result = json;
		} );
	}

} );

NGApp.controller( 'PexCardReportOldCtrl', function ( $scope, $filter, PexCardService ) {

	$scope.range = {};

	var start = new Date();
	start.setDate( start.getDate() - 2 );
	$scope.range.start = start;

	var end = new Date();
	end.setDate( end.getDate() - 1 );
	$scope.range.end = end;

	$scope.result = null;

	$scope.report = function(){
		report( false );
	}

	$scope.import_data = function(){
		report( true );
	}

	var report = function( import_data ){

		$scope.result = false;

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;

		var params = {  'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy'),
										'import': import_data };

		PexCardService.report( params, function( json ){
			$scope.isProcessing = false;
			$scope.result = json;
		} );
	}

} );

NGApp.controller( 'PexCardReportOldCtrl', function ( $scope, $filter, PexCardService ) {

	$scope.range = {};

	var start = new Date();
	start.setDate( start.getDate() - 2 );
	$scope.range.start = start;

	var end = new Date();
	end.setDate( end.getDate() - 1 );
	$scope.range.end = end;

	$scope.result = null;

	$scope.report = function(){
		report( false );
	}

	$scope.import_data = function(){
		report( true );
	}

	var report = function( import_data ){

		$scope.result = false;

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;

		var params = {  'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy'),
										'import': import_data };

		PexCardService.report_old( params, function( json ){
			$scope.isProcessing = false;
			$scope.result = json;
		} );
	}

} );

NGApp.controller( 'PexCardIdCtrl', function ( $scope, $routeParams, $route, PexCardService) {

	$scope.submitted = false;
	$scope.isSearching = false;

	$scope.status = PexCardService.status;

	$scope._id_admin = false;

	$scope.search = function() {

		$scope.card = null;

		if( $scope.isSearching ){
			return;
		}

		if( $scope && $scope.form && $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSearching = false;
			return;
		}

		$scope.isSearching = true;

		PexCardService.pex_id( $scope.crunchbutton_card_id,
			function( json ){
				$scope.isSearching = false;
				$scope.submitted = false;
				if( json.id ){
					$scope.card = json;
					if( $scope._id_admin ){
						$scope.card.id_admin = $scope._id_admin;
					}
					console.log('$scope._id_admin',$scope._id_admin);
					console.log('$scope.card',$scope.card);
				} else {
					$scope.flash.setMessage( json.error, 'error' );
					$scope.crunchbutton_card_id = '';
				}
			}
		);
	};

	$scope.payinfo = function(){
		if( $scope.card.admin_login ){
			$scope.navigation.link( '/staff/' + $scope.card.admin_login + '/payinfo' );
		}
	}

	$scope.remove_assignment = function(){
		if( confirm( 'Confirm remove assignment?' ) ){
			PexCardService.admin_pexcard_remove( $scope.card.id, function( json ){
				if( json.success ){
					$scope.card.id_admin = null;
					$scope.card.admin_name = null;
					$scope.card.admin_login = null;
					$scope.flash.setMessage( 'Driver assigned removed!', 'success' );
				} else {
					$scope.flash.setMessage( 'Error removing assignment!', 'error' );
				}

			} );
		}
	}

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
						$scope.card = json;
						$scope.flash.setMessage( 'Card status changed to ' + status, 'success' );
					} else {
						$scope.flash.setMessage( json.error, 'error' );
					}
				}
			);
		}
	}

	$scope.assign = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.formAssign.$invalid ){
			$scope.assignedSubmitted = true;
			$scope.isSaving = false;
			return;
		}
		var last_four = $scope.card.cards[ 0 ].cardNumber;
		var data = { 'id_pexcard': $scope.card.id, 'id_admin': $scope.card.id_admin, 'card_serial': $scope.card.lastName, 'last_four': last_four };
		PexCardService.admin_pexcard( data, function( json ){
			$scope.isSaving = false;
			if( json.success ){
				$scope.card.admin_name = json.success.name;
				$scope.card.admin_login = json.success.login;
				$scope.flash.setMessage( 'Driver assigned!', 'success' );
			} else {
				$scope.flash.setMessage( 'Error assigning driver!', 'error' );
			}

		} );
	}

	PexCardService.list(function(json) {
		$scope.drivers = json;
	});

	if( $routeParams.id ){
		if( $route.current.driver ){
			setTimeout( function() {
				$scope._id_admin = parseInt( $routeParams.id );
				console.log('$scope._id_admin',$scope._id_admin);
			}, 50 );
		} else {
			setTimeout( function() {
				$scope.crunchbutton_card_id = parseInt( $routeParams.id );
				App.rootScope.$safeApply();
				$scope.search();
			}, 500 );
		}
	}

} );


NGApp.controller('PexCardLogViewCtrl', function ($scope, $routeParams, PexCardService) {

	$scope.loading = true;

	PexCardService.action( $routeParams.id, function( action ){
		$scope.action = action.success;
		$scope.loading = false;
	} );

} );

NGApp.controller('PexConfigCtrl', function ($scope, PexCardService) {

	$scope.yesNo = PexCardService.yesNo();

	$scope.business = { 'serial': '' };
	$scope.test = { 'serial': '' };

	var load = function(){
		PexCardService.config.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.business.cards = json.cards.business;
				$scope.test.cards = json.cards.test;
				$scope.ready = true;
			}
		} );
	}

	$scope.add_business = function(){

		if( $scope.idAdding ){
			return;
		}

		if( $scope.formBusiness.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.businessSubmitted = true;
			return;
		}
		$scope.idAdding = true;
		PexCardService.config.add_business( { 'serial' : $scope.business.serial }, function( data ){
			$scope.idAdding = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.business.serial = '';
				$scope.business.cards = data.cards.business;
				$scope.saved = true;
				$scope.flash.setMessage( 'Business card addedd!' );
			}
		} );
	}

	$scope.remove_business = function( id_config ){
		if( confirm( 'Confirm remove the Business Card?' ) ){
			PexCardService.config.remove_business( { 'id_config' : id_config }, function( data ){
				if( data.error ){
					App.alert( data.error);
					return;
				} else {
					$scope.business.cards = data.cards.business;
					$scope.flash.setMessage( 'Business card removed!' );
				}
			} );
		}
	}

	$scope.add_test = function(){

		if( $scope.idAdding ){
			return;
		}

		if( $scope.formTest.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.testSubmitted = true;
			return;
		}
		$scope.idAdding = true;
		console.log('$scope.test',$scope.test);
		PexCardService.config.add_test( { 'serial' : $scope.test.serial }, function( data ){
			$scope.idAdding = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.test.serial = '';
				$scope.test.cards = data.cards.test;
				$scope.saved = true;
				$scope.flash.setMessage( 'Test card addedd!' );
			}
		} );
	}

	$scope.remove_test = function( id_config ){
		if( confirm( 'Confirm remove the Test Card?' ) ){
			PexCardService.config.remove_test( { 'id_config' : id_config }, function( data ){
				if( data.error ){
					App.alert( data.error);
					return;
				} else {
					$scope.test.cards = data.cards.test;
					$scope.flash.setMessage( 'Test card removed!' );
				}
			} );
		}
	}

	$scope.save = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isSaving = true;
		PexCardService.config.save( $scope.config, function( data ){
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


	load();

} );

NGApp.controller('PexCardLogCtrl', function ($scope, PexCardService, ViewListService) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			status: 'all',
			type: 'all',
			_action: 'all'
		},
		update: function() {
			PexCardService.logs($scope.query, function(d) {
				$scope.logs = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('PexCardCardLogCtrl', function ($scope, PexCardService, ViewListService) {
	angular.extend( $scope, ViewListService );
	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'card_assign',
		},
		update: function() {
			PexCardService.cardlog($scope.query, function(d) {
				$scope.logs = d.results;
				$scope.complete(d);
			});
		}
	});
});
