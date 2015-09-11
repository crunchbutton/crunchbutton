NGApp.factory( 'QuoteService', function( $rootScope, $resource, ResourceFactory ) {

	// Create a private resource 'drivers'
	var quote = $resource( App.service + 'quotes/:action/:id_quote', { id_quote: '@id_quote', action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'get' : { 'method': 'GET', params : { } },
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true }
			}
		);


	var quotes = ResourceFactory.createResource(App.service + 'quotes/list', {}, {
		'query' : {
			method: 'GET',
			params : {}
		},
	});

	var service = {}

	service.get = function( id_quote, callback ){
		quote.get( { 'id_quote': id_quote }, function( json ){
			callback( json );
		} );
	}

	service.driver = function( callback ){
		quote.driver( { }, function( json ){
			callback( json );
		} );
	}

	service.save = function( _quote, callback ){
		quote.save( _quote, function( json ){
			callback( json );
		} );
	}

	service.list = function(params, callback) {
		quotes.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	return service;
} );
