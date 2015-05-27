NGApp.controller( 'CreditDialogCtrl', function ( $scope, $rootScope, CustomerService, CreditService ) {


	$rootScope.$on('creditDialog', function(e, id_user) {

		$scope.isLoading = true;

		$(':focus').blur();

		$scope.credit = {};

		$scope.formCreditSubmitted = false;
		$scope.isSaving = false;

		CustomerService.get( id_user, function( json ){
			$scope.credit.id_user = json.id_user;
			$scope.credit.name = json.name;
			App.dialog.show('.credit-dialog');

			$scope.isLoading = false;

		} );

		$scope.complete = $rootScope.closePopup;

	});

	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.formCredit.$invalid ){
			$scope.formCreditSubmitted = true;
			$scope.isSaving = false;
			return;
		}

		$scope.isSaving = true;

		CreditService.add( $scope.credit, function( json ){
			if( json.success ){
				App.alert( json.success );
			} else {
				App.alert( 'Error: ' + json.error , 'error' );
			}
			$scope.isSaving = false;
		} );
	}
});