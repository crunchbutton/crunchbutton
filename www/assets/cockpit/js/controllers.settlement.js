NGApp.controller( 'SettlementCtrl', function ( $scope ) {} );

NGApp.controller('SettlementListCtrl', function ($rootScope, $scope, $location, SettlementService, ViewListService ) {

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: '0',
			status: 0,
			payment_type: '0',
			date: '',
			id_driver: null,
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		$scope.ready = false;
		SettlementService.list({
			'page': $scope.query.page,
			'search': $scope.query.search,
			'id_driver': $scope.query.driver,
			'payment_type': $scope.query.payment_type,
			'type': $scope.query.type,
			'status': $scope.query.status,
			'date': $scope.query.date,
			'id_driver': $scope.query.id_driver
		}, function( data ){
			$scope.payments = data.results;
			$scope.pages = data.pages;
			$scope.complete(data);
		});
	}

	$scope.show_more_options = false;

	$scope.showMoreOptions = function() {
		$scope.show_more_options=!$scope.show_more_options;
		$rootScope.$broadcast('search-toggle');
	};

	$scope.types = SettlementService.types();
	$scope.pay_types = SettlementService.pay_types();
	$scope.payment_statuses = SettlementService.scheduled_statuses();
	$scope.update();

	$scope.$watch( 'query.type', function( newValue, oldValue, scope ) {
		if( newValue == 'restaurant' ){
			$scope.query.payment_type = 'payment';
		}
	});

	$scope.delete = function( id_payment_schedule ){
		App.confirm ( 'Confirm delete payment ' + id_payment_schedule + '?', 'Confirm', function(){
			SettlementService.drivers.delete( id_payment_schedule, function(){
				update();
			} );
		}, function(){}, null, true );
	}

	$scope.payment_status = function( id_payment ){
		$scope.balancedRefresh = id_payment;
		SettlementService.drivers.payment_status( id_payment, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error );
			} else {
				update();
			}
			$scope.balancedRefresh = null;
		} );
	}

	$scope.archive = function( id_payment_schedule ){
		App.confirm ( 'Confirm archive payment ' + id_payment_schedule + '?', 'Confirm', function(){
			SettlementService.drivers.archive( id_payment_schedule, function(){
				update();
			} );
		}, function(){}, null, true );
	}

});

NGApp.controller('SettlementQueueListCtrl', function ($scope, $location, SettlementService, ViewListService ) {

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: null,
			status: null,
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		$scope.ready = false;
		SettlementService.queue({
			'page': $scope.query.page,
			'type': $scope.query.type,
			'status': $scope.query.status,
			'date': $scope.query.date
		}, function( data ){
			$scope.queues = data.results;
			$scope.pages = data.pages;
			$scope.complete(data);
		});
	}
});

NGApp.controller( 'SettlementRestaurantsPaymentArbitraryCtrl', function ( $scope, $rootScope, $routeParams, SettlementService, RestaurantService) {

	$scope.ready = false;
	$scope.id_restaurant = 0;

	var drivers = function(){
		if( $routeParams.id_restaurant ){
			RestaurantService.get( $routeParams.id_restaurant, function(restaurant) {
				$scope.restaurant = restaurant;
				$scope.payment = { id_restaurant: $scope.restaurant.id_restaurant };
				$scope.ready = true;
			});
		} else {
			RestaurantService.shortlist( function( data ){
				var restaurants = [];
				$scope.restaurants = data;
				$scope.ready = true;
			} );
		}
	}

	var payments_type = function(){
		$scope.payments_type = [ { 'pay_type': 'payment', 'name': 'Payment'  }, { 'pay_type': 'reimbursement', 'name': 'Reimbursement'  } ];
	}

	var load = function(){
		$scope.payment = { 'pay_type': 'payment' };
		drivers();
		payments_type();
	}

	$scope.open = function( id_payment ){
		$scope.navigation.link( '/settlement/restaurant/payment/' + id_payment );
	}

	$scope.pay = function(){

		if( $scope.isPaying ){
			return;
		}

		if( !$scope.payment.id_restaurant ){
			App.alert( 'Select a restaurant!' );
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isPaying = false;
			return;
		}
		$scope.isPaying = true;
		SettlementService.restaurants.schedule_arbitrary_payment( $scope.payment.id_restaurant, $scope.payment.amount, $scope.payment.pay_type, $scope.payment.notes,
			function( json ){
				id_schedule = json.success;
				var url = '/settlement/restaurants/scheduled/' + id_schedule;
				$scope.navigation.link( url );
			}
		);
	}


	load();

});

