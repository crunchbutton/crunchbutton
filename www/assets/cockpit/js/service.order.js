
NGApp.factory('OrderService', function(ResourceFactory, $rootScope) {

	var service = {};

	var order = ResourceFactory.createResource(App.service + 'orders/:id_order', { id_order: '@id_order'}, {
		'load' : {
			url: App.service + 'order/:id_order',
			method: 'GET',
			params : {}
		},
		'put' : {
			url: App.service + 'order/:id_order',
			method: 'PUT',
			params : {}
		},
		'ticket' : {
			url: App.service + 'order/:id_order/ticket',
			method: 'GET',
			params : {}
		},
		'saveeta' : {
			url: App.service + 'order/:id_order/eta',
			method: 'POST',
			params : {}
		},
		'eta' : {
			url: App.service + 'order/:id_order/eta',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'resend_notification' : {
			url: App.service + 'order/:id_order/resend_notification',
			method: 'GET',
			params : {}
		},
		'resend_notification_drivers' : {
			url: App.service + 'order/:id_order/resend_notification_drivers',
			method: 'GET',
			params : {}
		},
		'refund' : { url: App.service + 'order/:id_order/refund', method: 'GET', params : {} },
		'do_not_reimburse_driver' : { url: App.service + 'order/:id_order/do_not_reimburse_driver', method: 'GET', params : {} },
		'do_not_pay_driver' : { url: App.service + 'order/:id_order/do_not_pay_driver', method: 'GET', params : {} },
		'do_not_pay_restaurant' : { url: App.service + 'order/:id_order/do_not_pay_restaurant', method: 'GET', params : {} }
	});

	service.list = function(params, callback) {
		order.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_order, callback) {
		order.load({id_order: id_order}, function(data) {
			callback(data);
		});
	}
	
	service.ticket = function(id_order, callback) {
		order.ticket({id_order: id_order}, function(data) {
			callback(data);
		});
	}

	service.put = function(params, callback) {
		order.put(params, function(data) {
			if (!callback) { return; }
			callback(data);
		});
	}

	service.refund = function( id_order, callback ){
		order.refund( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}
	
	service.askRefund = function(order, callback) {

		var question = 'Are you sure you want to refund this order?';
		if (parseFloat(order.credit ) > 0) {
			question += "\nA gift card was used at this order the refund value will be $" + $scope.ticket.order.charged + ' + $' + $scope.ticket.order.credit + ' as gift card.' ;
		}

		App.confirm(question, 'Refund #' + id_order, service.refund(order.id_order, function(result) {
			if (result.success) {
				callback(true);
			} else {
				console.log(result.responseText);
				var er = result.errors ? '<br>' + result.errors : 'See the console.log!';
				App.alert('Refunding fail! ' + er);
				callback(false);
			}
		}));
	}

	service.do_not_reimburse_driver = function( id_order, callback ){
		order.do_not_reimburse_driver( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.do_not_pay_driver = function( id_order, callback ){
		order.do_not_pay_driver( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.do_not_pay_restaurant = function( id_order, callback ){
		order.do_not_pay_restaurant( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}
	
	service.resend_notification_drivers = function( order, callback ){
		if (order.status.status != 'new' || order.status.status != 'rejected') {
			$rootScope.flash.setMessage('Order has already been accepted.');
			callback({status:false});
			return;
		}
		var question = 'Are you sure you want to resend driver notifications to #' + order.id_order;
		App.confirm(question, 'Renotify #' + order.id_order, order.resend_notification_drivers( { id_order: order.id_order }, function( data ) {
			if (result.status != 'success') {
				$rootScope.flash.setMessage('Error!');
			} else {
				$rootScope.flash.setMessage('Notifications sent');
			}
			callback(data);
		}));
	}
	
	service.resend_notification = function( order, callback ){
		if (order.confirmed) {
			$rootScope.flash.setMessage('Order has already been confirmed.');
			callback({status:false});
			return;
		}
		var question = 'Are you sure you want to resend restaurant notifications to #' + order.id_order;
		App.confirm(question, 'Renotify #' + order.id_order, order.resend_notification( { id_order: order.id_order }, function( data ) {
			if (result.status != 'success') {
				$rootScope.flash.setMessage('Error!');
			} else {
				$rootScope.flash.setMessage('Notifications sent');
			}
			callback(data);
		}));
	}

	service.saveeta = function(params, callback) {
		order.saveeta(params, function(data) {
			callback(data);
		});
	}

	$rootScope.$on('order-route', function(event, args) {

		var eta = {
			time: 0,
			distance: 0
		};
		for (var x in args.legs) {
			eta.time += args.legs[x].duration.value/60;
			eta.distance += args.legs[x].distance.value * 0.000621371;
		}

		if (args.order.status.status == 'accepted' ||args.order.status.status == 'transferred') {
			if (args.restaurant.formal_relationship == 1 || args.restaurant.order_notifications_sent) {
				eta.time += 5;
			} else {
				eta.time += 15;
			}
		}

		service.saveeta({
			id_order: args.order.id_order,
			time: eta.time,
			distance: eta.distance,
			method: 'google-route-js'
		}, function(){});

		$rootScope.$broadcast('order-route-' + args.order.id_order, eta);
	});

	return service;

});