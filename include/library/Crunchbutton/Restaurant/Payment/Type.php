<?php

class Crunchbutton_Restaurant_Payment_Type extends Cana_Table_Trackchange {

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

	public function updateEntityName(){
		$paymentType = $this;
		if ( $paymentType->stripe_id && $paymentType->legal_name_payment ) {
			$stripeAccount = \Stripe\Account::retrieve($this->stripe_id);
			$stripeAccount->business_name = $paymentType->legal_name_payment ;
			$stripeAccount->legal_entity->business_name = $paymentType->legal_name_payment ;
			$stripeAccount->save();
			return $stripeAccount;
		}
		return false;
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

	// uses company reps info
	public function setStripeRep($so = null) {
		// $r = 'everythingisawesome';
		// $a = c::config()->site->config('restaurant-awesome')->value;
		// $b = json_decode(substr(hex2bin(c::crypt()->decrypt($a)), 0, -strlen($r)));
		// $b->d = str_split($b->d, 2);

		if (is_null($so)) {
			$stripe = $this->getAndMakeStripe();
		} else {
			$stripe = $so;
		}

		if ($stripe->legal_entity->verification->status == 'verified') {
			if (!$this->verified) {
				$this->verified = true;
				$this->save();
			}
			return $stripe;
		}

		$info = c::config()->site->config('david-is-awesome')->value;
		$info = c::crypt()->decrypt( $info );
		$info = unserialize( $info );

		$stripe->legal_entity->ssn_last_4 = $info['ssn'];
		$stripe->legal_entity->dob = [
			'day' => $info['dob']['d'],
			'month' => $info['dob']['m'],
			'year' => $info['dob']['y']
		];
		$stripe->legal_entity->first_name = $info['first_name'];
		$stripe->legal_entity->last_name = $info['last_name'];

		if (is_null($so)) {
			$res = $stripe->save();
		}

		return $stripe;
	}

	// if there is not already a stripe account, it will make one
	public function getAndMakeStripe($params = []) {
		// params accepts taxid and bank account to create

		$paymentType = $this;
		$restaurant = $this->restaurant();

		$entity = $params['entity'] == 'individual' ? 'individual' : 'company'; // stripe docs are right. the dashboard is wrong. updated 6/11/15
		$name = explode(' ',$paymentType->contact_name);

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

		/*
		if ($entity == 'individual') {
			$info['legal_entity']['ssn_last_4'] = $ssn;
			$info['legal_entity']['first_name'] = array_shift($name);
			$info['legal_entity']['last_name'] = implode(' ',$name);
		} else {
			$info['legal_entity']['business_name'] = $paymentType->legal_name_payment ? $paymentType->legal_name_payment : $restaurant->name;
		}

		if ($params['bank_account']) {
			$info['bank_account'] = $params['bank_account'];
		}
		*/

		try {
			$stripeAccount = \Stripe\Account::create($info);
		} catch (Exception $e) {
			return $e->getMessage();
		}

		if ($stripeAccount->id) {
			// set legal info
			$stripeAccount = $this->setStripeRep($stripeAccount);

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

	public static function byRestaurant( $id_restaurant ){
		if( $id_restaurant ){
			$payment = Crunchbutton_Restaurant_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant = ? ORDER BY id_restaurant_payment_type DESC LIMIT 1', [$id_restaurant]);
			if( $payment->id_restaurant_payment_type ){
				return Crunchbutton_Restaurant_Payment_Type::o( $payment->id_restaurant_payment_type );
			} else{
				$payment = new Crunchbutton_Restaurant_Payment_Type();
				$payment->id_restaurant = $id_restaurant;
				$payment->formal_relationship = 1;
				$payment->charge_credit_fee = 1;
				$payment->waive_fee_first_month = 0;
				$payment->pay_apology_credits = 1;
				$payment->max_apology_credit = 5;
				$payment->max_pay_promotion = 3;
				$payment->save();
				return $payment;
			}
		}
		return false;
	}
}
