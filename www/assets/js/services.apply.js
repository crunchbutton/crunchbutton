
NGApp.factory('ApplyService', function($resource) {

	var service = {};

	var apply = $resource(App.service + 'apply', {}, {
		
		'save' : {
			url: App.service + 'apply',
			method: 'POST',
			params : {}
		}
		
	});

	var communities = $resource( App.service + 'community/:action/', { action: '@action' }, {
		'list' : { 'method': 'GET', params : { 'action' : 'apply-list' }, isArray: true },
			}
		);
	
	service.communities = function( callback ){
		communities.list( function( data ){
			callback( data );
		} );
	}

	service.post = function(params, callback) {
		apply.save(params, function(data) {
			callback(data);
		});
	}

	return service;

});