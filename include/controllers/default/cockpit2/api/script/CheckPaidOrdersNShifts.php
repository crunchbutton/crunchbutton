<?php

class Controller_Api_Script_CheckPaidOrdersNShifts extends Crunchbutton_Controller_RestAccount {

	const DATE_START = '20141101';
	const DATE_END = '20141112';
	const NOTES = 'Order payment fix';

	public function init() {
		// $this->orders();
		$this->shifts();
	}

	public function shifts(){

		$settlement = new Crunchbutton_Settlement;

		$query = 'SELECT DISTINCT( asa.id_admin ) AS id_admin, a.name, apt.using_pex FROM admin_shift_assign asa
								INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
								INNER JOIN admin a ON a.id_admin = asa.id_admin
								INNER JOIN admin_payment_type apt ON apt.id_admin = asa.id_admin
								WHERE
									DATE_FORMAT( cs.date_start, "%Y%m%d" ) >= "' . Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START . '"
								AND
									DATE_FORMAT( cs.date_end, "%Y%m%d" ) <= "' . Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END . '"
								AND
									asa.id_admin_shift_assign NOT IN ( SELECT id_admin_shift_assign FROM payment_schedule_shift pss )
								AND
									apt.payment_type = "hours"';


		$assigned_shifts = Crunchbutton_Admin_Shift_Assign::q( $query );

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
		if( false )
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

		if( false )
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
			$settlement->scheduleDriverPaymentTimeout( $driver, Controller_Api_Script_CheckPaidOrdersNShifts::NOTES, 0, 0, $driver[ 'id_admin' ], Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT );
		}

		// usort( $pay, function( $a, $b ) {
			// return $a[ 'name'] > $b[ 'name' ];
		// } );

	}

	public function orders() {

		$pay_type = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT; // Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT

		$query = 'SELECT * FROM `order` o
								WHERE
									DATE_FORMAT( o.date, "%Y%m%d" ) >= "' . Controller_Api_Script_CheckPaidOrdersNShifts::DATE_START . '"
								AND
									DATE_FORMAT( o.date, "%Y%m%d" ) <= "' . Controller_Api_Script_CheckPaidOrdersNShifts::DATE_END . '"
								AND
									o.delivery_service = 1
								AND o.id_order NOT IN ( SELECT pso.id_order FROM payment_schedule_order pso
																					INNER JOIN payment_schedule ps
																						ON
																							ps.id_payment_schedule = pso.id_payment_schedule
																						AND
																							ps.pay_type = "' . $pay_type . '" )';

		$_orders = Crunchbutton_Order::q( $query );

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

			$settlement->scheduleDriverPaymentTimeout( $driver, Controller_Api_Script_CheckPaidOrdersNShifts::NOTES, 0, 0, $driver[ 'id_admin' ], $pay_type );

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