NGApp.controller( 'SettlementRestaurantsNoPaymentCtrl', function ( $scope, RestaurantService ) {
	$scope.result = [];
	RestaurantService.no_payment_method( function( json ){
		$scope.ready = true;
		$scope.result.restaurants = json;
	} );
} );

NGApp.controller( 'SettlementRestaurantsCtrl', function ( $scope, $filter, SettlementService ) {

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

	$scope.do_not_pay_restaurant = function( id_order, do_not_pay_restaurant ){
		$scope.makeBusy();
		var params = { 'id_order': id_order, 'do_not_pay_restaurant' : do_not_pay_restaurant };
		SettlementService.restaurants.do_not_pay_restaurant( params, function( json ){
			id_restaurant = json.id_restaurant;
			if( id_restaurant ){
				$scope.begin( function(){
					for( x in $scope.result.restaurants ){
						if( $scope.result.restaurants[ x ].id_restaurant == id_restaurant ){
							$scope.show_details( $scope.result.restaurants[ x ], true );
						}
					}
				} );
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

	$scope.begin = function( callback ){

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
			if( callback ){
				callback();
			}
		} );
	}

	$scope.schedule = function(){

		$scope.scheduling = true;

		$scope.makeBusy();

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'yyyy-MM-dd' ),
										'end': $filter( 'date' )( $scope.range.end, 'yyyy-MM-dd' ),
										'pay_type': $scope.pay_type }

		var id_restaurants = new Array();
		for( x in $scope.result.restaurants ){
			if( $scope.result.restaurants[ x ].pay ){
				id_restaurants.push( $scope.result.restaurants[ x ].id_restaurant );
				params[ 'notes_' + $scope.result.restaurants[ x ].id_restaurant ] = $scope.result.restaurants[ x ].notes;
				params[ 'adjustments_' + $scope.result.restaurants[ x ].id_restaurant ] = $scope.result.restaurants[ x ].adjustment;
				params[ 'adjustments_notes_' + $scope.result.restaurants[ x ].id_restaurant ] = $scope.result.restaurants[ x ].adjustment_notes;
			}
		}
		id_restaurants = id_restaurants.join( ',' );
		params[ 'id_restaurants' ] = id_restaurants;

		SettlementService.restaurants.schedule( params, function( json ){
			$scope.scheduling = false;
			$scope.unBusy();
			if( json.success ){
				$scope.navigation.link( '/settlement/queue/' );
			} else {
				App.alert( "Oops, something bad happened!<br>Make sure that the system isn't running another restaurant's payment queue at Schedule Queue's page." );
			}
		} );
	}

	$scope.summary = function(){

		var sum = { 'card_subtotal': 0, 'tax': 0, 'delivery_fee': 0, 'tip': 0, 'customer_fee': 0, 'markup': 0, 'credit_charge': 0, 'restaurant_fee': 0, 'promo_gift_card': 0, 'apology_gift_card': 0, 'order_payment': 0, 'cash_reimburse': 0, 'cash_subtotal': 0, 'total_due': 0, 'adjustment' : 0 };

		var total_restaurants = 0;
		var total_payments = 0;
		var total_orders = 0;
		var total_not_included = 0;
		var total_reimburse_cash_orders = 0;
		var total_adjustments = 0;
		var total_refunded = 0;
		for( x in $scope.result.restaurants ){
			$scope.result.restaurants[ x ].total_due = ( $scope.result.restaurants[ x ].total_due_without_adjustment + $scope.result.restaurants[ x ].adjustment );
			if( $scope.result.restaurants[ x ].pay ){
				total_restaurants++;
				// include the adjustment at total_due
				total_payments += $scope.result.restaurants[ x ].total_due;
				total_orders += $scope.result.restaurants[ x ].orders_count;
				total_not_included += $scope.result.restaurants[ x ].not_included;
				total_reimburse_cash_orders += $scope.result.restaurants[ x ].reimburse_cash_orders;

				angular.forEach( sum, function( value, key ) {
					sum[ key ] += $scope.result.restaurants[ x ][ key ];
				} );
				total_refunded += $scope.result.restaurants[ x ].refunded_count;
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
		$scope.total_refunded = total_refunded;
		$scope.sum = sum;
	}

	$scope.show_details = function( restaurant, ignoreWalk ){
		if( !restaurant.show_details ){
			$scope.showing_details = true;
			restaurant.show_details = true;
			if( !ignoreWalk ){
				setTimeout( function(){
					$scope.walkTo( '#restaurant-' + restaurant.id_restaurant, -80 );
				} );
			}
		} else {
			restaurant.show_details = false;
			$scope.showing_details = false;
			for( x in $scope.result.restaurants ){
				if( $scope.result.restaurants[ x ].show_details ){
					$scope.showing_details = true;
				}
			}
		}
	}
	range();

});

NGApp.controller( 'SettlementRestaurantsScheduledCtrl', function ( $scope, SettlementService ) {

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

	$scope.update();

});

NGApp.controller( 'SettlementRestaurantsSummaryCtrl', function ( $scope, $routeParams, SettlementService ) {

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

	load();

});

NGApp.controller( 'SettlementRestaurantsPaymentsCtrl', function ( $scope, $rootScope, SettlementService, RestaurantService ) {

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

	restaurants();
	list();

});

NGApp.controller( 'SettlementRestaurantsScheduledViewCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;
	$scope.schedule = true;

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
				load();
				$scope.unBusy();
			} else {
				load();
			}
		} );
	}

	$scope.payment_status = function(){
		if( $scope.result.id_payment ){
			$scope.makeBusy();
			SettlementService.restaurants.payment_status( $scope.result.id_payment, function( json ){
				if( json.error ){
					App.alert( 'Oops, something bad happened: ' + json.error );
				} else {
					load();
				}
				$scope.unBusy();
			} );
		}
	}

	$scope.view_payment = function( id_payment ){
		$scope.navigation.link( '/settlement/restaurants/payment/' + id_payment );
	}

	$scope.view_summary = function(){
		$scope.navigation.link( '/settlement/restaurants/summary/' + $scope.result.id_payment );
	}

	$scope.download_summary = function(){
		SettlementService.restaurants.download_summary( $scope.result.id_payment );
	}

	$scope.send_summary = function(){
		$scope.makeBusy();
		SettlementService.restaurants.send_summary( $scope.result.id_payment, function( json ){
			if( json.success ){
				load();
			} else {
				$scope.unBusy();
				App.alert( 'Oops, something bad happened!' );
			}
		} )
	}

	load();

});

