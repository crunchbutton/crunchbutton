

NGApp.factory( 'DriverOrdersService', function( $rootScope, $resource, $http, $routeParams ) {

	var service = {};

	// Create a private resource 'orders'
	var orders = $resource( App.service + 'driver/orders/:id_order/:action', { id_order: '@id_order', action: '@action' }, {
				// actions
				'hasSignature' : { 'method': 'GET', params : { 'action' : 'has-signature' } },
				'receipt' : { 'method': 'GET', params : { 'action' : 'receipt' } },
				'get' : { 'method': 'GET', params : { 'action' : 'order' } },
				'outstanding_Order' : {'method': 'GET', params : {'action' : 'undelivered' } },
				'revenue' : {'method': 'GET', params : {'action' : 'revenue' } },
				'revenue_last' : {'method': 'GET', params : {'action' : 'revenue' } },
				'avg_time_last' : {'method': 'GET', params : {'action' : 'times' } },
				'avg_time_current' : {'method': 'GET', params : {'action' : 'times' } },
				'accept' : { 'method': 'POST', params : { 'action' : 'delivery-accept' } },
				'signature' : { 'method': 'POST', params : { 'action' : 'signature' } },
				'text_customer_5_min_away' : { 'method': 'POST', params : { 'action' : 'text-customer-5-min-away' } },
				'cancel_order' : { 'method': 'POST', params : { 'action' : 'cancel-order' } },
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

	service.cancel_order = function( id_order, callback ){
		$rootScope.$broadcast( 'openDriverCancelOrderOptions', {id_order: id_order, onSuccess: function(order){
			orders.cancel_order(order, function( json ){ callback( json ); $rootScope.closePopup(); } );
		} } );
	}



	service.accept = function( params, callback ){
		if( !typeof params == 'object' ){
			params = { id_order: params };
		}
		orders.accept( params, function( json ){
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

	service.signature = function( params, callback ){
		orders.signature( params, function( data ){
			callback( data );
		} );
	}

	service.hasSignature = function( id_order, callback ){
		orders.hasSignature( { 'id_order': id_order }, function( data ){
			callback( data );
		} );
	}

	service.getReceipt = function( id_order, callback ){
		orders.receipt( { 'id_order': id_order }, function( data ){
			callback( data );
		} );
	}

	return service;
} );


NGApp.factory( 'DriverOrdersViewService', function( $rootScope, $resource, $routeParams, DriverOrdersService, MainNavigationService, AccountService) {

	var service = {
		order: null
	};

	service.prep = function() {
		service.order = null;
		service.ready = false;
		service.text_customer_5_min_away_sending = false;
	};

	$rootScope.$on('$routeChangeSuccess', function ($currentRoute, $previousRoute) {
		service.order = null;
	});

	service.load = function( callback ) {
		DriverOrdersService.get( function( json ){
			service.order = json;
			service.ready = true;
			var totalTake = 0;
			totalTake = (1 * json._tip) + (1 * json.delivery_fee);
			$rootScope.driverTake = { total: totalTake };

			if( callback ){
				if ( typeof callback === 'function' ) {
					callback();
				}
			}
			$rootScope.unBusy();
		});
	}

	service.text_customer_5_min_away_sending = null;

	service.text_customer_5_min_away = function(){

		var success = function(){
			service.text_customer_5_min_away_sending = true;

			if( service && service.textLoader && service.textLoader.start ){
				service.textLoader.start();
			}

			DriverOrdersService.text_customer_5_min_away(service.order.id_order,
				 function( json ){
						if (json.status) {
							 service.load();
						} else {
							 App.alert('Message failed to send. Please try again.');
						}
						if( service && service.textLoader && service.textLoader.start ){
							service.textLoader.stop();
						}
						service.text_customer_5_min_away_sending = false;
				 }
			);
		}

		var fail = function(){};

		App.confirm( 'Confirm send message to customer?' , 'Confirm', success, fail, null, true);

	}

	service.cancel_order = function(){
		DriverOrdersService.cancel_order(service.order.id_order, function(){
				service.order.status.status = 'canceled';
		})
	}

	service.accept = function() {

		var accept = function( create_shift ){

			$rootScope.makeBusy();
			var params = { id_order: service.order.id_order };
			if( create_shift ){
				params.create_shift = true;
			}
			DriverOrdersService.accept( params,
				function( json ){
					if( params.create_shift ){
						$rootScope.$broadcast( 'adminWorking', true );
					}
					if( json.status ) {
						service.load();
					} else {
						service.load();
						var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
						App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
					}
				}
			);
		}

		if( !AccountService.user.working ){
			var acceptAndCreateShift = function(){
				accept( true );
			}
			var acceptAndDontCreateShift = function(){
				accept( false );
			}
			App.confirm( 'You are not currently on shift. Would you like to deliver for the next hour?', 'Confirm?', acceptAndCreateShift, acceptAndDontCreateShift, 'Accept & deliver for next hour,Accept only this order', true );
		} else {
			accept();
		}

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

	service.close_banner = function(){
		service.order = null;
	}

	return service;
} );
