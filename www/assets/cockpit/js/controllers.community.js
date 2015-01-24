NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/communities', {
			action: 'communities',
			controller: 'CommunitiesCtrl',
			templateUrl: 'assets/view/communities.html',
			reloadOnSearch: false
		})
		.when('/community/edit/:id', {
			action: 'community',
			controller: 'CommunityFormCtrl',
			templateUrl: 'assets/view/communities-form.html'
		})
		.when('/community/new', {
			action: 'community',
			controller: 'CommunityFormCtrl',
			templateUrl: 'assets/view/communities-form.html'
		})
		.when('/community/:id', {
			action: 'community',
			controller: 'CommunityCtrl',
			templateUrl: 'assets/view/communities-community.html'
		});

}]);

NGApp.controller('CommunitiesCtrl', function ($rootScope, $scope, CommunityService, ViewListService) {
	$rootScope.title = 'Communities';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: ''
		},
		update: function() {
			CommunityService.list($scope.query, function(d) {
				$scope.communities = d.results;
				$scope.complete(d);
			});
		}
	});


 	CommunityService.closed( function( json ){
 		$scope.closed_communities = json;
	} )

});


NGApp.controller('CommunityFormCtrl', function ($scope, $routeParams, $rootScope, CommunityService, MapService ) {

	$scope.ready = false;
	$scope.isSaving = false;
	$scope.isSavingAlias = false;

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		CommunityService.save( $scope.community, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.community = json;
				$scope.navigation.link( '/community/edit/' + json.permalink );
				load_alias();
			}
		} );
	}

	$scope.$watch( 'community.address', function( newValue, oldValue, scope ) {
		var address = newValue;
		if( address ){
			g = new google.maps.Geocoder();
			g.geocode( {address:address},function(data,s) {
				if(s === 'ZERO_RESULTS') { return; }
				if(s !== 'OK') { return; }

				if( !data || !data.length ) { return; }
				$scope.community.loc_lat = data[0].geometry.location.lat();
				$scope.community.loc_lon = data[0].geometry.location.lng();
		});
		}
	});

	$scope.cancel = function(){
		$rootScope.navigation.back();
	}

	var load = function(){
		$scope.timezones = CommunityService.timezones();
		$scope.yesNo = CommunityService.yesNo();
		$scope.ready = true;
	}

	var load_alias = function(){
		console.log('$scope.community.next_sort',$scope.community.next_sort);
		$scope.alias = { id_community: $scope.community.id_community, permalink: $scope.community.permalink, sort: $scope.community.next_sort };
		CommunityService.alias.list( $routeParams.id, function( json ){
			$scope.aliases = json;
		} );
	}

	$scope.remove_alias = function( id_community_alias ){
		if( confirm( 'Confirm remove the alias?' ) ){
			CommunityService.alias.remove( { 'id_community_alias' : id_community_alias, permalink: $scope.community.permalink }, function( data ){
				if( data.error ){
					App.alert( data.error);
					return;
				} else {
					community();
					$scope.flash.setMessage( 'Alias removed!' );
				}
			} );
		}
	}

	$scope.add_alias = function(){

		if( $scope.formAlias.$invalid ){
			$scope.formAliasSubmitted = true;
			return;
		}

		$scope.isSavingAlias = true;

		CommunityService.alias.add( $scope.alias, function( json ){
			$scope.isSavingAlias = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				community();
			}
			$scope.formAliasSubmitted = false;
		} );
	}


	var community = function(){
		if( $routeParams.id ){
			CommunityService.get( $routeParams.id, function( d ) {
				$rootScope.title = d.name + ' | Community';
				$scope.community = d;
				load_alias();
				load();
			});
		} else {
			$scope.community = { 'active': 1, 'private': 0, 'image': 0, 'close_all_restaurants': 0, 'close_3rd_party_delivery_restaurants': 0 };
			load();
		}
	}

	community();


});


NGApp.controller('CommunityCtrl', function ($scope, $routeParams, $rootScope, MapService, CommunityService, RestaurantService, OrderService, StaffService) {
	$scope.loading = true;
	$scope.loadingOrders = true;
	$scope.loadingRestaurants = true;
	$scope.loadingStaff = true;

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		update();
	});

	var load_alias = function(){
		CommunityService.alias.list( $routeParams.id, function( json ){
			$scope.aliases = json;
		} );
	}

	var load_closelog = function(){
		CommunityService.closelog.list( $routeParams.id, function( json ){
			$scope.closelogs = json;
		} );
	}

	var update = function() {
		if (!$scope.map || !$scope.community) {
			return;
		}

		MapService.trackCommunity({
			map: $scope.map,
			community: $scope.community,
			scope: $scope,
			id: 'community-location'
		});
	};

	CommunityService.get($routeParams.id, function(d) {
		$rootScope.title = d.name + ' | Community';
		$scope.community = d;
		$scope.loading = false;

		load_alias();
		load_closelog();

		update();

		OrderService.list({community: d.id_community, limit: 5}, function(d) {
			$scope.orders = d.results;
			$scope.loadingOrders = false;
		});

		RestaurantService.list({community: d.id_community, limit: 50}, function(d) {
			$scope.restaurants = d.results;
			$scope.loadingRestaurants = false;
		});

		StaffService.list({community: d.id_community, limit: 50, type: 'driver'}, function(d) {
			$scope.staff = d.results;
			$scope.loadingStaff = false;
		});
	});
});
