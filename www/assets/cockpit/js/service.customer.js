NGApp.factory( 'CustomerRewardService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var reward = $resource( App.service + 'customer/reward/:action', { action: '@action' }, {
				// list methods
				'config' : { 'method': 'GET', params : { 'action' : 'config' } },
				'config_save' : { 'method': 'POST', params : { 'action' : 'config' } },
			}
		);

	service.reward = {
		config: {
			load: function( callback ){
				reward.config( function( data ){
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