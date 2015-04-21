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

	public function restaurant(){
		return Restaurant::o( $this->id_restaurant );
	}

	public function testAccount(){
		$restaurant = $this->restaurant();
		$settlement = new Crunchbutton_Settlement();
		$id_payment_schedule = $settlement->scheduleRestaurantArbitraryPayment( $restaurant->id_restaurant, 0.01, Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT, 'Test Deposit' );
		Cana::timeout( function() use( $settlement, $id_payment_schedule ) {
			$settlement->payRestaurant( $id_payment_schedule );
		} );
	}

	public function exports(){
		$out = $this->properties();
		$out[ 'max_pay_promotion' ] = intval( $out[ 'max_pay_promotion' ] );
		return $out;
	}

	public function getStripe(){

		if( $this->stripe_id ){

			$env = c::getEnv();

			\Stripe\Stripe::setApiKey(c::config()->stripe->{$env}->secret);

			$stripeAccount = \Stripe\Account::retrieve( $this->stripe_id );

			return $stripeAccount;

		}
		return null;
	}

	public function stripeTransfer( $amount ){

		if( $this->stripe_id ){

			$transfer = Crunchbutton_Stripe_Credit::credit( $this->stripe_id, $amount );

			if( $transfer->id ){
				return true;
			}

		}
		return false;
	}

	public function migrateFromBalanced( $params ){

		if( $this->balanced_bank ){
			try {
				$bank = Crunchbutton_Balanced_BankAccount::byId( $this->balanced_bank );
			} catch ( Exception $e ) {
				echo "ERROR: Failed to get balanced id\n";
				exit();
			}

			$stripeBankToken = $bank->meta->{ 'stripe_customer.funding_instrument.id' };

			$stripeAccount = $this->getStripe();
			if( $stripeAccount ){
				$stripeAccount->bank_account = $stripeBankToken;
				$stripeAccount->save();
				$this->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
				$this->save();
			} else {
				return $this->createStripe( [ 'bank_account' => $stripeBankToken, 'account_type' => $params[ 'account_type' ]	 ] );
			}

		}
		return false;
	}

	public function createStripe( $params ){

		$env = c::getEnv();

		\Stripe\Stripe::setApiKey(c::config()->stripe->{$env}->secret);

		$account_type = $params[ 'account_type' ];
		$name = explode( ' ', $this->legal_name_payment );
		$first_name = array_shift( $name );
		$last_name = implode( ' ',$name );

		$this->payment_method = Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT;
		$this->save();

		try {

			$info = [
				'managed' => true,
				'country' => $this->check_address_country,
				'email' => $this->summary_email,
				'bank_account' => $params[ 'bank_account' ],
				'tos_acceptance' => [
					'date' => time(),
					'ip' => $_SERVER['REMOTE_ADDR']
				],
				'legal_entity' => [
					'type' => $account_type,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'address' => [
						'line1' => $this->check_address,
						'city' => $this->check_address_city,
						'state' => $this->check_address_state,
						'country' => $this->check_address_country,
					]
				]
			];

			if( $params[ 'account_type' ] ){
				$info[ 'legal_entity' ][ 'type' ] = $params[ 'account_type' ];
			}


			if( $params[ 'dob' ] ){
				$info[ 'legal_entity' ][ 'dob' ] = [ // @note: this viloates stripes docs but this is the correct way
																							'day' => $params[ 'dob' ][ 'day' ],
																							'month' => $params[ 'dob' ][ 'month' ],
																							'year' => $params[ 'dob' ][ 'year' ]
																						];
			}

			if( $params[ 'ssn' ] ){
				$info[ 'legal_entity' ][ 'ssn_last_4' ] = $params[ 'ssn' ];
			}

			$stripeAccount = \Stripe\Account::create( $info );

			if( $stripeAccount->id && $stripeAccount->bank_accounts->data[0]->id ){
				$this->stripe_id = $stripeAccount->id;
				$this->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
				$this->save();
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return [ 'error' => $e->getMessage() ];
		}
		return false;
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
				$payment->formal_relationship = 1;
				$payment->save();
				return $payment;
			}
		}
		return false;
	}
}