NGApp.factory( 'CommunityService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	// Create a private resource 'community'
	var communities = $resource( App.service + 'community/:action', { action: '@action' }, {
				// list methods
				'listSimple' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
			}
		);

	var community = ResourceFactory.createResource(App.service + 'communities/:id_community/', { id_community: '@id_community', action: '@action' }, {
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
	});

	var aliases = $resource( App.service + 'community/:permalink/aliases/:action', { permalink: '@permalink', action: '@action' }, {
				// list methods
				'list' : { 'method': 'GET', params : { 'action' : null }, isArray: true },
				'add' : { 'method': 'POST', params : { 'action' : 'add' } },
				'remove' : { 'method': 'POST', params : { 'action' : 'remove' } }
			}
		);

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
		console.log('params',params);
		community.save(params,  function(data) {
			callback(data);
		});
	}

	service.listSimple = function( callback ){
		communities.listSimple( function( data ){
			callback( data );
		} );
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	service.timezones = function(){
		var timezones = [];
		timezones.push( { value: 'America/New_York', label: 'Eastern' } );
		timezones.push( { value: 'America/Chicago', label: 'Central' } );
		timezones.push( { value: 'America/Denver', label: 'Mountain' } );
		timezones.push( { value: 'America/Los_Angeles', label: 'Pacific' } );
		return timezones;
	}

	return service;
} );