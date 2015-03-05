<?php

class Crunchbutton_Payment extends Cana_Table {

	const PAY_TYPE_PAYMENT = 'payment';
	const PAY_TYPE_REIMBURSEMENT = 'reimbursement';

	const BALANCED_STATUS_PENDING = 'pending';
	const BALANCED_STATUS_SUCCEEDED = 'succeeded';
	const BALANCED_STATUS_FAILED = 'failed';

	public static function credit($params = null) {

		$payment = new Payment((object)$params);
		$payment->date = date('Y-m-d H:i:s');
		$payment_type = Crunchbutton_Restaurant_Payment_Type::byRestaurant( $payment->id_restaurant );

		if( $payment->type == 'balanced' ){
			try {
					$credit = Crunchbutton_Balanced_Credit::credit( $payment_type, $payment->amount, $payment->note);
				} catch ( Exception $e ) {
						throw new Exception( $e->getMessage() );
						exit;
				}
				if( $credit && $credit->id ){
					$payment->balanced_id = $credit->id;
				}

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
		$payment->id_admin = c::user()->id_admin;
		$payment->save();

		if( $payment->balanced_id || $payment->stripe_id ){
			return $payment->id_payment;
		} else {
			return false;
		}
	}

	public function checkBalancedStatus(){

		Log::debug( [ 'method' => 'checkBalancedStatus_start', 'id_payment' => $this->id_payment, 'type' => 'settlement' ] );
		if( $this->amount > 0 ){
			if( $this->balanced_id && $this->env ){
				$env = ( $this->env == 'live' ) ? 'live' : 'dev';
				$api_key = c::config()->balanced->{$env}->secret;
				Balanced\Settings::$api_key = $api_key;
				$url = '/credits/' . $this->balanced_id;
				$credit = Balanced\Credit::get( $url );
				switch ( $credit->status ) {
					case Crunchbutton_Payment::BALANCED_STATUS_FAILED:
						$this->balanced_status = Crunchbutton_Payment::BALANCED_STATUS_FAILED;
						$this->balanced_failure_reason = $credit->failure_reason;
						$this->schedule_error();
						break;
					case Crunchbutton_Payment::BALANCED_STATUS_SUCCEEDED:
						$this->balanced_status = Crunchbutton_Payment::BALANCED_STATUS_SUCCEEDED;
						break;
					case Crunchbutton_Payment::BALANCED_STATUS_PENDING;
					default:
						$this->balanced_status = Crunchbutton_Payment::BALANCED_STATUS_PENDING;
						break;
				}
				$this->balanced_date_checked = date('Y-m-d H:i:s');
				$this->save();
			}
		} else {
			$this->balanced_date_checked = date('Y-m-d H:i:s');
			$this->balanced_status = Crunchbutton_Payment::BALANCED_STATUS_SUCCEEDED;
			$this->save();
		}
		Log::debug( [ 'method' => 'checkBalancedStatus_end', 'id_payment' => $this->id_payment, 'type' => 'settlement' ] );
		return $this->balanced_status;
	}

	public function schedule(){
		$schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $this->id_payment . '" ORDER BY id_payment_schedule DESC' );
		if( $schedule->id_payment_schedule ){
			return $schedule;
		}
		return false;
	}

	public function schedule_error(){
		$schedule = $this->schedule();
		if( $schedule ){
			$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
			$schedule->status_date = date('Y-m-d H:i:s');
			$schedule->log = $this->balanced_failure_reason;
			$schedule->save();

			$settlement = new Settlement;
			$settlement->driverPaymentError( $schedule->id_payment_schedule );
		}
	}

	public static function credit_driver($params = null) {

		if( $params[ 'id_payment' ] ){
			$payment = Payment::o( $params[ 'id_payment' ] );
			// just redo payments with error
			if( !$payment->balanced_status == Crunchbutton_Payment::BALANCED_STATUS_FAILED ){
				return;
			}
			$payment->balanced_status = Crunchbutton_Payment::BALANCED_STATUS_PENDING;
			$payment->balanced_date_checked = null;
			$payment->balanced_failure_reason = null;
			$payment->balanced_id = null;
		} else {
			$payment = new Payment((object)$params);
		}

		$payment->date = date('Y-m-d H:i:s');
		$payment_type = Crunchbutton_Admin_Payment_Type::byAdmin( $payment->id_driver );

		if( $payment->type == 'balanced' || $params[ 'type' ] == 'balanced' ){
			try {
				$credit = Crunchbutton_Balanced_Credit::credit( $payment_type, $payment->amount, $payment->note);
			} catch ( Exception $e ) {
					throw new Exception( $e->getMessage() );
					exit;
			}
			if( $credit && $credit->id ){
				$payment->balanced_id = $credit->id;
			}
		}

		$payment->env = c::getEnv(false);
		$payment->id_admin = c::user()->id_admin;
		$payment->save();

		if( $payment->balanced_id || $payment->stripe_id ){
			return $payment->id_payment;
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

	public function listPayments( $search = [] ){
		$query = '';
		$where = ' WHERE 1=1 ';
		$limit = ( $search[ 'limit' ] ) ? ' LIMIT ' . $search[ 'limit' ] : '';
		if( $search[ 'type' ] ){
			if( $search[ 'type' ] == 'restaurant' ){

				if( $search[ 'id_restaurant' ] ){
					$where .= ' AND p.id_restaurant = ' . $search[ 'id_restaurant' ];
				}

				$query = 'SELECT p.*, r.name AS restaurant, ps.id_payment_schedule FROM payment p
								LEFT OUTER JOIN payment_schedule ps ON ps.id_payment = p.id_payment
								INNER JOIN restaurant r ON r.id_restaurant = p.id_restaurant
								' . $where . '
								ORDER BY p.id_payment DESC ' . $limit;

			}
			if( $search[ 'type' ] == 'driver' ){

				if( $search[ 'id_driver' ] ){
					$where .= ' AND p.id_driver = ' . $search[ 'id_driver' ];
				}

				if( $search[ 'pay_type' ] ){
					$where .= ' AND p.pay_type = "' . $search[ 'pay_type' ] . '"';
				}

				if( $search[ 'balanced_status' ] ){
					$where .= " AND p.balanced_status = '" . $search[ 'balanced_status' ] . "'";
				}

				$query = 'SELECT p.*, a.name AS driver, ps.id_payment_schedule FROM payment p
								LEFT OUTER JOIN payment_schedule ps ON ps.id_payment = p.id_payment
								INNER JOIN admin a ON a.id_admin = p.id_driver
								' . $where . '
								ORDER BY p.id_payment DESC ' . $limit;
			}
		}

		if( $query != '' ){
			return Crunchbutton_Payment::q( $query );
		}
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function balanced_date_checked(){
		if (!isset($this->_balanced_date_checked)) {
			if( $this->balanced_date_checked ){
				$this->_balanced_date_checked = new DateTime( $this->balanced_date_checked, new DateTimeZone( c::config()->timezone ) );
			} else {
				return false;
			}
		}
		return $this->_balanced_date_checked;
	}

	public function summary_sent_date() {
		if (!isset($this->_summary_sent_date)) {
			$this->_summary_sent_date = new DateTime( $this->summary_sent_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_summary_sent_date;
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