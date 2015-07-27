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
				Crunchbutton_Stripe_Dispute::create( $webhook->id_stripe_webhook );
				break;
		}


		return $webhook;
	}

	public function data(){
		if( !$this->_data ){
			$this->_data = json_decode( $this->data );
		}
		return $this->_data;
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
