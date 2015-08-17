NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/delivery-signup', {
			action: '',
			controller: 'DeliverySignUpCtrl',
			templateUrl: 'assets/view/delivery-signup-form.html'
		})
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

NGApp.controller( 'DeliverySignUpCtrl', function( $scope, DeliverySignUpService ) {

	$scope.ready = false;
	$scope.submitted = false;

	$scope.delivery = {};

	$scope.sending = false;

	$scope.restaurants = {};
	$scope.restaurants[ 'Taco Bell' ] = { 'name': 'Taco Bell', 'checked': false };
	$scope.restaurants[ 'McDonalds' ] = { 'name': 'McDonalds', 'checked': false };
	$scope.restaurants[ 'Burger King' ] = { 'name': 'Burger King', 'checked': false };
	$scope.restaurants[ 'In-N-Out' ] = { 'name': 'In-N-Out', 'checked': false };
	$scope.restaurants[ 'Other' ] = { 'name': 'Other', 'checked': false };

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.sending = true;

		$scope.delivery.restaurants = [];

		angular.forEach( $scope.restaurants, function(value, key) {
			if( value.checked ){
				$scope.delivery.restaurants.push( value.name );
			}
		} );

		if( $scope.delivery.otherRestaurant ){
			$scope.delivery.restaurants.push( $scope.delivery.otherRestaurant );
		}

		DeliverySignUpService.save( $scope.delivery, function( json ){
			if( json.success ){
				$scope.finished = true;
			} else {
				$scope.sending = false;
				$scope.error = json.error;
			}
		} );
	}
} );