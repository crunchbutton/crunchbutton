
NGApp.factory('CallService', function(ResourceFactory, SocketService, $rootScope, AccountService, NotificationService, MainNavigationService) {

	var service = {};

	var call = ResourceFactory.createResource( App.service + 'calls/:id_call', { id_support: '@id_call'}, {
		'load' : {
			url: App.service + 'call/:id_call',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'call/:id_call',
			method: 'POST',
			params : {}
		},
		'register_voip' : {
			url: App.service + 'call/register-voip',
			method: 'POST',
			params : {}
		},
		'make_call' : {
			url: App.service + 'call/make-call',
			method: 'POST',
			params : {}
		},
		'send_sms' : {
			url: App.service + 'call/send-sms',
			method: 'POST',
			params : {}
		},
		'send_sms_list' : {
			url: App.service + 'call/send-sms-list',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		call.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_call, callback) {
		customer.load({id_call: id_call}, function(data) {
			callback(data);
		});
	}

	service.make_call = function( params, callback ){
		call.make_call(params, function(data) {
			callback(data);
		});
	}

	service.register_voip = function( params, callback ){
		call.register_voip(params, function(data) {
			callback(data);
		});
	}

	service.send_sms = function( params, callback ){
		call.send_sms(params, function(data) {
			callback(data);
		});
	}

	service.send_sms_list = function( params, callback ){
		call.send_sms_list(params, function(data) {
			callback(data);
		});
	}

	service.post = function(params, callback) {
		customer.save(params, function(data) {
			callback(data);
		});
	}

	service.call_to = function(){
		var to = [];
		to.push( { to: 'customer', label: 'Customer' } );
		to.push( { to: 'driver', label: 'Driver' } );
		to.push( { to: 'restaurant', label: 'Restaurant' } );
		return to;
	}

	$rootScope.$on('userAuth', function(e, data) {

		if (AccountService.user && AccountService.user.id_admin) {

			if (AccountService.user.permissions.GLOBAL || AccountService.user.permissions['SUPPORT-ALL'] ||  AccountService.user.permissions['SUPPORT-VIEW'] ||  AccountService.user.permissions['SUPPORT-CRUD']) {
				if (!SocketService) {
					return null;
				}
				SocketService.listen('calls', $rootScope)
					.on('update', function(d) {
						console.log('CALL UPDATED', d);
					}).on('create', function(d) {
						if (d.direction == 'inbound') {
							var content = '';
							if (d.id_admin_from) {
								content += 'Driver: ' + d.name + "\n";
							} else if (d.id_user_from) {
								content += 'Customer: ' + d.name + "\n";
							}
							content += 'Phone: ' + d.from;
							NotificationService.notify('Incoming call', content, null, function() {
								MainNavigationService.link('/call/' + d.id_call);
								$rootScope.$safeApply();
							});
						}
					});
			}
		}
	});

	return service;
});
