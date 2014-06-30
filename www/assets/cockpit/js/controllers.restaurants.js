NGApp.controller('RestaurantOrderView', function ( $scope, RestaurantOrderService ) {
	RestaurantOrderService.get( function( json ){
		if( json.id_order ){
			$scope.order = json;
		} else {
			$scope.error = true;
		}
		$scope.ready = true;
	} );
	$scope.list = function(){
		$scope.navigation.link( '/restaurant/order/list' );
	}
} );

NGApp.controller('RestaurantOrderList', function ( $scope, RestaurantOrderService ) {
	RestaurantOrderService.list( function( json ){
		if( !json.error ){
			$scope.orders = json;
		}
		$scope.ready = true;
	} );
	$scope.new = function(){
		$scope.navigation.link( '/restaurant/order/new' );
	}
	$scope.open = function( id_order ){
		$scope.navigation.link( '/restaurant/order/' + id_order );
	}
} );

NGApp.controller( 'RestaurantOrderNew', function ( $scope, RestaurantService, RestaurantOrderService, PositionService ) {

	$scope.order = { 'tip_type': 'dollar', 'pay_type': 'card' };
	$scope.tip = { 'dollar' : '', 'percent': '10' };
	$scope.card = { 'month': 0, 'year': 0 };
	$scope.map = {};

	var start = function(){

		$scope.card._months = RestaurantOrderService.cardMonths();
		$scope.card._years = RestaurantOrderService.cardYears();
		$scope.tip._percents = RestaurantOrderService.tipPercents();

		// get info about the restaurant
		RestaurantService.order_placement( function( json ){
			if( json.id_restaurant ){
				$scope.restaurant = json;
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
			$scope.finalAmount = RestaurantOrderService.calcTotal( $scope.order, $scope.restaurant );
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
		RestaurantOrderService.process( order, $scope.card, function( data ){
			$scope.isProcessing = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				if( data.id_order ) {
					$scope.navigation.link( '/restaurant/order/' + data.id_order );
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
		$scope.navigation.link( '/restaurant/order/list' );
	}

	$scope.test = function (){
		$scope.card.number = '4111111111111111';
		$scope.card.year = '2015';
		$scope.card.month = '2';
		$scope.order = { name: 'MR TEST', phone: '646-783-1444', pay_type: 'card', delivery_type: 'delivery', address: '1120 Princeton Drive, Marina del Rey CA 90292', notes: 'Second floor', subtotal:10, tip:1.50, tip_type:'dollar' };
		setTimeout( function(){ calcTotal(); $scope.checkAddress() }, 1000 );
	}

	if( $scope.account.isLoggedIn() ){
		start();
	}

} );