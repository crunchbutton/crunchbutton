NGApp.controller('PexCardIdCtrl', function ( $scope, PexCardService ) {

	$scope.submitted = false;
	$scope.isSearching = false;

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
});
