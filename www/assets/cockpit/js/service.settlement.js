NGApp.factory( 'SettlementService', function(ResourceFactory, $resource, $http, $routeParams ) {

	var service = { restaurants : {}, drivers : {} };
	var settlement = { restaurants : {}, drivers : {} };

	// constants
	service.PAY_TYPE_PAYMENT = 'payment';
	service.PAY_TYPE_REIMBURSEMENT = 'reimbursement';

	service.PAYMENT_STATUS_PENDING = 'pending';
	service.PAYMENT_STATUS_SUCCEEDED = 'succeeded';
	service.PAYMENT_STATUS_FAILED = 'failed';

	service.PAYMENT_STATUS_SCHEDULED = 'scheduled';
	service.PAYMENT_STATUS_PROCESSING = 'processing';
	service.PAYMENT_STATUS_DONE = 'done';
	service.PAYMENT_STATUS_DELETED = 'deleted';
	service.PAYMENT_STATUS_ARCHIVED = 'archived';
	service.PAYMENT_STATUS_ERROR = 'error';

	service.pay_type_options = [ { 'name': 'All', 'value' : 'all' }, { 'name': 'Check', 'value' : 'check' }, { 'name': 'Deposit', 'value' : 'deposit' } ];
	service.sort_options = [ { 'name': 'Last Payment', 'value' : 'last_payment' }, { 'name': 'Alphabetical', 'value' : 'alphabetical' } ];

	settlement.list = $resource( App.service + 'settlement/list', {}, {
		'list' : { 'method': 'GET', params : { action: 'list' } },
	}	);

	settlement.queue = $resource( App.service + 'settlement/queue', {}, {
		'list' : { 'method': 'GET', params : { action: 'queue' } },
	}	);

	settlement.restaurants = $resource( App.service + 'settlement/restaurants/:action/:id_payment_schedule/:id_payment/:page/', { action: '@action', id_payment_schedule: '@id_payment_schedule', id_payment: '@id_payment' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
		'restaurant' : { 'method': 'POST', params : { action: 'restaurant' } },
		'pay_if_refunded' : { 'method': 'POST', params : { action: 'pay-if-refunded' } },
		'payment' : { 'method': 'POST', params : { action: 'payment' } },
		'do_payment' : { 'method': 'POST', params : { action: 'do-payment' } },
		'scheduled_payment' : { 'method': 'POST', params : { action: 'scheduled' } },
		'send_summary' : { 'method': 'POST', params : { action: 'send-summary' } },
		'payments' : { 'method': 'POST', params : { action: 'payments' } },
		'schedule_arbitrary_payment' : { 'method': 'POST', params : { action: 'schedule-arbitrary-payment' } },
		'reimburse_cash_order' : { 'method': 'POST', params : { action: 'reimburse-cash-order' } },
		'do_not_pay_restaurant' : { 'method': 'POST', params : { action: 'do-not-pay-restaurant' } },
		'schedule' : { 'method': 'POST', params : { action: 'schedule' } },
		'scheduled' : { 'method': 'POST', params : { action: 'scheduled' } },
		'payment_status' : { 'method': 'POST', params : { action: 'payment-status' } },
	}	);

	settlement.drivers = ResourceFactory.createResource( App.service + 'settlement/drivers/:action/:id_payment_schedule/:id_payment/', { action: '@action', id_payment_schedule: '@id_payment_schedule', id_payment: '@id_payment' }, {
		'range' : { 'method': 'GET', params : { action: 'range' } },
		'do_not_pay_driver' : { 'method': 'POST', params : { action: 'do-not-pay-driver' } },
		'transfer_driver' : { 'method': 'POST', params : { action: 'transfer-driver' } },
		'schedule' : { 'method': 'POST', params : { action: 'schedule' } },
		'scheduled' : { 'method': 'POST', params : { action: 'scheduled' } },
		'archived' : { 'method': 'POST', params : { action: 'archived' } },
		'deleted' : { 'method': 'POST', params : { action: 'deleted' } },
		'scheduled_payment' : { 'method': 'POST', params : { action: 'scheduled' } },
		'do_payment' : { 'method': 'POST', params : { action: 'do-payment' } },
		'force_payment' : { 'method': 'POST', params : { action: 'force-payment' } },
		'old_payments' : { 'method': 'POST', params : { action: 'old-payments' } },
		'do_err_payments' : { 'method': 'POST', params : { action: 'do-err-payments' } },
		'send_summary' : { 'method': 'POST', params : { action: 'send-summary' } },
		'schedule_arbitrary_payment' : { 'method': 'POST', params : { action: 'schedule-arbitrary-payment' } },
		'payment' : { 'method': 'POST', params : { action: 'payment' } },
		'payments' : { 'method': 'POST', params : { action: 'payments' } },
		'begin' : { 'method': 'POST', params : { action: 'begin' } },
		'payment_status' : { 'method': 'POST', params : { action: 'payment-status' } },
		'change_status' : { 'method': 'POST', params : { action: 'change-status' } }
	}	);

	service.list = function( params, callback ){
		settlement.list.list( params, function( json ){
			callback( json );
		} );
	}

	service.queue = function( params, callback ){
		settlement.queue.list( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.begin = function( params, callback ){
		settlement.restaurants.begin( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.paid_list = function( callback ){
		settlement.restaurants.paid_list( function( json ){
			callback( json );
		} );
	}

	service.restaurants.schedule = function( params, callback ){
		settlement.restaurants.schedule( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.scheduled = function( params, callback ){
		settlement.restaurants.scheduled( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.scheduled_payment = function( callback ){
		settlement.restaurants.scheduled_payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.payment = function( callback ){
		settlement.restaurants.payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.do_payment = function( id_payment_schedule, callback ){
		settlement.restaurants.do_payment( { 'id_payment_schedule' : id_payment_schedule  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.send_summary = function( id_payment_schedule, callback ){
		settlement.restaurants.send_summary( { 'id_payment_schedule' : id_payment_schedule  }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.download_summary = function( id_payment ){
		window.open( App.service + 'settlement/restaurants/download-summary/' + id_payment );
	}

	service.restaurants.view_summary = function( callback ){
		var url = App.service + 'settlement/restaurants/view-summary/' + $routeParams.id;
		$http( { method: 'POST', url: url } ).
			success( function( data, status, headers, config ) {
				callback( data );
			}).
			error( function(data, status, headers, config ) {
				callback( false );
			} );
	}

	service.restaurants.payment_status = function( id_payment, callback ){
		settlement.restaurants.payment_status( { 'id_payment' : id_payment }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.payments = function( params, callback ){
		settlement.restaurants.payments( { 'page' : params.page, 'id_restaurant' : params.id_restaurant }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.pay_if_refunded = function( params, callback ){
		settlement.restaurants.pay_if_refunded( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.reimburse_cash_order = function( params, callback ){
		settlement.restaurants.reimburse_cash_order( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.do_not_pay_restaurant = function( params, callback ){
		settlement.restaurants.do_not_pay_restaurant( params, function( json ){
			callback( json );
		} );
	}

	service.restaurants.schedule_arbitrary_payment = function( id_restaurant, amount, pay_type, notes, callback ){
		settlement.restaurants.schedule_arbitrary_payment( { 'id_restaurant': id_restaurant, 'amount': amount, 'pay_type': pay_type, 'notes': notes }, function( json ){
			callback( json );
		} );
	}

	service.restaurants.range = function( callback ){
		settlement.restaurants.range( function( json ){
			callback( json );
		} );
	}

	service.drivers.begin = function( params, callback ){
		settlement.drivers.begin( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.do_not_pay_driver = function( params, callback ){
		settlement.drivers.do_not_pay_driver( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.transfer_driver = function( params, callback ){
		settlement.drivers.transfer_driver( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.schedule = function( params, callback ){
		settlement.drivers.schedule( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.scheduled = function( params, callback ){
		settlement.drivers.scheduled( params, function( json ){
			callback( json );
		} );
	}

	service.drivers.archived = function( callback ){
		settlement.drivers.archived( function( json ){
			callback( json );
		} );
	}

	service.drivers.deleted = function( callback ){
		settlement.drivers.deleted( function( json ){
			callback( json );
		} );
	}

	service.drivers.download_summary = function( id_payment ){
		window.open( App.service + 'settlement/drivers/download-summary/' + id_payment );
	}

	service.drivers.do_payment = function( id_payment_schedule, callback ){
		settlement.drivers.do_payment( { 'id_payment_schedule' : id_payment_schedule }, function( json ){
			callback( json );
		} );
	}

	service.drivers.force_payment = function( id_payment_schedule, callback ){
		settlement.drivers.force_payment( { 'id_payment_schedule' : id_payment_schedule }, function( json ){
			callback( json );
		} );
	}


	service.drivers.do_err_payments = function( callback ){
		settlement.drivers.do_err_payments( {}, function( json ){
			callback( json );
		} );
	}

	service.drivers.schedule_arbitrary_payment = function( id_driver, amount, pay_type, notes, callback ){
		settlement.drivers.schedule_arbitrary_payment( { 'id_driver': id_driver, 'amount': amount, 'pay_type': pay_type, 'notes': notes }, function( json ){
			callback( json );
		} );
	}

	service.drivers.scheduled_payment = function( callback ){
		settlement.drivers.scheduled_payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.drivers.payments = function(params, callback) {
		settlement.drivers.payments(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.drivers.payment = function( callback ){
		settlement.drivers.payment( { 'id_payment_schedule' : $routeParams.id  }, function( json ){
			callback( json );
		} );
	}

	service.drivers.send_summary = function( id_payment, callback ){
		settlement.drivers.send_summary( { 'id_payment' : id_payment  }, function( json ){
			callback( json );
		} );
	}

	service.drivers.old_payments = function( id_driver, callback ){
		settlement.drivers.old_payments( { 'id_driver' : id_driver  }, function( json ){
			callback( json );
		} );
	}

	service.drivers.payment_status = function( id_payment, callback ){
		settlement.drivers.payment_status( { 'id_payment' : id_payment }, function( json ){
			callback( json );
		} );
	}

	service.drivers.delete = function( id_payment_schedule, callback ){
		settlement.drivers.change_status( { 'id_payment_schedule' : id_payment_schedule, 'status' : service.PAYMENT_STATUS_DELETED }, function( json ){
			callback( json );
		} );
	}

	service.drivers.archive = function( id_payment_schedule, callback ){
		settlement.drivers.change_status( { 'id_payment_schedule' : id_payment_schedule, 'status' : service.PAYMENT_STATUS_ARCHIVED }, function( json ){
			callback( json );
		} );
	}

	service.drivers.view_summary = function( callback ){
		var url = App.service + 'settlement/drivers/view-summary/' + $routeParams.id;
		$http( { method: 'POST', url: url } ).
			success( function( data, status, headers, config ) {
				callback( data );
			}).
			error( function(data, status, headers, config ) {
				callback( false );
		} );
	}

	service.types = function(){
		var types = [];
		types.push( { type: '0', label: 'All' } );
		types.push( { type: 'driver', label: 'Driver' } );
		types.push( { type: 'restaurant', label: 'Restaurant' } );
		return types;
	}

	service.pay_types = function(){
		var types = [];
		types.push( { type: '0', label: 'All' } );
		types.push( { type: service.PAY_TYPE_PAYMENT, label: 'Payment' } );
		types.push( { type: service.PAY_TYPE_REIMBURSEMENT, label: 'Reimbursement' } );
		return types;
	}

	service.payment_statuses = function(){
		var types = [];
		types.push( { type: '0', label: 'All' } );
		types.push( { type: service.PAYMENT_STATUS_PENDING, label: 'Pending' } );
		types.push( { type: service.PAYMENT_STATUS_SUCCEEDED, label: 'Succeeded' } );
		types.push( { type: service.PAYMENT_STATUS_FAILED, label: 'Failed' } );
		return types;
	}

	service.scheduled_statuses = function(){
		var types = [];
		types.push( { type: 0, label: 'All' } );
		types.push( { type: service.PAYMENT_STATUS_DONE, label: 'Done' } );
		types.push( { type: service.PAYMENT_STATUS_SCHEDULED, label: 'Scheduled' } );
		types.push( { type: service.PAYMENT_STATUS_PROCESSING, label: 'Processing' } );
		types.push( { type: service.PAYMENT_STATUS_DELETED, label: 'Deleted' } );
		types.push( { type: service.PAYMENT_STATUS_ARCHIVED, label: 'Archived' } );
		types.push( { type: service.PAYMENT_STATUS_ERROR, label: 'Error' } );
		types.push( { type: service.PAYMENT_STATUS_FAILED, label: 'Failed' } );
		return types;
	}

	service.drivers.range = function( callback ){
		settlement.drivers.range( function( json ){
			callback( json );
		} );
	}

	return service;
} );