NGApp.controller( 'SettlementRestaurantsPaymentCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	$scope.payment = true;

	load = function(){
		SettlementService.restaurants.payment( function( json ){
			$scope.result = json;
			$scope.ready = true;
			$scope.unBusy();
		} );
	}


	$scope.payment_status = function(){
		if( $scope.result.id_payment ){
			$scope.makeBusy();
			SettlementService.restaurants.payment_status( $scope.result.id_payment, function( json ){
				if( json.error ){
					App.alert( 'Oops, something bad happened: ' + json.error );
				} else {
					load();
				}
				$scope.unBusy();
			} );
		}
	}

	$scope.view_summary = function(){
		$scope.navigation.link( '/settlement/restaurants/summary/' + $scope.result.id_payment );
	}

	$scope.download_summary = function(){
		SettlementService.restaurants.download_summary( $scope.result.id_payment );
	}

	$scope.send_summary = function(){
		$scope.makeBusy();
		SettlementService.restaurants.send_summary( $scope.result.id_payment, function( json ){
			if( json.success ){
				load();
			} else {
				$scope.unBusy();
				App.alert( 'Oops, something bad happened!' );
			}
		} )
	}

	load();

});

NGApp.controller( 'SettlementDriversCtrl', function ( $scope, $filter, SettlementService, DriverService ) {

	$scope.ready = false;

	$scope.id_driver = false;

	$scope.isSearching = false;
	$scope.showForm = true;

	function range(){
		SettlementService.drivers.range( function( json ){
			if( json.start && json.end ){
				$scope.range = { 'start' : new Date( json.start ), 'end' : new Date( json.end ) };
				$scope.ready = true;
				// setTimeout( function() { $scope.begin() }, 100 );
			}
		} );
	}

	$scope.begin = function( callback ){

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
										'end': $filter( 'date' )( $scope.range.end, 'MM/dd/yyyy') };
		if( $scope.id_driver ){
			params.id_driver = $scope.id_driver;
		}

		SettlementService.drivers.begin( params, function( json ){
			if( $scope.id_driver ){
				for( x in $scope.result.drivers ){
					if( $scope.result.drivers[ x ].id_admin == $scope.id_driver ){
						$scope.result.drivers[ x ] = json.drivers[ 0 ];
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
			if( callback ){
				callback();
			}
		} );
	}

	$scope.summary = function(){

		var sum = { 'subtotal': 0, 'tax': 0, 'delivery_fee': 0, 'tip': 0, 'customer_fee': 0, 'markup': 0, 'credit_charge': 0, 'gift_card': 0, 'restaurant_fee': 0, 'total_payment': 0, 'total_reimburse':0, 'adjustment' : 0, 'worked_hours': 0, 'delivery_fee_collected': 0, 'customer_fee_collected': 0 };

		var total_drivers = 0;
		var total_payments = 0;
		var total_spent = 0;
		var total_reimbursements = 0;
		var total_standard_reimbursements = 0;
		var total_orders = 0;
		var total_not_included = 0;
		var total_adjustments = 0;
		var total_adjustments = 0;
		var total_worked_hours = 0;
		var total_refunded = 0;
		var total_invited_users = 0;
		var total_payment_invited_users = 0;
		var delivery_fee_collected = 0;
		var customer_fee_collected = 0;
		for( x in $scope.result.drivers ){
			$scope.result.drivers[ x ].total_payments = ( $scope.result.drivers[ x ].total_payment_without_adjustment + $scope.result.drivers[ x ].adjustment );
			if( $scope.result.drivers[ x ].pay ){
				total_drivers++;
				// include the adjustment at total_due
				total_payments += $scope.result.drivers[ x ].total_payments;
				if( $scope.result.drivers[ x ].total_spent ){
					total_spent += $scope.result.drivers[ x ].total_spent;
				}
				if( $scope.result.drivers[ x ].total_reimburse ){
					total_reimbursements += $scope.result.drivers[ x ].total_reimburse;
				}
				if( $scope.result.drivers[ x ].standard_reimburse ){
					total_standard_reimbursements += $scope.result.drivers[ x ].standard_reimburse;
				}
				if( $scope.result.drivers[ x ].delivery_fee_collected ){
					delivery_fee_collected += $scope.result.drivers[ x ].delivery_fee_collected;
				}
				if( $scope.result.drivers[ x ].customer_fee_collected ){
					customer_fee_collected += $scope.result.drivers[ x ].customer_fee_collected;
				}

				total_orders += $scope.result.drivers[ x ].orders_count;
				total_not_included += $scope.result.drivers[ x ].not_included;
				total_not_included += $scope.result.drivers[ x ].not_included;
				if( $scope.result.drivers[ x ].invites_total ){
					total_invited_users += $scope.result.drivers[ x ].invites_total;
				}
				if( $scope.result.drivers[ x ].invites_total_payment ){
					total_payment_invited_users += $scope.result.drivers[ x ].invites_total_payment;
				}
				if( $scope.result.drivers[ x ].worked_hours ){
					total_worked_hours += $scope.result.drivers[ x ].worked_hours;
				}
				angular.forEach( sum, function( value, key ) {
					if( $scope.result.drivers[ x ][ key ] ){
						sum[ key ] += $scope.result.drivers[ x ][ key ];
					}
				} );

			}
			if( !$scope.result.drivers[ x ].notes ){
				$scope.result.drivers[ x ].notes = $scope.result.notes;
			}
		}

		$scope.total_drivers = total_drivers;
		$scope.total_payments = total_payments;
		$scope.total_spent = total_spent;
		$scope.total_reimbursements = total_reimbursements;
		$scope.total_standard_reimbursements = total_standard_reimbursements;
		$scope.total_orders = total_orders;
		$scope.total_not_included = total_not_included;
		$scope.total_refunded = total_refunded;
		$scope.total_worked_hours = total_worked_hours;
		$scope.total_invited_users = total_invited_users;
		$scope.total_payment_invited_users = total_payment_invited_users;
		$scope.delivery_fee_collected = delivery_fee_collected;
		$scope.customer_fee_collected = customer_fee_collected;
		$scope.sum = sum;
	}

	$scope.do_not_pay_driver = function( id_order, id_driver, do_not_pay_driver ){
		$scope.makeBusy();
		var params = { 'id_order': id_order, 'id_driver': id_driver, 'do_not_pay_driver' : do_not_pay_driver };
		SettlementService.drivers.do_not_pay_driver( params, function( json ){
			$scope.id_driver = json.id_driver;
			if( $scope.id_driver ){
				$scope.begin( function(){
					for( x in $scope.result.drivers ){
						if( $scope.result.drivers[ x ].id_admin == id_driver ){
							$scope.show_details( $scope.result.drivers[ x ], true );
						}
					}
				} );
			} else {
				App.alert( 'Oops, something bad happened!' )
				$scope.unBusy();
			}
		} );
	}

	$scope.transfer_driver_modal = function( id_order, id_driver ){
		$scope.transfer_id_order = id_order;
		$scope.transfer_id_driver = id_driver;
		App.dialog.show( '.transfer-driver' );
	}

	$scope.transfer_driver = function(){
		$scope.closePopup();
		if( $scope.transfer_id_driver && $scope.transfer_id_order ){
			$scope.makeBusy();
			var params = { 'id_order': $scope.transfer_id_order, 'id_driver': $scope.transfer_id_driver };
			SettlementService.drivers.transfer_driver( params, function( json ){
				$scope.id_driver = false;
				// reload all
				$scope.begin();
				$scope.unBusy();
				$scope.transfer_id_driver = false;
			} );
		} else {
			App.alert( 'Oops, something bad happened!' )
		}
	}

	$scope.schedule_reimbursement = function(){
		schedule( SettlementService.PAY_TYPE_REIMBURSEMENT );
	}

	$scope.schedule_payment = function(){
		schedule( SettlementService.PAY_TYPE_PAYMENT );
	}

	var schedule = function( pay_type ){

		$scope.scheduling = true;

		$scope.makeBusy();

		var params = { 'start': $filter( 'date' )( $scope.range.start, 'yyyy-MM-dd' ),
										'end': $filter( 'date' )( $scope.range.end, 'yyyy-MM-dd' ),
										'pay_type': pay_type }

		var id_drivers = new Array();
		for( x in $scope.result.drivers ){
			if( $scope.result.drivers[ x ].pay ){
				id_drivers.push( $scope.result.drivers[ x ].id_admin );
				params[ 'notes_' + $scope.result.drivers[ x ].id_admin ] = $scope.result.drivers[ x ].notes;
				params[ 'adjustments_' + $scope.result.drivers[ x ].id_admin ] = $scope.result.drivers[ x ].adjustment;
				params[ 'adjustments_notes_' + $scope.result.drivers[ x ].id_admin ] = $scope.result.drivers[ x ].adjustment_notes;
			}
		}
		id_drivers = id_drivers.join( ',' );
		params[ 'id_drivers' ] = id_drivers;
		SettlementService.drivers.schedule( params, function( json ){
			$scope.scheduling = false;
			$scope.unBusy();
			if( json.success ){
				$scope.navigation.link( '/settlement/queue/' );
			} else {
				App.alert( "Oops, something bad happened!<br>Make sure that the system isn't running another driver's payment queue at Schedule Queue's page." );
			}
		} );
	}

	$scope.show_details = function( driver, ignoreWalk ){
		if( !driver.show_details ){
			$scope.showing_details = true;
			driver.show_details = true;
			if( !ignoreWalk ){
				setTimeout( function(){
					$scope.walkTo( '#driver-' + driver.id_admin, -80 );
				} );
			}
		} else {
			driver.show_details = false;
			$scope.showing_details = false;
			for( x in $scope.result.drivers ){
				if( $scope.result.drivers[ x ].show_details ){
					$scope.showing_details = true;
				}
			}
		}
	}

	range();
	// Load the drivers list
	DriverService.listSimple( function( json ){ $scope.drivers = json; } )

});

NGApp.controller( 'SettlementDriversDeletedCtrl', function ( $scope, SettlementService ) {

	$scope.ready = false;
	$scope.filter = false;
	$scope.status = SettlementService.PAYMENT_STATUS_DELETED;

	SettlementService.drivers.deleted( function( json ){
		$scope.result = json;
		$scope.ready = true;
	} );

});

NGApp.controller( 'SettlementDriversArchivedCtrl', function ( $scope, SettlementService ) {

	$scope.ready = false;
	$scope.filter = false;
	$scope.status = SettlementService.PAYMENT_STATUS_ARCHIVED;

	SettlementService.drivers.archived( function( json ){
		$scope.result = json;
		$scope.ready = true;
	} );

});

NGApp.controller( 'SettlementDriversOldPaymentsCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	load = function(){
		SettlementService.drivers.old_payments( id_driver, function( json ){
			$scope.result = json;
			$scope.ready = true;
		} );
	}

	var id_driver = 0;

	id_driver = ( $routeParams.id ? $routeParams.id : $scope.account.id_admin );
	load();

});

