NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/quotes', {
			action: 'quotes',
			controller: 'CommunityQuotesCtrl',
			templateUrl: 'assets/view/quotes.html',
			reloadOnSearch: false
		})
		.when('/quote/:id?', {
			action: 'quotes',
			controller: 'CommunityQuoteCtrl',
			templateUrl: 'assets/view/quotes-quote.html'
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

NGApp.controller( 'CommunityQuoteCtrl', function ($scope, $routeParams, $rootScope, $location, QuoteService, CommunityService ) {

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

	var load = function(){
		QuoteService.get( $routeParams.id, function( json ){
			$scope.quote = json;
			communities();
		} );
	}

	if( $routeParams.id ){
		load();
	} else {
		$scope.quote = { 'all': false, 'active': true, 'communities': [] };
		communities();
	}

} );
