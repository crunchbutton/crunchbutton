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
		'locations' : {
			url: App.service + 'staff/:id_admin/locations',
			method: 'GET',
			isArray:true
		},
		'phones' : {
			url: App.service + 'staff/phones',
			method: 'GET',
			isArray:true
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		staff.query(params).$promise.then(function success(data, responseHeaders) {
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

	service.locations = function(id_admin, callback) {
		staff.locations({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.has_pexcard = function(id_admin, callback) {
		staff.has_pexcard({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	service.status = function(id_admin, callback) {
		staff.status({id_admin: id_admin}, function(data) {
			callback(data);
		});
	}

	// Create a private resource 'staff'
	var marketing = $resource( App.service + 'staff/marketing/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
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

	service.yesNo = function(){
		var options = [];
		options.push( { value: '0', label: 'No' } );
		options.push( { value: '1', label: 'Yes' } );
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
		methods.push( { value: 'orders', label: 'Orders' } );
		methods.push( { value: 'hours', label: 'Hours' } );
		return methods;
	}

	service.typesUsingPex = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	service.bankInfoTest = function( callback ){
		callback( { 'routing_number':'321174851', 'account_number':'9900000000' } );
	}

	service.bankAccount = function( payload, callback ){
		ConfigService.processor( function( json ){
			if( !json.error && json.processor ){
				var marketplaceUri = json.processor.balanced;
				balanced.init( marketplaceUri );
				console.debug('Creating bank account: ', payload);
				balanced.bankAccount.create( payload, function( response ) {
					console.debug('Balanced response : ', arguments);
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