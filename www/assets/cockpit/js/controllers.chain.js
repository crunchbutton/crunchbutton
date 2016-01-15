NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/chains', {
			action: 'chains',
			controller: 'ChainsCtrl',
			templateUrl: '/assets/view/chains-hub.html',
			reloadOnSearch: false
		})
		.when('/chains/list', {
			action: 'chains',
			controller: 'ChainsCtrl',
			templateUrl: '/assets/view/chains.html',
			reloadOnSearch: false
		})
		.when('/chain/community/:id?', {
			action: 'chains',
			controller: 'ChainCommunityFormCtrl',
			templateUrl: '/assets/view/chain-community-form.html'
		})
		.when('/chain/communities/', {
			action: 'chains',
			controller: 'ChainsCommunityCtrl',
			templateUrl: '/assets/view/chains-communities.html',
		})
		.when('/chain/:id?', {
			action: 'chains',
			controller: 'ChainFormCtrl',
			templateUrl: '/assets/view/chain-form.html'
		})
		.when('/chain/', {
			action: 'chains',
			controller: 'ChainFormCtrl',
			templateUrl: '/assets/view/chain-form.html'
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

NGApp.controller('ChainFormCtrl', function ($scope, $routeParams, $rootScope, ChainService ) {
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

	$scope.chain = { 'active': true };

	var chain = function(){
		if( $routeParams.id ){
			ChainService.get( $routeParams.id, function( d ) {
				$rootScope.title = d.name + ' | Chain';
				$scope.chain = d;
			});
		}
		$scope.ready = true;
	}
	chain();
});

NGApp.controller('ChainsCommunityCtrl', function ($rootScope, $scope, ChainService, RestaurantService, CommunityService, CommunityChainService, ViewListService) {
	$rootScope.title = 'Communitie x Chains';
	angular.extend($scope, ViewListService);
	$scope.view({
		scope: $scope,
		allowAll: true,
		watch: {
			search: '',
			community: '',
			chain: '',
			status: 'active',
			fullcount: false
		},
		update: function() {
			CommunityChainService.list($scope.query, function(d) {
				$scope.chains = d.results;
				$scope.complete(d);
			});
		}
	});

	if( !$scope.communities ){
		CommunityService.listSimple( function( json ){
			$scope.communities = json;
		} );
	}
	if( !$scope._chains ){
		ChainService.simple( function( json ){
			$scope._chains = json;
		} );
	}

});


NGApp.controller('ChainCommunityFormCtrl', function ($scope, $routeParams, $rootScope, ChainService, RestaurantService, CommunityService, CommunityChainService ) {

	$scope.ready = false;

	$scope.community_chain = { exist_at_community: false, within_range: true, linked_restaurant: false };

	$scope.$watch( 'community_chain.id_community', function( newValue, oldValue, scope ) {
		$scope.restaurants = null;
		if( newValue ){
			CommunityChainService.shortlistByCommunity( newValue, function( json ){
				$scope.restaurants = json;
			} );
		}
	} );

	var lists = function(){
		if( !$scope.chains ){
			ChainService.simple( function( json ){
				$scope.chains = json;
			} );
		}

		if( !$scope.communities ){
			CommunityService.listSimple( function( json ){
				$scope.communities = json;
			} );
		}
		$scope.ready = true;
	}

	var load = function(){

		if( $routeParams.id ){
			CommunityChainService.get( $routeParams.id, function( d ) {
				$scope.community_chain = d;
				lists();
				$scope.ready = true;
			});
		} else {
			lists();
		}
	}

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		if( !$scope.community_chain.id_chain ){
			App.alert( 'Please select a chain!' );
			return;
		}

		if( !$scope.community_chain.id_community ){
			App.alert( 'Please select a community!' );
			return;
		}

		if( $scope.community_chain.linked_restaurant && !$scope.community_chain.id_restaurant ){
			App.alert( 'Please select a restaurant!' );
			return;
		}

		$scope.isSaving = true;
		CommunityChainService.save( $scope.community_chain, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.navigation.link( '/chain/communities/'  );
			}
		} );
	}

	load();
});

