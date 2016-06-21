NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tools', {
			action: 'tools',
			controller: 'ToolsCtrl',
			templateUrl: '/assets/view/tools.html'
		})
		.when('/tools/eta', {
			action: 'tools-eta',
			controller: 'ToolsETACtrl',
			templateUrl: '/assets/view/tools-eta.html'
		})
		.when('/tools/fax', {
			action: 'tools',
			controller: 'ToolsFaxCtrl',
			templateUrl: '/assets/view/tools-fax.html',
			title: 'Tools',
			reloadOnSearch: false
		});
}]);

NGApp.controller('ToolsCtrl', function () {});

NGApp.controller('ToolsETACtrl', function ( $scope, RestaurantService ) {
	$scope.loading = true;
	RestaurantService.eta( function( json ){
		$scope.restaurants = json;
		$scope.loading = false;
	} );
});

NGApp.controller('ToolsFaxCtrl', function($scope, $location, FaxService){

	$scope.fax = {};

	var query = $location.search();
	if(query){
		if(query.id_restaurant){
			$scope.fax.id_restaurant = query.id_restaurant;
		}
		if(query.fax){
			$scope.fax.fax = query.fax;
		}
	}

	$scope.send = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		if(!$scope.fax.file){
			App.alert( 'Please upload a pdf or image file!<br>' );
			return;
		}
		$scope.isSending = true;
		FaxService.save($scope.fax, function(res){
			$scope.isSending = false;
			App.alert(res.message + '<br>');
			$scope.fax = {};
		});
	}
	$scope.$on( 'faxFileUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again!' );
	} );
	$scope.$on( 'faxFileUploaded', function(e, data) {
		if( data.success ){
			App.alert( 'File saved!' );
			console.log('data',data);
			$scope.fax.file = data.success;
		} else {
			App.alert( 'Upload error, please try again!' );
		}
	});
} );