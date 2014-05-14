NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'drivers'
	var drivers = $resource( App.service + 'driver/:action/:id_admin', { id_admin: '@id_admin', action: '@action' }, {
				'get' : { 'method': 'GET', params : { action : 'driver' } },
				'list' : { 'method': 'GET', params : { action: 'list', id_admin: null } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}	
		);

	// documents resource
	var documents = $resource( App.service + 'driver/documents/:action/:id_admin/:id_driver_document', { id_admin: '@id_admin', id_driver_document: '@id_driver_document' }, {
				'status' : { 'method': 'GET', params : { action : null }, isArray: true },
				'save' : { 'method': 'POST', params : { action : 'save' } },
			}	
		);

	service.save = function( driver, callback ){
		drivers.save( driver, function( driver ){
			callback( driver );
		} );
	}

	service.list = function( callback ){
		drivers.query( {}, function( drivers ){ 
			callback( drivers ); 
		} );
	}

	// returns the driver's docs
	service.docs = {};

	service.docs.list = function( callback ){
		var id_admin = $routeParams.id;
		if( id_admin ){
			documents.status( { 'id_admin': id_admin }, function( docs ){ 
				callback( docs ); 
			} );	
		} 
	}

	service.docs.save = function( doc, callback ){
		documents.save( doc, function( doc ){
			callback( doc );
		} ); 
	}

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