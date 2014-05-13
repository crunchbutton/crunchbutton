<?php

class Crunchbutton_Payment extends Cana_Table {
	public static function credit($params = null) {

		$payment = new Payment((object)$params);
		$payment->date = date('Y-m-d H:i:s');
		$payment_type = Crunchbutton_Restaurant_Payment_Type::byRestaurant( $payment->id_restaurant );

		if( $payment->type == 'balanced' ){
			// Balanced payment
			$credit = Crunchbutton_Balanced_Credit::credit( $payment_type, $payment->amount, $payment->note);
			$payment->balanced_id = $credit->id;

		} elseif( $payment->type == 'stripe' ){

			// Stripe payment
			Stripe::setApiKey(c::config()->stripe->{c::getEnv()}->secret);

			try {
				if ( $payment_type->stripe_id ) {
					$credit = Stripe_Transfer::create( array(
						'amount' => $payment->amount * 100,
						'currency' => 'usd',
						'recipient' => $payment_type->stripe_id,
						'description' => $payment->note,
						'statement_descriptor' => 'Crunchbutton Orders'
					) );
					$payment->stripe_id = $credit->id;
				}

			} catch (Exception $e) {
				print_r($e);
				exit;
			}
		}
		$payment->env = c::getEnv(false);
		$payment->save();

		if( $payment->balanced_id || $payment->stripe_id ){
			return true;
		} else {
			return false;
		}

	}
	
	public function infoLink(){
		if( $this->type() == 'stripe' ){
			return '<a href="https://manage.stripe.com/transfers/' . $this->stripe_id . '">' . $this->stripe_id . '</a>';
		}
		if( $this->type() == 'balanced' ){
			return $this->balanced_id;
		}
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function type(){
		if( $this->stripe_id ){
			return 'stripe';
		} else {
			return 'balanced';
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('payment')
			->idVar('id_payment')
			->load($id);
	}
}