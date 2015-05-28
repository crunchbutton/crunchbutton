NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams, $window ) {

	var service = {};

	// Create a private resource 'drivers'
	var drivers = $resource( App.service + 'driver/:action/:method/:id_admin/:page/:search/:phone', { id_admin: '@id_admin', action: '@action' }, {
				'get' : { 'method': 'GET', params : { action : null } },
				'notify' : { 'method': 'POST', params : { action: 'notify' } },
				'referral' : { 'method': 'POST', params : { action: 'referral' } },
				'pexcard' : { 'method': 'GET', params : { action: 'list', method: 'pexcard' }, isArray: true },
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'setupValidate' : { 'method': 'GET', params : { action: 'setup' } },
				'setupSave' : { 'method': 'POST', params : { action: 'setup' } },
			}
		);

	var vehicles = $resource( App.service + 'driver/onboarding/vehicles', {}, {
				'options' : { 'method': 'GET' }
			}
		);

	var phone_types = $resource( App.service + 'driver/onboarding/phone_types', {}, {
				'options' : { 'method': 'GET' }
			}
		);

	var tshirt_sizes = $resource( App.service + 'driver/onboarding/tshirt_sizes', {}, {
			'options' : { 'method': 'GET' }
		}
	);

	var defaults = $resource( App.service + 'driver/onboarding/defaults', {}, {
			'options' : { 'method': 'GET' }
		}
	);

	var carrier_types = $resource( App.service + 'driver/onboarding/carrier_types', {}, {
				'options' : { 'method': 'GET' }
			}
		);

	// documents resource
	var documents = $resource( App.service + 'driver/documents/:action/:id_admin/:id_driver_document/:id_driver_document_status/:page/:disapprove', { id_admin: '@id_admin', id_driver_document: '@id_driver_document', id_driver_document_status: '@id_driver_document_status', page: '@page', disapprove:'@disapprove' }, {
				'status' : { 'method': 'GET', params : { action : null }, isArray: true },
				'save' : { 'method': 'POST', params : { action : 'save' } },
				'pendency' : { 'method': 'GET', params : { action : 'pendency' } },
				'list' : { 'method': 'GET', params : { action : 'list' } },
				'approve' : { 'method': 'GET', params : { action : 'approve' } },
				'remove' : { 'method': 'GET', params : { action : 'remove' } }
			}
		);

	// logs resource
	var log = $resource( App.service + 'driver/log/:id_admin', { id_admin: '@id_admin' }, {
				'get' : { 'method': 'GET', params : {}, isArray: true },
			}
		);

	service.phone_types = function( callback ){
		phone_types.options( {}, function( json ){
			callback( json );
		} );
	}

	service.tshirt_sizes = function( callback ){
		tshirt_sizes.options( {}, function( json ){
			callback( json );
		} );
	}

	service.carrier_types = function( callback ){
		carrier_types.options( {}, function( json ){
			callback( json );
		} );
	}

	service.yesNo = function(){
		var options = [];
		options.push( { value: '0', label: 'No' } );
		options.push( { value: '1', label: 'Yes' } );
		return options;
	}

	service.vehicles = function( callback ){
		vehicles.options( {}, function( json ){
			callback( json );
		} );
	}

	service.logs = function( id_admin, callback ){
		if( id_admin ){
			log.get( { 'id_admin' : id_admin }, function( json ){
				callback( json );
			} );
		}
	}

	service.setupSave = function( driver, callback ){
		drivers.setupSave( driver, function( json ){
			callback( json );
		} );
	}

	service.setupValidate = function( callback ){
		var phone = $routeParams.phone;
		drivers.setupValidate( { 'phone' : phone }, function( json ){
			callback( json );
		} );
	}

	// send setup notification
	service.notifySetup = function( id_admin, callback ){
		var message = 'setup';
		if( id_admin ){
			service.notify( id_admin, message, callback );
		}
	}

	// send setup notification
	service.notifyWelcome = function( id_admin, callback ){
		var message = 'welcome';
		if( id_admin ){
			service.notify( id_admin, message, callback );
		}
	}

	// send notification
	service.notify = function( id_admin, message, callback ){
		var params = { id_admin : id_admin, message : message  };
		drivers.notify( params, function( data ){
			callback( data );
		} );
	}

	// save driver info
	service.save = function( driver, callback ){
		var notify = driver.notify;
		drivers.save( driver, function( json ){
			callback( json );
		} );
	}

	// referral code
	service.referral = function( phone, name, callback ){
		drivers.referral( { 'phone': phone, 'name': name }, function( json ){
			callback( json );
		} );
	}

	service.pexcard = function( callback ){
		drivers.pexcard( {}, function( data ){
			callback( data );
		} );
	}

	service.docs = {};

	service.docs.listDocs = function( page, callback ){
		documents.list( { page : page }, function( data ){
			callback( data );
		} );
	}

	service.docs.approve = function( id_driver_document_status, approve, callback ){
		var disapprove = ( approve ) ? null : 'disapprove';
		documents.approve( { id_driver_document_status: id_driver_document_status, disapprove: disapprove }, function( data ){
			callback( data );
		} );
	}

	service.docs.remove = function( id_driver_document_status, callback ){
		documents.remove( { id_driver_document_status: id_driver_document_status }, function( data ){
			callback( data );
		} );
	}

	service.docs.download = function( id_driver_document_status ){
		var url =  App.service + 'driver/documents/download/' + id_driver_document_status;
		$window.open( url );
	}

	// get docs list
	service.docs.list = function( id_admin, callback ){
		if( id_admin ){
			documents.status( { 'id_admin': id_admin }, function( docs ){
				callback( docs );
			} );
		}
	}

	service.docs.pendency = function( id_admin, callback ){
		if( id_admin ){
			documents.pendency( { 'id_admin': id_admin }, function( data ){
				callback( data );
			} );
		}
	}

	// save driver's doc
	service.docs.save = function( doc, callback ){
		documents.save( doc, function( doc ){
			callback( doc );
		} );
	}

	// get admin
	service.get = function( id_admin, callback ){
		if( id_admin ){
			drivers.get( { 'id_admin': id_admin }, function( driver ){
				callback( driver );
			} );
		} else {
			defaults.options( {}, function( json ){
				callback( json );
			} );
			// callback( {} );
		}
	}

	return service;
} );