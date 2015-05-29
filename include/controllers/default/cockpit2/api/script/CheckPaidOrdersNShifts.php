<?php

// Big Payment Mess #4055

class Controller_Api_Script_CheckPaidOrdersNShifts extends Crunchbutton_Controller_RestAccount {

	const DATE_START = '20141105';
	const DATE_END = '20141107';
	const NOTES = 'Order payment fix';

	public function init() {

		// $this->markOrdersAsPaid();
		$this->markShiftsAsPaid();
		// $this->orders();
		// $this->shifts();
	}

	public function markShiftsAsPaid(){

		$out = [ 'ok' => [], 'nope' => [] ];

		$settlement = new Crunchbutton_Settlement;

		$query = 'SELECT DISTINCT( asa.id_admin ) AS id_admin, a.name, apt.using_pex FROM admin_shift_assign asa
								INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
								INNER JOIN admin a ON a.id_admin = asa.id_admin
								INNER JOIN admin_payment_type apt ON apt.id_admin = asa.id_admin
								WHERE
									cs.date_start >= ?
								AND
									cs.date_end <= ?
								AND
									asa.id_admin_shift_assign NOT IN ( SELECT id_admin_shift_assign FROM payment_schedule_shift pss )
								AND
									apt.payment_type = "hours"';


		$assigned_shifts = Crunchbutton_Admin_Shift_Assign::q( $query, [Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END]);

		$pay = [];

		foreach( $assigned_shifts as $shift ){
			$pay[ $shift->id_admin ] = [ 'subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'gift_card' => 0, 'total_spent' => 0, 'orders' => [] ];
			$pay[ $shift->id_admin ][ 'id_admin' ] = $shift->id_admin;
			$pay[ $shift->id_admin ][ 'name' ] = $shift->name;
			$pay[ $shift->id_admin ][ 'using_pex' ] = $shift->using_pex;
			$pay[ $shift->id_admin ][ 'pay_type' ][ 'payment_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
		}

		foreach( $pay as $id_driver => $driver ){
			$pay_type = Admin::o( $id_driver )->payment_type();
			if( $pay_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
				$shifts = $settlement->workedShiftsByPeriod( $id_driver, [ 'start' => Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, 'end' => Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END ] );
				$worked_shifts = [];
				$_hours = 0;
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
				$pay[ $id_driver ][ 'shifts' ] = [ 'worked_total' => 0, 'amount' => 0, 'hour_rate' => $pay_type->hour_rate ];
				foreach( $shifts as $shift ){
					if( !Cockpit_Payment_Schedule_Shift::checkShiftWasPaidDriver( $shift->id_admin_shift_assign ) || $recalculatePaidOrders ){
						$_shift = [];
						$_shift[ 'id_community_shift' ] = $shift->id_community_shift;
						$_shift[ 'id_admin_shift_assign' ] = $shift->id_admin_shift_assign;
						$_shift[ 'period' ] = $shift->startEndToStringCommunityTz();
						$_shift[ 'hours' ] = $shift->duration();
						$_shift[ 'amount' ] = round( $shift->duration() * $pay_type->hour_rate, 2 );
						$pay[ $id_driver ][ 'shifts' ][ 'worked_total' ]++;
						$pay[ $id_driver ][ 'shifts' ][ 'amount' ] += round( $_shift[ 'amount' ], 2 );
						$worked_shifts[] = $_shift;
						$_hours += $_shift[ 'hours' ];


						$schedule_shift = new Cockpit_Payment_Schedule_Shift;
						$schedule_shift->id_payment_schedule = null;
						$schedule_shift->id_admin_shift_assign = $_shift[ 'id_admin_shift_assign' ];
						$schedule_shift->hours = $_shift[ 'hours' ];
						$schedule_shift->amount = $_shift[ 'amount' ];
						$schedule_shift->save();
// echo '<pre>';var_dump( $schedule_shift );exit();
					}
				}
				$tip = 0;
				foreach( $pay[ $id_driver ][ 'orders' ] as $id_order => $order ){

					if( !$order[ 'driver_paid' ] || $recalculatePaidOrders ){
						$tip += $order[ 'pay_info' ][ 'tip' ];
					}
				}

				$pay[ $id_driver ][ 'hours' ] = $_hours;
				$pay[ $id_driver ][ 'worked_hours' ] = $pay[ $id_driver ][ 'shifts' ][ 'amount' ];
				$pay[ $id_driver ][ 'total_payment' ] = ( $pay[ $id_driver ][ 'shifts' ][ 'amount' ] + $tip + $pay[ $id_driver ][ 'markup' ] );
				$pay[ $id_driver ][ 'shifts' ][ 'worked' ] = $worked_shifts;
			} else {
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
			}
		}
		echo json_encode( $pay );exit();
	}

	public function markOrdersAsPaid(){

		$out = [ 'ok' => [], 'nope' => [] ];

		$settlement = new Crunchbutton_Settlement;

		$query = 'SELECT * FROM `order` AS o WHERE DATE_FORMAT( o.date, "%Y%m%d" ) >= ? AND DATE_FORMAT( o.date, "%Y%m%d" ) <= ? AND o.delivery_service = true AND o.id_order NOT IN ( SELECT id_order FROM order_transaction WHERE type = ? )';

		$orders = Crunchbutton_Order::q( $query, [Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END, Crunchbutton_Order_Transaction::TYPE_PAID_TO_DRIVER]);

		$payments = [];

		foreach( $orders as $order ){
			$driver = $order->getDeliveryDriver();
			$schedule_order = Cockpit_Payment_Schedule_Order::q('SELECT * FROM payment_schedule_order WHERE id_order = ?', [$order->id_order]);
			if( $schedule_order->id_payment_schedule ){
				$payment_schedule = Cockpit_Payment_Schedule::o( $schedule_order->id_payment_schedule );
				if( $payment_schedule->id_payment ){
					$payments[ $driver->id_admin ] = $payment_schedule->id_payment;
				}
			}
		}

		foreach( $orders as $order ){

			$driver = $order->getDeliveryDriver();

			if( $driver->id_admin ){
				if( !$out[ 'ok' ][ $driver->id_admin ] ){
					$out[ 'ok' ][ $driver->id_admin ] = [ 'ok' => [], 'nope' => [] ];
				}
					$id_payment = false;
					$schedule_order = Cockpit_Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_order = ' . $order->id_order );
					if( $schedule_order->id_payment_schedule ){
						$payment_schedule = Cockpit_Payment_Schedule::o( $schedule_order->id_payment_schedule );
						$total_payment = $schedule_order->amount;
						if( $payment_schedule->id_payment ){
							$id_payment = $payment_schedule->id_payment;
						}
						$out[ 'ok' ][ $driver->id_admin ][ 'ok' ][] = $order->id_order;
					} else {
						$variables = $settlement->orderExtractVariables( $order );
						$pay_info = $settlement->driversProcess( [ $variables ], true, false, false );
						$total_payment = $pay_info[0][ 'total_payment' ];
						$out[ 'ok' ][ $driver->id_admin ][ 'nope' ][] = $order->id_order;
					}

					if( !$id_payment ){
						$id_payment = $payments[ $driver->id_admin ];
					}

					$order_transaction = new Crunchbutton_Order_Transaction;
					$order_transaction->id_order = $order->id_order;
					$order_transaction->amt = $total_payment;
					$order_transaction->date = date( 'Y-m-d H:i:s' );
					$order_transaction->type = Crunchbutton_Order_Transaction::TYPE_PAID_TO_DRIVER;
					$order_transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
					$order_transaction->id_admin = 3;
					$order_transaction->save();

					if( $id_payment ){
						$payment_order_transaction = new Cockpit_Payment_Order_Transaction;
						$payment_order_transaction->id_payment = $id_payment;
						$payment_order_transaction->id_order_transaction = $order_transaction->id_order_transaction;
						$payment_order_transaction->save();
					}

			} else {
				$out[ 'nope' ][] = $order->id_order;
			}
		}
		echo json_encode( $out );exit();
	}

	public function shifts(){

		$settlement = new Crunchbutton_Settlement;

		$query = 'SELECT DISTINCT( asa.id_admin ) AS id_admin, a.name, apt.using_pex FROM admin_shift_assign asa
								INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
								INNER JOIN admin a ON a.id_admin = asa.id_admin
								INNER JOIN admin_payment_type apt ON apt.id_admin = asa.id_admin
								WHERE
									cs.date_start >= ?
								AND
									cs.date_end <= ?
								AND
									asa.id_admin_shift_assign NOT IN ( SELECT id_admin_shift_assign FROM payment_schedule_shift pss )
								AND
									apt.payment_type = "hours"';


		$assigned_shifts = Crunchbutton_Admin_Shift_Assign::q( $query, [Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END]);

		$pay = [];

		foreach( $assigned_shifts as $shift ){
			$pay[ $shift->id_admin ] = [ 'subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'gift_card' => 0, 'total_spent' => 0, 'orders' => [] ];
			$pay[ $shift->id_admin ][ 'id_admin' ] = $shift->id_admin;
			$pay[ $shift->id_admin ][ 'name' ] = $shift->name;
			$pay[ $shift->id_admin ][ 'using_pex' ] = $shift->using_pex;
			$pay[ $shift->id_admin ][ 'pay_type' ][ 'payment_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
		}

		foreach( $pay as $id_driver => $driver ){
			$pay_type = Admin::o( $id_driver )->payment_type();
			if( $pay_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
				$shifts = $settlement->workedShiftsByPeriod( $id_driver, [ 'start' => Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, 'end' => Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END ] );
				$worked_shifts = [];
				$_hours = 0;
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
				$pay[ $id_driver ][ 'shifts' ] = [ 'worked_total' => 0, 'amount' => 0, 'hour_rate' => $pay_type->hour_rate ];
				foreach( $shifts as $shift ){
					if( !Cockpit_Payment_Schedule_Shift::checkShiftWasPaidDriver( $shift->id_admin_shift_assign ) || $recalculatePaidOrders ){
						$_shift = [];
						$_shift[ 'id_community_shift' ] = $shift->id_community_shift;
						$_shift[ 'id_admin_shift_assign' ] = $shift->id_admin_shift_assign;
						$_shift[ 'period' ] = $shift->startEndToStringCommunityTz();
						$_shift[ 'hours' ] = $shift->duration();
						$_shift[ 'amount' ] = round( $shift->duration() * $pay_type->hour_rate, 2 );
						$pay[ $id_driver ][ 'shifts' ][ 'worked_total' ]++;
						$pay[ $id_driver ][ 'shifts' ][ 'amount' ] += round( $_shift[ 'amount' ], 2 );
						$worked_shifts[] = $_shift;
						$_hours += $_shift[ 'hours' ];
					}
				}
				$tip = 0;
				foreach( $pay[ $id_driver ][ 'orders' ] as $id_order => $order ){

					if( !$order[ 'driver_paid' ] || $recalculatePaidOrders ){
						$tip += $order[ 'pay_info' ][ 'tip' ];
					}
				}

				$pay[ $id_driver ][ 'hours' ] = $_hours;
				$pay[ $id_driver ][ 'worked_hours' ] = $pay[ $id_driver ][ 'shifts' ][ 'amount' ];
				$pay[ $id_driver ][ 'total_payment' ] = ( $pay[ $id_driver ][ 'shifts' ][ 'amount' ] + $tip + $pay[ $id_driver ][ 'markup' ] );
				$pay[ $id_driver ][ 'shifts' ][ 'worked' ] = $worked_shifts;
			} else {
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
			}
		}

// echo json_encode( $pay );exit();
		$separator = ';';
		$new_line = "\n";
		// if( false )
		foreach( $pay as $driver ){
			echo $driver[ 'id_admin' ];
				echo $separator;
			echo $driver[ 'name' ];
				echo $separator;
			echo max( $driver[ 'shifts' ][ 'worked_total' ], 0 );
				echo $separator;
			echo max( $driver[ 'hours' ], 0 );
				echo $separator;
			echo max( $driver[ 'shifts' ][ 'hour_rate' ], 0 );
				echo $separator;
			echo max( $driver[ 'shifts' ][ 'amount' ], 0 );
			echo $new_line;
		}

		// if( false )
		foreach( $pay as $driver ){
			foreach( $driver[ 'shifts' ][ 'worked' ] as $shift ){
				echo $driver[ 'id_admin' ];
					echo $separator;
				echo $driver[ 'name' ];
					echo $separator;
				echo $shift[ 'period' ][ 'start' ];
					echo $separator;
				echo $shift[ 'period' ][ 'end' ];
					echo $separator;
				echo $shift[ 'hours' ];
					echo $separator;
				echo $shift[ 'amount' ];
				echo $new_line;
			}
		}

		foreach( $pay as $driver ){
			// $settlement->scheduleDriverPaymentTimeout( $driver, Controller_Api_Script_CheckPaidOrdersNShifts::NOTES, 0, 0, $driver[ 'id_admin' ], Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT );
		}
	}

	public function orders() {

		$pay_type = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT; // Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT

		$query = 'SELECT * FROM `order` o
								WHERE
									DATE_FORMAT( o.date, "%Y%m%d" ) >= ?
								AND
									DATE_FORMAT( o.date, "%Y%m%d" ) <= ?
								AND
									o.delivery_service = true
								AND o.id_order NOT IN ( SELECT pso.id_order FROM payment_schedule_order pso
																					INNER JOIN payment_schedule ps
																						ON
																							ps.id_payment_schedule = pso.id_payment_schedule
																						AND
																							ps.pay_type = ?)';

		$_orders = Crunchbutton_Order::q( $query, [Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START, Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END, $pay_type]);

		$settlement = new Crunchbutton_Settlement;

		$orders = [];
		foreach ( $_orders as $order ) {
			$orders[] = $settlement->orderExtractVariables( $order );
		}

		$result = $settlement->driversProcess( $orders );

		$out = [ 'drivers' => [] ];

		$_total_payments = 0;
		$_total_drivers = 0;
		$_total_orders = 0;
		foreach ( $result as $key => $val ) {

			if( !$result[ $key ][ 'name' ] ){
				continue;
			}

			$driver = $result[ $key ];

			$driver[ 'orders' ] = [];

			if( $pay_type == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT && $driver[ 'pay_type' ][ 'payment_type' ] != 'orders' ){
				continue;
			}

			if( $result[ $key ][ 'orders' ] ){
				foreach( $result[ $key ][ 'orders' ] as $order ){

					if( $pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
						if( $order[ 'pay_info' ][ 'total_reimburse' ] <= 0 ){
							continue;
						}
					} else {
						if( $order[ 'pay_info' ][ 'total_payment' ] <= 0 ){
							continue;
						}
					}

					$_order = [];
					$_order[ 'id_order' ] = $order[ 'id_order' ];
					$_order[ 'name' ] = $order[ 'name' ];
					$_order[ 'restaurant' ] = $order[ 'restaurant' ];
					$_order[ 'pay_type' ] = ucfirst( $order[ 'pay_type' ] );
					$_order[ 'total' ] = $order[ 'final_price_plus_delivery_markup' ];
					$_order[ 'tip' ] = $order[ 'pay_info' ][ 'tip' ] ;
					$_order[ 'delivery_fee' ] = $order[ 'pay_info' ][ 'delivery_fee' ] ;
					$_order[ 'standard_reimburse' ] = $order[ 'pay_info' ][ 'standard_reimburse' ] ;
					$_order[ 'total_reimburse' ] = $order[ 'pay_info' ][ 'total_reimburse' ] ;
					$_order[ 'total_payment' ] = $order[ 'pay_info' ][ 'total_payment' ];
					$_order[ 'date' ] = $order[ 'date' ];
					$_order[ 'refunded' ] = $order[ 'refunded' ];
					$_order[ 'included' ] = !$order[ 'do_not_pay_driver' ];
					if( !$_order[ 'included' ] ){
						$driver[ 'not_included' ]++;
					}
					$driver[ 'orders' ][] = $_order;
					$_total_orders++;
				}
			}

			if( $pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				if( $driver[ 'total_reimburse' ] <= 0 ){
					continue;
				}
			} else {
				if( $driver[ 'total_payment' ] <= 0 ){
					continue;
				}
			}

			$_total_drivers++;

			$driver[ 'total_payment_without_adjustment' ] = $driver[ 'total_payment' ];
			$driver[ 'adjustment' ] = 0;
			$driver[ 'adjustment_notes' ] = '';
			$driver[ 'standard_reimburse' ] = ( $driver[ 'standard_reimburse' ] ? $driver[ 'standard_reimburse' ] : 0 );
			$driver[ 'total_reimburse' ] = ( $driver[ 'total_reimburse' ] ? $driver[ 'total_reimburse' ] : 0 );
			$driver[ 'total_payment' ] = ( $driver[ 'total_payment' ] ? $driver[ 'total_payment' ] : 0 );

			$_total_payments += $driver[ 'total_payment' ];

			$driver[ 'orders_count' ] = count( $driver[ 'orders' ] );

			$out[ 'drivers' ][] = $driver;

			// $settlement->scheduleDriverPaymentTimeout( $driver, Controller_Api_Script_CheckPaidOrdersNShifts::NOTES, 0, 0, $driver[ 'id_admin' ], $pay_type );

		}

		$separator = ';';
		$new_line = "\n";

		if( $pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){

			if( false )
			foreach( $out[ 'drivers' ] as $driver ){
				echo $driver[ 'id_admin' ];
					echo $separator;
				echo $driver[ 'name' ];
					echo $separator;
				echo max( $driver[ 'orders_count' ], 0 );
					echo $separator;
				echo max( $driver[ 'total_reimburse' ], 0 );
				echo $new_line;
			}
			if( false )
			foreach( $out[ 'drivers' ] as $driver ){
				foreach( $driver[ 'orders' ] as $order ){
					echo $order[ 'id_order' ];
						echo $separator;
					echo $driver[ 'name' ];
						echo $separator;
					echo $order[ 'name' ];
						echo $separator;
					echo $order[ 'restaurant' ];
						echo $separator;
					echo $order[ 'pay_type' ];
						echo $separator;
					echo $order[ 'total_reimburse' ];
						echo $separator;
					echo ( $order[ 'refunded' ] ? 'Yes' : 'No' );
					echo $new_line;
				}
			}

		} else {

			if( false )
			foreach( $out[ 'drivers' ] as $driver ){
				echo $driver[ 'id_admin' ];
					echo $separator;
				echo $driver[ 'name' ];
					echo $separator;
				echo max( $driver[ 'invites_total' ], 0 );
					echo $separator;
				echo min( $driver[ 'markup' ], 0 );
					echo $separator;
				echo max( $driver[ 'orders_count' ], 0 );
					echo $separator;
				echo max( $driver[ 'total_payment' ], 0 );
				echo $new_line;
			}

			if( false )
			foreach( $out[ 'drivers' ] as $driver ){
				foreach( $driver[ 'orders' ] as $order ){
					// echo json_encode( $order );exit();
					echo $order[ 'id_order' ];
						echo $separator;
					echo $driver[ 'name' ];
						echo $separator;
					echo $order[ 'name' ];
						echo $separator;
					echo $order[ 'restaurant' ];
						echo $separator;
					echo $order[ 'pay_type' ];
						echo $separator;
					echo $order[ 'tip' ];
						echo $separator;
					echo $order[ 'delivery_fee' ];
						echo $separator;
					echo $order[ 'total_payment' ];
						echo $separator;
					echo ( $order[ 'refunded' ] ? 'Yes' : 'No' );
					echo $new_line;
				}
			}
		}
		// echo '<pre>';var_dump( $_total_payments, $_total_drivers, $_total_orders );exit();

		// echo json_encode( $out );exit();
	}
}