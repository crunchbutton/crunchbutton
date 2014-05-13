NGApp.factory( 'DriverOnboardingService', function( $rootScope, $resource, $routeParams ) {
	
	var service = {};

	// Create a private resource 'drivers'
	var drivers = $resource( App.service + 'driveronboarding/:action/:id_admin', { id_admin: '@id_admin', action: '@action' }, {
				// actions
				'get' : { 'method': 'GET', params : { 'action' : 'driver' } },
				'list' : { 'method': 'GET', params : { action: 'list', id_admin: null } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
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

	service.get = function( callback ){
		var id_admin = $routeParams.id;
		if( id_admin && id_admin != 'new' ){
			drivers.get( { 'id_admin': id_admin }, function( driver ){ 
				if( driver.communities ){
					angular.forEach( driver.communities, function( name, id_community ){
						driver.id_community = id_community;
					} );
				}
				callback( driver ); 
			} );	
		}
		
	}

	return service;
} );