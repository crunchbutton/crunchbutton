NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/groups', {
			action: 'group',
			controller: 'GroupsCtrl',
			templateUrl: '/assets/view/groups.html',
			reloadOnSearch: false
		})
		.when('/group/edit/:id', {
			action: 'group',
			controller: 'GroupFormCtrl',
			templateUrl: '/assets/view/groups-form.html'
		})
		.when('/group/new', {
			action: 'group',
			controller: 'GroupFormCtrl',
			templateUrl: '/assets/view/groups-form.html'
		})
		.when('/group/:id', {
			action: 'group',
			controller: 'GroupCtrl',
			templateUrl: '/assets/view/groups-group.html'
		});

}]);

NGApp.controller('GroupsCtrl', function ($rootScope, $scope, GroupService, ViewListService) {
	$rootScope.title = 'Groups';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		allowAll: true,
		watch: {
			search: '',
			fullcount: false
		},
		update: function() {
			GroupService.list($scope.query, function(d) {
				$scope.groups = d.results;
				$scope.complete(d);
			});
		}
	});

});


NGApp.controller('GroupFormCtrl', function ($scope, $routeParams, $rootScope, $filter, GroupService, MapService ) {

	$scope.ready = false;
	$scope.isSaving = false;

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		GroupService.save( $scope.group, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$scope.navigation.link( '/group/' + json.name );
			}
		} );
	}

	$scope.cancel = function(){
		$rootScope.navigation.back();
	}

	var group = function(){
		if( $routeParams.id ){
			GroupService.get( $routeParams.id, function( d ) {
				$rootScope.title = d.name + ' | Group';
				$scope.group = d;
			});
		} else {
			$scope.group = {};
		}
		$scope.ready = true;
	}
	group();


});


NGApp.controller('GroupCtrl', function ($scope, $routeParams, $rootScope, GroupService, StaffService ) {

	$scope.loading = true;

	$scope.loadingStaff = true;

	GroupService.get($routeParams.id, function(d) {

		$rootScope.title = d.name + ' | Group';
		$scope.group = d;
		$scope.loading = false;

		StaffService.list({group: d.id_group, limit: '100' }, function(d) {
			$scope.staff = d.results;
			$scope.loadingStaff = false;
		});
	});
});
