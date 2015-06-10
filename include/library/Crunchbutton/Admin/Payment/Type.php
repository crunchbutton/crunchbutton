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

	public function createStripe( $params ){

		$admin = $this->admin();

		$env = c::getEnv();

		\Stripe\Stripe::setApiKey( c::config()->stripe->{ $env }->secret );

		$account_type = 'individual';

		$name = explode( ' ', $this->legal_name_payment );
		$first_name = array_shift( $name );
		$last_name = implode( ' ',$name );

		$params[ 'email' ] = ( $this->summary_email ? $this->summary_email : $admin->email );

		$params[ 'ssn' ] = substr( $admin->ssn(), -4 );
		$dob = explode( '-', $admin->dob );
		$params[ 'dob' ] = [ 'day' => $dob[ 2 ], 'month' => $dob[ 1 ], 'year' => $dob[ 0 ] ];

		$address = explode( "\n", $this->address );
		$address[ 1 ] = explode( ',', $address[ 1 ] );
		$address[ 1 ][ 1 ] = explode( ' ', $address[ 1 ][ 1 ] );

		$params[ 'address' ] = $address[ 0 ];
		$params[ 'city' ] = $address[ 1 ][ 0 ];
		$params[ 'state' ] = $address[ 1 ][ 1 ][ 0 ];
		$params[ 'zip' ] = $address[ 1 ][ 1 ][ 1 ];
		$params[ 'country' ] = 'US';

		try {

			$info = [
				'managed' => true,
				'country' => $country,
				'email' => $email,
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
						'line1' => $params[ 'address' ],
						'city' => $params[ 'city' ],
						'state' => $params[ 'state' ],
						'country' => $params[ 'country' ],
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

	public function getStripe(){

		if( $this->stripe_id ){

			$env = c::getEnv();

			\Stripe\Stripe::setApiKey(c::config()->stripe->{$env}->secret);

			$stripeAccount = \Stripe\Account::retrieve( $this->stripe_id );

			return $stripeAccount;

		}
		return null;
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