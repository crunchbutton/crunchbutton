NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/quotes', {
			action: 'quotes',
			controller: 'CommunityQuotesCtrl',
			templateUrl: '/assets/view/quotes.html',
			reloadOnSearch: false
		})
		.when('/quote/:id?', {
			action: 'quotes',
			controller: 'CommunityQuoteCtrl',
			templateUrl: '/assets/view/quotes-quote.html'
		});
}]);

NGApp.controller('CommunityQuotesCtrl', function ($rootScope, $scope, ViewListService, QuoteService, CommunityService ) {

	$rootScope.title = 'Quotes';

	$scope.show_more_options = false;

	$scope.moreOptions = function(){

		$scope.show_more_options = !$scope.show_more_options;

		if( !$scope.communities ){
			CommunityService.listSimple( function( json ){
				$scope.communities = [];
				$scope.communities.push( { 'name': 'All', 'id_community': 'all' } );
				angular.forEach( json, function( community, key ) {
					this.push( { 'name': community.name, 'id_community': community.id_community } );
				}, $scope.communities );
			} );
		}

	}

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			community: 'all'
		},
		update: function() {
			QuoteService.list( $scope.query, function(d) {
				$scope.quotes = d.results;
				$scope.complete(d);
			});
		}
	});
} );

NGApp.controller( 'CommunityQuoteCtrl', function ($scope, $routeParams, $rootScope, $location, QuoteService, CommunityService, RestaurantService ) {

	$scope.quote = { communities: [] };

	$scope.save = function(){
		if( $scope.isUploading || $scope.isSaving ){
			return;
		}
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			App.alert( 'Please fill all the required fields!' );
			return;
		}
		save();
	}

	var save = function(){
		$scope.isSaving = true;
		QuoteService.save( $scope.quote, function( json ){
			$scope.isUploading = false;
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				if( $routeParams.id ){
					App.alert( 'Quote saved!' );
					load();
				} else {
					$location.path( '/quote/' + json.id_quote );
				}
			}
		} );
	}

	$scope.$watch( 'quote.communities', function( newValue, oldValue, scope ) {
		if( newValue && newValue.length == 1 ){
			restaurants();
		} else {
			$scope.restaurants = [];
			$scope.quote.restaurants = [];
		}
	});

	var restaurants = function(){
		$scope.restaurants = [];
		var id_community = $scope.quote.communities[ 0 ];
		if( id_community ){
			RestaurantService.shortlistByCommunity( id_community, function( json ){
				angular.forEach( json, function( restaurant, key ) {
					var ticked = ( $scope.quote.restaurants.indexOf( restaurant.id_restaurant ) >= 0 );
					this.push( { 'name': restaurant.name, 'id_restaurant': restaurant.id_restaurant, 'ticked': ticked} );
				}, $scope.restaurants );
				$scope.ready = true;
			} );
		}
	}

	var communities = function(){
		if( $scope.communities ){
			return;
		}
		CommunityService.listSimple( function( json ){
			$scope.communities = [];
			angular.forEach( json, function( community, key ) {
				var ticked = ( $scope.quote.communities.indexOf( community.id_community ) >= 0 );
				this.push( { 'name': community.name, 'id_community': community.id_community, 'ticked': ticked} );
			}, $scope.communities );
			$scope.ready = true;
		} );
	}

	$scope.quote = { 'all': true, 'active': true, 'communities': [], 'all_restaurants': true, restaurants: [] };

	var load = function(){
		QuoteService.get( $routeParams.id, function( json ){
			$scope.quote = json;
			communities();
			restaurants();
		} );
	}

	if( $routeParams.id ){
		load();
	} else {
		communities();
		restaurants();
	}

} );
