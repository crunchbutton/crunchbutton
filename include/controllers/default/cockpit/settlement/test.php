<?php

class Controller_settlement_test extends Crunchbutton_Controller_Account {
	public function init() {

		if (!c::admin()->permission()->check(['global','settlement'])) {
			return;
		}

		$id_orders = c::getPagePiece( 3 );
		if( $id_orders ){
			$settlement = new Settlement;
			$id_orders = explode( ',', $id_orders );
			$orders = [];
			$orders_values = [];
			foreach( $id_orders as $key => $val ){
				$id_order = trim( $val );
				if( is_numeric( $id_order ) )
				$order = Order::o( $id_order );
				if( $order->id_order ){
					$orders[] = $order;
				}
			}
			if( count( $orders ) > 0 ){
				foreach ( $orders as $order ) {
					$values = $settlement->orderExtractVariables( $order );
					$orders_values[] = $values;
					$order->values = [ 	'subtotal_card' => $settlement->orderCardSubtotalPayment( $values ),
															'subtotal_cash' => $settlement->orderCashSubtotalPayment( $values ),
															'tax' => $settlement->orderTaxPayment( $values ),
															'delivery_fee' => $settlement->orderDeliveryFeePayment( $values ),
															'tip' => $settlement->orderTipPayment( $values ),
															'tip' => $settlement->orderTipPayment( $values ),
															'card_charge' => $settlement->orderCreditChargePayment( $values ),
															'restaurant_fee' => $settlement->orderRestaurantFeePayment( $values ),
															'driver' => $values[ 'driver' ]
														];
				}

				$type = c::getPagePiece( 2 );
				if( $type == 'restaurants' ){
					c::view()->pay_restaurants = $settlement->restaurantsProcessOrders( $orders_values );
				} else if( $type == 'drivers' ){
					c::view()->pay_drivers = $settlement->driversProcessOrders( $orders_values );;
				}
				c::view()->orders = $orders;
				c::view()->order_ids = c::getPagePiece( 3 );
			}
		}
		c::view()->display('settlement/test');
	}
}
