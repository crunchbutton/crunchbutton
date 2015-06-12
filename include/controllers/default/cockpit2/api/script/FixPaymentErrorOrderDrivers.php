<?php

// Big Payment Mess #4055

class Controller_Api_Script_FixPaymentErrorOrderDrivers extends Crunchbutton_Controller_RestAccount {

	const DATE_START = '2015-04-09 00:00:00';
	const DATE_END = '2015-06-10 23:59:59';

	public function init() {

		$payments = c::db()->query( 'select r.name as restaurant, a.name as driver, ps.date as payment_date, o.date as order_date, tp.*, o.* from temp_paymentfix tp
																	inner join admin a on a.id_admin = tp.id_driver
																	inner join payment_schedule ps on ps.id_payment_schedule = tp.id_payment_schedule
																	inner join `order` o on o.id_order = tp.id_order
																	inner join restaurant r on r.id_restaurant = o.id_restaurant order by o.id_order' );

		$out = [];

		foreach( $payments as $p ){
			if( !$out[ $p->id_driver ] ){
				$out[ $p->id_driver ] = [ 'id_admin' => intval( $p->id_driver ), 'name' => $p->driver, 'delivery_fee' => 0, 'orders' => [], 'payments' => [], 'total_payments' => 0, 'total_orders' => 0 ];
			}

			$order = [ 'id_order' => intval( $p->id_order ),
									'id_payment' => intval( $p->id_payment ),
									'id_payment_schedule' => intval( $p->id_payment_schedule ),
									'id_restaurant' => intval( $p->id_restaurant ),
									'order_date' => $p->order_date,
									'payment_date' => $p->payment_date,
									'restaurant' => $p->restaurant,
									'delivery_fee' => intval( $p->delivery_fee ) ];



			if( !$out[ $p->id_driver ][ 'payments' ][ $p->id_payment ] ){
				$out[ $p->id_driver ][ 'payments' ][ $p->id_payment ] = [ 'id_payment' => intval( $p->id_payment ), 'id_payment_schedule' => intval( $p->id_payment_schedule ), 'date' => $p->payment_date, 'orders' => [], 'total_orders' => 0, 'delivery_fee' => 0 ];
				$out[ $p->id_driver ][ 'total_payments' ]++;
			}

			$out[ $p->id_driver ][ 'payments' ][ $p->id_payment ][ 'orders' ][] = $order;
			$out[ $p->id_driver ][ 'payments' ][ $p->id_payment ][ 'delivery_fee' ] += intval( $p->delivery_fee );;
			$out[ $p->id_driver ][ 'payments' ][ $p->id_payment ][ 'total_orders' ]++;

			$out[ $p->id_driver ][ 'delivery_fee' ] += intval( $p->delivery_fee );
			$out[ $p->id_driver ][ 'orders' ][] = $order;
			$out[ $p->id_driver ][ 'total_orders' ]++;

		}

		usort ( $out, function( $a, $b ) {
        return $a[ 'name' ] > $b[ 'name' ];
    }  );

		// by driver
		if( false ){
			echo "id_admin,Driver,Total Orders,Total Payments,Amount\n";
			foreach( $out as $d ){
				echo $d[ 'id_admin' ] . ",";
				echo $d[ 'name' ] . ",";
				echo $d[ 'total_orders' ] . ",";
				echo $d[ 'total_payments' ] . ",";
				echo $d[ 'delivery_fee' ] . ",";
				echo "\n";
			}
		}

		// by order
		if( false ){
			echo "id_admin,Driver,id_order,Order Date,Amount,Restaurant,id_payment,id_payment_schedule,Payment Date\n";
			foreach( $out as $d ){
				foreach( $d[ 'orders' ] as $o ){
					echo $d[ 'id_admin' ] . ",";
					echo $d[ 'name' ] . ",";
					echo $o[ 'id_order' ] . ",";
					echo $o[ 'order_date' ] . ",";
					echo $o[ 'delivery_fee' ] . ",";
					echo $o[ 'restaurant' ] . ",";
					echo $o[ 'id_payment' ] . ",";
					echo $o[ 'id_payment_schedule' ] . ",";
					echo $o[ 'payment_date' ] . ",";
					echo "\n";
				}
			}
		}

		// by order
		if( true ){
			echo "id_admin,Driver,id_payment,id_payment_schedule,Payment Date,Amount,Total Orders\n";
			foreach( $out as $d ){
				foreach( $d[ 'payments' ] as $p ){
					echo $d[ 'id_admin' ] . ",";
					echo $d[ 'name' ] . ",";
					echo $p[ 'id_payment' ] . ",";
					echo $p[ 'id_payment_schedule' ] . ",";
					echo $p[ 'date' ] . ",";
					echo $p[ 'delivery_fee' ] . ",";
					echo $p[ 'total_orders' ] . ",";
					echo "\n";
				}
			}
		}

		// only once
		// $this->get_data();
	}

	public function get_data(){

		$drivers = Admin::q( "select
														distinct(id_driver)

														from payment_schedule ps
															where
																ps.driver_payment_hours is null and
																ps.arbritary = 0 and
																ps.pay_type = 'payment' and
																ps.date >= '" . static::DATE_START . "' and
																ps.date <= '" . static::DATE_END . "' and
																ps.status = 'done'
																order by ps.id_payment_schedule asc" );

		foreach( $drivers as $driver ){
			$orders = static::orders( $driver->id_driver );
			foreach( $orders as $order ){
				$temp = new Crunchbutton_Temp_PaymentFix;
				$temp->id_driver = $driver->id_driver;
				$temp->id_order = $order[ 'id_order' ];
				$temp->id_payment = $order[ 'id_payment' ];
				$temp->id_payment_schedule = $order[ 'id_payment_schedule' ];
				$temp->amount = $order[ 'delivery_fee' ];
				$temp->save();
			}
		}
	}

	public function orders( $id_driver ){
		$orders = Order::q( "select o.*, ps.id_payment_schedule, ps.id_payment

														from payment_schedule ps

														inner join payment_schedule_order pso on pso.id_payment_schedule = ps.id_payment_schedule
														inner join `order` o on o.id_order = pso.id_order

														where

															ps.driver_payment_hours is null
															and pso.amount is not null
															and o.pay_type = 'cash'
															and o.refunded = 0
															and ps.arbritary = 0
															and ps.pay_type = 'payment'
															and ps.date >= '" . static::DATE_START . "' and ps.date <= '" . static::DATE_END . "'
															and ps.status = 'done'
															and ps.id_payment is not null
															and ps.id_driver = '" . $id_driver . "'
															order by ps.id_payment_schedule asc" );
		$out = [];
		foreach( $orders as $order ){
			$out[] = [ 'id_order' => $order->id_order, 'id_payment' => $order->id_payment, 'id_payment_schedule' => $order->id_payment_schedule, 'delivery_fee' => $order->delivery_fee  ];
		}
		return $out;
	}

}