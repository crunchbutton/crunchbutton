NGApp.factory( 'ConfigService', function( $resource ) {

	var service = {};

	var settings = $resource( App.service + 'config/:action', { action: '@action' }, {
				'processor' : { 'method': 'GET', params : { 'action' : 'processor' } },
			}
		);

	service.getProcessor = function( callback ){
		if( service._processor ){
			callback( service._processor );
		} else {
			service.processor( function( data ){
				service._processor = data;
				service.getProcessor( callback );
			} )
		}
	}

	service.processor = function( callback ){
		settings.processor( function( data ){
			callback( data );
		} );
	}

	return service;
} );


NGApp.factory( 'CustomerRewardService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var reward = $resource( App.service + 'config/reward/:action', { action: '@action' }, {
				// list methods
				'config' : { 'method': 'GET', params : { 'action' : 'config' } },
				'config_save' : { 'method': 'POST', params : { 'action' : 'config' } },
				'config_value' : { 'method': 'POST', params : { 'action' : 'config-value' } },
			}
		);

	service.constants = {
		'key_admin_refer_user_amt': 'reward_points_admin_refer_user_amt',
		'key_customer_get_referred_amt': 'reward_points_get_referred_discount_amt'
	}

	service.reward = {
		config: {
			load: function( callback ){
				reward.config( function( data ){
					callback( data );
				} );
			},
			value: function( key, callback ){
				reward.config_value( { key: key }, function( data ){
					callback( data );
				} );
			},
			save: function( params, callback ){
				reward.config_save( params, function( data ){
					callback( data );
				} );
			}
		}
	}

	return service;

} );

NGApp.factory( 'RulesService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var rules = $resource( App.service + 'config/rules/:action', { action: '@action' }, {
				// list methods
				'config' : { 'method': 'GET', params : { 'action' : 'config' } },
				'config_save' : { 'method': 'POST', params : { 'action' : 'config' } },
				'config_value' : { 'method': 'POST', params : { 'action' : 'config-value' } },
			}
		);

	service.rules = {
		config: {
			load: function( callback ){
				rules.config( function( data ){
					callback( data );
				} );
			},
			value: function( key, callback ){
				rules.config_value( { key: key }, function( data ){
					callback( data );
				} );
			},
			save: function( params, callback ){
				rules.config_save( params, function( data ){
					callback( data );
				} );
			}
		}
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}


	return service;

} );

NGApp.factory( 'ConfigAutoReplyService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var settings = $resource( App.service + 'config/autoreply/:action', { action: '@action' }, {
				// list methods
				'load' : { 'method': 'GET', params : { 'action' : 'load' }, isArray: true },
				'save' : { 'method': 'POST', params : { 'action' : 'save' } },
				'remove' : { 'method': 'POST', params : { 'action' : 'remove' } },
			}
		);

	service.load = function( callback ){
			settings.load( function( data ){
				callback( data );
			} );
		};

	service.save = function( params, callback ){
			settings.save( params, function( data ){
				callback( data );
			} );
		};

	service.remove = function( id_config, callback ){
			settings.remove( { 'id_config' : id_config }, function( data ){
				callback( data );
			} );
		};

	return service;

} );