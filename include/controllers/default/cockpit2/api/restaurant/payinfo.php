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
			$this->error(404);
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			$this->error(401);
		}

		return $restaurant;
	}

	private function _exports(){
		$restaurant = $this->_restaurant();
		$paymentType = $restaurant->payment_type();
		echo json_encode( $paymentType->exports() );exit;
	}

	private function _stripe(){

		$token = $this->request()[ 'token' ];
		if( !$token ){
			$this->_error( 'Invalid token!' );
		}

		$restaurant = $this->_restaurant();
		$paymentType = $restaurant->payment_type();
		$stripeAccount = $restaurant->getAndMakeStripe([
			'bank_account' => $token,
			'account_type' => $this->request()['account_type']
		]);

		if ($stripeAccount){
			$this->_exports();
		} else {
			$this->_error( 'Error creating stripe account' );
		}

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