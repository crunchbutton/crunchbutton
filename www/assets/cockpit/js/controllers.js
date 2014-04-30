NGApp.controller('DefaultCtrl', function ($scope, $http, $location, MainNavigationService, AccountService) {
	if ( AccountService.user && AccountService.user.id_admin ) {
		MainNavigationService.link('/drivers/orders');
	}

});

NGApp.controller('MainHeaderCtrl', function ( $scope) {} );

NGApp.controller('SideMenuCtrl', function ($scope) {
	$scope.setupPermissions = function() {}
});

NGApp.controller('LoginCtrl', function($scope, AccountService) {
	$scope.login = function() {
		if( !$scope.username ){
			App.alert( 'Please type your username' );
			$scope.focus( '[name="username"]' );
			return;
		}
		if( !$scope.password ){
			App.alert( 'Please type your password' );
			$scope.focus( '[name="password"]' );
			return;
		}
		AccountService.login($scope.username, $scope.password, function(status) {
//			$scope.$apply(function() {
				$scope.error = !status;
//			})
		});
	}
});


NGApp.controller('DriversOrderCtrl', function ( $scope, DriverOrdersService ) {
	DriverOrdersService.get( function( json ){
		$scope.order = json;
	} )
});

NGApp.controller('DriversOrdersCtrl', function ( $scope, DriverOrdersService, MainNavigationService ) {

	$scope.show = { all : true };

	$scope.filterOrders = function( order ){
		if( $scope.show.all ){
			return true;	
		} else {
			if( order.lastStatus.id_admin == $scope.account.user.id_admin ){
				return true;
			}
		}
		return false;
	}

	$scope.list = function(){
		$scope.unBusy();
		DriverOrdersService.list( function( data ){
			$scope.driverorders = data;
		} );
	}

	$scope.accept = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.accept( id_order, 
			function( json ){
				if( json.status ) {
					$scope.list();
				} else {
					$scope.unBusy();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Ops, error!\n It seems this order was already accepted ' + name + '!'  );
					$scope.list();
				}
			}
		);
	};

	$scope.pickedup = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.pickedup( id_order, function(){ $scope.list(); } );
	};
	
	$scope.delivered = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.delivered( id_order, function(){ $scope.list();	} );
	};

	$scope.showOrder = function( id_order ){
		MainNavigationService.link( '/drivers/order/' + id_order );
	}

	$scope.list();

} );

NGApp.controller( 'DriversShiftsCtrl', function ( $scope, DriverShiftsService ) {

	$scope.show = { all : true };

	$scope.filterShifts = function( shift ){
		if( $scope.show.all ){
			return true;	
		} else {
			if( shift.mine ){
				return true;
			}
		}
		return false;
	}

	$scope.list = function(){
		DriverShiftsService.list( function( data ){
			$scope.drivershifts = data;
		} );
	}

	$scope.list();
} );

NGApp.controller( 'NotificationAlertCtrl', function ( $scope, $rootScope  ) {
	$rootScope.$on('notificationAlert', function(e, title, message) {
		alert( message );
		// todo: make it work with modals and stuff
		return;
		if ($scope.$$phase) {
			$scope.title = title;
			$scope.message = message;
			App.dialog.show('.notification-alert-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.title = title;
				scope.message = message;
				App.dialog.show('.notification-alert-container');
			}); 
		}			
	});
});