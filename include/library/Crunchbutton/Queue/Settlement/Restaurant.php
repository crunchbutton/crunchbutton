<?php

class Crunchbutton_Queue_Settlement_Restaurant extends Crunchbutton_Queue {

	public function run() {

		$data = $this->info;

		$data = json_decode( $data );

		if( !$data ){ return self::STATUS_FAILED; }

		// check if there is another schedule queue running and cancel it to avoid dup payments
		$queue = Settlement::hasSettlementQueRunning( Crunchbutton_Queue::TYPE_SETTLEMENT_RESTAURANT, $this->id_queue );
		if( $queue->id_queue ){
			$message = "Restaurant schedule queue failed - duplicated queues:<br>\n";
			$message .= "Queue running #{$queue->id_queue}<br>\n";
			$message .= "Queue failed: #{$this->id_queue}<br>\n";
			$message .= "Payment type: {$pay_type}<br>\n";
			$email = new Crunchbutton_Email( [ 	'to' => 'dev@crunchbutton.com,payment@crunchbutton.com,cc@crunchbutton.com',
																					'from' => 'support@crunchbutton.com',
																					'subject' => 'Restaurant schedule queue failed #' . $this->id_queue,
																					'messageHtml' => $message,
																					'reason' => Crunchbutton_Email_Address::REASON_CRON_ERROR ] );
			$email->send();

			return self::STATUS_FAILED;
		}

		$start = $data->start;;
		$end = $data->end;;
		$_id_restaurants = explode( ',', $data->id_restaurants );
		$id_restaurants = [];
		foreach ( $_id_restaurants as $key => $val ) {
			$id_restaurant = trim( $val );
			$notes = $data->{'notes_' . $id_restaurant};
			$adjustment = $data->{'notes_' . $id_restaurant};
			$id_restaurants[ $id_restaurant ] = [];
			$id_restaurants[ $id_restaurant ][ 'notes' ] = ( $notes ) ? $notes : Crunchbutton_Settlement::DEFAULT_NOTES;
			$id_restaurants[ $id_restaurant ][ 'adjustment' ] = $adjustment;
		}
		$pay_type = ( $data->pay_type == 'all' ) ? '' : $data->pay_type;
		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end ] );
		if( $settlement->scheduleRestaurantPayment( $id_restaurants, $this->id_admin ) ){
			return self::STATUS_SUCCESS;
		} else {
			$message = "Restaurant schedule queue failed:<br>\n";
			$message .= "Queue running #{$queue->id_queue}<br>\n";
			$message .= "Payment type: {$pay_type}<br>\n";
			$email = new Crunchbutton_Email( [ 	'to' => 'dev@crunchbutton.com,payment@crunchbutton.com,cc@crunchbutton.com',
																					'from' => 'support@crunchbutton.com',
																					'subject' => 'Restaurant schedule queue failed #' . $this->id_queue,
																					'messageHtml' => $message,
																					'reason' => Crunchbutton_Email_Address::REASON_CRON_ERROR ] );
			$email->send();
			return self::STATUS_FAILED;
		}
	}
}