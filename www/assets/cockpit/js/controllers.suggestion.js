NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/suggestions', {
			action: 'suggestions',
			controller: 'SuggestionsCtrl',
			templateUrl: 'assets/view/suggestions.html',
			reloadOnSearch: false

		});
}]);

NGApp.controller('SuggestionsCtrl', function ($rootScope, $scope, SuggestionService, ViewListService) {

	$rootScope.title = 'Suggestions';

	$scope._statuses = SuggestionService._statuses();
	$scope._types = SuggestionService._types();

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			'search': '',
			'type': 'all',
			'status': 'new'
		},
		update: function() {
			update();
		}
	});

	$scope.apply = function( id_suggestion ){
		SuggestionService.apply( id_suggestion, function( json ){
			if( json.success ){
				update();
			} else {
				App.alert( 'Error: ' + json.error , 'error' );
			}
		} );
	}

	$scope.remove = function( id_suggestion ){
		SuggestionService.remove( id_suggestion, function( json ){
		if( json.success ){
				update();
			} else {
				App.alert( 'Error: ' + json.error , 'error' );
			}
		} );
	}

	var update = function(){
		SuggestionService.list( $scope.query, function(d) {
			$scope.suggestions = d.results;
			$scope.complete( d );
		});
	}



});