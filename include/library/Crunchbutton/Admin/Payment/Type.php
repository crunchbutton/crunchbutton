<?php

class Crunchbutton_Admin_Payment_Type extends Crunchbutton_Admin_Payment_Type_Trackchange {

	const PAYMENT_METHOD_DEPOSIT = 'deposit';
	const PAYMENT_TYPE_HOURS = 'hours';
	const PAYMENT_TYPE_ORDERS = 'orders';
	const PAYMENT_TYPE_HOURS_WITHOUT_TIPS = 'hours_without_tips';
	const PAYMENT_TYPE_MAKING_WHOLE = 'making_whole';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_payment_type')
			->idVar('id_admin_payment_type')
			->load($id);
	}

	public function admin() {
		return Admin::o($this->id_admin);
	}

	public function social_security_number( $id_admin ){
		return Crunchbutton_Admin_Info::getSSN( $id_admin );
	}

	public function save_social_security_number( $id_admin, $ssn ){
		return Crunchbutton_Admin_Info::storeSSN( $id_admin, $ssn );
	}

	// alias
	public function ssn( $id_admin ){
		return Crunchbutton_Admin_Payment_Type::social_security_number( $id_admin );
	}

	public function save_ssn( $id_admin, $ssn ){
		return Crunchbutton_Admin_Payment_Type::save_social_security_number( $id_admin, $ssn );
	}

	public function getAndMakeStripe( $params ){

		$admin = $this->admin();
		$paymentType = $this;

		$params[ 'email' ] = $paymentType->summary_email ? $paymentType->summary_email : $admin->email;

		$name = explode(' ', $paymentType->legal_name_payment);
		$first_name = array_shift($name);
		$last_name = implode( ' ', $name);

		$dob = explode( '-', $admin->dob );
		$params[ 'dob' ] = [ 'day' => $dob[ 2 ], 'month' => $dob[ 1 ], 'year' => $dob[ 0 ] ];


		if ($paymentType->stripe_id) {
			$stripeAccount = \Stripe\Account::retrieve($paymentType->stripe_id);

			if ($params['bank_account']) {
				$stripeAccount->bank_account = $params['bank_account'];
				$stripeAccount->save();

				if ($stripeAccount->bank_accounts->data[0]->id) {
					$paymentType->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
					$paymentType->save();
				}
			}

			if ($save) {
				$stripeAccount->save();
			}

			return $stripeAccount;
		}


		$formattedAddress = Util::formatAddress($paymentType->address);
		if ($formattedAddress != $paymentType->address) {
			$paymentType->address = $formattedAddress;
			$paymentType->save();
		}
		$address = Util::addressParts($formattedAddress);

		$params[ 'ssn' ] = substr( $admin->ssn(), -4 );

		try {

			$info = [
				'managed' => true,
				'country' => $country,
				'email' => $email,
				'bank_account' => $params[ 'bank_account' ],
				'tos_acceptance' => [
					'date' => time(),
					'ip' => c::getIp()
				],
				'legal_entity' => [
					'type' => 'individual',
					'first_name' => $first_name,
					'last_name' => $last_name,
					'address' => [
						'line1' => $address['address'],
						'city' => $address['city'],
						'state' => $address['state'],
						'postal_code' => $address['zip'],
						'country' => 'US',
					]
				]
			];

			if ($params['dob']) {
				$info[ 'legal_entity' ][ 'dob' ] = [
					'day' => $params[ 'dob' ][ 'day' ],
					'month' => $params[ 'dob' ][ 'month' ],
					'year' => $params[ 'dob' ][ 'year' ]
				];
			}

			if ($params['ssn']) {
				$info[ 'legal_entity' ][ 'ssn_last_4' ] = $params['ssn'];
			}

			$stripeAccount = \Stripe\Account::create($info);

			if ($stripeAccount->id && $stripeAccount->bank_accounts->data[0]->id) {

				$this->stripe_id = $stripeAccount->id;
				$this->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
				$this->save();
				return $stripeAccount;

			} else {
				return false;
			}
		} catch (Exception $e) {
			return [ 'error' => $e->getMessage() ];
		}
		return false;
	}


	// @todo: i dont understand why we NEED a payment type for admin but ok. - devin
	public static function byAdmin($id_admin) {
		if ($id_admin){
			$payment = Crunchbutton_Admin_Payment_Type::q( 'SELECT * FROM admin_payment_type WHERE id_admin = ? ORDER BY id_admin_payment_type DESC LIMIT 1', [$id_admin])->get(0);

			if (!$payment->id_admin_payment_type) {
				$payment = new Crunchbutton_Admin_Payment_Type([
					'id_admin' => $id_admin,
					'using_pex' => true,
					'using_pex_date' => date('Y-m-d H:i:s')
				]);
				$payment->save();
			}
		} else {
			$payment = new Crunchbutton_Admin_Payment_Type;
		}

		return $payment;
	}

	function claimBankAccount( $bank_account ){
		$env = c::getEnv();
		$headers = [ "Accept: application/vnd.api+json;revision=1.1" ];
		$url = 'https://api.balancedpayments.com/bank_accounts/' . $bank_account;
		$auth = c::config()->balanced->{$env}->secret;
		$request = new \Cana_Curl($url, null, 'get', null, $headers, null, [ 'user' => $auth, 'pass' => '' ] );
		Log::debug( [ 'request' => $request, 'type' => 'claim-account' ] );
	}

	public function amountPerOrder( $id_community = null, $force = false ){
		if( $force || ( $this->payment_type == self::PAYMENT_TYPE_ORDERS || $this->payment_type == self::PAYMENT_TYPE_MAKING_WHOLE ) ){
			if( $this->amount_per_order ){
				return floatval( $this->amount_per_order );
			} else {
				if( $id_community ){
					$community = Community::o( $id_community );
					if( $community->amount_per_order ){
						return floatval( $community->amount_per_order );
					}
				} else {
					$community = $this->admin()->communityDriverDelivery();
					if( $community->amount_per_order ){
						return floatval( $community->amount_per_order );
					}
				}
			}
		}
		return null;
	}

	public function stripeVerify(){
		$this->admin()->autoStripeVerify( false );
	}

	public function testAccount(){
		$admin = $this->admin();
		// When a driver enters their payment info, make a $0.01 deposit into their bank account #4029
		$settlement = new Crunchbutton_Settlement();
		$id_payment_schedule = $settlement->scheduleDriverArbitraryPayment( $admin->id_admin, 0.01, Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT, 'Test Deposit' );
		Cana::timeout( function() use( $settlement, $id_payment_schedule ) {
			$settlement->payDriver( $id_payment_schedule );
		} );
	}

	public function using_pex_date(){
		if( $this->using_pex ){
			if( $this->using_pex_date && $this->using_pex_date != '' ){
				$this->_using_pex_date = new DateTime($this->using_pex_date, new DateTimeZone(c::config()->timezone));
			} else {
				$this->_using_pex_date = new DateTime( '2015-04-01 00:00:01', new DateTimeZone(c::config()->timezone));
			}
		}
		return $this->_using_pex_date;
	}
}
