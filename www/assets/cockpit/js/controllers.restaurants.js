NGApp.controller('RestaurantOrderView', function ($scope, $http, $routeParams) {
	$http.get('/api/order/' + $routeParams.id).success(function(data){
		$scope.order = data;
	});
});

NGApp.controller( 'RestaurantOrderNew', function ( $scope, RestaurantService, RestaurantOrderService, PositionService ) {

	$scope.order = { 'tip_type': 'dollar', 'pay_type': 'card' };
	$scope.tip = { 'dollar' : '', 'percent': '10' };
	$scope.card = {};
	$scope.map = {};

	var start = function(){

		$scope.card._months = RestaurantOrderService.cardMonths();
		$scope.card._years = RestaurantOrderService.cardYears();
		$scope.tip._percents = RestaurantOrderService.tipPercents();

		// get info about the restaurant
		RestaurantService.order_placement( function( json ){
			if( json.id_restaurant ){
				$scope.restaurant = json;
				App.config.processor = { type: 'balanced' };
			}
			$scope.ready = true;
		} );
	}

	$scope.$watchCollection('[order.subtotal, order.tip]', function(newValues, oldValues){
		calcTotal();
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

		if( $scope.order.address ){
			PositionService.find( $scope.order.address,
				function( address ){
					var pos = PositionService.getPosition( address );
					if( pos ){
						var distance = PositionService.checkDistance( pos.lat, pos.lon );
						if( distance ){
							$scope.map.out_of_range = ( distance > $scope.restaurant.range );
							var zoom = ( distance < 2 ) ? 14 : 13;
							zoom = ( distance < 4 ) ? 15 : zoom;
							$scope.map.distance = distance;
							$scope.map.img = PositionService.getMapImageSource( { 'lat': pos.lat, 'lon': pos.lon }, { 'lat': $scope.restaurant.lat, 'lon': $scope.restaurant.lon }, zoom );
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
		if( $scope.form.$invalid ){
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
			if( data.error ){
				App.alert( data.error);
				$scope.isProcessing = false;
				return;
			} else {
				if( data.id_order ) {
					// $scope.navigation.link( '/restaurant/order/' + data.id_order );
					console.log('data',data);
					$scope.isProcessing = false;
				} else {
					Alert.alert( data.errors );
				}
			}
		} );
	}

	$scope.test = function (){
		$scope.card.number = '4111111111111111';
		$scope.card.year = '2015';
		$scope.card.month = '2';
		$scope.order = { name: 'MR TEST', phone: '646-783-1444', pay_type: 'card', delivery_type: 'delivery', address: '1120 Princeton Drive, Marina del Rey CA 90292', notes: 'Second floor', subtotal:10, tip:1.50, tip_type:'dollar' };
		// setTimeout( function(){ $scope.processOrder(); }, 1000 );
		setTimeout( function(){ calcTotal() }, 1000 );
	}

	if( $scope.account.isLoggedIn() ){
		start();
	}

} );