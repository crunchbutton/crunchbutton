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

			case 'update-entity-name':
				$this->_updateEntityName();
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

			case 'stripe-status':
				$this->_stripeStatus();
			break;

			case 'stripe-send-verification-info':
				$this->_stripeSendVerificationInfo();
			break;

			default:
			$this->_error();
			break;
		}
	}

	private function _stripeSendVerificationInfo(){
		$restaurant = $this->_restaurant();
		$paymentType = $restaurant->payment_type();
		$stripe = $paymentType->setStripeRep();
		if( $stripe->id ){
			echo json_encode( [ 'status' => 'success' ] );exit;
		} else {
			echo json_encode( [ 'status' => 'error' ] );exit;
		}
	}

	private function _stripeStatus(){

		$restaurant = $this->_restaurant();
		$paymentType = $restaurant->payment_type();
		$stripe = $paymentType->getAndMakeStripe();
		if ( $stripe->legal_entity->verification->status == 'verified' ) {
			echo json_encode( [ 'status' => 'success' ] );exit;
		} else {
			if( $stripe->verification ){
				$msg = [];
				if( $stripe->verification->fields_needed ){
					$fields = [];
					foreach( $stripe->verification->fields_needed as $field ){
						$fields[] = $field;
					}
					if( count( $fields ) > 0 ){
						$msg[] = 'Fields needed: ' . join( $fields, ', ' );
					}
				}
				if( $stripe->verification->disabled_reason ){
					$msg[] = 'Disabled reason: ' . $stripe->verification->disabled_reason;
				}
				if( count( $msg ) ){
					echo json_encode( [ 'status' => join( $msg, '. ' ) ] );exit;
				}
				echo json_encode( [ 'status' => 'Unable to get the status' ] );exit;
			}
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

		$restaurant = $this->_restaurant();
		$paymentType = $restaurant->payment_type();
		$stripeAccount = $paymentType->getAndMakeStripe([
			'bank_account' => $this->request()['token'],
			'account_type' => $this->request()['account_type'],
			'tax_id' => $this->request()['tax_id']
		]);

		if ($stripeAccount){
			$this->_exports();
		} else {
			$this->_error( 'Error creating stripe account' );
		}

	}

	private function _updateEntityName(){
		$restaurant = $this->_restaurant();
		$payment_method = $restaurant->payment_type();
		$payment_method->legal_name_payment = $this->request()[ 'legal_name_payment' ];;
		$payment_method->save();

		$payment_method->updateEntityName();

		$this->_exports();
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