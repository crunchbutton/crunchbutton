NGApp.controller('SettlementCtrl', function ( $scope, SettlementService ) {

	$scope.ready = false;
	$scope.pay_type = 'all';
	$scope.sort = 'last_payment';

	$scope.isSearching = false;
	$scope.showForm = true;

	$scope.pay_type_options = [ { 'name': 'All', 'value' : 'all' }, { 'name': 'Check', 'value' : 'check' }, { 'name': 'Deposit', 'value' : 'deposit' } ];
	$scope.sort_options = [ { 'name': 'Last Payment', 'value' : 'last_payment' }, { 'name': 'Alphabetical', 'value' : 'alphabetical' } ];

	function range(){
		SettlementService.range( function( json ){
			if( json.start && json.end ){
				$scope.range = { 'start' : new Date( json.start ), 'end' : new Date( json.end ) };
				$scope.ready = true;
			}
		} );
	}

	$scope.begin = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}


		for( x in $scope.pay_type_options ){
			if( $scope.pay_type_options[ x ].value == $scope.pay_type ){
				$scope.pay_type_label = $scope.pay_type_options[ x ].name;
				break;
			}
		}

		for( x in $scope.sort_options ){
			if( $scope.sort_options[ x ].value == $scope.sort ){
				$scope.sort_label = $scope.sort_options[ x ].name;
				break;
			}
		}

		$scope.isSearching = true;
		$scope.showForm = false;
// $filter('date')(date, format)
		$scope.start = $scope.range.start.formatted();
		$scope.end = $scope.range.end.formatted();

		$scope.isSearching = false;

	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		range();
	}

});