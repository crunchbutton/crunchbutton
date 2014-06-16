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

	var id_restaurant = false;

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

	$scope.reimburse_cash_order = function( id_order, reimburse_cash_order ){
		$scope.makeBusy();
		var params = { 'id_order': id_order, 'reimburse_cash_order' : reimburse_cash_order };
		SettlementService.restaurants.reimburse_cash_order( params, function( json ){
			id_restaurant = json.id_restaurant;
			if( id_restaurant ){
				$scope.begin();
			} else {
				App.alert( 'Oops, something bad happened!' )
				$scope.unBusy();
			}
		} );
	}

	$scope.pay_if_refunded = function( id_order, pay_if_refunded ){
		$scope.makeBusy();
		var params = { 'id_order': id_order, 'pay_if_refunded' : pay_if_refunded };
		SettlementService.restaurants.pay_if_refunded( params, function( json ){
			id_restaurant = json.id_restaurant;
			if( id_restaurant ){
				$scope.begin();
			} else {
				App.alert( 'Oops, something bad happened!' )
				$scope.unBusy();
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

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'yyyy-MM-dd' ),
										'end': $filter( 'date' )( $scope.range.end, 'yyyy-MM-dd' ),
										'pay_type': $scope.pay_type };

		if( id_restaurant ){
			params.id_restaurant = id_restaurant;
		}

		SettlementService.restaurants.begin( params, function( json ){
			if( id_restaurant ){
				for( x in $scope.result.restaurants ){
					if( $scope.result.restaurants[ x ].id_restaurant == id_restaurant ){
						$scope.result.restaurants[ x ] = json.restaurants[ 0 ];
						break;
					}
				}
			} else {
				$scope.result = json;
			}
			$scope.showForm = false;
			$scope.isSearching = false;
			$scope.summary();
			$scope.unBusy();
		} );
	}

	$scope.schedule = function(){
		$scope.makeBusy();
		var id_restaurants = new Array();
		for( x in $scope.result.restaurants ){
			if( $scope.result.restaurants[ x ].pay ){
				id_restaurants.push( $scope.result.restaurants[ x ].id_restaurant );
			}
		}
		id_restaurants = id_restaurants.join( ',' );

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'yyyy-MM-dd' ),
										'end': $filter( 'date' )( $scope.range.end, 'yyyy-MM-dd' ),
										'pay_type': $scope.pay_type, 'id_restaurants' : id_restaurants };

		SettlementService.restaurants.schedule( params, function( json ){
			$scope.unBusy();
			$scope.navigation.link( '/settlement/restaurants/status' );
		} );
	}

	$scope.summary = function(){
		var total_restaurants = 0;
		var total_payments = 0;
		var total_orders = 0;
		var total_not_included = 0;
		var total_reimburse_cash_orders = 0;
		for( x in $scope.result.restaurants ){
			if( $scope.result.restaurants[ x ].pay ){
				total_restaurants++;
				total_payments += $scope.result.restaurants[ x ].total_due;
				total_orders += $scope.result.restaurants[ x ].orders_count;
				total_not_included += $scope.result.restaurants[ x ].not_included;
				total_reimburse_cash_orders += $scope.result.restaurants[ x ].reimburse_cash_orders;
			}
		}
		$scope.total_restaurants = total_restaurants;
		$scope.total_payments = total_payments;
		$scope.total_orders = total_orders;
		$scope.total_not_included = total_not_included;
		$scope.total_reimburse_cash_orders = total_reimburse_cash_orders;
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		range();
	}

});

NGApp.controller('SettlementCtrl', function ( $scope ) {

	$scope.ready = true;

	$scope.restaurants = function(){
		$scope.navigation.link( '/settlement/restaurants' );
	}

	$scope.drivers = function(){
		$scope.navigation.link( '/settlement/drivers' );
	}

} );

NGApp.controller('SettlementRestaurantsStatusCtrl', function ( $scope, $timeout, SettlementService ) {

	$scope.ready = false;

	var scheduled_payments = function(){
		SettlementService.restaurants.status( function( json ){
			$scope.result = json;
			$scope.ready = true;
			var timer = $timeout( function() {
				scheduled_payments();
			}, 5000 );
		} );
	}

	$scope.$on( '$destroy', function(){
		// Kills the timer when the controller is changed
		if( typeof( timer ) !== 'undefined' && timer ){
			try{ $timeout.cancel( timer ); } catch(e){}
		}
	} );

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		scheduled_payments();
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