NGApp.factory( 'DriverService', function( $rootScope, $resource, $routeParams ) {

	var service = { 'PAY_TYPE_REIMBURSEMENT' : 'reimbursement', 'PAY_TYPE_PAYMENT' : 'payment' };

	// Create a private resource 'Driver'
	var drivers = $resource( App.service + 'driver/:action/:id_admin', { action: '@action', id_admin: '@id_admin' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'all' }, isArray: true },
				'listAllAdmins' : { 'method': 'GET', params : { 'action' : 'all-admins' }, isArray: true },
				'list_payment_type' : { 'method': 'GET', params : { 'action' : 'list-payment-type' }, isArray: true },
				'paid' : { 'method': 'GET', params : { 'action' : 'paid' }, isArray: true },
				'summary' : { 'method': 'GET', params : { 'action' : 'summary' } }
			}
		);

	var payments = $resource( App.service + 'driver/payments/:action/:id_admin/:id_payment', { action: '@action', id_admin: '@id_admin', id_payment: '@id_payment' }, {
				'all' : { 'method': 'GET', params : { 'action' : 'all' } },
				'payment' : { 'method': 'GET', params : { 'action' : 'payment' } }
			}
		);

	service.paid = function( callback ){
		drivers.paid( function( data ){
			callback( data );
		} );
	}

	service.summary = function( id_admin, callback ){
		drivers.summary( { id_admin: id_admin }, function( data ){
			callback( data );
		} );
	}

	service.payments = function( id_admin, callback ){
		payments.all( { id_admin: id_admin }, function( data ){
			callback( data );
		} );
	}

	service.payment = function( callback ){
		payments.payment( { id_payment: $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.listSimple = function( callback ){
		drivers.listSimple( function( data ){
			callback( data );
		} );
	}

	service.listAllAdmins = function( callback ){
		drivers.listAllAdmins( function( data ){
			callback( data );
		} );
	}

	service.list_payment_type = function( callback ){
		drivers.list_payment_type( function( data ){
			callback( data );
		} );
	}

	return service;
} );