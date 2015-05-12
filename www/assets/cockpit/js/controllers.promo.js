NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/promo/discount-codes/', {
			action: 'promo',
			controller: 'PromoDiscountCodesCtrl',
			templateUrl: 'assets/view/promo-discount-codes.html'

		})
		.when('/promo/discount-code/:id?', {
			action: 'promo',
			controller: 'PromoDiscountCodeCtrl',
			templateUrl: 'assets/view/promo-discount-code-form.html'
		});
}]);

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

	$scope.$watch( 'outputCommunities', function( newValue, oldValue, scope ) {
		if( $scope.promo ){
			$scope.promo.id_community = null;
			angular.forEach( newValue, function( community, key ){
				$scope.promo.id_community = community.id_community;
			} );
		}
	});

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

			if( !$scope.promo.id_community && $scope.promo.id_community != '' ){
				$scope.promo.all = 1;
			} else {
				$scope.promo.all = 0;
			}
			communities();
		} )
	} else {
		$scope.promo = { 'paid_by': 'CRUNCHBUTTON', 'delivery_fee': 0, 'active': 1, 'usable_by': 'anyone', 'all': 1 };
		communities();
	}
});
