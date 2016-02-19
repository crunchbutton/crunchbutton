NGApp.factory( 'DriverService', function( $rootScope, $resource, $routeParams ) {

	var service = { 'PAY_TYPE_REIMBURSEMENT' : 'reimbursement', 'PAY_TYPE_PAYMENT' : 'payment' };

	// Create a private resource 'Driver'
	var drivers = $resource( App.service + 'driver/:action/:id_admin/:id_community', { action: '@action', id_admin: '@id_admin', id_community: '@id_community' }, {
				// list methods
				'byCommunity' : { 'method': 'GET', params : { 'action' : 'by-community' }, isArray: true },
				'listSimple' : { 'method': 'GET', params : { 'action' : 'all' }, isArray: true },
				'listAllAdmins' : { 'method': 'GET', params : { 'action' : 'all-admins' }, isArray: true },
				'listAllAdminsWithLogin' : { 'method': 'GET', params : { 'action' : 'all-admins-with-login' }, isArray: true },
				'list_payment_type' : { 'method': 'GET', params : { 'action' : 'list-payment-type' }, isArray: true },
				'paid' : { 'method': 'GET', params : { 'action' : 'paid' }, isArray: true },
				'summary' : { 'method': 'GET', params : { 'action' : 'summary' } }
			}
		);

	// Create a private resource 'Driver'
	var notifications = $resource( App.service + 'driver/notifications/:action/:id_admin/:id_admin_notification', { action: '@action', id_admin: '@id_admin', id_admin_notification: '@id_admin_notification' }, {
				'list' : { 'method': 'GET', params : { 'action' : 'list' } },
				'notification' : { 'method': 'GET', params : { 'action' : 'notification' } },
				'change_status' : { 'method': 'POST', params : { 'action' : 'change_status' } },
				'save' : { 'method': 'POST', params : { 'action' : 'save' } }
			}
		);

	var payments = $resource( App.service + 'driver/payments/:action/:id_admin/:id_payment', { action: '@action', id_admin: '@id_admin', id_payment: '@id_payment' }, {
				'all' : { 'method': 'GET', params : { 'action' : 'all' } },
				'payment' : { 'method': 'GET', params : { 'action' : 'payment' } },
				'payRollInfo' : { 'method': 'GET', params : { 'action' : 'pay-roll-info' } },
			}
		);

	service.paid = function( callback ){
		drivers.paid( function( data ){
			callback( data );
		} );
	}

	service.summary = function( query, callback ){
		drivers.summary( query, function( data ){
			callback( data );
		} );
	}

	service.payRollInfo = function( callback ){
		payments.payRollInfo( {}, function( data ){
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

	service.byCommunity = function( id_community, callback ){
		drivers.byCommunity( { id_community: id_community }, function( data ){
			callback( data );
		} );
	}


	service.listAllAdmins = function( callback ){
		drivers.listAllAdmins( function( data ){
			callback( data );
		} );
	}

	service.listAllAdminsWithLogin = function( callback ){
		drivers.listAllAdminsWithLogin( function( data ){
			callback( data );
		} );
	}

	service.list_payment_type = function( callback ){
		drivers.list_payment_type( function( data ){
			callback( data );
		} );
	}

	service.notifications = {
		list: function( id_admin, callback ){
			notifications.list( {id_admin: id_admin}, function( data ){
				callback( data );
			} );
		},
		save: function( params, callback ){
			notifications.save( params, function( data ){
				callback( data );
			} );
		},
		change_status: function( id_admin_notification, callback ){
			notifications.change_status( { id_admin_notification: id_admin_notification }, function( data ){
				callback( data );
			} );
		},
		notification: function( id_admin_notification, callback ){
			notifications.list( { id_admin_notification: id_admin_notification }, function( data ){
				callback( data );
			} );
		}
	}

	return service;
} );