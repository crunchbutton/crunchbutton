// Account controllers

function AccountModalHeaderCtrl( $scope, $http, AccountModalService ) {
	$scope.modal = AccountModalService;
}

function AccountSignInCtrl( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {

	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;

}

function AccountSignUpCtrl( $scope, $http, AccountModalService, AccountService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;

	// Watch the variable user
	$scope.$watch( 'account.user', function( newValue, oldValue, scope ) {
		$scope.account.user = newValue;
		if( newValue ){
			$scope.modal.header = false;
		}
	});
}

function RecommendCtrl( $scope, $http, RecommendRestaurantService ) {

	$scope.service = RecommendRestaurantService;

	$scope.formSent = $scope.service.getFormStatus();

	// Watch the variable status change
	$scope.$watch( 'service.getFormStatus()', function( newValue, oldValue, scope ) {
		$scope.formSent = newValue;
	});

	$scope.send = function(){

		if ( $.trim( $( '.home-recommend-text' ).val() ) == '' ){
			alert( "Please enter the restaurant\'s name." );
			$( '.home-recommend-text' ).focus();
			return;
		}

		var pos = App.loc.pos();

		var content = 'Address entered: ' + pos.addressEntered + '\n' + 
									'Address reverse: ' + pos.addressReverse + '\n' + 
									'City: ' + pos.city + '\n' + 
									'Region: ' + pos.region + '\n' + 
									'Lat: ' + pos.lat + '\n' + 
									'Lon: ' + pos.lon;
		var data = {
			name: $( '.home-recommend-text' ).val(),
			content : content
		};

		var url = App.service + $scope.service.api.add;

		$http.post( url , data )
			.success( function( data ) {
					RecommendRestaurantService.changeFormStatus( true );
					$scope.service.addRecommendation( data.id_suggestion );
					$( '.home-recommend-text' ).val( '' );
			}	);
	}
}