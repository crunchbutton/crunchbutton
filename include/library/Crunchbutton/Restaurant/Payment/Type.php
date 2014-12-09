<?php

class Crunchbutton_Restaurant_Payment_Type extends Cana_Table {

	const PAYMENT_METHOD_CHECK = 'check';
	const PAYMENT_METHOD_DEPOSIT = 'deposit';
	const PAYMENT_METHOD_NO_PAYMENT = 'no payment';

	const SUMMARY_METHOD_FAX = 'fax';
	const SUMMARY_METHOD_EMAIL = 'email';
	const SUMMARY_METHOD_NO_SUMMARY = 'no summary';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_payment_type')
			->idVar('id_restaurant_payment_type')
			->load($id);
	}

	public function getRecipientInfo(){
		if( $this->stripe_id && !$this->_stripe_recipient ){
			try{
				$this->_stripe_recipient = Stripe_Recipient::retrieve( $this->stripe_id );
			} catch (Exception $e) {
				print_r($e);
				exit;
			}
		}
		return $this->_stripe_recipient;
	}

	function byRestaurant( $id_restaurant ){
		if( $id_restaurant ){
			$payment = Crunchbutton_Restaurant_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant = ' . $id_restaurant . ' ORDER BY id_restaurant_payment_type DESC LIMIT 1' );
			if( $payment->id_restaurant_payment_type ){
				return Crunchbutton_Restaurant_Payment_Type::o( $payment->id_restaurant_payment_type );
			} else{
				$payment = new Crunchbutton_Restaurant_Payment_Type();
				$payment->id_restaurant = $id_restaurant;
				$payment->save();
				return $payment;
			}
		}
		return false;
	}
}