NGApp.controller( 'SettlementDriversScheduledViewCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;
	$scope.schedule = true;

	load = function(){
		SettlementService.drivers.scheduled_payment( function( json ){
			$scope.result = json;
			if( json.pay_type == SettlementService.PAY_TYPE_REIMBURSEMENT ){
				$scope.pay_type_reimbursement = true;
			} else {
				$scope.pay_type_payment = true;
			}
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.force_payment = function(){
		$scope.makeBusy();
		SettlementService.drivers.force_payment( $routeParams.id, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error + '. It may have created a ticket.' );
				load();
				$scope.unBusy();
			} else {
				load();
			}
		} );
	}

	$scope.do_payment = function(){
		$scope.makeBusy();
		SettlementService.drivers.do_payment( $routeParams.id, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error );
				load();
				$scope.unBusy();
			} else {
				load();
			}
		} );
	}

	$scope.view_summary = function(){
		$scope.navigation.link( '/settlement/drivers/summary/' + $routeParams.id );
	}

	$scope.download_summary = function(){
		SettlementService.drivers.download_summary( $routeParams.id );
	}

	$scope.send_summary = function(){
		$scope.makeBusy();
		SettlementService.drivers.send_summary( $scope.result.id_payment, function( json ){
			if( json.success ){
				load();
			} else {
				$scope.unBusy();
				App.alert( 'Oops, something bad happened!' );
			}
		} )
	}

	$scope.$on( 'do_payment', function(e, data) {
		$scope.do_payment();
	});

	$scope.view_payment = function( id_payment ){
		$scope.navigation.link( '/settlement/drivers/payment/' + id_payment );
	}

	load();
});

