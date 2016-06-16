
NGApp.factory('OrderService', function(ResourceFactory, $rootScope, $http) {

	var service = {};

	var order = ResourceFactory.createResource(App.service + 'orders/:id_order', { id_order: '@id_order'}, {
		'load' : {
			url: App.service + 'order/:id_order',
			method: 'GET',
			params : {}
		},
		'refund_info' : {
			url: App.service + 'order/:id_order/refund-info',
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
		'order_query' : {
			method: 'GET',
			params : {}
		},
		'resend_notification' : {
			url: App.service + 'order/:id_order/resend_notification',
			method: 'GET',
			params : {}
		},
		'delivery_status' : {
			url: App.service + 'order/:id_order/status',
			method: 'GET',
			params : {}
		},
		'change_delivery_status' : {
			url: App.service + 'order/:id_order/status-change',
			method: 'POST',
			params : {}
		},
		'text_5_min_away' : {
			url: App.service + 'order/:id_order/text-5-min-away',
			method: 'POST',
			params : {}
		},
		'resend_notification_drivers' : {
			url: App.service + 'order/:id_order/resend_notification_drivers',
			method: 'GET',
			params : {}
		},
		'refund' : { url: App.service + 'order/:id_order/refund', method: 'POST', params : {} },
		'do_not_reimburse_driver' : { url: App.service + 'order/:id_order/do_not_reimburse_driver', method: 'GET', params : {} },
		'text_drivers' : { url: App.service + 'order/:id_order/text_drivers', method: 'GET', params : {} },
		'do_not_pay_driver' : { url: App.service + 'order/:id_order/do_not_pay_driver', method: 'GET', params : {} },
		'do_not_pay_restaurant' : { url: App.service + 'order/:id_order/do_not_pay_restaurant', method: 'GET', params : {} },
		'mark_cash_card_charged' : { url: App.service + 'order/:id_order/mark_cash_card_charged', method: 'GET', params : {} },
		'approve_address' : { url: App.service + 'order/:id_order/approve_address', method: 'GET', params : {} },
		'campus_cash' : { url: App.service + 'order/:id_order/campus-cash', method: 'POST', params : {} }
	});

	service.list = function(params, callback) {
		order.order_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.exports = function(params, callback) {
		params.export = true;
		var url = App.service + 'orders/';
		var str = [];
		for ( var k in params ) {
			var v = params[ k ];
			str.push( k + "=" + encodeURIComponent( v ) );
		}
		url += '?' + str.join( '&' );
		window.open( url );
	}

	service.get = function(id_order, callback) {
		order.load({id_order: id_order}, function(data) {
			callback(data);
		});
	}

	service.refund_info = function(id_order, callback) {
		order.refund_info({id_order: id_order}, function(data) {
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

	service.refund = function( params, callback ){
		order.refund( params, function( data ) {
			callback( data );
		});
	}

	service.askRefund = function( id_order, delivery_service, formal_relationship, callback ){
		$rootScope.$broadcast( 'openRefundOrderOptions', { id_order: id_order, delivery_service: delivery_service, formal_relationship: formal_relationship, callback: callback } );
	}

	service.do_not_reimburse_driver = function( id_order, callback ){
		order.do_not_reimburse_driver( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.text_drivers = function( id_order, callback ){
		order.text_drivers( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.mark_cash_card_charged = function( id_order, callback ){
		order.mark_cash_card_charged( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.approve_address = function( id_order, callback ){
		order.approve_address( { id_order: id_order }, function( data ) {
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

	service.campus_cash = function( params, callback ){
		order.campus_cash( params, function( data ) {
			callback( data );
		});
	}

	service.text_5_min_away = function( id_order, callback ){
		order.text_5_min_away( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.delivery_status = function( id_order, callback ){
		order.delivery_status( { id_order: id_order }, function( data ) {
			callback( data );
		});
	}

	service.resend_notification_drivers = function( o, callback ){
		var fail = function() {
			callback({status:false});
		};

		if (o.status.status == 'pickedup' || o.status.status == 'accepted' || o.status.status == 'delivered' || o.status.status == 'transfered'  ) {
			App.alert('Order has already been accepted.');
			fail();
			return;
		}

		var question = 'Are you sure you want to resend driver notifications to #' + o.id_order + '?';

		var success = function() {
			order.resend_notification_drivers( { id_order: o.id_order }, function( result ) {
				if (!result || result.status != 'success') {
					App.alert('Error!');
				} else {
					App.alert('Notifications sent');
				}
				callback(result);
			});
		};

		App.confirm(question, 'Renotify #' + o.id_order, success, fail, null, true);
	}

	service.resend_notification = function( o, callback ){
		var fail = function() {
			callback({status:false});
		};

		if (o.confirmed) {
			App.alert('Order has already been confirmed.');
			fail();
			return;
		}
		var question = 'Are you sure you want to resend restaurant notifications to #' + o.id_order + '?';

		var success = function() {
			order.resend_notification( { id_order: o.id_order }, function( result ) {
				if (!result || result.status != 'success') {
					App.alert('Error!');
				} else {
					App.alert('Notifications sent');
				}
				callback(result);
			});
		};
		App.confirm(question, 'Renotify #' + o.id_order, success, fail, null, true);
	}

	service.saveeta = function(params, callback) {
		order.saveeta(params, function(data) {
			callback(data);
		});
	}

	service.change_delivery_status = function(params, callback) {
		order.change_delivery_status(params, function(data) {
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

		if (args.order.status.status == 'accepted' || args.order.status.status == 'transferred') {
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

	var statuses = [];
	statuses.push( { value: 'accepted', label: 'Accepted' } );
	statuses.push( { value: 'pickedup', label: 'Pickedup' } );
	statuses.push( { value: 'delivered', label: 'Delivered' } );
	statuses.push( { value: 'rejected', label: 'Rejected' } );
	statuses.push( { value: 'canceled', label: 'Canceled' } );

	service.statuses = statuses;

	return service;

});
