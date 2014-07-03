NGApp.factory( 'StaffService', function( $resource, $routeParams ) {

	var service = {};

	// Create a private resource 'staff'
	var staff = $resource( App.service + 'staff/:action/:id_admin', { id_admin: '@id_admin', action: '@action' }, {
				'list' : { 'method': 'POST', params : { action: 'list' } },
			}
		);

	// get staff's list
	service.list = function( search, callback ){
		staff.list( { page : search.page, name : search.name, type: search.type, status: search.status }, function( data ){
			callback( data );
		} );
	}

	service.typeSearch = function(){
		var type = [];
		type.push( { value: 'all', label: 'All' } );
		type.push( { value: 'drivers', label: 'Drivers' } );
		return type;
	}

	service.statusSearch = function(){
		var status = [];
		status.push( { value: 'all', label: 'All' } );
		status.push( { value: 'active', label: 'Active' } );
		status.push( { value: 'inactive', label: 'Inactive' } );
		return status;
	}

	return service;

} );

NGApp.factory( 'StaffPayInfoService', function( $resource, $routeParams, ConfigService ) {

	var service = {};

	// Create a private resource 'staff'
	var staff = $resource( App.service + 'staff/payinfo/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'save_bank' : { 'method': 'POST', params : { action: 'save-bank' } },
			}
		);

	service.load = function( callback ){
		staff.load( { 'id_admin': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.save = function( params, callback ){
		params.id_admin = $routeParams.id;
		staff.save( params, function( data ){
			callback( data );
		} );
	}

	service.save_bank = function( params, callback ){
		params.id_admin = $routeParams.id;
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

	service.bankAccount = function( payload, callback ){
		ConfigService.processor( function( json ){
			if( !json.error && json.processor ){
				var marketplaceUri = json.processor.balanced;
				balanced.init( marketplaceUri );
				balanced.bankAccount.create( payload, function( response ) {
						// Successful tokenization
						if( response.status_code === 201 ) {
							callback( response.bank_accounts[ 0 ] );
						} else {
							console.log( response );
							callback( {} );
						}
				} );
			}
		} );
	}

	return service;

} );