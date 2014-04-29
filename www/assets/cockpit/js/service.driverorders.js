NGApp.factory( 'DriverOrdersService', function( $rootScope, $resource ) {
	
	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driverorders/:id_order/:action', { id_order: '@id_order', action: '@action' }, {
				// actions
				'accept' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
				'pickedup' : { 'method': 'POST', params : { 'action' : 'delivery-pickedup' } },
				'delivered' : { 'method': 'POST', params : { 'action' : 'delivery-delivered' } }
			}	
		);

	service.list = function( callback ){
		orders.query( {}, function( data ){ 
			var orders = [];
			var newDriverOrders = 0;
			for( var x in data ){
				var order = data[ x ];
				if( order && order.date && order.date.date ){
					order._date = new Date( order.date.date );	
					orders.push( order );
					if( order.lastStatus.status == 'new' ) {
						newDriverOrders++;
					}
				}
			}
			$rootScope.newDriverOrders = {
				count: newDriverOrders,
				time: new Date
			};
			callback( orders ); 
		} );
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

	return service;
} );