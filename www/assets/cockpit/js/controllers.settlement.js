NGApp.controller('SettlementCtrl', function ( $scope ) {

	$scope.ready = true;

	$scope.restaurants = function(){
		$scope.navigation.link( '/settlement/restaurants' );
	}
	$scope.restaurants_payments = function(){
		$scope.navigation.link( '/settlement/restaurants/payments' );
	}
	$scope.restaurants_scheduled_payments = function(){
		$scope.navigation.link( '/settlement/restaurants/scheduled' );
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

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'yyyy-MM-dd' ),
										'end': $filter( 'date' )( $scope.range.end, 'yyyy-MM-dd' ),
										'pay_type': $scope.pay_type }

		var id_restaurants = new Array();
		var notes = new Array();
		for( x in $scope.result.restaurants ){
			if( $scope.result.restaurants[ x ].pay ){
				id_restaurants.push( $scope.result.restaurants[ x ].id_restaurant );
				params[ 'notes_' + $scope.result.restaurants[ x ].id_restaurant ] = $scope.result.restaurants[ x ].notes;
			}
		}
		id_restaurants = id_restaurants.join( ',' );
		params[ 'id_restaurants' ] = id_restaurants;

		SettlementService.restaurants.schedule( params, function( json ){
			$scope.unBusy();
			$scope.navigation.link( '/settlement/restaurants/scheduled' );
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
			if( !$scope.result.restaurants[ x ].notes ){
				$scope.result.restaurants[ x ].notes = $scope.result.notes;
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

NGApp.controller('SettlementRestaurantsScheduledCtrl', function ( $scope, SettlementService ) {

	$scope.ready = false;

	$scope.update = function(){
		SettlementService.restaurants.scheduled( function( json ){
			$scope.result = json;
			$scope.ready = true;
		} );
	}

	$scope.payment = function( id_payment ){
		$scope.navigation.link( '/settlement/restaurants/scheduled/' + id_payment );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		$scope.update();
	}

});

NGApp.controller('SettlementRestaurantsSummaryCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	var load = function(){
		SettlementService.restaurants.view_summary( function( data ){
			$scope.summary = data;
			$scope.ready = true;
		} );
	}

	$scope.payment = function(){
		$scope.navigation.link( '/settlement/restaurants/payment/' + $routeParams.id );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller('SettlementRestaurantsPaymentsCtrl', function ( $scope, $rootScope, SettlementService, RestaurantService ) {

	$scope.ready = false;
	$scope.id_restaurant = 0;
	$scope.page = 1;

	var list = function(){
		SettlementService.restaurants.payments( { 'page': $scope.page, 'id_restaurant': $scope.id_restaurant }, function( data ){
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.payments = data.results;
			$scope.count = data.count;
			$scope.ready = true;
		} );
	}

	var restaurants = function(){
		RestaurantService.paid_list( function( data ){
			$scope.restaurants = data;
		} );
	}

	$scope.open = function( id_payment ){
		$scope.navigation.link( '/settlement/restaurants/payment/' + id_payment );
	}

	$scope.$watch( 'id_restaurant', function( newValue, oldValue, scope ) {
		$scope.page = 1;
		list();
	} );

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		restaurants();
		list();
	}

});

NGApp.controller('SettlementRestaurantsScheduledViewCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	load = function(){
		SettlementService.restaurants.scheduled_payment( function( json ){
			$scope.result = json;
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.do_payment = function(){
		$scope.makeBusy();
		SettlementService.restaurants.do_payment( $routeParams.id, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error );
				$scope.unBusy();
			} else {
				load();
			}
		} );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller('SettlementRestaurantsPaymentCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	load = function(){
		SettlementService.restaurants.payment( function( json ){
			$scope.result = json;
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.view_summary = function(){
		$scope.navigation.link( '/settlement/restaurants/summary/' + $routeParams.id );
	}

	$scope.send_summary = function(){
		$scope.makeBusy();
		SettlementService.restaurants.send_summary( function( json ){
			if( json.success ){
				load();
			} else {
				$scope.unBusy();
				App.alert( 'Oops, something bad happened!' );
			}
		} )
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		load();
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