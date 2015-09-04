NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/delivery-signups', {
			action: 'tools',
			controller: 'DeliverySignUpsCtrl',
			templateUrl: 'assets/view/delivery-signups.html',
			reloadOnSearch: false
		})
}]);

NGApp.controller('DeliverySignUpsCtrl', function ($rootScope, $scope, $timeout, DeliverySignUpService, ViewListService) {

	$rootScope.title = 'Delivery Sign Up';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			status: 'new',
			search: '',
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		DeliverySignUpService.list($scope.query, function(d) {
			$scope.deliveries = d.results;
			$scope.complete(d);
		});
	}


	$scope.review = function( id_delivery_signup ){ changeStatus( id_delivery_signup, 'review' ); }
	$scope.delete = function( id_delivery_signup ){ changeStatus( id_delivery_signup, 'deleted' ); }
	$scope.archive = function( id_delivery_signup ){ changeStatus( id_delivery_signup, 'archived' ); }


	var changeStatus = function( id_delivery_signup, status ){
		DeliverySignUpService.change_status( { id_delivery_signup: id_delivery_signup, status: status }, function( json ){
			if( json.success ){
				update();
			} else {
				App.alert( 'Ops, please try again!' );
				console.log(json);
			}
		} );
	}

});