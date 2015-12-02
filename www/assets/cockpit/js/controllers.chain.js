NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/chains', {
			action: 'chains',
			controller: 'ChainsCtrl',
			templateUrl: 'assets/view/chains-hub.html',
			reloadOnSearch: false
		})
		.when('/chains/list', {
			action: 'chains',
			controller: 'ChainsCtrl',
			templateUrl: 'assets/view/chains.html',
			reloadOnSearch: false
		})
		.when('/chain/edit/:id', {
			action: 'chain',
			controller: 'ChainFormCtrl',
			templateUrl: 'assets/view/chain-form.html'
		})
		.when('/chain/new', {
			action: 'chain',
			controller: 'ChainFormCtrl',
			templateUrl: 'assets/view/chain-form.html'
		});

}]);

NGApp.controller('ChainsCtrl', function ($rootScope, $scope, ChainService, ViewListService) {
	$rootScope.title = 'Chains';
	angular.extend($scope, ViewListService);
	$scope.view({
		scope: $scope,
		allowAll: true,
		watch: {
			search: '',
			status: 'active',
			fullcount: false
		},
		update: function() {
			ChainService.list($scope.query, function(d) {
				$scope.chains = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('ChainFormCtrl', function ($scope, $routeParams, $rootScope, $filter, ChainService, MapService ) {

	$scope.ready = false;
	$scope.isSaving = false;

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		ChainService.save( $scope.chain, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.navigation.link( '/chains/list' );
			}
		} );
	}

	var chain = function(){
		if( $routeParams.id ){
			ChainService.get( $routeParams.id, function( d ) {
				$rootScope.title = d.name + ' | Chain';
				$scope.chain = d;
			});
		} else {
			$scope.chain = { 'active': true };
		}
		$scope.ready = true;
	}

	chain();


});

