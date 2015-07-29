<?php

class Crunchbutton_Stripe_Webhook extends Cana_Table {

	public static function create( $params = [] ) {

		$webhook = new Crunchbutton_Stripe_Webhook;
		$webhook->data = json_encode( $params ); // raw object
		$webhook->datetime = date( 'Y-m-d H:i:s' );
		$webhook->created = $params->created;
		$webhook->event_id = $params->id;
		$webhook->object_id = $params->data->object->id;
		if( !$webhook->object_id && $params->data->object->charge ){
			$webhook->object_id = $params->data->object->charge;
		}
		$webhook->amount = $params->data->object->amount;
		$webhook->status = $params->data->object->status;
		$type = Crunchbutton_Stripe_Webhook_Type::byType( $params->type );
		if( !$type ){
			return;
		}
		$webhook->id_stripe_webhook_type = $type->id_stripe_webhook_type;
		$webhook->save();
		switch ( $type->type ) {
			case Crunchbutton_Stripe_Webhook_Type::TYPE_DISPUTE_CREATED:
				// create dispute
				Crunchbutton_Stripe_Dispute::create( $webhook->id_stripe_webhook );
				break;
			case Crunchbutton_Stripe_Webhook_Type::TYPE_DISPUTE_UPDATED:
				$charge_id = $webhook->charge_id();
				if( $charge_id ){
					$dispute = Crunchbutton_Stripe_Dispute::getDisputeByChargeId( $charge_id );
					if( $dispute->id_stripe_dispute ){
						// update dispute log
						Crunchbutton_Stripe_Dispute_Log::create( [ 'id_stripe_dispute' => $dispute->id_stripe_dispute, 'id_stripe_webhook' => $webhook->id_stripe_webhook ] );
					}
				}
				break;
		}
		return $webhook;
	}

	public function charge_id(){
		$details = $this->data();
		if( $details->data && $details->data->object && $details->data->object->charge ){
			return $details->data->object->charge;
		}
	}

	public function data(){
		if( !$this->_data ){
			$this->_data = json_decode( $this->data );
		}
		return $this->_data;
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function webhookType(){
		if( !$this->_webhook_type ){
			$this->_webhook_type = Crunchbutton_Stripe_Webhook_Type::o( $this->id_stripe_webhook_type );
		}
		return $this->_webhook_type;
	}

	public function type(){
		return $this->webhookType();
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('stripe_webhook')
			->idVar('id_stripe_webhook')
			->load($id);
	}
}
