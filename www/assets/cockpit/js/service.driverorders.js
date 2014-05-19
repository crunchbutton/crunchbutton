NGApp.factory( 'DriverOrdersService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driver/orders/:id_order/:action', { id_order: '@id_order', action: '@action' }, {
				// actions
				'get' : { 'method': 'GET', params : { 'action' : 'order' } },
				'count' : { 'method': 'GET', params : { 'action' : 'count' } },
				'accept' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
				'reject' : { 'method': 'POST', params : { 'action' : 'delivery-reject' } },
				'pickedup' : { 'method': 'POST', params : { 'action' : 'delivery-pickedup' } },
				'delivered' : { 'method': 'POST', params : { 'action' : 'delivery-delivered' } }
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

	service.accept = function( id_order, callback ){
		orders.accept( { 'id_order': id_order }, function( json ){ callback( json ); } );
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

	return service;
} );