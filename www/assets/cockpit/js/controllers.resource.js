NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/community/resources', {
			action: 'resources',
			controller: 'CommunityResourcesCtrl',
			templateUrl: 'assets/view/communities-resources.html',
			reloadOnSearch: false
		})
		.when('/community/resource/:id?', {
			action: 'resources',
			controller: 'CommunityResourceCtrl',
			templateUrl: 'assets/view/communities-resource.html'
		});
}]);

NGApp.controller('CommunityResourcesCtrl', function ($rootScope, $scope, ViewListService, CommunityResourceService, CommunityService ) {

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
			CommunityResourceService.list( $scope.query, function(d) {
				$scope.resources = d.results;
				$scope.complete(d);
			});
		}
	});
} );

NGApp.controller( 'CommunityResourceCtrl', function ($scope, $routeParams, $rootScope, CommunityResourceService, CommunityService ) {

	$scope.save = function(){
		if( $scope.isSaving ){
			return;
		}
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			App.alert( 'Please fill all the required fields!' );
			return;
		}
		$rootScope.$broadcast( 'triggerStartUpload' );
		$scope.isSaving = true;
	}

	var save = function(){
		CommunityResourceService.save( $scope.resource, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				if( $routeParams.id ){
					App.alert( 'Resource saved!' );
				}
				$scope.navigation.link( '/community/resource/' + json.id_resource );
			}
		} );
	}

	var communities = function(){
		CommunityService.listSimple( function( json ){
			$scope.communities = [];
			angular.forEach( json, function( community, key ) {
				var ticked = ( $scope.resource.communities.indexOf( community.id_community ) >= 0 );
	  		this.push( { 'name': community.name, 'id_community': community.id_community, 'ticked': ticked} );
			}, $scope.communities );
			$scope.ready = true;
		} );
	}

	if( $routeParams.id ){
		CommunityResourceService.get( $routeParams.id, function( json ){
			$scope.resource = json;
			communities();
		} )
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
		var id_driver_document = data.id_driver_document;
		var response = data.response;
		if( response.success ){
			$scope.resource.file = response.success;
			save();
		} else {
			App.alert( 'File not saved! ');
		}
	});


});
