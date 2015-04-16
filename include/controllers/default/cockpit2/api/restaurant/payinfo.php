<?php

class Controller_api_restaurant_payinfo extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( $this->method() ) {
			case 'post':
				$this->_post();
				break;

			case 'get':
				$this->_get();
				break;

		}
	}

	private function _post(){

		switch ( c::getPagePiece( 3 ) ) {

			case 'payment-method':
				$this->_paymentMethodSave();
			break;

			case 'stripe':
				$this->_stripe();
			break;

			default:
			$this->_error();
			break;
		}

	}

	private function _get(){

		switch ( c::getPagePiece( 3 ) ) {

			case 'payment-method':
				$this->_exports();
			break;

			default:
			$this->_error();
			break;
		}
	}

	private function _restaurant(){

		if( $this->request()[ 'id_restaurant' ] ){
			$restaurant = Restaurant::o( $this->request()[ 'id_restaurant' ] );
		}

		if( !$restaurant->id_restaurant ) {
			$restaurant = Restaurant::permalink( c::getPagePiece( 4 ) );
		}

		if( !$restaurant->id_restaurant ) {
			$restaurant = Restaurant::o( c::getPagePiece( 4 ) );
		}

		if( !$restaurant->id_restaurant ){
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		return $restaurant;
	}

	private function _exports(){
		$restaurant = $this->_restaurant();
		$payment_method = $restaurant->payment_type();
		echo json_encode( $payment_method->exports() );exit;
	}

	private function _stripe(){

		$token = $this->request()[ 'token' ];
		if( !$token ){
			$this->_error( 'Invalid token!' );
		}

		$env = c::getEnv();

		\Stripe\Stripe::setApiKey( c::config()->stripe->{$env}->secret );

		$recipient = \Stripe\Recipient::create( array(
			'name' => $this->request()[ 'name' ],
			'type' => $this->request()[ 'account_type' ],
			'bank_account' => $token,
			'email' => $this->request()[ 'email' ] )
		);

		if( $recipient->id && $recipient->active_account && $recipient->active_account->id ){
			$restaurant = $this->_restaurant();
			$payment_method = $restaurant->payment_type();
			$payment_method->legal_name_payment = $this->request()[ 'name' ];
			$payment_method->summary_email = $this->request()[ 'email' ];
			$payment_method->tax_id = $this->request()[ 'tax_id' ];
			$payment_method->stripe_id = $recipient->id;
			$payment_method->stripe_account_id = $recipient->active_account->id;
			$payment_method->save();
			$this->_exports();
		}
		$this->_error( 'Error saving' );

	}

	private function _paymentMethodSave(){

		$restaurant = $this->_restaurant();

		$payment_method = $restaurant->payment_type();
		$payment_method->charge_credit_fee = $this->request()[ 'charge_credit_fee' ];;
		$payment_method->check_address = $this->request()[ 'check_address' ];;
		$payment_method->check_address_city = $this->request()[ 'check_address_city' ];;
		$payment_method->check_address_country = $this->request()[ 'check_address_country' ];;
		$payment_method->check_address_state = $this->request()[ 'check_address_state' ];;
		$payment_method->check_address_zip = $this->request()[ 'check_address_zip' ];;
		$payment_method->contact_name = $this->request()[ 'contact_name' ];;
		$payment_method->legal_name_payment = $this->request()[ 'legal_name_payment' ];;
		$payment_method->max_apology_credit = $this->request()[ 'max_apology_credit' ];;
		$payment_method->max_pay_promotion = $this->request()[ 'max_pay_promotion' ];;
		$payment_method->pay_apology_credits = $this->request()[ 'pay_apology_credits' ];;
		$payment_method->payment_method = $this->request()[ 'payment_method' ];;
		$payment_method->summary_email = $this->request()[ 'summary_email' ];;
		$payment_method->summary_fax = $this->request()[ 'summary_fax' ];;
		$payment_method->summary_frequency = $this->request()[ 'summary_frequency' ];;
		$payment_method->summary_method = $this->request()[ 'summary_method' ];;
		$payment_method->waive_fee_first_month = $this->request()[ 'waive_fee_first_month' ];;
		$payment_method->save();

		$this->_exports();

	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}