NGApp.controller('DefaultCtrl', function ($scope, $http, $location, $routeParams, MainNavigationService, AccountService) {
	var id_order = $location.path().replace( '/', '' );
	if( !isNaN( parseInt( id_order ) ) ){
		MainNavigationService.link('/drivers/order/' + id_order);
	} else {
		MainNavigationService.link('/drivers/orders');
	}
});

NGApp.controller('MainHeaderCtrl', function ( $scope) {} );

NGApp.controller('SideMenuCtrl', function ($scope) {
	$scope.setupPermissions = function() {}
});

NGApp.controller('LoginCtrl', function($scope, AccountService, MainNavigationService) {
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
		AccountService.login( $scope.username, $scope.password, function( status ) {
			if( status ){
				MainNavigationService.link( '/' );
			} else {
				$scope.error = true;
			}
		} );
	}
});

NGApp.controller( 'NotificationAlertCtrl', function ( $scope, $rootScope  ) {
	$rootScope.$on('notificationAlert', function(e, title, message) {
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