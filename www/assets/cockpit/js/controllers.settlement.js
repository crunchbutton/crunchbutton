NGApp.controller('SettlementCtrl', function ( $scope ) {

	$scope.ready = true;

	$scope.restaurants = function(){
		$scope.navigation.link( '/settlement/restaurants' );
	}

	$scope.drivers = function(){
		$scope.navigation.link( '/settlement/drivers' );
	}

} );

NGApp.controller('SettlementRestaurantsCtrl', function ( $scope, $filter, SettlementService ) {

	$scope.ready = false;
	$scope.pay_type = 'all';
	$scope.sort = 'last_payment';

	$scope.isSearching = false;
	$scope.showForm = true;

	$scope.pay_type_options = SettlementService.pay_type_options;
	$scope.sort_options = SettlementService.sort_options;

	function range(){
		SettlementService.restaurants.range( function( json ){
			if( json.start && json.end ){
				$scope.range = { 'start' : new Date( json.start ), 'end' : new Date( json.end ) };
				$scope.ready = true;
			}
		} );
	}

	$scope.begin = function(){

		$scope.results = false;

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

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy'),
										'pay_type': $scope.pay_type };

		SettlementService.restaurants.begin( params, function( json ){
			$scope.result = json;
			$scope.showForm = false;
			$scope.isSearching = false;
		} );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		range();
	}

});

NGApp.controller('SettlementDriversCtrl', function ( $scope, $filter, SettlementService ) {

	$scope.ready = false;
	$scope.pay_type = 'all';
	$scope.sort = 'last_payment';

	$scope.isSearching = false;
	$scope.showForm = true;

	$scope.pay_type_options = SettlementService.pay_type_options;
	$scope.sort_options = SettlementService.sort_options;

	function range(){
		SettlementService.drivers.range( function( json ){
			if( json.start && json.end ){
				$scope.range = { 'start' : new Date( json.start ), 'end' : new Date( json.end ) };
				$scope.ready = true;
			}
		} );
	}

	$scope.begin = function(){

		$scope.results = false;

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

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'MM/dd/yyyy'),
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy'),
										'pay_type': $scope.pay_type };

		SettlementService.drivers.begin( params, function( json ){
			$scope.result = json;
			$scope.showForm = false;
			$scope.isSearching = false;
		} );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		range();
	}

});