
NGApp.factory('SuggestionService', function(ResourceFactory) {

	var service = {};

	var suggestion = ResourceFactory.createResource(App.service + 'suggestions/:id_user', { id_user: '@id_user'}, {
		'load' : {
			url: App.service + 'suggestion/:id_user',
			method: 'GET',
			params : {}
		},
		'remove' : {
			url: App.service + 'suggestion/delete/',
			method: 'POST',
			params : {}
		},
		'apply' : {
			url: App.service + 'suggestion/apply/',
			method: 'POST',
			params : {}
		},
		'query' : {
			method: 'GET',
			params : {}
		}
	});

	service.list = function(params, callback) {
		suggestion.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.get = function(id_user, callback) {
		suggestion.load({id_user: id_user}, function(data) {
			callback(data);
		});
	}

	service.apply = function( id_suggestion, callback) {
		suggestion.apply({ 'id_suggestion': id_suggestion }, function(data) {
			callback(data);
		});
	}

	service.remove = function( id_suggestion, callback) {
		suggestion.remove({ 'id_suggestion': id_suggestion }, function(data) {
			callback(data);
		});
	}

	service._types = function(){
		var methods = [];
		methods.push( { value: 'all', label: 'All' } );
		methods.push( { value: 'dish', label: 'Add Item' } );
		methods.push( { value: 'restaurant', label: 'Add Restaurant' } );
		methods.push( { value: 'suggestion', label: 'Suggestion' } );
		methods.push( { value: 'email', label: 'Email me' } );
		return methods;
	}

	service._statuses = function(){
		var methods = [];
		methods.push( { value: 'all', label: 'All' } );
		methods.push( { value: 'new', label: 'New' } );
		methods.push( { value: 'deleted', label: 'Rejected' } );
		methods.push( { value: 'applied', label: 'Applied' } );
		return methods;
	}

	return service;

});