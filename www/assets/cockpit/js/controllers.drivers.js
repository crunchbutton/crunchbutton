NGApp.controller('DriversOrderCtrl', function ( $scope, DriverOrdersService ) {

	$scope.ready = false;

	// private method
	var load = function(){
		DriverOrdersService.get( function( json ){
			$scope.order = json;
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	$scope.accept = function() {
		$scope.makeBusy();
		DriverOrdersService.accept( $scope.order.id_order, 
			function( json ){
				if( json.status ) {
					load();
				} else {
					load();
					var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
					App.alert( 'Ops, error!\n It seems this order was already accepted ' + name + '!'  );
				}
			}
		);
	};

	$scope.pickedup = function() {
		$scope.makeBusy();
		DriverOrdersService.pickedup( $scope.order.id_order, function(){ load(); } );
	};

	$scope.delivered = function() {
		$scope.makeBusy();
		DriverOrdersService.delivered( $scope.order.id_order, function(){ load();	} );
	};

	$scope.reject = function() {
		$scope.makeBusy();
		DriverOrdersService.reject( $scope.order.id_order, function(){ load();	} );
	};

	// Just run if the user is loggedin 
	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller('DriversOrdersCtrl', function ( $scope, DriverOrdersService, MainNavigationService ) {

	$scope.show = { all : true };
	$scope.ready = false;

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
			$scope.ready = true;
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

	// Just run if the user is loggedin 
	if( $scope.account.isLoggedIn() ){
		$scope.list();	
	}
} );

NGApp.controller( 'DriversShiftsCtrl', function ( $scope, DriverShiftsService ) {

	$scope.show = { all : true };
	$scope.ready = false;

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
			$scope.ready = true;
		} );
	}

	// Just run if the user is loggedin 
	if( $scope.account.isLoggedIn() ){
		$scope.list();	
	}

} );

NGApp.controller( 'DriversOnboardingCtrl', function ( $scope, DriverOnboardingService ) {
	$scope.ready = false;
});

NGApp.controller( 'DriversOnboardingNewCtrl', function ( $scope, DriverOnboardingService, CommunityService ) {
	
	$scope.tab = { step : 1 };
	$scope.tab.change = function( step ){
		$scope.tab.step = step;
	}

	$scope.ready = true;

	$scope.communities = [];

	// Load the communities and put them at scope
	CommunityService.listSimple( function( data ){
		$scope.communities = data;
	} );

} );
