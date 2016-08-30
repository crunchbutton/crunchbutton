<?php

class Crunchbutton_Stripe_Webhook_Type extends Cana_Table {

	const TYPE_DISPUTE_CREATED = 'charge.dispute.created';
	const TYPE_DISPUTE_UPDATED = 'charge.dispute.updated';
	const TYPE_ACCOUNT_UPDATED = 'account.updated';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('stripe_webhook_type')
			->idVar('id_stripe_webhook_type')
			->load($id);
	}

	public static function byType( $type ){
		if( $type ){
			$webhookType = Crunchbutton_Stripe_Webhook_Type::q( 'SELECT * FROM stripe_webhook_type WHERE type = ? ', [ $type ] );
			$webhookType = $webhookType->get( 0 );
			if( $webhookType->id_stripe_webhook_type ){
				return $webhookType;
			} else {
				$webhookType = new Crunchbutton_Stripe_Webhook_Type;
				$webhookType->type = $type;
				$webhookType->save();
				return $webhookType;
			}
		}
		return false;
	}
}
