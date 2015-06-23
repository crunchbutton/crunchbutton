NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/orders', {
			action: 'orders',
			controller: 'OrdersCtrl',
			templateUrl: 'assets/view/orders.html',
			reloadOnSearch: false

		}).when('/order/:id', {
			action: 'order',
			controller: 'OrderCtrl',
			templateUrl: 'assets/view/orders-order.html'
		});
}]);

NGApp.controller('OrdersCtrl', function ($scope, $location, OrderService, ViewListService, SocketService, MapService, TicketService, RestaurantService, CommunityService) {
	angular.extend($scope, ViewListService);

//	var query = $location.search();
//	$scope.query.view = 'list';

	SocketService.listen('orders', $scope)
		.on('update', function(d) {
			for (var x in $scope.orders) {
				if ($scope.orders[x].id_order == d.id_order) {
					$scope.orders[x] = d;
				}
			}

		}).on('create', function(d) {
			$scope.update();
		});

	var draw = function() {
		if (!$scope.map || !$scope.orders) {
			return;
		}

		MapService.trackOrders({
			map: $scope.map,
			orders: $scope.orders,
			id: 'orders-location',
			scope: $scope
		});
	};

	$scope.ticket = function(id_order) {
		OrderService.ticket(id_order, function(json) {
			if (json.id_support) {
				ticket(json.id_support);
			} else {
				App.alert('Fail retrieving support ticket!' );
			}
		});
	}

	var ticket = function( id_support ){
		var url = '/ticket/' + id_support;
		$scope.navigation.link( url );
	}

	$scope.$on('mapInitialized', function(event, map) {
		console.log('INIT');
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			restaurant: '',
			community: '',
			date: '',
			view: 'list',
			datestart: '',
			dateend: '',
			user: '',
			phone: '',
			fullcount: false
		},
		update: function() {
			OrderService.list($scope.query, function(d) {
				$scope.orders = d.results;
				$scope.complete(d);
				draw();
			});
		}
	});

	$scope.show_more_options = false;

	$scope.exports = function(){
		var params = {};
		angular.copy( $scope.query, params );
		console.log('params',params);
		OrderService.exports( params, function(){
		} );
	}


	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options) {

			if( !$scope.restaurants ){
				$scope.restaurants = [];
				RestaurantService.shortlist( function( json ){
					$scope.restaurants = json;
				} );
			}

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
	}


	var options = [];
	options.push( { value: '20', label: '20' } );
	options.push( { value: '50', label: '50' } );
	options.push( { value: '100', label: '100' } );
	options.push( { value: '200', label: '200' } );
	$scope.limits = options;

});

