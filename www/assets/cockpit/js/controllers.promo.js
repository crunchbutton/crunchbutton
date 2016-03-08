NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/promo/', {
			action: 'giftcard',
			controller: 'PromoCtrl',
			templateUrl: '/assets/view/promo.html'

		})
		.when('/promo/discount-codes/', {
			action: 'giftcard',
			controller: 'PromoDiscountCodesCtrl',
			templateUrl: '/assets/view/promo-discount-codes.html',
			reloadOnSearch: false
		})
		.when('/promo/discount-code/:id?', {
			action: 'giftcard',
			controller: 'PromoDiscountCodeCtrl',
			templateUrl: '/assets/view/promo-discount-code-form.html'
		})
		.when('/promo/gift-cards/', {
			action: 'giftcard',
			controller: 'GiftCardsCtrl',
			templateUrl: '/assets/view/promo-gift-cards.html'

		})
		.when('/promo/gift-card/generate', {
			action: 'giftcard',
			controller: 'GiftCardGenerateCtrl',
			templateUrl: '/assets/view/promo-gift-card-generate.html'
		})
		.when('/promo/gift-card/create', {
			action: 'giftcard',
			controller: 'GiftCardCreateCtrl',
			templateUrl: '/assets/view/promo-gift-card-create.html'
		});
}]);

NGApp.controller('PromoCtrl', function ($scope) {});

NGApp.controller('PromoDiscountCodesCtrl', function ($rootScope, $scope, ViewListService, PromoDiscountCodeService, CommunityService ) {

	$rootScope.title = 'Discount code';

	CommunityService.listSimple( function( json ){
		$scope.communities = [];
		$scope.communities.push( { 'name': 'All', 'id_community': 'all' } );
		angular.forEach( json, function( community, key ) {
  		this.push( { 'name': community.name, 'id_community': community.id_community } );
		}, $scope.communities );
	} );

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			community: 'all'
		},
		update: function() {
			PromoDiscountCodeService.list( $scope.query, function(d) {
				$scope.promos = d.results;
				$scope.complete(d);
			});
		}
	});
} );

NGApp.controller('GiftCardsCtrl', function ($rootScope, $scope, ViewListService, GiftCardService, CommunityService ) {

	$rootScope.title = 'Discount code';

	$scope.yesNo = GiftCardService.yesNo();

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			redeemed: 'all'
		},
		update: function() {
			GiftCardService.list( $scope.query, function(d) {
				$scope.promos = d.results;
				$scope.complete(d);
			});
		}
	});
} );

NGApp.controller( 'GiftCardCreateCtrl', function ($scope, $routeParams, $filter, GiftCardService ) {

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		GiftCardService.create( $scope.promo, function( json ){
			$scope.isSaving = false;
			if( json.success ){
				App.alert( 'Gift Cards created!');
				$scope.navigation.link( '/promo/gift-cards' );
			} else {
				App.alert( 'Error saving: ' + json.error + '<br>' );
			}
		} );
	}

	$scope.ready = true;
	$scope.yesNo = GiftCardService.yesNo();
	$scope.paidBy = GiftCardService.paidBy();
	$scope.promo = { 	'paid_by': 'CRUNCHBUTTON',
										'value': 1,
										'random_code': 0,
										'notify_email': 0,
										'notify_phone': 0,
										'random_code': false };

});

NGApp.controller( 'GiftCardGenerateCtrl', function ($scope, $routeParams, $filter, GiftCardService ) {

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		GiftCardService.generate( $scope.promo, function( json ){
			$scope.isSaving = false;
			if( json.success ){
				App.alert( json.success + ' Gift Cards saved!');
				$scope.navigation.link( '/promo/gift-cards' );
			} else {
				App.alert( 'Error saving: ' + json.error );
			}
		} );
	}

	$scope.ready = true;
	$scope.yesNo = GiftCardService.yesNo();
	$scope.paidBy = GiftCardService.paidBy();
	$scope.promo = { 'paid_by': 'CRUNCHBUTTON', 'value': 1, 'total': 1, 'chars_length': 7, 'include_gift_card_id': true, 'use_numbers': true, 'use_letters': false, 'exclude_chars': '0O', 'prefix': '' };

	$scope.$watchCollection( 'promo', function( newValue, oldValue, scope ) {
		updateInfo();
	});

	var updateInfo = function(){
		var letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		var numbers = '0123456789';
		var exclude_chars = $( '#exclude_chars' ).val();
		var chars_to_use = '';

		if( $scope.promo.use_numbers ){
			chars_to_use += numbers;
		}
		if( $scope.promo.use_letters ){
			chars_to_use += letters;
		}
		var exclude_chars = $scope.promo.exclude_chars;
		exclude_chars = exclude_chars.split('');
		$( exclude_chars ).each( function( k, v ){
			chars_to_use = chars_to_use.replace( v.toUpperCase(), '' );
		} );
		$scope.promo.chars_to_use = chars_to_use;

		var length = $scope.promo.chars_length;
		if( parseInt( length ) > 12 ){
			length = 12;
		}
		if( parseInt( length ) < 7 ){
			length = 7;
		}
		$scope.promo.chars_length = length;

		length = length - $scope.promo.prefix.length;
		if( $scope.promo.include_gift_card_id ){
			length -= 6;
		}

		var possible_combinations = Math.pow( chars_to_use.length, length );

		if( $scope.promo.include_gift_card_id ){
			possible_combinations = parseInt( possible_combinations ) + $scope.promo.total;
		}

		if( possible_combinations >= 1 && possible_combinations > $scope.promo.total ){
			$scope.is_ok_to_generate = true;
		} else {
			$scope.is_ok_to_generate = false;
		}
		$scope.possible_combinations = possible_combinations;
	}

});


NGApp.controller( 'PromoDiscountCodeCtrl', function ($scope, $routeParams, $filter, PromoDiscountCodeService, CommunityService ) {

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.promo.date_start = $filter( 'date' )( $scope.date_start, 'yyyy-MM-dd' );
		$scope.promo.date_end = $filter( 'date' )( $scope.date_end, 'yyyy-MM-dd' );


		$scope.isSaving = true;

		PromoDiscountCodeService.save( $scope.promo, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				if( $routeParams.id ){
					App.alert( 'Discount code saved!' );
				}
				$scope.navigation.link( '/promo/discount-code/' + json.id_promo );
			}
		} );
	}

	$scope.yesNo = PromoDiscountCodeService.yesNo();
	$scope.paidBy = PromoDiscountCodeService.paidBy();
	$scope.usableBy = PromoDiscountCodeService.usableBy();

	var communities = function(){
		CommunityService.listSimple( function( json ){
			$scope.communities = [];
			angular.forEach( json, function( community, key ) {
				var ticked = ( $scope.promo.id_community == community.id_community );
	  		this.push( { 'name': community.name, 'id_community': community.id_community, 'ticked': ticked} );
			}, $scope.communities );
			$scope.ready = true;
		} );
	}

	if( $routeParams.id ){

		PromoDiscountCodeService.get( $routeParams.id, function( json ){

			$scope.promo = json;
			console.log('$scope.promo',$scope.promo);

			if( json.date_start ){
				$scope.date_start = new Date( json.date_start );
			}

			if( json.date_end ){
				$scope.date_end = new Date( json.date_end );
			}

			communities();
		} )
	} else {
		$scope.promo = { 'paid_by': 'CRUNCHBUTTON', 'delivery_fee': false, 'active': true, 'usable_by': 'anyone', 'all': true };
		communities();
	}
});
