NGApp.factory( 'CommunityService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	// Create a private resource 'community'
	var communities = $resource( App.service + 'community/:action/:alias', { action: '@action', alias: '@alias' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
				'by_alias' : { 'method': 'GET', params : { 'action' : 'by-alias' } },
			}
		);

	var community = ResourceFactory.createResource(App.service + 'communities/:id_community/:action', { id_community: '@id_community', action: '@action' }, {
		'load' : {
			url: App.service + 'community/:id_community',
			method: 'GET',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'community/:id_community/:action',
			method: 'POST',
			params : { 'action' : 'save' }
		},
		'saveOpenClose' : {
			url: App.service + 'community/:id_community/:action',
			method: 'POST',
			params : { 'action' : 'save-open-close' }
		}
		/*,
		@depreciated
		'closed' : {
			method: 'GET',
			params : { 'action': 'closed' },
			isArray: true
		},
		*/
	});

	var aliases = $resource( App.service + 'community/:permalink/aliases/:action', { permalink: '@permalink', action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : null }, isArray: true },
				'add' : { 'method': 'POST', params : { 'action' : 'add' } },
				'remove' : { 'method': 'POST', params : { 'action' : 'remove' } }
			}
		);

	var closelog = $resource( App.service + 'community/:permalink/closelog', { action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : null }, isArray: true }
			}
		);

	service.closelog = {
		list: function( permalink, callback ){
			closelog.list( { permalink: permalink }, function( data ){
				callback( data );
			} );
		}
	}

	service.alias = {
		list: function( permalink, callback ){
			aliases.list( { permalink: permalink }, function( data ){
				callback( data );
			} );
		},
		add: function( params, callback ){
			aliases.add( params, function( data ){
				callback( data );
			} );
		},
		remove: function( params, callback ){
			aliases.remove( params, function( data ){
				callback( data );
			} );
		}
	}

	/*
	@depreciated
	service.closed = function( callback ) {
		community.closed( function(data) {
			callback( data );
		});
	}
	*/

	service.list = function(params, callback) {
		community.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_community, callback) {
		community.load({id_community: id_community},  function(data) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		community.save(params,  function(data) {
			callback(data);
		});
	}

	service.saveOpenClose = function(params, callback) {
		community.saveOpenClose(params,  function(data) {
			callback(data);
		});
	}

	service.listSimple = function( callback ){
		communities.listSimple( function( data ){
			callback( data );
		} );
	}

	service.by_alias = function( alias, callback ){
		communities.by_alias( { alias: alias }, function( data ){
			callback( data );
		} );
	}



	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	service.timezones = function(){
		var timezones = [];
		timezones.push( { value: 'America/New_York', label: 'Eastern' } );
		timezones.push( { value: 'America/Chicago', label: 'Central' } );
		timezones.push( { value: 'America/Denver', label: 'Mountain' } );
		timezones.push( { value: 'America/Phoenix', label: 'Arizona (no DST)' } );
		timezones.push( { value: 'America/Los_Angeles', label: 'Pacific' } );
		return timezones;
	}

	return service;
} );