NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/permission/staff/:id', {
			action: 'staff',
			controller: 'PermissionCtrl',
			templateUrl: '/assets/view/permission-form.html'
		})
		.when('/permission/group/:id', {
			action: 'group',
			controller: 'PermissionCtrl',
			templateUrl: '/assets/view/permission-form.html'
		});
}]);


// Using the same controller for staff/groups permissions
NGApp.controller('PermissionCtrl', function( $scope, $rootScope, $routeParams, $route, PermissionService, RestaurantService, CommunityService ) {

	var service = null;

	if( $route.current.action == 'staff' ){
		service = PermissionService.staff;
	} else if( $route.current.action == 'group' ){
		service = PermissionService.group;
	}

	$scope.isSaving = false;

	$scope.save = function(){
		$scope.isSaving = true;
		var params = {};
		params.id_admin = $routeParams.id;
		params.permissions = $scope.permissions;
		service.save( params, function( json ){
			App.alert( 'Permissions saved' );
			$scope.isSaving = false;
			load();
		} );
	}

	$scope.back = function(){
		$rootScope.navigation.back();
	}

	$scope.hasGlobal = function(){
		return PermissionService.hasGlobal( $scope );
	}

	var load = function(){

		service.load( function( json ){
			$scope.list = json.permissions;
			$scope.info = json.info;
			$scope.group = {};
			for( x in $scope.list ){
				var name = $scope.list[ x ].group;
				var doAllPermission = $scope.list[ x ].doAllPermission;
				$scope.group[ name ] = {
					doAllChecked : false,
					doAllPermission : doAllPermission
				}
			}
			$scope.ready = true;
		} );

		if( !$scope.communities ){
			CommunityService.listSimple( function( json ){
				$scope.communities = json;
			} );
		}

		if( !$scope.restaurants ){
			RestaurantService.shortlist( function( json ){
				$scope.restaurants = json;
			} );
		}
	}

	// hack to wacth every change at the scope
	$scope.$watch( function( $scope ) {
		PermissionService.checkGroupVisible( $scope );
		PermissionService.startDependenciesCheck( $scope );
	}, function(){} );

	if( service ){
		load();
	}

} );