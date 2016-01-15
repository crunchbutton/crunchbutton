NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/community/resources', {
			action: 'resources',
			controller: 'CommunityResourcesCtrl',
			templateUrl: '/assets/view/resources.html',
			reloadOnSearch: false
		})
		.when('/community/resource/:id?', {
			action: 'resources',
			controller: 'CommunityResourceCtrl',
			templateUrl: '/assets/view/resources-resource.html'
		});
}]);

NGApp.controller('CommunityResourcesCtrl', function ($rootScope, $scope, ViewListService, ResourceService, CommunityService ) {

	$rootScope.title = 'Resources';

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
			ResourceService.list( $scope.query, function(d) {
				$scope.resources = d.results;
				$scope.complete(d);
			});
		}
	});
} );

NGApp.controller( 'CommunityResourceCtrl', function ($scope, $routeParams, $rootScope, $location, ResourceService, CommunityService ) {

	$scope.resource = { communities: [] };

	$scope.save = function(){
		if( $scope.isUploading || $scope.isSaving ){
			return;
		}
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			App.alert( 'Please fill all the required fields!' );
			return;
		}
		if( $scope.resource.temp_name == $scope.resource.file ){
			save();
		} else {
			$rootScope.$broadcast( 'triggerStartUpload' );
			$scope.isUploading = true;
		}
	}

	var save = function(){
		$scope.isSaving = true;
		ResourceService.save( $scope.resource, function( json ){
			$scope.isUploading = false;
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				if( $routeParams.id ){
					App.alert( 'Resource saved!' );
					load();
				} else {
					$location.path( '/community/resource/' + json.id_resource );
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
				var ticked = ( $scope.resource.communities.indexOf( community.id_community ) >= 0 );
				this.push( { 'name': community.name, 'id_community': community.id_community, 'ticked': ticked} );
			}, $scope.communities );
			$scope.ready = true;
		} );
	}

	var load = function(){
		ResourceService.get( $routeParams.id, function( json ){
			$scope.resource = json;
			$scope.resource.temp_name = $scope.resource.file;
			communities();
		} );
	}

	if( $routeParams.id ){
		load();
	} else {
		$scope.resource = { 'all': false, 'page': true, 'side': true, 'order_page': true, 'active': true, 'communities': [] };
		communities();
	}

	$rootScope.$on( 'triggerUploadFileAdded', function(e, file_name) {
		$scope.resource.temp_name = file_name;
	});

	// this is a listener to upload error
	$scope.$on( 'resourceUploadError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );

	$scope.$on( 'triggerUploadProgress', function(e, progress) {
		console.log('progress',progress);
	} );

	// this is a listener to upload success
	$scope.$on( 'resourceUpload', function(e, data) {
		var response = data.response;
		if( response.success ){
			$scope.resource.file = response.success;
			save();
		} else {
			App.alert( 'File not saved! ');
		}
	});


});
