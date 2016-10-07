NGApp.factory('StaffService', function(ResourceFactory, $routeParams, $resource) {

	var service = {};

	var staff = ResourceFactory.createResource(App.service + 'staff/:id_admin', { id_admin: '@id_admin'}, {
		'load' : {
			method: 'GET',
			params : {}
		},
		'status' : {
			url: App.service + 'staff/:id_admin/status',
			method: 'GET',
			params : {}
		},
		'has_pexcard' : {
			url: App.service + 'staff/:id_admin/has_pexcard',
			method: 'GET',
			params : {}
		},
		'reverify' : {
			url: App.service + 'staff/:id_admin/reverify',
			method: 'GET',
			params : {}
		},
		'sendDriverLicenceToStripe' : {
			url: App.service + 'staff/:id_admin/send-driver-licence-to-stripe',
			method: 'GET',
			params : {}
		},
		'locations' : {
			url: App.service + 'staff/:id_admin/locations',
			method: 'GET',
			isArray:true
		},
		'chat' : {
			url: App.service + 'staff/:id_admin/chat',
			method: 'GET'
		},
		'phones' : {
			url: App.service + 'staff/phones',
			method: 'GET',
			isArray:true
		},
		'notes' : {
			url: App.service + 'staff/notes',
			method: 'GET'
		},
		'activations' : {
			url: App.service + 'staff/activations',
			method: 'GET'
		},
		'support_list' : {
			url: App.service + 'staff/support',
			method: 'GET',
			isArray: true
		},
		'notes_list' : {
			url: App.service + 'staff/notes-list',
			method: 'GET',
			isArray: true
		},
		'active' : {
			url: App.service + 'staff/active',
			method: 'GET',
			isArray: true
		},
		'staff_query' : {
			method: 'GET',
			params : {}
		},
		'group' : {
			url: App.service + 'staff/:id_admin/group',
			method: 'POST',
			params : {}
		},
		'note' : {
			url: App.service + 'staff/:id_admin/note',
			method: 'GET',
			params : {}
		},
		'save_note' : {
			url: App.service + 'staff/:id_admin/note',
			method: 'POST',
			params : {}
		},
		'send_text_about_schedule' : {
			url: App.service + 'staff/:id_admin/text-message-about-schedule',
			method: 'POST',
			params : {}
		},
		'community' : {
			url: App.service + 'staff/:id_admin/community',
			method: 'POST',
			params : {}
		},
		'change_status' : {
			url: App.service + 'staff/:id_admin/change-status',
			method: 'POST',
			params : {}
		},
		'change_down_to_help_notifications' : {
			url: App.service + 'staff/:id_admin/change-down-to-help-notifications',
			method: 'POST',
			params : {}
		}
	});


	service.active = function(callback) {
		staff.active( {}, function( data ){
			callback( data );
		} );
	}

	service.support_list = function(callback) {
		staff.support_list( {}, function( data ){
			callback( data );
		} );
	}

	service.notes_list = function(callback) {
		staff.notes_list( {}, function( data ){
			callback( data );
		} );
	}

	service.list = function(params, callback) {
		staff.staff_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_admin, callback) {
		staff.load({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.phones = function( callback ){
		staff.phones( {}, function( data ){
			callback( data );
		} );
	}

	service.notes = function( params, callback ){
		staff.notes( params, function( data ){
			callback( data );
		} );
	}

	service.activations = function( callback ){
		staff.activations( {}, function( data ){
			callback( data );
		} );
	}

	service.note = function(id_admin, callback) {
		staff.note({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.save_note = function(params, callback) {
		staff.save_note( params, function(data) {
			callback(data);
		});
	}

	service.change_status = function(params, callback) {
		staff.change_status( params, function(data) {
			callback(data);
		});
	}

	service.change_down_to_help_notifications = function(params, callback) {
		staff.change_down_to_help_notifications( params, function(data) {
			callback(data);
		});
	}

	service.send_text_about_schedule = function(params, callback) {
		staff.send_text_about_schedule( params, function(data) {
			callback(data);
		});
	}

	service.locations = function(id_admin, callback) {
		staff.locations({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.chat = function(id_admin, callback) {
		staff.chat({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}



	service.has_pexcard = function(id_admin, callback) {
		staff.has_pexcard({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.sendDriverLicenceToStripe = function(id_admin, callback){
		var params = {id_admin: id_admin};
		staff.sendDriverLicenceToStripe(params, function(data) {
			callback(data);
		});
	}

	service.reverify = function(id_admin, force, callback) {
		var params = {id_admin: id_admin};
		if (force) {
			params.force = true;
		}
		staff.reverify(params, function(data) {
			callback(data);
		});
	}

	service.group = function(params, callback) {
		staff.group( params, function(data) {
			callback(data);
		});
	}

	service.community = function(params, callback) {
		staff.community( params, function(data) {
			callback(data);
		});
	}

	service.status = function(id_admin, callback) {
		staff.status({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	var marketing = $resource( App.service + 'staff/marketing/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}
		);

	var communityDirector = $resource( App.service + 'staff/communitydirector/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}
		);

	// documents resource
	var documents = $resource( App.service + 'driver/documents/:action/:id_admin/:id_driver_document/:id_driver_document_status/:page/:disapprove', { id_admin: '@id_admin', id_driver_document: '@id_driver_document', id_driver_document_status: '@id_driver_document_status', page: '@page', disapprove:'@disapprove' }, {
				'status' : { 'method': 'GET', params : { action : 'marketing-rep' }, isArray: true },
				'save' : { 'method': 'POST', params : { action : 'save' } },
				'pendency' : { 'method': 'GET', params : { action : 'pendency' } },
				'list' : { 'method': 'GET', params : { action : 'list' } },
				'approve' : { 'method': 'GET', params : { action : 'approve' } },
				'remove' : { 'method': 'GET', params : { action : 'remove' } }
			}
		);

	service.marketing = {
		load: function( id_admin, callback ){
			marketing.load( { id_admin: id_admin }, function( data ) {
				callback( data );
			});
		},
		save: function( staff, callback ){
			marketing.save( staff, function( json ){
				callback( json );
			} );
		},
		docs: {
			list: function( id_admin, callback ){
							if( id_admin ){
								documents.status( { 'id_admin': id_admin }, function( docs ){
									callback( docs );
								} );
							}
						},
			save: function( doc, callback ){
							documents.save( doc, function( doc ){
								callback( doc );
							} );
						},
			approve: function( id_driver_document_status, approve, callback ){
				var disapprove = ( approve ) ? null : 'disapprove';
				documents.approve( { id_driver_document_status: id_driver_document_status, disapprove: disapprove }, function( data ){
					callback( data );
				} );
			},

			remove: function( id_driver_document_status, callback ){
				documents.remove( { id_driver_document_status: id_driver_document_status }, function( data ){
					callback( data );
				} );
			},
			download: function( id_driver_document_status ){
				var url =  App.service + 'driver/documents/download/' + id_driver_document_status;
				$window.open( url );
			}
		}
	}

	service.communityDirector = {
		load: function( id_admin, callback ){
			communityDirector.load( { id_admin: id_admin }, function( data ) {
				callback( data );
			});
		},
		save: function( staff, callback ){
			communityDirector.save( staff, function( json ){
				callback( json );
			} );
		},
	}

	service.yesNo = function(){
		var options = [];
		options.push( { value: false, label: 'No' } );
		options.push( { value: true, label: 'Yes' } );
		return options;
	}

	return service;
});

NGApp.factory( 'StaffPayInfoService', function( $resource, $routeParams, ConfigService ) {

	var service = {};

	// Create a private resource 'staff'
	var staff = $resource( App.service + 'staff/payinfo/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'pexcard' : { 'method': 'GET', params : { action: 'pexcard' } },
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'save_bank' : { 'method': 'POST', params : { action: 'save-bank' } },
				'save_stripe_bank' : { 'method': 'POST', params : { action: 'save-stripe-bank' } },
			}
		);

	service.load = function( callback ){
		staff.load( { 'id_admin': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.pexcard = function( callback ){
		staff.pexcard( { 'id_admin': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.loadById = function( id_admin, callback ){
		staff.load( { 'id_admin': id_admin }, function( data ){
			callback( data );
		} );
	};

	service.save = function( params, callback ){
		if( !params.id_admin ){
			params.id_admin = $routeParams.id;
		}
		staff.save( params, function( data ){
			callback( data );
		} );
	}

	service.save_bank = function( params, callback ){
		if( !params.id_admin ){
			params.id_admin = $routeParams.id;
		}
		staff.save_bank( params, function( data ){
			callback( data );
		} );
	}

	service.methodsPayment = function(){
		var methods = [];
		methods.push( { value: 'deposit', label: 'Deposit' } );
		return methods;
	}

	service.typesPayment = function(){
		var methods = [];
		methods.push( { value: 'orders', label: 'Commission' } );
		methods.push( { value: 'hours', label: 'Hourly with tips' } );
		methods.push( { value: 'hours_without_tips', label: 'Hourly without tips (but a higher hourly rate)' } );
		methods.push( { value: 'making_whole', label: 'Making whole' } );
		return methods;
	}

	service.typesUsingPex = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	service.bankInfoTest = function( callback ){
		ConfigService.getProcessor( function( json ){
			if( json.processor && json.processor.type ){
				if ( json.processor.type == 'balanced' ) {
					callback( { 'routing_number':'321174851', 'account_number':'9900000000' } );
				} else if ( json.processor.type == 'stripe' ) {
					callback( { 'routing_number':'111000025', 'account_number':'000123456789' } );
				}
			}
		} );
	}

	service.save_stripe_bank = function( params, callback ){
		staff.save_stripe_bank( params, function( data ){
			callback( data );
		} );
	}

	service.createBankAccount = function( callback ){
		ConfigService.getProcessor( function( json ){
			callback( json );
		} );
	}

	service.bankAccount = function( payload, callback ){
		ConfigService.getProcessor( function( json ){
			if( !json.error && json.processor ){
				var marketplaceUri = json.processor.balanced;
				balanced.init( marketplaceUri );
				balanced.bankAccount.create( payload, function( response ) {
						// Successful tokenization
						if( response.status_code === 201 ) {
							callback( response.bank_accounts[ 0 ] );
						} else {
							callback( {} );
						}
				} );
			}
		} );
	}

	return service;

} );