NGApp.controller( 'SettlementDriversScheduledCtrl', function ( $scope, $rootScope, $location, SettlementService, DriverService, ViewListService) {

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 0,
			status: 0
		},
		update: function() {
			$scope.ready = false;
			SettlementService.drivers.scheduled({
				'page': $scope.query.page,
				'search': $scope.query.search,
				'id_driver': $scope.query.driver,
				'type': $scope.query.type,
				'status': $scope.query.status
			}, function( data ){
				$scope.payments = data.results;
				$scope.complete(data);
			});
		}
	});

	$scope.query.status = 0;
	$scope.query.type = 0;
	$scope.pay_types = SettlementService.pay_types();
	$scope.payment_statuses = SettlementService.scheduled_statuses();
	$scope.update();

	$scope.delete = function( id_payment_schedule ){
		if( confirm( 'Confirm delete payment ' + id_payment_schedule + '?' ) ){
			SettlementService.drivers.delete( id_payment_schedule, function(){
				$scope.update();
			} );
		}
	}

	$scope.archive = function( id_payment_schedule ){
		if( confirm( 'Confirm archive payment ' + id_payment_schedule + '?' ) ){
			SettlementService.drivers.archive( id_payment_schedule, function(){
				$scope.update();
			} );
		}
	}

});

