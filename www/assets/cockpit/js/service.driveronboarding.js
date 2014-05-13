NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'drivers'
	var drivers = $resource( App.service + 'driver/:action/:id_admin', { id_admin: '@id_admin', action: '@action' }, {
				'get' : { 'method': 'GET', params : { 'action' : 'driver' } },
				'list' : { 'method': 'GET', params : { action: 'list', id_admin: null } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}	
		);

	// documents resource
	var documents = $resource( App.service + 'driver/documents/:id_admin', { id_admin: '@id_admin' }, {
				'status' : { 'method': 'GET', params : {}, isArray: true },
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
	service.docs = function( callback ){
		var id_admin = $routeParams.id;
		if( id_admin ){
			documents.status( { 'id_admin': id_admin }, function( docs ){ 
				callback( docs ); 
			} );	
		} 
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