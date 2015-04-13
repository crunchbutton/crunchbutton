

NGApp.factory( 'DriverOrdersService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driver/orders/:id_order/:action', { id_order: '@id_order', action: '@action' }, {
				// actions
				'get' : { 'method': 'GET', params : { 'action' : 'order' } },
				'outstanding_Order' : {'method': 'GET', params : {'action' : 'undelivered' } },
				'revenue' : {'method': 'GET', params : {'action' : 'revenue' } },
				'revenue_last' : {'method': 'GET', params : {'action' : 'revenue' } },
				'avg_time_last' : {'method': 'GET', params : {'action' : 'times' } },
				'avg_time_current' : {'method': 'GET', params : {'action' : 'times' } },
				'accept' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
				'text_customer_5_min_away' : { 'method': 'POST', params : { 'action' : 'text-customer-5-min-away' } },
				'reject' : { 'method': 'POST', params : { 'action' : 'delivery-reject' } },
				'pickedup' : { 'method': 'POST', params : { 'action' : 'delivery-pickedup' } },
				'delivered' : { 'method': 'POST', params : { 'action' : 'delivery-delivered' } },
				'undo' : { 'method': 'POST', params : {'action' : 'undo' } }
			}
		);

	service.list = function( callback ){
		orders.query( {}, function( data ){
			var orders = [];
			for( var x in data ){
				var order = data[ x ];
				if( order && order.date && order.date.date ){
					order._date = new Date( order.date.date );
					orders.push( order );
				}
			}
			callback( orders );
		} );
	}

	$rootScope.$on('totalOrders', function(e, data) {
		$rootScope.totalDriverOrders = {
			count: data,
			time: new Date
		};
	});

	$rootScope.$on('newOrders', function(e, data) {
		$rootScope.newDriverOrders = {
			count: data,
			time: new Date
		};
	});

	$rootScope.$on('acceptedOrders', function(e, data) {
		$rootScope.acceptedDriverOrders = {
			count: data,
			time: new Date
		};
	});

	$rootScope.$on('pickedupOrders', function(e, data) {
		$rootScope.pickedupOrders = {
			count: data,
			time: new Date
		};
	});


	service.revThisShift = function( callback ){
		orders.revenue( {}, function( json ){ $rootScope.driverRevenue = { revenue: json.totalCurrent }; } );
	}

	service.revLastShift = function( callback ){
		orders.revenue_last( {}, function( json ){ $rootScope.driverRevenueLast = { revenue: json.totalLast }; } );

	}

	service.timeLastShift = function( callback ){
		orders.avg_time_last( {}, function( json ){ $rootScope.driverTimeLast = { time: json.total_last }; } );
	}

	service.timeThisShift = function( callback ){
		orders.avg_time_current( {}, function( json ){ $rootScope.driverTimeCurrent = { time: json.total_current }; } );
	}

	service.outstandingOrders = function( callback ){
		orders.outstanding_Order( {}, function( json ){ $rootScope.driverOutstandingOrders = { count: json.total }; } );
	}

	service.text_customer_5_min_away = function( id_order, callback ){
		orders.text_customer_5_min_away( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.accept = function( id_order, callback ){
		orders.accept( { 'id_order': id_order }, function( json ){
			App.playAudio('orders-delivered');
			callback( json ); } );
	}

	service.undo = function( id_order, callback ){
		orders.undo( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.pickedup = function( id_order, callback ){
		orders.pickedup( { 'id_order': id_order }, function( json ){
			App.playAudio('orders-delivered');
			callback( json ); } );
	}

	service.delivered = function( id_order, callback ){
		orders.delivered( { 'id_order': id_order }, function( json ){
			App.playAudio('orders-delivered');
			callback( json ); } );
	}

	service.reject = function( id_order, callback ){
		orders.reject( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.get = function( callback ){
		var id_order = $routeParams.id;
		orders.get( { 'id_order': id_order }, function( order ){
			order._date = new Date( order.date );
			callback( order );
		} );
	}


	//Driver fee
	service.driver_take = function( callback ){
		var totalTake = 0;
		var id_order = $routeParams.id;
		orders.get( { 'id_order': id_order }, function( order ) {
			totalTake = (1 * order._tip) + (1 * order.delivery_fee);
			$rootScope.driverTake = { total: totalTake };
			if (callback) {
				callback( order );
			}
		} );

	}

	return service;
} );




NGApp.factory( 'DriverOrdersViewService', function( $rootScope, $resource, $routeParams, DriverOrdersService, MainNavigationService) {
	var service = {
		order: null
	};

	service.prep = function() {
		service.order = null;
		service.ready = false;
		service.text_customer_5_min_away_sending = false;
	};

	$rootScope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {
		console.log(service.order);
		service.order = null;
	});

	service.load = function() {
		DriverOrdersService.get( function( json ){
			service.order = json;
			service.ready = true;
			$rootScope.unBusy();
		});
	}

	service.text_customer_5_min_away_sending = null;

	service.text_customer_5_min_away = function(){
		if( 'Confirm send message to customer?' ){
			service.text_customer_5_min_away_sending = true;
			//var l;
			//setTimeout(function(){
			//	l = Ladda.create($(' .ladda-button').get(0));
			//	},700);
			//l.start();
			DriverOrdersService.text_customer_5_min_away(service.order.id_order,
				function( json ){
					if (json.status) {
						service.load();

					} else {
						App.alert('Message failed to send. Please try again.');
					}
					service.text_customer_5_min_away_sending = false;
					//l.stop();
				}
			);
		}
	}

	service.accept = function() {
		$rootScope.makeBusy();
		DriverOrdersService.accept( service.order.id_order,
			function( json ){
				if( json.status ) {
					service.load();
				} else {
					service.load();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
				}
			}
		);
	};

	service.undo = function() {
		$rootScope.makeBusy();
		DriverOrdersService.undo( service.order.id_order, service.load );
	};

	service.pickedup = function() {
		$rootScope.makeBusy();
		DriverOrdersService.pickedup( service.order.id_order, service.load );
	};

	service.delivered = function() {
		$rootScope.makeBusy();
		DriverOrdersService.delivered( service.order.id_order, function(){
			service.load();
			MainNavigationService.link('/drivers/orders');
		} );
	};

	service.reject = function() {
		$rootScope.makeBusy();
		DriverOrdersService.reject( service.order.id_order, service.load );
	};

	DriverOrdersService.driver_take();

	return service;
} );
