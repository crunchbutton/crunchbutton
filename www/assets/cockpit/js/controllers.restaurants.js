NGApp.controller('RestaurantOrderView', function ($scope, $http, $routeParams) {
	$http.get('/api/order/' + $routeParams.id).success(function(data){
		$scope.order = data;
	});
});

NGApp.controller( 'RestaurantOrderNew', function ( $scope, RestaurantService, RestaurantOrderService, PositionService ) {

	$scope.order = {};
	$scope.card = {};
	$scope.map = {};

	var start = function(){

		$scope.card._months = RestaurantOrderService.cardMonths();
		$scope.card._years = RestaurantOrderService.cardYears();

		// get info about the restaurant
		RestaurantService.order_placement( function( json ){
			if( json.id_restaurant ){
				$scope.restaurant = json;
				App.config.processor = { type: 'balanced' };
				$scope.test();
			}
			$scope.ready = true;
		} );
	}


	$scope.calc = function(){



		var fee = function( total ){
			if ( $scope.restaurant.fee_customer ) {
				return App.ceil( total * ( parseFloat( $scope.restaurant.fee_customer ) / 100 ) );
			}
			return 0;
		}

		var tax = function( total ){
			return ( total * ( $scope.restaurant.tax / 100 ) );
		}

		var markup = function( total ){
			if( $scope.restaurant.delivery_service_markup ){
				return App.ceil( ( total * ( $scope.restaurant.delivery_service_markup / 100 ) ) );
			}
			return 0;
		}

		var delivery = function(){
			return App.ceil( parseFloat( $scope.restaurant.delivery_fee ) );
		}

		var tip = function( total ){
			// calc tip % of total or real value
			return $scope.order.tip;
		}

		var breakdown = {};
		var total = $scope.order.subtotal + markup();
		var totalWithoutMarkup = $scope.order.subtotal;
		var feeTotal = total;
		breakdown['subtotal'] = $scope.order.subtotal;
		breakdown['subtotalWithoutMarkup'] = totalWithoutMarkup;
		breakdown['delivery'] = delivery();
		feeTotal += breakdown['delivery'];
		breakdown['fee'] = fee( feeTotal );
		feeTotal += breakdown['fee'];

		if( parseInt( $scope.restaurant.delivery_service ) ==  0 ){
			totalWithoutMarkup += breakdown[ 'delivery' ];
		}

		breakdown['taxes'] = tax( totalWithoutMarkup );

		breakdown['tip'] = tip( total );

		total = breakdown.subtotal;
		feeTotal = total;
		feeTotal += breakdown.delivery;
		feeTotal += breakdown.fee;
		finalAmount = feeTotal + breakdown.taxes;
		finalAmount += tip( total );
		console.log('total',App.ceil(finalAmount).toFixed(2));
		return App.ceil(finalAmount).toFixed(2);





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
		$scope.order.restaurant = $scope.restaurant.id_restaurant;
		RestaurantOrderService.process( $scope.order, $scope.card, function( data ){
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
		$scope.order = { name: 'MR TEST', phone: '646-783-1444', pay_type: 'card', delivery_type: 'delivery', address: '1120 Princeton Drive, Marina del Rey CA 90292', notes: 'Second floor', subtotal:10, tip:1.50 };
		// setTimeout( function(){ $scope.processOrder(); }, 1000 );
		setTimeout( function(){ $scope.calc(); }, 1000 );
	}

	if( $scope.account.isLoggedIn() ){
		start();
	}

});
