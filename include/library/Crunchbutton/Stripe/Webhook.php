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
			case Crunchbutton_Stripe_Webhook_Type::TYPE_ACCOUNT_UPDATED:
				self::accountUpdateWarning($webhook);
				break;
			case Crunchbutton_Stripe_Webhook_Type::TYPE_TRANSFER_FAILED:
				self::transferFail($webhook);
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

	public static function transferFail($webhook){
		$data = $webhook->data();
		$content = 'Stripe transfer failed ' . $data->data->object->business_name;
		$amount = 0;
		if($data->data->object->amount){
			$amount = $data->data->object->amount / 100;
		}
		$content .= "\n".'Amount: $' . $amount ;
		$content .= "\n".'More Info: https://dashboard.stripe.com/transfers/' . $data->data->object->id ;
		$params = [];
		$params['subject'] = 'Action required: Stripe transfer failed';
		$params['body'] = $content;
		Support::createNewWarning(['body'=>$content, 'bubble'=>true]);
		$mail = new Crunchbutton_Email_Payment_Error($params);
		$send = $mail->send();
	}

	public static function accountUpdateWarning($webhook){
		$data = $webhook->data();
		if(count($data->data->object->verification->fields_needed)){
			$content = 'There is a problem with the Stripe Account: ' . $data->data->object->business_name . ' (' . $data->data->object->display_name . ')';
			$content .= "\n".'URL: https://dashboard.stripe.com/applications/users/' . $data->data->object->id ;
			$content .= "\n\n".'Field(s) Needed: ';
			$content .= join($data->data->object->verification->fields_needed, "\n");
			$content .= "\n\n".'Please take some actions otherwise this account will not be able to create charges or receive transfers.';
			$params = [];
			$params['subject'] = 'Action required';
			$params['body'] = $content;
			Support::createNewWarning(['body'=>$content, 'bubble'=>true]);
			$mail = new Crunchbutton_Email_Payment_Error($params);
			$send = $mail->send();
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
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
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