NGApp.controller('OrderCtrl', function ($scope, $rootScope, $routeParams, $interval, OrderService, MapService, SocketService) {

	SocketService.listen('order.' + $routeParams.id, $scope)
		.on('update', function(d) {
			$scope.order = d;
		});

	$scope.loading = true;

	var draw = function() {
		if (!$scope.map || !$scope.order) {
			return;
		}

		MapService.trackOrder({
			map: $scope.map,
			order: $scope.order,
			restaurant: $scope.order.restaurant,
			driver: $scope.order.driver,
			id: 'order-driver-location',
			scope: $scope
		});
	};

	var update = function() {
		var loading = true;
		OrderService.get($routeParams.id, function(d) {
			$rootScope.title = 'Order #' + d.id_order;
			$scope.order = d;
			$scope.loading = false;
			draw();
		});
	};

	$rootScope.$on( 'orderDeliveryStatusChanged', function(e, data) {
		update();
	});

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	var cleanup = $rootScope.$on('order-route-' + $routeParams.id, function(event, args) {
		$scope.$apply(function() {
			$scope.eta = args;
		});
		console.debug('Got route update: ', args);
	});

	$scope.updater = $interval(update, 30000); // update every 30 seconds
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
		cleanup();
	});

	$scope.isRefunding = false;

	$scope.changeOrderStatus = function(){
		$rootScope.$broadcast( 'orderDeliveryStatusChange', { id_order: $routeParams.id, id_community: $scope.order.id_community }  );
	}

	$scope.refund = function(){

		if( $scope.isRefunding ){
			return;
		}

		var question = 'Are you sure you want to refund this order?';
		if( parseFloat( $scope.order.credit ) > 0 ){
			question += "\n";
			question += 'A gift card was used at this order the refund value will be $' + $scope.order.charged + ' + $' + $scope.order.credit + ' as gift card.' ;
		}

		if ( confirm( question ) ){

			$scope.isRefunding = true;
			OrderService.refund( $scope.order.id_order, function( result ){
				$scope.isRefunding = false;
				if( result.success ){
					$rootScope.reload();
				} else {
					console.log( result.responseText );
					var er = result.errors ? "<br>" + result.errors : 'See the console.log!';
					App.alert('Refunding fail! ' + er);
				}
			} );
		}
	}

	$scope.do_not_pay_driver = function(){
		OrderService.do_not_pay_driver( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_pay_driver = ( $scope.order.do_not_pay_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_pay_restaurant = function(){
		OrderService.do_not_pay_restaurant( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_pay_restaurant = ( $scope.order.do_not_pay_restaurant ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_reimburse_driver = function(){
		OrderService.do_not_reimburse_driver( $scope.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.order.do_not_reimburse_driver = ( $scope.order.do_not_reimburse_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.resend_notification_drivers = function(){
		$scope.isDriverNotifying = true;
		OrderService.resend_notification_drivers( $scope.order, function(result) {
			$scope.isDriverNotifying = false;
		});
	}

	$scope.resend_notification = function(){
		$scope.isRestaurantNotifying = true;
		OrderService.resend_notification( $scope.order, function(result) {
			$scope.isRestaurantNotifying = false;
		});
	}

	update();
});

NGApp.controller('OrderDeliveryStatusCtrl', function ( $scope, $rootScope, OrderService, DriverService ) {

	var id_order = null;
	var id_community = null;
	var id_driver = null;

	$rootScope.$on( 'orderDeliveryStatusChange', function(e, data) {
		$scope.status = null;
		$scope.notify_customer = false;
		id_order = data.id_order;
		id_community = data.id_community;
		App.dialog.show('.change-status-dialog-container');
		load();
	} );

	$scope.driverChanged = function(){
		$scope.notify_customer = ( id_driver != $scope.status.driver.id_admin );
	}

	var load = function(){

		$scope.text5MinAwaySending = false;

		OrderService.delivery_status( id_order, function( data ){
			$scope.status = data;
			if( data && data.driver && data.driver.id_admin ){
				id_driver = data.driver.id_admin;
			}
		} );
		DriverService.byCommunity( id_community, function( data ){
			$scope.drivers = data;
		} );
	}

	$scope.statuses = OrderService.statuses;

	$scope.text5MinAway = function(){

		if( $scope.text5MinAwaySending ){
			return;
		}

		if( confirm( 'Confirm send message to customer?' ) ){
			$scope.text5MinAwaySending = true;
			OrderService.text_5_min_away( id_order, function( json ){
				if( json.success ){
					$rootScope.$broadcast( 'orderDeliveryStatusChanged', json );
				} else {
					App.alert( 'Error saving: ' + json.error );
				}
				$scope.text5MinAwaySending = false;
			} );
		}
	}

	$scope.formDeliveryStatusSave = function(){

		if( $scope.formDeliveryStatus.$invalid ){
			$scope.formDeliveryStatusSubmitted = true;
			return;
		}

		$scope.isSaving = true;

		var params = { 	id_order: id_order,
										notify_customer: $scope.notify_customer,
										status: $scope.status.status,
										id_admin: $scope.status.driver.id_admin };

		OrderService.change_delivery_status( params, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$rootScope.closePopup();
				$rootScope.$broadcast( 'orderDeliveryStatusChanged', json );
			}
		} );
	}

} );
