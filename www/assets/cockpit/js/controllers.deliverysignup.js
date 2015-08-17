NGApp.controller( 'DeliverySignUpCtrl', function( $scope, DeliverySignUpService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.delivery = {};

	$scope.sending = false;

	$scope.restaurants = {};
	$scope.restaurants[ 'Taco Bell' ] = { 'name': 'Taco Bell', 'checked': false };
	$scope.restaurants[ 'McDonalds' ] = { 'name': 'McDonalds', 'checked': false };
	$scope.restaurants[ 'Burger King' ] = { 'name': 'Burger King', 'checked': false };
	$scope.restaurants[ 'In-N-Out' ] = { 'name': 'In-N-Out', 'checked': false };
	$scope.restaurants[ 'Other' ] = { 'name': 'Other', 'checked': false };


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