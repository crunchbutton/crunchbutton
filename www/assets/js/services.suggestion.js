NGApp.factory( 'SuggestionService', function( $resource ) {

	var service = {};

	var suggestion = $resource( App.service + 'suggestion/:action/', { action: '@action' }, {
			'save' : { 'method': 'POST', params : { 'action' : 'save-suggestion' } }
		} );

	service.save = function( data, callback ){
		suggestion.save( data, function( json ){
			callback( json );
		} );
	}

	return service;
} );