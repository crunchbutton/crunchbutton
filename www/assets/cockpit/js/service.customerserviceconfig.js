NGApp.factory( 'CustomerServiceConfig', function( $rootScope, $resource, $routeParams, ResourceFactory ) {

	var service = {};

	var config = $resource( App.service + 'customerservice/config', {}, {
				'save' : {'method': 'POST', params: {}},
				'load' : {'method': 'GET'},
			}
		);

	service.load = function(callback){
		config.load(function( data ){
			callback( data );
		} );
	}

	service.save = function(params, callback){
		config.save( params, function( data ){
			callback( data );
		} );
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	return service;
});