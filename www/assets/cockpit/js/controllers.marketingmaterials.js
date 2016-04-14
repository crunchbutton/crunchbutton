NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/marketing/materials/refil', {
			action: 'marketing-rep-request-materials',
			controller: 'MarketingMaterialsRefilCtrl',
			templateUrl: '/assets/view/marketing-materials-refil.html'
		});
}]);

NGApp.controller('MarketingMaterialsRefilCtrl', function ($rootScope, $scope, MarketingMaterialsService) {

	$scope.loading = true;

	$scope.save = function(){
		MarketingMaterialsService.save( function( json ){
			if(json.success){
				App.alert('You should have them in 3-5 business days!<br>');
			} else {
				App.alert('Oops, something bad happened!<br>');
			}
		});
	}

	MarketingMaterialsService.load( function( json ){
		if( json.name ){
			$scope.loading = false;
			$scope.info = json;
		}
	} );
});