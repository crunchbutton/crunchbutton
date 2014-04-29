NGApp.controller('DefaultCtrl', function ($scope, $http, $location, MainNavigationService, AccountService) {
	if (AccountService.user.id_admin) {
		MainNavigationService.link('/drivers/orders');
	}
});

NGApp.controller('MainHeaderCtrl', function ( $scope, $rootScope) {

});

NGApp.controller('SideMenuCtrl', function ($scope) {
	$scope.setupPermissions = function() {
		
	}
});

NGApp.controller('LoginCtrl', function($scope, AccountService) {
	$scope.login = function() {
		AccountService.login($scope.username, $scope.password, function(status) {
//			$scope.$apply(function() {
				$scope.error = !status;
//			})
		});
	}
});

NGApp.controller('DriversOrderCtrl', function ($http, $scope, $rootScope, DriverOrders) {

});


NGApp.controller('DriversOrdersCtrl', function ($http, $scope, $rootScope, DriverOrdersService, AccountService ) {

	// The scope just need the account's user object 
	$scope.account = { user : AccountService.user } ;

	// List
	$scope.list = function(){
		DriverOrdersService.list( function( data ){
			$scope.driverorders = data;
		} );
	}

	// Accept
	$scope.accept = function( id_order ) {
		// $rootScope.makebusy();
		DriverOrdersService.accept( id_order, 
			function( json ){
				if( json.status ) {
					$scope.list();
				} else {
					var name = json['delivery-status'].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert('Ops, error!\n It seems this order was already accepted' + name + '!'  );
					$scope.list();
				}
			}
		);
	};

	// Picked up
	$scope.pickedup = function( id_order ) {
		// $rootScope.makebusy();
		DriverOrdersService.pickedup( id_order, function(){ $scope.list(); } );
	};
	
	// Delivered
	$scope.delivered = function( id_order ) {
		// $rootScope.makebusy();
		DriverOrdersService.delivered( id_order, function(){ $scope.list();	} );
	};

	// Load the orders
	$scope.list();

} );

NGApp.controller('DriversShiftsCtrl', function ($http, $scope, $rootScope) {

});