NGApp.controller( 'SettlementDriversPaymentsCtrl', function ( $scope, $rootScope, $location, SettlementService, DriverService, ViewListService) {

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: '0',
			id_driver: '0',
			status: '0'
		},
		update: function() {
			$scope.ready = false;
			SettlementService.drivers.payments({
				'page': $scope.query.page,
				'search': $scope.query.search,
				'id_driver': $scope.query.id_driver,
				'pay_type': $scope.query.type,
				'payment_status': $scope.query.status
			}, function( data ){
				$scope.payments = data.results;
				$scope.complete(data);

			});
		}
	});


	var drivers = function(){
		DriverService.paid( function( data ){
			$scope.drivers = data;
		} );
	}

	$scope.payment_status = function( id_payment ){
		$scope.balancedRefresh = id_payment;
		SettlementService.drivers.payment_status( id_payment, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error );
			} else {
				$scope.update();
			}
			$scope.balancedRefresh = false;
		} );
	}

	$scope.pay_type = 0;
	$scope.pay_types = SettlementService.pay_types();
	$scope.payment_statuses = SettlementService.payment_statuses();
	drivers();
	$scope.update();

});

NGApp.controller( 'SettlementDriversPaymentArbitraryCtrl', function ( $scope, $rootScope, $routeParams, SettlementService, DriverService, StaffService) {

	$scope.ready = false;
	$scope.id_driver = 0;

	var drivers = function(){
		if( $routeParams.id_driver ){
			StaffService.get($routeParams.id_driver, function(staff) {
				$scope.staff = staff;
				console.log('$scope.staff',$scope.staff);
				$scope.payment = { id_driver: $scope.staff.id_admin };
				$scope.ready = true;
			});
		} else {
			DriverService.listAllAdminsWithLogin( function( data ){
				var drivers = [];
				$scope.drivers = data;
				$scope.ready = true;
			} );
		}
	}

	var payments_type = function(){
		$scope.payments_type = [ { 'pay_type': 'payment', 'name': 'Payment'  }, { 'pay_type': 'reimbursement', 'name': 'Reimbursement'  } ];
	}

	var load = function(){
		$scope.payment = {};
		drivers();
		payments_type();
	}

	$scope.open = function( id_payment ){
		$scope.navigation.link( '/settlement/drivers/payment/' + id_payment );
	}

	$scope.pay = function(){

		if( $scope.isPaying ){
			return;
		}

		if( !$scope.payment.id_driver ){
			App.alert( 'Select a driver!' );
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isPaying = false;
			return;
		}
		$scope.isPaying = true;
		SettlementService.drivers.schedule_arbitrary_payment( $scope.payment.id_driver,
																											$scope.payment.amount,
																											$scope.payment.pay_type,
																											$scope.payment.notes,

			function( json ){

				id_schedule = json.success;
				var url = '/settlement/drivers/scheduled/' + id_schedule;
				$scope.navigation.link( url );
			}
		);
	}


	load();

});

NGApp.controller( 'SettlementDriversPaymentCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	$scope.payment = true;

	load = function(){
		SettlementService.drivers.payment( function( json ){
			$scope.result = json;
			if( json.pay_type == SettlementService.PAY_TYPE_REIMBURSEMENT ){
				$scope.pay_type_reimbursement = true;
			} else {
				$scope.pay_type_payment = true;
			}
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.payment_info = function( id_driver ){
		$scope.navigation.link( '/staff/payinfo/' + id_driver );
	}

	$scope.view_summary = function(){
		$scope.navigation.link( '/settlement/drivers/summary/' + $scope.result.id_payment_schedule );
	}

	$scope.download_summary = function(){
		SettlementService.drivers.download_summary( $scope.result.id_payment_schedule );
	}

	$scope.send_summary = function(){
		$scope.makeBusy();
		SettlementService.drivers.send_summary( $scope.result.id_payment, function( json ){
			if( json.success ){
				load();
			} else {
				$scope.unBusy();
				App.alert( 'Oops, something bad happened!' );
			}
		} )
	}

	$scope.do_payment = function(){
		$scope.makeBusy();
		SettlementService.drivers.do_payment( $scope.result.id_payment_schedule, function( json ){
			if( json.error ){
				App.alert( 'Oops, something bad happened: ' + json.error );
				load();
				$scope.unBusy();
			} else {
				load();
			}
		} );
	}

	$scope.payment_status = function(){
			$scope.makeBusy();
			SettlementService.drivers.payment_status( $routeParams.id, function( json ){
				if( json.error ){
					App.alert( 'Oops, something bad happened: ' + json.error );
				} else {
					load();
				}
				$scope.unBusy();
			} );
		}

	load();

});

NGApp.controller( 'SettlementDriversSummaryCtrl', function ( $scope, $routeParams, SettlementService ) {

	$scope.ready = false;

	var load = function(){
		SettlementService.drivers.view_summary( function( data ){
			$scope.summary = data;
			$scope.ready = true;
		} );
	}

	$scope.payment = function(){
		$scope.navigation.link( '/settlement/drivers/payment/' + $routeParams.id );
	}

	load();

});
