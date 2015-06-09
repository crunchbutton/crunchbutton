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

	public function stripeTransfer( $amount ){

		if( $this->stripe_id ){

			$transfer = Crunchbutton_Stripe_Credit::credit( $this->stripe_id, $amount );

			if( $transfer->id ){
				return true;
			}

		}
		return false;
	}

	
	// if there is not already a stripe account, it will make one
	public function getAndMakeStripe($params = []) {
		// params accepts taxid and bank account to create
		
		$paymentType = $this;
		$restaurant = $this->restaurant();
		
		$entity = $params['entity'] == 'individual' ? 'individual' : 'corporation'; // stripe docs are wrong. not company
		
		// some fields are duplicated because stripe docs are wrong for them
		
		if ($paymentType->stripe_id) {
			$stripeAccount = \Stripe\Account::retrieve($this->stripe_id);

			if ($params['bank_account']) {
				$stripeAccount->bank_account = $params['bank_account'];
				$stripeAccount->save();
				
				if ($stripeAccount->bank_accounts->data[0]->id) {
					$paymentType->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
				}

			}

			if ($params['tax_id']) {
				$stripeAccount->legal_entity->business_tax_id = $params['tax_id'];
				$stripeAccount->legal_entity->type = $entity;
			}

			$stripeAccount->legal_entity->address = [
				'line1' => $paymentType->check_address, 
				'city' => $paymentType->check_address_city,
				'state' => $paymentType->check_address_state,
				'postal_code' => $paymentType->check_address_zip,
				'country' => 'US'
			];
			
			$stripeAccount->save();
			
			return $stripeAccount;
		}

		
		
		$info = [
			'managed' => true,
			'country' => 'US',
			'email' => $paymentType->summary_email ? $paymentType->summary_email : $restaurant->email,
			'tos_acceptance' => [
				'date' => time(),
				'ip' => '76.171.15.26'
			],
			'legal_entity' => [
				'type' => $entity,
				'business_tax_id' => $params['tax_id'],
				'address' => [
					'line1' => $paymentType->check_address, 
					'city' => $paymentType->check_address_city,
					'state' => $paymentType->check_address_state,
					'postal_code' => $paymentType->check_address_zip,
					'country' => 'US'
				]
			]
		];
		
		if ($entity == 'individual') {
			$info['legal_entity']['first_name'] = array_shift($name);
			$info['legal_entity']['last_name'] = implode(' ',$name);
			$info['legal_entity']['ssn_last_4'] = $ssn;
		} else {
			$info['legal_entity']['business_name'] = $paymentType->legal_name_payment ? $paymentType->legal_name_payment : $restaurant->name;
		}
		
		if ($params['bank_account']) {
			$info['bank_account'] = $params['bank_account'];
		}
		
		try {
			$stripeAccount = \Stripe\Account::create($info);
		} catch (Exception $e) {
			return $e->getMessage();
		}

		if ($stripeAccount->id) {
			$paymentType->stripe_id = $stripeAccount->id;
			
			$paymentType->payment_method = Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT;
			
			if ($stripeAccount->bank_accounts->data[0]->id) {
				$paymentType->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
			}
			$paymentType->save();
			
			return $stripeAccount;
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