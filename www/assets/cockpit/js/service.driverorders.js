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
		
	service.accept = function( id_order, callback ){
		orders.accept( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.undo = function( id_order, callback ){
		orders.undo( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.pickedup = function( id_order, callback ){
		orders.pickedup( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.delivered = function( id_order, callback ){
		orders.delivered( { 'id_order': id_order }, function( json ){ callback( json ); } );
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