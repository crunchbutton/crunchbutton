<?php

class Crunchbutton_Stripe_Dispute extends Cana_Table {

	const STATUS_NEEDS_RESPONSE = 'needs_response';
	const STATUS_UNDER_REVIEW = 'under_review';
	const STATUS_LOST = 'lost';
	const STATUS_WON = 'won';

	const EVIDENCES_SEND_LIMIT = 5;

	public function create( $id_stripe_webhook ){

		$webhook = Crunchbutton_Stripe_Webhook::o( $id_stripe_webhook );
		if( $webhook->id_stripe_webhook ){

			$txn = $webhook->charge_id();

			$dispute = Crunchbutton_Stripe_Dispute::getDisputeByChargeId( $txn );
			if( $dispute->id_stripe_dispute ){
				$dispute->addLog( $dispute->id_stripe_dispute, $webhook->id_stripe_webhook );
				return;
			}

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
				$dispute->autoSendEvidence();
			}
		}
	}

	public function canSendMoreEvidences(){
		return ( $this->submission_count < self::EVIDENCES_SEND_LIMIT && $this->status != self::STATUS_LOST  && $this->status != self::STATUS_WON );
	}

	public function getDisputeByChargeId( $charge_id ){
		$dispute = Crunchbutton_Stripe_Dispute::q( 'SELECT * FROM stripe_dispute sd INNER JOIN `order` o ON o.id_order = sd.id_order WHERE o.txn = ?', [ $charge_id ] )->get( 0 );
		if( $dispute->id_stripe_dispute ){
			return $dispute;
		}
		return null;
	}

	public function charge_id(){
		if( !$this->_charge_id ){
			$details = $this->details();
			if( $details ){
				$this->_charge_id = $details->charge_id();
			}
			if( !$this->_charge_id ){
				$order = $this->order();
				$this->_charge_id = $order->txn;
			}
		}
		return $this->_charge_id;
	}

	public function details(){
		if( !$this->_details ){
			$log = Crunchbutton_Stripe_Dispute_Log::q( 'SELECT sdl.* FROM stripe_dispute_log sdl
														INNER JOIN stripe_webhook sw ON sw.id_stripe_webhook = sdl.id_stripe_webhook
														INNER JOIN stripe_webhook_type swt ON swt.id_stripe_webhook_type = sw.id_stripe_webhook_type
														WHERE sdl.id_stripe_dispute = ? AND swt.type = ? ORDER BY id_stripe_dispute_log ASC LIMIT 1', [ $this->id_stripe_dispute, Crunchbutton_Stripe_Webhook_Type::TYPE_DISPUTE_CREATED ] )->get( 0 );
			if( $log->id_stripe_dispute_log ){
				$this->_details = $log->webhook();
			}
		}
		return $this->_details;
	}

	public function lastEvidence(){
		return Crunchbutton_Stripe_Dispute_Evidence::q( 'SELECT * FROM stripe_dispute_evidence WHERE id_stripe_dispute = ? ORDER BY id_stripe_dispute_evidence DESC LIMIT 1', [ $this->id_stripe_dispute ] )->get( 0 );
	}

	// evidence automaticaly sent
	public function autoSendEvidence(){
		$evidence = Crunchbutton_Stripe_Dispute_Evidence::create( [ 'id_stripe_dispute' => $this->id_stripe_dispute ] );
		$evidence->generateEvidence();
		$this->updateDispute();
	}

	public function uploadReceipt( $file ){

		\Stripe\Stripe::setApiKey( c::config()->stripe->{c::getEnv()}->secret );

		$file = realpath( $file );
		$post = array( 'purpose' => 'dispute_evidence', 'file'=> '@' . $file );
		$url = Stripe\Stripe::$apiUploadBase . '/v1/files';
		$stripe_auth = c::config()->stripe->{ c::getEnv() }->secret . ':';
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch,CURLOPT_USERPWD, $stripe_auth );
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch,CURLOPT_POSTFIELDS, $post );
		$result = json_decode( curl_exec( $ch ) );

		@unlink( $file );

		if( !$result->id ){
			$e = curl_error($ch);
			return [ 'upload_error' => $e ];
		} else {
			return $result;
		}
	}

	public function updateDispute( $type = 'evidence' ){
		switch ( $type ) {
			case 'evidence':
				$evidence = $this->lastEvidence();
				$charge_id = $this->charge_id();
				if( !$charge_id || !$evidence ){
					return;
				}
				\Stripe\Stripe::setApiKey( c::config()->stripe->{c::getEnv()}->secret );
				$ch = \Stripe\Charge::retrieve( $charge_id );
				$result = $ch->updateDispute( [ 'evidence' => $evidence->exportsEvidence() ] );
				$evidence->status = Crunchbutton_Stripe_Dispute_Evidence::STATUS_SENT;
				$evidence->save();
				$this->addLog( $this->id_stripe_dispute, null, $evidence->id_stripe_dispute_evidence );
				$this->submission_count++;
				$this->save();
				return $result;
				break;

			// some more options here
			default:
				# code...
				break;
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

	public function log(){
		return Crunchbutton_Stripe_Dispute_Log::q( 'SELECT * FROM stripe_dispute_log WHERE id_stripe_dispute = ? ORDER BY id_stripe_dispute_log DESC', [ $this->id_stripe_dispute ] );
	}

	public function addLog( $id_stripe_dispute, $id_stripe_webhook = null, $id_stripe_dispute_evidence = null ){
		Crunchbutton_Stripe_Dispute_Log::create( [ 'id_stripe_dispute' => $id_stripe_dispute,
		 											'id_stripe_webhook' => $id_stripe_webhook,
		 											'id_stripe_dispute_evidence' => $id_stripe_dispute_evidence ] );
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
