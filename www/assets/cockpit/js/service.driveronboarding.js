NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driveronboarding/:action/:id_driver', { id_driver: '@id_driver', action: '@action' }, {
				// actions
				'get' : { 'method': 'GET', params : { 'action' : 'list', 'id' : 0 } },
				'add' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
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

	service.get = function( callback ){
		var id_driver = $routeParams.id;
		orders.get( { 'id_driver': id_driver }, function( order ){ 
			order._date = new Date( order.date );
			callback( order ); 
		} );
	}

	return service;
} );