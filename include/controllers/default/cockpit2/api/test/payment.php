<?php

class Controller_Api_Test_Payment extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$fix = 'payment_without_shifts';

		switch ( $fix ) {
			case 'payment_info':
				$this->paymentInfo();
				break;

			case 'payment_without_shifts':
				$this->paymentWithoutShifts();
				break;

			case 'schedule_error':
				$this->scheduleError();
				break;

			default:
				echo json_encode( [ 'error' => 'what should it do?' ] );exit;
				break;
		}
	}

	public function scheduleError(){
		$out = [ 'ok' => [], 'nope' => [] ];

		// Get the payments schedule with error
		$payments_schedule = Cockpit_Payment_Schedule::q("SELECT * FROM payment_schedule WHERE status = '" . Cockpit_Payment_Schedule::STATUS_PROCESSING . "' ORDER BY id_payment_schedule ASC");
		foreach( $payments_schedule as $payment_schedule ){
			$nope = false;

			// Get the payment of the schedule
			$payment = Crunchbutton_Payment::q( 'SELECT * FROM payment WHERE id_driver = "' . $payment_schedule->id_driver . '" AND ROUND( amount, 2 ) = ' . $payment_schedule->amount . ' ORDER BY id_payment DESC' );
			if( $payment->id_payment ){

				// Check if the payment is already related with another payment schedule
				$chedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = ' . $payment->id_payment );
				if( $chedule->count() == 0 ){
					$payment_schedule->id_payment = $payment->id_payment;
					$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
					$payment_schedule->log = 'Payment finished';
					$payment_schedule->status_date = $payment->date;

					// Hard codded values !!!!
					$payment_schedule->id_admin = 3;
					$payment_schedule->pay_type = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;

					$payment_schedule->save();
					$out[ 'ok' ][] = [ 'id_payment_schedule' => $payment_schedule->id_payment_schedule, 'id_payment' => $payment->id_payment ];

					// Update payment info if needed
					if( !$payment->id_admin ){
						$payment->id_admin = $payment_schedule->id_admin;
					}

					if( !$payment->pay_type ){
						$payment->pay_type = $payment_schedule->pay_type;
					}

					$payment->save();

					$nope = true;
				}
			}
			if( !$nope ){
				$payment_schedule->id_payment = $payment->id_payment;
				$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
				$payment_schedule->log = 'Payment error';
				$payment_schedule->status_date = date( 'YmdHis' );
				$payment_schedule->save();
				$out[ 'nope' ][] = $payment_schedule->id_payment_schedule;
			}
		}
		echo json_encode( $out );exit;
	}

	public function paymentInfo(){
		// Update payment info if needed
		$out = [ 'ok' => [], 'nope' => [] ];
		$payments = Crunchbutton_Payment::q( 'SELECT * FROM payment WHERE pay_type IS NULL OR id_admin IS NULL' );
		foreach( $payments as $payment ){
			$payment_schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = ' . $payment->id_payment );
			if( $payment_schedule->id_payment ){
				$updated = [ 'id_payment' => $payment->id_payment ];
				$updated = [ 'date' => $payment->date()->format( 'd/m/Y' ) ];
				$updated[ 'id_payment_schedule' ] = $payment_schedule->id_payment_schedule;
				if( !$payment->id_admin && $payment_schedule->id_admin ){
					$payment->id_admin = $payment_schedule->id_admin;
					$updated[ 'id_admin' ] = true;
				}
				if( !$payment->pay_type && $payment_schedule->pay_type ){
					$payment->pay_type = $payment_schedule->pay_type;
					$updated[ 'pay_type' ] = true;
				}
				$out[ 'ok' ][] = $updated;
				$payment->save();
			}
		}
		echo json_encode( $out );exit;
	}

	public function paymentWithoutShifts(){

		$out = [ 'shifts' => [ 'ok' => [], 'nope' => [] ] ];

		$settlement = new Crunchbutton_Settlement;

		// Script to fix the hours payments without shifts
		$schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE amount IS NOT NULL AND amount > 0 AND type = "' . Cockpit_Payment_Schedule::TYPE_DRIVER . '" AND driver_payment_hours = 1 AND ( pay_type IS NULL OR pay_type != "' . Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT . '" ) AND id_payment_schedule NOT IN ( SELECT DISTINCT( id_payment_schedule ) AS id_payment_schedule FROM payment_schedule_shift ) ORDER by id_payment_schedule DESC' );

		foreach( $schedules as $schedule ){

			$is_ok = false;

			$driver = $schedule->driver();
			$payment_type = $driver->payment_type();
			$hour_rate = floatval( $payment_type->hour_rate );

			$_total_reimburse = 0;
			$_total_payment = 0;
			$_orders_total_reimburse = 0;
			$_orders_total_payment = 0;
			$_markup = 0;

			$has_orders = false;

			$orders = $schedule->orders();
			if( $orders->count() <= 0 ){
				$range = $schedule->range_date;
				if( $range ){
					$range = explode( '=>' , $range );
					$orders = Crunchbutton_Order::q('
						SELECT DISTINCT( o.id_order ) FROM `order` o
						INNER JOIN order_action oa ON oa.id_order = o.id_order
						WHERE
							DATE_FORMAT( o.date, "%m/%d/%Y" ) >= ?
							AND DATE_FORMAT( o.date, "%m/%d/%Y" ) <= ?
							AND oa.type = "delivery-delivered" AND oa.id_admin = ?
					',[trim( $range[ 0 ] ), trim( $range[ 1 ] ), $schedule->id_driver]);
				}
			} else {
				$has_orders = true;
			}
			if( $orders->count() > 0 ){
				foreach( $orders as $order ){
					$variables = $settlement->orderExtractVariables( Order::o( $order->id_order ) );
					$pay_info = $settlement->driversProcess( [ $variables ], true, false, false );
					$_markup += $pay_info[ 0 ][ 'markup' ];
					$_orders_total_reimburse += $pay_info[ 0 ][ 'orders' ][ 0 ][ 'pay_info' ][ 'total_reimburse' ];
					$_orders_total_payment += $pay_info[ 0 ][ 'orders' ][ 0 ][ 'pay_info' ][ 'total_payment' ];
				}
			}

			// Confirm that the schedule doesn't have a shift
			if( $schedule->shifts()->count() == 0 ){
				$range = $schedule->range_date;
				if( $range ){
					$range = explode( '=>' , $range );

					$shifts = Crunchbutton_Community_Shift::q('
						SELECT cs.*, asa.id_admin_shift_assign
						FROM admin_shift_assign asaINNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
						WHERE
							asa.id_admin = ?
							AND cs.date_start >= ?
							AND cs.date_end <= ?
					', [$schedule->id_driver, trim( $range[ 0 ] ), trim( $range[ 1 ] )]);
					$_total = 0;
					$_amount = 0;
					$_hours = 0;
					// Relate the shifts with the schedule
					foreach( $shifts as $shift ){
						$hours = $shift->duration();
						$amount = round( $hours * $hour_rate, 2 );
						$schedule_shift = new Cockpit_Payment_Schedule_Shift;
						$schedule_shift->id_payment_schedule = $schedule->id_payment_schedule;
						$schedule_shift->id_admin_shift_assign = $shift->id_admin_shift_assign;
						$schedule_shift->hours = $hours;
						$schedule_shift->amount = $amount;
						$_amount += $amount;
						$_hours += $hours;
						$_total++;
						$is_ok = true;
					}
				}
			}

			$is_ok = ( ( $_total_paid = $_amount + $_orders_total_payment + $_markup ) == floatval( $schedule->amount ) );

			if( $is_ok ){
				$out[ 'shifts' ][ 'ok' ][] = [ 'id_payment_schedule' => $schedule->id_payment_schedule, 'total' => $_total, 'shifts_amount' => $_amount, 'shift_hours' => $_hours, 'schedule_amount' => floatval( $schedule->amount ), 'total_paid' => $_total_paid, 'hour_rate' => $hour_rate, 'orders_total_reimburse' => $_orders_total_reimburse, 'orders_total_payment' => $_orders_total_payment, 'has_orders' => $has_orders, 'markup' => $_markup ] ;


				if( !$has_orders ){
					foreach( $orders as $order ){
						echo '<pre>';var_dump( $order );exit();
					}
				}

				foreach( $shifts as $shift ){
					$hours = $shift->duration();
					$amount = round( $hours * $hour_rate, 2 );
					$schedule_shift = new Cockpit_Payment_Schedule_Shift;
					$schedule_shift->id_payment_schedule = $schedule->id_payment_schedule;
					$schedule_shift->id_admin_shift_assign = $shift->id_admin_shift_assign;
					$schedule_shift->hours = $hours;
					$schedule_shift->amount = $amount;
					$schedule_shift->save();
				}
			} else {
				$out[ 'shifts' ][ 'nope' ][] = $schedule->id_payment_schedule;
			}
		}

		echo json_encode( $out );exit;
	}

}