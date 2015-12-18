NGApp.factory( 'GiftCardService', function( $rootScope, $resource, ResourceFactory ) {

	// Create a private promo 'drivers'
	var promo = $resource( App.service + 'promo/giftcard/:action/:id_promo', { action: '@action' }, {
				'generate' : { 'method': 'POST', params : { action: 'generate' } },
				'create' : { 'method': 'POST', params : { action: 'create' } },
				'get' : { 'method': 'GET', params : { } },
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true }
			}
		);


	var promos = ResourceFactory.createResource( App.service + 'promo/giftcard/list', {}, {
		'query' : {
			method: 'GET',
			params : {}
		},
	});

	var service = {}

	service.get = function( id_promo, callback ){
		promo.get( { 'id_promo': id_promo }, function( json ){
			callback( json );
		} );
	}

	service.generate = function( giftcard, callback ){
		promo.generate( giftcard, function( json ){
			callback( json );
		} );
	}

	service.create = function( giftcard, callback ){
		promo.create( giftcard, function( json ){
			callback( json );
		} );
	}

	service.list = function(params, callback) {
		promos.query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.paidBy = function(){
		var methods = [];
		methods.push( { value: 'CRUNCHBUTTON', label: 'Crunchbutton' } );
		methods.push( { value: 'RESTAURANT', label: 'Restaurant' } );
		methods.push( { value: 'PROMOTIONAL', label: 'Promotional' } );
		return methods;
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	return service;
} );