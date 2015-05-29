NGApp.factory( 'PromoDiscountCodeService', function( $rootScope, $resource, ResourceFactory ) {

	// Create a private promo 'drivers'
	var promo = $resource( App.service + 'promo/discountcode/:action/:id_promo', { action: '@action' }, {
				'save' : { 'method': 'POST', params : { action: 'save' } },
				'get' : { 'method': 'GET', params : { } },
				'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true }
			}
		);


	var promos = ResourceFactory.createResource( App.service + 'promo/discountcode/list', {}, {
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

	service.save = function( _promo, callback ){
		promo.save( _promo, function( json ){
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

	service.usableBy = function(){
		var methods = [];
		methods.push( { value: 'anyone', label: 'All users' } );
		methods.push( { value: 'new-users', label: 'New users' } );
		methods.push( { value: 'old-users', label: 'Existing users' } );
		return methods;
	}

	service.yesNo = function(){
		var methods = [];
		methods.push( { value: 0, label: 'No' } );
		methods.push( { value: 1, label: 'Yes' } );
		return methods;
	}

	return service;
} );