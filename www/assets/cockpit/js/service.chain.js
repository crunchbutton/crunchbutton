NGApp.factory( 'ChainService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var chain = ResourceFactory.createResource(App.service + 'chains/:id_chain/:action', { id_chain: '@id_chain', action: '@action' }, {
		'load' : {
			url: App.service + 'chain/:id_chain',
			method: 'GET',
			params : {}
		},
		'simple' :{
			url: App.service + 'chain/:id_chain/:action',
			method: 'GET',
			params : { 'action' : 'simple' },
			isArray: true
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'chain/:id_chain/:action',
			method: 'POST',
			params : { 'action' : 'save' }
		}
	});

	service.list = function(params, callback) {
		chain.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.simple = function(callback) {
		chain.simple({},  function(data) {
			callback(data);
		});
	}

	service.get = function(id_chain, callback) {
		chain.load({id_chain: id_chain},  function(data) {
			callback(data);
		});
	}

	service.actives = function(){
		var params = { 'status': 'active', 'limit': none };
		chain.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		chain.save(params,  function(data) {
			callback(data);
		});
	}

	return service;
} );

NGApp.factory( 'CommunityChainService', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var chain = ResourceFactory.createResource(App.service + 'communities/chains/:id_community_chain/:action', { id_community_chain: '@id_community_chain', action: '@action' }, {
		'load' : {
			url: App.service + 'community/chain/:id_community_chain',
			method: 'GET',
			params : {}
		},
		'by_community' : {
			url: App.service + 'community/chain/by-community/:id_community',
			method: 'GET',
			params : {},
			isArray: true
		},
		'query' : {
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'community/chain/:id_community_chain/:action',
			method: 'POST',
			params : { 'action' : 'save' }
		}
	});

	service.list = function(params, callback) {
		chain.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.shortlistByCommunity = function( id_community, callback ){
		chain.by_community( { id_community:id_community }, function( data ){
			callback( data );
		} );
	}


	service.get = function(id_community_chain, callback) {
		chain.load({id_community_chain: id_community_chain},  function(data) {
			callback(data);
		});
	}

	service.save = function(params, callback) {
		chain.save(params,  function(data) {
			callback(data);
		});
	}

	return service;
} );