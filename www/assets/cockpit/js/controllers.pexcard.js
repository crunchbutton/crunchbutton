NGApp.controller( 'PexCardCtrl', function(){} );

NGApp.controller( 'PexCardReportCtrl', function ( $scope, $filter, PexCardService ) {

	$scope.range = {};

	var start = new Date();
	start.setDate( start.getDate() - 2 );
	$scope.range.start = start;

	var end = new Date();
	end.setDate( end.getDate() - 1 );
	$scope.range.end = end;

	$scope.result = null;

	$scope.report = function(){

		$scope.result = false;

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy') };

		PexCardService.report( params, function( json ){
			$scope.isProcessing = false;
			$scope.result = json;
		} );

	}

} );

NGApp.controller( 'PexCardIdCtrl', function ( $scope, $routeParams, PexCardService, DriverOnboardingService ) {

	$scope.submitted = false;
	$scope.isSearching = false;

	$scope.status = PexCardService.status;

	$scope.search = function() {

		$scope.card = null;

		if( $scope.isSearching ){
			return;
		}

		if( $scope.form.$invalid ){
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

	DriverOnboardingService.pexcard( function( json ){ $scope.drivers = json; } );

	if( $routeParams.id ){
		setTimeout( function() {
			$scope.crunchbutton_card_id = parseInt( $routeParams.id );
			App.rootScope.$safeApply();
			$scope.search();
		}, 500 );
	}

} );


NGApp.controller('PexCardLogViewCtrl', function ($scope, $routeParams, PexCardService) {

	$scope.loading = true;

	PexCardService.action( $routeParams.id, function( action ){
		$scope.action = action.success;
		$scope.loading = false;
	} );

} );

NGApp.controller('PexCardLogCtrl', function ($scope, PexCardService, ViewListService) {

	angular.extend( $scope, ViewListService );

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
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
