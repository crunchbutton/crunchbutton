NGApp.factory( 'MarketingService', function( $resource, $http, $routeParams ) {

	var pexcard = $resource( App.service + 'marketing/:action/', { action: '@action' }, {
		'outgoing' : { 'method': 'GET', params : { action: 'outgoing' } }
	}	);


	service.outgoing = function(params, callback) {
		pexcard.outgoing(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	return service;

} );