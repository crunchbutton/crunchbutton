<?php

class Crunchbutton_Queue_Settlement_Driver extends Crunchbutton_Queue {

	public function run() {

		$data = $this->info;

		$data = json_decode( $data );

		if( !$data ){ return self::STATUS_FAILED; }

		$start = $data->start;
		$end = $data->end;
		$pay_type = $data->pay_type;
		$id_drivers = $data->id_drivers;
		$_id_drivers = explode( ',', $id_drivers );

		$id_drivers = [];

		// check if there is another schedule queue running and cancel it to avoid dup payments
		$queue = Settlement::hasSettlementQueRunning( Crunchbutton_Queue::TYPE_SETTLEMENT_DRIVER, $this->id_queue );
		if( $queue->id_queue ){
			$message = "Driver schedule queue failed - duplicated queues:<br>\n";
			$message .= "Queue running #{$queue->id_queue}<br>\n";
			$message .= "Queue failed: #{$this->id_queue}<br>\n";
			$message .= "Payment type: {$pay_type}<br>\n";
			$email = new Crunchbutton_Email( [ 	'to' => 'dev@crunchbutton.com,payment@crunchbutton.com,cc@crunchbutton.com',
																					'from' => 'support@crunchbutton.com',
																					'subject' => 'Driver schedule queue failed #' . $this->id_queue,
																					'messageHtml' => $message,
																					'reason' => Crunchbutton_Email_Address::REASON_CRON_ERROR ] );
			$email->send();

			return self::STATUS_FAILED;
		}

		foreach ( $_id_drivers as $key => $val ) {
			$id_driver = floatval( trim( $val ) );
			$notes = $data->{'notes_' . $id_driver};
			$adjustment = $data->{'adjustments_' . $id_driver};
			$adjustment_notes = $data->{'adjustments_notes_' . $id_driver};

			$id_drivers[ $id_driver ] = [];
			$id_drivers[ $id_driver ][ 'notes' ] = ( $notes ) ? $notes : Crunchbutton_Settlement::DEFAULT_NOTES;
			$id_drivers[ $id_driver ][ 'adjustment' ] = $adjustment;
			$id_drivers[ $id_driver ][ 'adjustment_notes' ] = $adjustment_notes;
		}

		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end ] );

		if( $settlement->scheduleDriverPayment( $id_drivers, $pay_type, $this->id_admin ) ){
			return self::STATUS_SUCCESS;
		} else {
			$message = "Driver schedule queue failed:<br>\n";
			$message .= "Queue running #{$queue->id_queue}<br>\n";
			$message .= "Payment type: {$pay_type}<br>\n";
			$email = new Crunchbutton_Email( [ 	'to' => 'dev@crunchbutton.com,payment@crunchbutton.com,cc@crunchbutton.com',
																					'from' => 'support@crunchbutton.com',
																					'subject' => 'Driver schedule queue failed #' . $this->id_queue,
																					'messageHtml' => $message,
																					'reason' => Crunchbutton_Email_Address::REASON_CRON_ERROR ] );
			$email->send();
			return self::STATUS_FAILED;
		}
	}
}