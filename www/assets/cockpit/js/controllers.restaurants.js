NGApp.controller('RestaurantOrderPlacementDashboard', function ( $scope, RestaurantOrderPlacementService ) {
	RestaurantOrderPlacementService.get( function( json ){
		console.log('json',json);
		if( json.id_order ){
			$scope.order = json;
		} else {
			$scope.error = true;
		}
		$scope.ready = true;
	} );
	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list' );
	}
} );

NGApp.controller('RestaurantOrderPlacementView', function ( $scope, RestaurantOrderPlacementService ) {
	RestaurantOrderPlacementService.get( function( json ){
		if( json.id_order ){
			$scope.order = json;
			if( $scope.account.isAdmin ){
				$scope.id_restaurant = json.id_restaurant;
			}
		} else {
			$scope.error = true;
		}
		$scope.ready = true;
	} );
	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}
} );

NGApp.controller('RestaurantOrderPlacementList', function ( $scope, RestaurantOrderPlacementService, $routeParams ) {

	// Load restaurants that are allowed to place orders
	var restaurants = function(){
		RestaurantOrderPlacementService.restaurant.all( function( json ){
			$scope.restaurants = json;
		} );
	}

	var start = function(){
		RestaurantOrderPlacementService.list( $scope.id_restaurant, function( json ){
			if( !json.error ){
				$scope.orders = json;
			}
			$scope.ready = true;
		} );
	}

	$scope.new = function(){
		$scope.navigation.link( '/restaurant/order/placement/new/' + $scope.id_restaurant );
	}
	$scope.open = function( id_order ){
		$scope.navigation.link( '/restaurant/order/placement/' + id_order );
	}

	$scope.load_restaurant = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}

	if( $scope.account.isLoggedIn() ){
		if( $scope.account.isAdmin ){
			restaurants();
			if( $routeParams.id ){
				$scope.id_restaurant = parseInt( $routeParams.id );
			}
		}
		start();
	}

} );

NGApp.controller( 'RestaurantOrderPlacementNew', function ( $scope, RestaurantService, RestaurantOrderPlacementService, PositionService, $routeParams ) {

	$scope.order = { 'tip_type': 'dollar', 'pay_type': 'card' };
	$scope.tip = { 'dollar' : '', 'percent': '10' };
	$scope.card = { 'month': 0, 'year': 0 };
	$scope.map = {};

	// Load restaurants that are allowed to place orders
	var restaurants = function(){
		RestaurantOrderPlacementService.restaurant.all( function( json ){
			$scope.restaurants = json;
		} );
	}

	$scope.load_restaurant = function(){
		$scope.navigation.link( '/restaurant/order/placement/new/' + $scope.id_restaurant );
	}

	var start = function(){
		$scope.card._months = RestaurantOrderPlacementService.cardMonths();
		$scope.card._years = RestaurantOrderPlacementService.cardYears();
		$scope.tip._percents = RestaurantOrderPlacementService.tipPercents();

		// get info about the restaurant
		RestaurantOrderPlacementService.restaurant.get( $scope.id_restaurant, function( json ){
			if( json.id_restaurant ){
				$scope.restaurant = json;
				$scope.id_restaurant = $scope.restaurant.id_restaurant;
				PositionService.bounding( $scope.restaurant.lat, $scope.restaurant.lon );
				App.config.processor = { type: 'balanced' };
			}
			$scope.ready = true;
		} );
	}

	$scope.$watchCollection('[order.subtotal, order.tip]', function(newValues, oldValues){
		calcTotal();
	} );

	$scope.$watch( 'order.pay_type', function( newValue, oldValue, scope ) {
		$scope.order.tip = 0;
	} );

	$scope.$watch( 'order.tip_type', function( newValue, oldValue, scope ) {
		if( oldValue == 'dollar' ){
			$scope.tip.dollar = $scope.order.tip;
			$scope.order.tip = $scope.tip.percent;
		} else {
			$scope.tip.percent = $scope.order.tip;
			$scope.order.tip = $scope.tip.dollar;
		}
	} );

	var calcTotal = function(){
		$scope.finalAmount = 0;
		if( $scope.order && $scope.restaurant ){
			$scope.finalAmount = RestaurantOrderPlacementService.calcTotal( $scope.order, $scope.restaurant );
		}
	}

	$scope.checkAddress = function(){

		$scope.map.link = false;
		$scope.map.distance = false;
		$scope.map.img = false;
		$scope.map.out_of_range = false;

		if( $scope.order.address ){
			PositionService.find( $scope.order.address,
				function( address ){
					var pos = PositionService.getPosition( address );
					if( pos ){
						var distance = PositionService.checkDistance( pos.lat, pos.lon );
						if( distance ){
							$scope.map.distance = parseFloat( distance );
							$scope.restaurant.range = parseFloat( $scope.restaurant.range );
							$scope.map.out_of_range = ( $scope.map.distance > $scope.restaurant.range );
							console.log('$scope.map.out_of_range',$scope.map.out_of_range);
							$scope.$safeApply( function(){
								$scope.map.out_of_range = ( $scope.map.distance > $scope.restaurant.range );
							} );
							setTimeout( function(){
								$scope.$safeApply( function(){
									var zoom = 13;
									$scope.map.img = PositionService.getMapImageSource( { 'lat': pos.lat, 'lon': pos.lon }, { 'lat': $scope.restaurant.lat, 'lon': $scope.restaurant.lon }, zoom );
								} );
							}, 1 );

						} else {
							// error
							$scope.map.distance = -1;
						}
					}
				},
				// error
				function(){ $scope.map.distance = -1; }
			);
			$scope.map.link = PositionService.getDirectionsLink( $scope.restaurant.address, $scope.order.address );
		} else {
			$scope.distance = false;
		}
	}

	$scope.processOrder = function(){

		if( $scope.map.out_of_range ){
			App.alert( 'The address: ' + $scope.order.address + '\nis out of the range.' );
			$scope.submitted = true;
			return;
		}

		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}

		$scope.isProcessing = true;
		var order = angular.copy( $scope.order );
		if( $scope.order.tip_type == 'dollar' ){
			order.autotip_value = $scope.order.tip;
			order.tip = 'autotip';
		} else {
			order.tip = $scope.order.tip;
		}
		order.restaurant = $scope.restaurant.id_restaurant;
		RestaurantOrderPlacementService.process( order, $scope.card, function( data ){
			$scope.isProcessing = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				if( data.id_order ) {
					$scope.navigation.link( '/restaurant/order/placement/' + data.id_order );
				} else {
					var errors = '';
					var error = '';
					for ( var x in data.errors ) {
						if( x != 'debug' ){
							error += '<li></i>' + data.errors[x] + '</li>';
						}
					}
					App.alert('<ul>' + error + '</ul>');
				}
			}
		} );
	}

	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/placement/list/' + $scope.id_restaurant );
	}

	$scope.test = function (){
		$scope.card.number = '4111111111111111';
		$scope.card.year = '2015';
		$scope.card.month = '2';
		$scope.order = { name: 'MR TEST', phone: '646-783-1444', pay_type: 'card', delivery_type: 'delivery', address: $scope.restaurant.address, notes: 'Second floor', subtotal:10, tip:1.50, tip_type:'dollar' };
		setTimeout( function(){ calcTotal(); $scope.checkAddress() }, 1000 );
	}

	if( $scope.account.isLoggedIn() ){
		if( $scope.account.isAdmin ){
			restaurants();
			if( $routeParams.id ){
				$scope.id_restaurant = parseInt( $routeParams.id );
			}
		}
		start();
	}

} );