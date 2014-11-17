<?php

class Controller_Api_Script_DriversPaymentRangeFix extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// Driver Payment Summaries Broken #4044

		$out = [ 'ok' => [], 'nope' => [] ];

		$schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule ORDER BY id_payment_schedule DESC' );

		foreach( $schedules as $schedule ){

			if( $schedule->arbritary ){
				continue;
			}

			$range_date = $schedule->range_date;
			$new_range_date = false;

			$type = '';

			if( $schedule->driver_payment_hours ){
				// it is using shifts
				$first_shift = Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM payment_schedule_shift pss INNER JOIN admin_shift_assign asa ON pss.id_admin_shift_assign = asa.id_admin_shift_assign INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift WHERE pss.id_payment_schedule = ' . $schedule->id_payment_schedule . ' ORDER BY date_start ASC LIMIT 1' );
				$last_shift = Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM payment_schedule_shift pss INNER JOIN admin_shift_assign asa ON pss.id_admin_shift_assign = asa.id_admin_shift_assign INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift WHERE pss.id_payment_schedule = ' . $schedule->id_payment_schedule . ' ORDER BY date_end DESC LIMIT 1' );

				if( $first_shift->count() && $last_shift->count() && $first_shift->id_community_shift && $last_shift->id_community_shift ){
					$new_range_date = $first_shift->date()->format( 'm/d/Y' ) . ' => ' . $last_shift->date()->format( 'm/d/Y' );
				}


			} else {
				// it is using orders

				$first_order = Crunchbutton_Order::q( 'SELECT * FROM `order` WHERE id_order = ( SELECT MIN( id_order ) AS id_order FROM payment_schedule_order WHERE id_payment_schedule = ' . $schedule->id_payment_schedule . ' )' );
				$last_order = Crunchbutton_Order::q( 'SELECT * FROM `order` WHERE id_order = ( SELECT MAX( id_order ) AS id_order FROM payment_schedule_order WHERE id_payment_schedule = ' . $schedule->id_payment_schedule . ' )' );

				if( $first_order->count() && $last_order->count() ){
					$new_range_date = $first_order->date()->format( 'm/d/Y' ) . ' => ' . $last_order->date()->format( 'm/d/Y' );
				}
			}

			if( $new_range_date ){
				if( $new_range_date == $range_date ){
					$out[ 'ok' ][ $schedule->id_payment_schedule ] = [ 'range' => $range_date, 'type' => ( $schedule->driver_payment_hours ? 'hours' : 'orders' ) ] ;
				} else {
					$out[ 'nope' ][ $schedule->id_payment_schedule ] = [ 'old' => $range_date, 'new' => $new_range_date, 'type' => ( $schedule->driver_payment_hours ? 'shift' : 'hours' ) ] ;
					$schedule->range_date = $new_range_date;
					$schedule->save();
				}
			}
		}

		echo json_encode( $out );exit();

	}
}