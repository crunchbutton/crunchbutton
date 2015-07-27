<?php

class Crunchbutton_Stripe_Dispute extends Cana_Table {

	public function create( $id_stripe_webhook ){

		$webhook = Crunchbutton_Stripe_Webhook::o( $id_stripe_webhook );
		if( $webhook->id_stripe_webhook ){
			$txn = $webhook->object_id;

			$order = Crunchbutton_Order::q( 'SELECT * FROM `order` WHERE txn = ? ORDER BY id_order DESC LIMIT 1', [ $txn ] )->get( 0 );
			if( $order->id_order ){
				// webhook info
				$info = $webhook->data();
				$timestamp = $info->data->object->evidence_details->due_by;
				if( $timestamp ){
					$due_to = new DateTime();
					$due_to = DateTime::createFromFormat( 'U', $timestamp );
					$due_to->setTimezone( new DateTimeZone( c::config()->timezone ) );
				}

				$dispute = new Crunchbutton_Stripe_Dispute;
				$dispute->id_order = $order->id_order;
				$dispute->datetime = date( 'Y-m-d H:i:s' );
				if( $due_to ){
					$dispute->due_to = $due_to->format( 'Y-m-d H:i:s' );
				} else {
					$now = new DateTime("now", new DateTimeZone( c::config()->timezone ));
					$now->modify( '+14 days' );
					$dispute->due_to = $due_to->format( $now->format( 'Y-m-d H:i:s' ) );
				}
				$dispute->reason = $info->data->object->reason;
				$dispute->status = $info->data->object->status;
				$dispute->submission_count = 0;
				$dispute->save();
				// Add log
				$dispute->addLog( $dispute->id_stripe_dispute, $webhook->id_stripe_webhook );
			}
		}
	}

	public function order(){
		if( !$this->_order ){
			$this->_order = Order::o( $this->id_order );
		}
		return $this->_order;
	}

	public function setStatus( $status ){
		$this->status = $status;
		$this->save();
	}

	public function setSubmissionCount( $submission_count ){
		$this->submission_count = $submission_count;
		$this->save();
	}

	public function log(){
		return Crunchbutton_Stripe_Dispute_Log::q( 'SELECT * FROM stripe_dispute_log WHERE id_stripe_dispute = ? ORDER BY id_stripe_dispute_log DESC', [ $this->id_stripe_dispute ] );
	}

	public function addLog( $id_stripe_dispute, $id_stripe_webhook, $id_admin = null ){
		Crunchbutton_Stripe_Dispute_Log::create( [ 'id_stripe_dispute' => $id_stripe_dispute,
		 											'id_stripe_webhook' => $id_stripe_webhook,
		 											'id_admin' => $id_admin ] );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('stripe_dispute')
			->idVar('id_stripe_dispute')
			->load($id);
	}
}
