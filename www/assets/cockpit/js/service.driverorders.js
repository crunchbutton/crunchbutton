NGApp.factory( 'DriverOrdersService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driver/orders/:id_order/:action', { id_order: '@id_order', action: '@action' }, {
				// actions
				'get' : { 'method': 'GET', params : { 'action' : 'order' } },
				'count' : { 'method': 'GET', params : { 'action' : 'count' } },
				'count_accepted' : {'method': 'GET', params : {'action' : 'accepted' } },
				'count_pickedup' : {'method': 'GET', params : {'action' : 'pickedup' } },
				'outstanding_Order' : {'method': 'GET', params : {'action' : 'undelivered' } },
				'revenue' : {'method': 'GET', params : {'action' : 'revenue' } },
				'revenue_last' : {'method': 'GET', params : {'action' : 'revenue' } },
				'avg_time_last' : {'method': 'GET', params : {'action' : 'times' } },
				'avg_time_current' : {'method': 'GET', params : {'action' : 'times' } },
				'accept' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
				'reject' : { 'method': 'POST', params : { 'action' : 'delivery-reject' } },
				'pickedup' : { 'method': 'POST', params : { 'action' : 'delivery-pickedup' } },
				'delivered' : { 'method': 'POST', params : { 'action' : 'delivery-delivered' } },
				'undo_accepted' : { 'method': 'POST', params : {'action' : 'undo-accepted' } },
				'undo_delivered' : { 'method': 'POST', params : {'action' : 'undo-delivered' } },				
				'undo_pickedup' : { 'method': 'POST', params : {'action' : 'undo-pickedup' } }
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
			service.newOrdersBadge();
			callback( orders );
		} );
	}

	service.newOrdersBadge = function( callback ){
		orders.count( {}, function( json ){ $rootScope.newDriverOrders = { count: json.total, time: new Date }; } );
	}

	service.acceptedOrders = function( callback ){
		orders.count_accepted( {}, function( json ){ $rootScope.acceptedDriverOrders = { accepted: json.total }; } );
	}

	service.pickedupOrders = function( callback ){
		orders.count_pickedup( {}, function( json ){ $rootScope.pickedupDriverOrders = { pickedup: json.total }; } );
	}
	
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

	service.undoPickedup = function( id_order, callback ){
		orders.undo_pickedup( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.undoAccepted = function( id_order, callback ){
		orders.undo_accepted( { 'id_order': id_order }, function( json ){ callback( json ); } );
	}

	service.undoDelivered = function( id_order, callback ){
		orders.undo_delivered( { 'id_order': id_order }, function( json){ callback( json ); } );
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
			callback( order );
		} );
		
	}

	return service;
} );