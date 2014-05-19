NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'drivers'
	var drivers = $resource( App.service + 'driver/:action/:id_admin/:page/:search/:phone', { id_admin: '@id_admin', action: '@action' }, {
				'get' : { 'method': 'GET', params : { action : null } },
				'notify' : { 'method': 'POST', params : { action: 'notify' } },
				'list' : { 'method': 'GET', params : { action: 'list', id_admin: null } },
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'setupValidate' : { 'method': 'GET', params : { action: 'setup' } },
				'setupSave' : { 'method': 'POST', params : { action: 'setup' } },
			}	
		);

	// documents resource
	var documents = $resource( App.service + 'driver/documents/:action/:id_admin/:id_driver_document', { id_admin: '@id_admin', id_driver_document: '@id_driver_document' }, {
				'status' : { 'method': 'GET', params : { action : null }, isArray: true },
				'save' : { 'method': 'POST', params : { action : 'save' } },
			}	
		);

	// logs resource
	var log = $resource( App.service + 'driver/log/:id_admin', { id_admin: '@id_admin' }, {
				'get' : { 'method': 'GET', params : {}, isArray: true },
			}	
		);

	service.logs = function( callback ){
		var id_admin = $routeParams.id;
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
			if( json.success && notify ){
				service.notifySetup( json.success.id_admin, function( json ){
					if( json.success ){
						$rootScope.flash.setMessage( 'Notification sent!' );
					} else {
						$rootScope.flash.setMessage( 'Notification not sent: ' + json.error , 'error' );	
					}
				} );
			}
		} );
	}

	// get driver's list
	service.list = function( page, search, callback ){
		drivers.list( { page : page, search : search }, function( data ){ 
			callback( data ); 
		} );
	}

	// returns the driver's docs
	service.docs = {};

	// get docs list
	service.docs.list = function( callback ){
		var id_admin = $routeParams.id;
		if( id_admin ){
			documents.status( { 'id_admin': id_admin }, function( docs ){ 
				callback( docs ); 
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
	service.get = function( callback ){
		var id_admin = $routeParams.id;
		if( id_admin ){
			drivers.get( { 'id_admin': id_admin }, function( driver ){ 
				if( driver.communities ){
					angular.forEach( driver.communities, function( name, id_community ){
						driver.id_community = id_community;
					} );
				}
				callback( driver ); 
			} );	
		} else {
			callback( {} ); 
		}
	}

	return service;
} );