NGApp.factory( 'PexCardService', function( $resource, $http, $routeParams ) {

	var service = { 'status': { 'OPEN': 'OPEN', 'BLOCKED': 'BLOCKED' } };

	var pexcard = $resource( App.service + 'pexcard/:action/', { action: '@action' }, {
		'pex_id' : { 'method': 'POST', params : { action: 'pex-id' } },
		'id_pexcard' : { 'method': 'POST', params : { action: 'id-pexcard' } },
		'cache' : { 'method': 'POST', params : { action: 'clear-cache' } },
		'admin_pexcard' : { 'method': 'POST', params : { action: 'admin-pexcard' } },
		'driver_search' : { 'method': 'POST', params : { action: 'driver-search' } },
		'driver_active' : { 'method': 'POST', params : { action: 'driver-active' } },
		'add_funds' : { 'method': 'POST', params : { action: 'add-funds' } },
		'admin_pexcard_remove' : { 'method': 'POST', params : { action: 'admin-pexcard-remove' } },
		'pex_change_card_status' : { 'method': 'POST', params : { action: 'pexcard-change-card-status' } },
		'report' : { 'method': 'POST', params : { action: 'report' } },
		'report_dates' : { 'method': 'POST', params : { action: 'report-processed-dates' } },
		'report_old' : { 'method': 'POST', params : { action: 'report-old' } },
		'logs' : { 'method': 'GET', params : { action: 'log' } },
		'cardlog' : { 'method': 'GET', params : { action: 'cardlog' } },
		'action' : { 'method': 'GET', params : { action: 'log' } },
		'list' : {
			url: App.service + 'pexcards',
			method: 'GET',
			params : {},
			isArray: true
		}
	});

	var config = $resource( App.service + 'config/pexcard/:action', { action: '@action' }, {
				// list methods
				'config' : { 'method': 'GET', params : { 'action' : 'config' } },
				'config_save' : { 'method': 'POST', params : { 'action' : 'config' } },
				'config_value' : { 'method': 'POST', params : { 'action' : 'config-value' } },
				'add_business' : { 'method': 'POST', params : { 'action' : 'add-business' } },
				'remove_business' : { 'method': 'POST', params : { 'action' : 'remove-business' } },
				'add_test' : { 'method': 'POST', params : { 'action' : 'add-test' } },
				'remove_test' : { 'method': 'POST', params : { 'action' : 'remove-test' } },
			}
		);

	service.config = {
		load: function( callback ){
			config.config( function( data ){
				callback( data );
			} );
		},
		value: function( key, callback ){
			config.config_value( { key: key }, function( data ){
				callback( data );
			} );
		},
		add_business: function( params, callback ){
			config.add_business( params, function( data ){
				callback( data );
			} );
		},
		remove_business: function( params, callback ){
			config.remove_business( params, function( data ){
				callback( data );
			} );
		},
		add_test: function( params, callback ){
			config.add_test( params, function( data ){
				callback( data );
			} );
		},
		remove_test: function( params, callback ){
			config.remove_test( params, function( data ){
				callback( data );
			} );
		},
		save: function( params, callback ){
			config.config_save( params, function( data ){
				callback( data );
			} );
		}
	}

	service.pex_id = function( id, callback ){
		pexcard.pex_id( { 'id': id }, function( data ){
			callback( data );
		} );
	}

	service.id_pexcard = function( id, callback ){
		pexcard.id_pexcard( { 'id': id }, function( data ){
			callback( data );
		} );
	}

	service.cache = function( id, callback ){
		pexcard.cache( { 'id': id }, function( data ){
			callback( data );
		} );
	}

	service.action = function( id_pexcard_action, callback ){
		pexcard.action( { 'id_pexcard_action': id_pexcard_action }, function( data ){
			callback( data );
		} );
	}

	service.list = function(callback ){
		pexcard.list( {}, function( data ){
			callback( data );
		} );
	}


	service.driver_search = function( params, callback ){
		pexcard.driver_search( params, function( data ){
			callback( data );
		} );
	}

	service.report = function( params, callback ){
		pexcard.report( params, function( data ){
			callback( data );
		} );
	}

	service.report_dates = function( callback ){
		pexcard.report_dates( {}, function( data ){
			callback( data );
		} );
	}



	service.report_old = function( params, callback ){
		pexcard.report_old( params, function( data ){
			callback( data );
		} );
	}

	service.add_funds = function( params, callback ){
		pexcard.add_funds( params, function( data ){
			callback( data );
		} );
	}

	service.pex_change_card_status = function( params, callback ){
		pexcard.pex_change_card_status( params, function( data ){
			callback( data );
		} );
	}

	service.driver_active = function( id_pexcard, callback ){
		pexcard.driver_active( { 'id_pexcard': id_pexcard }, function( data ){
			callback( data );
		} );
	}

	service.admin_pexcard = function( params, callback ){
		pexcard.admin_pexcard( params, function( data ){
			callback( data );
		} );
	}

	service.admin_pexcard_remove = function( id_pexcard, callback ){
		pexcard.admin_pexcard_remove( { id_pexcard: id_pexcard }, function( data ){
			callback( data );
		} );
	}

	service.cardlog = function(params, callback) {
		pexcard.cardlog(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.logs = function(params, callback) {
		pexcard.logs(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	return service;

} );