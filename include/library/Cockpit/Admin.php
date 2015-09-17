<?php

class Cockpit_Admin extends Crunchbutton_Admin {

	public function stripeVerificationStatus() {
		//if (!isset($this->_stripeVerificationStatus)) {
			$stripeAccount = $this->stripeAccount();
			$paymentType = $this->payment_type();
			$ssn = $paymentType->social_security_number($this->id_admin);
			$data = [
				'status' => $stripeAccount->legal_entity->verification->status,
				'fields' => $stripeAccount->verification->fields_needed,
				'due_by' => $stripeAccount->verification->due_by,
				'contacted' => trim($stripeAccount->verification->contacted) ? true : false,
				'ssn' => $ssn ? true : false
			];

			$this->_stripeVerificationStatus = $data;
		//}
		return $this->_stripeVerificationStatus;
	}

	public function stripeAccount() {
		if (!isset($this->_stripeAccount)) {
			$paymentType = $this->payment_type();
			$this->_stripeAccount = \Stripe\Account::retrieve($paymentType->stripe_id);
		}
		return $this->_stripeAccount;
	}

	public function isStripeVerified() {
		$status = $this->stripeVerificationStatus();
		if ($status[ 'status' ] == 'unverified') {
			return false;
		} else {
			return true;
		}
	}

	public function unPaidPayments(){
		$out = [];
		$payments = Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_driver = ? AND ( status = ? OR status = ? ) ORDER BY id_payment_schedule DESC', [ $this->id_admin, Cockpit_Payment_Schedule::STATUS_SCHEDULED, Cockpit_Payment_Schedule::STATUS_ERROR ] );
		foreach( $payments as $payment ){
			$out[] = [ 'id_payment_schedule' => $payment->id_payment_schedule, 'log' => strip_tags( $payment->log ), 'status' => $payment->status, 'amount' => $payment->amount, 'date' => $payment->date()->format( 'M jS Y g:i:s A' )  ];
		}
		return $out;
	}

	public function autoStripeVerify( $force = false ) {
		$stripeAccount = $this->stripeAccount();
		$status = $this->stripeVerificationStatus();
		$paymentType = $this->payment_type();
		$name = explode(' ', $paymentType->legal_name_payment);
		$ssn = $paymentType->social_security_number($this->id_admin);
		$ssn4 = substr($paymentType->social_security_number($this->id_admin), -4);

		$formattedAddress = Util::formatAddress($paymentType->address);
		if ($formattedAddress != $paymentType->address) {
			$paymentType->address = $formattedAddress;
			$paymentType->save();
		}

		$address = Util::addressParts($formattedAddress);

		// make sure we can verify it
		// ref #6702 sometimes it says they are verified but still wants info
		// also we cant force update when its verified or it will error out
		//if (trim($status['status']) == 'unverified' && !$status['contacted'] && ($force || $status['due_by'])) {
		if ($status['contacted']) {

			if ($status['status'] != 'verified') {
				if ($paymentType->verified) {
					$paymentType->verified = false;
					$paymentType->save();
				}
			}
			return false;
			
		} elseif ($status['fields'] || $force || $status['due_by']) {
			$saving = 0;

			if (!$force) {
				foreach ($status['fields'] as $field) {
					switch ($field) {
						case 'legal_entity.first_name':
							if (!$stripeAccount->legal_entity->first_name) {
								$stripeAccount->legal_entity->first_name = array_shift($name);
								$saving++;
							}
							break;
						case 'legal_entity.last_name':
							if (!$stripeAccount->legal_entity->last_name) {
								$stripeAccount->legal_entity->last_name = implode(' ',$name);
								$saving++;
							}
							break;
						case 'legal_entity.address.line1':
							if (!$stripeAccount->legal_entity->address->line1) {
								$stripeAccount->legal_entity->address->line1 = $address['address'];
								$saving++;
							}
							break;
						case 'legal_entity.address.city':
							if (!$stripeAccount->legal_entity->address->city) {
								$stripeAccount->legal_entity->address->city = $address['city'];
								$saving++;
							}
							break;
						case 'legal_entity.address.state':
							if (!$stripeAccount->legal_entity->address->state) {
								$stripeAccount->legal_entity->address->state = $address['state'];
								$saving++;
							}
							break;
						case 'legal_entity.address.postal_code':
							if (!$stripeAccount->legal_entity->address->postal_code) {
								$stripeAccount->legal_entity->address->postal_code = $address['zip'];
								$saving++;
							}
							break;
						case 'legal_entity.ssn_last_4':
							if (!$stripeAccount->legal_entity->ssn_last_4 && $ssn4) {
								$stripeAccount->legal_entity->ssn_last_4 = $ssn4;
								$saving++;
							}
							break;
						case 'legal_entity.personal_id_number':
							if (!$stripeAccount->legal_entity->personal_id_number && $ssn) {
								$stripeAccount->legal_entity->personal_id_number = $ssn;
								$saving++;
							}
							break;
					}
				}
			} else {
				$stripeAccount->legal_entity->type = 'individual';
				if (trim($status['status']) != 'verified') {
					$stripeAccount->legal_entity->first_name = array_shift($name);
					$stripeAccount->legal_entity->last_name = implode(' ',$name);
				}
				$stripeAccount->legal_entity->address->line1 = $address['address'];
				$stripeAccount->legal_entity->address->city = $address['city'];
				$stripeAccount->legal_entity->address->state = $address['state'];
				$stripeAccount->legal_entity->address->postal_code = $address['zip'];
				$saving = 6;
				if ($ssn) {
					$stripeAccount->legal_entity->ssn_last_4 = $ssn4;
					$stripeAccount->legal_entity->personal_id_number = $ssn;
					$saving+=2;
				}
				
			}

			if ($saving) {
				$stripeAccount->save();
			}

			if ($saving == count($status['fields'])) {
				$status = true;
			}

			if ($paymentType->verified) {
				$paymentType->verified = false;
				$paymentType->save();
			}

			return $status ? true : false;

		} elseif($status['status'] == 'verified') {
			$paymentType->verified = true;
			$paymentType->save();
			return true;

		} else {

			if ($paymentType->verified) {
				$paymentType->verified = false;
				$paymentType->save();
			}
			return false;
		}
	}

	public function publicExports() {
		$out = parent::publicExports();
		$out['phone'] = $this->phone;
		foreach ($this->deliveries() as $order) {
			$out['deliveries'][] = [
				'id_order' => $order->id_order,
				'status' => $order->stati[count($order->stati)-1]['status'],
				'update' => $order->stati[count($order->stati)-1]['timestamp']
			];
		}
		return $out;
	}

	public function status() {
		// get the work status
		$status = [
			'payment' => false
		];
		$paymentType = $this->payment_type();
		if ($paymentType->id_admin_payment_type) {
			if ($paymentType->legal_name_payment && $paymentType->address && $paymentType->stripe_account_id && $paymentType->stripe_id && $this->hasSSN() && $this->dob) {
				$status['payment'] = true;
			}
		}
		return $status;
	}

	public function locations() {
		if (!isset($this->_locations)) {
			$this->_locations = Admin_Location::q('
				select * from admin_location
				where id_admin="'.$this->id_admin.'"
				group by round(UNIX_TIMESTAMP(date) / 300)
				order by date desc
				limit 25
			');
		}
		return $this->_locations;
	}

	public function location() {
		if (!isset($this->_location) && $this->id_admin) {
			$this->_location = Admin_Location::q('SELECT * FROM admin_location WHERE id_admin=? ORDER BY date DESC LIMIT 1', [$this->id_admin])->get(0);
		}
		return $this->_location;
	}

	public function locationWithMaxTime($maxDT) {
		$location = null;
		if (!isset($this->_location) && $this->id_admin) {
			$location = Admin_Location::q('SELECT * FROM admin_location WHERE id_admin=? and date > ? and date is not null and lat is not null and lon is not null ORDER BY date DESC LIMIT 1',
				[$this->id_admin, $maxDT->format('Y-m-d H:i:s')])->get(0);
		}
		return $location;
	}

	public function locationsWithMaxTime($maxDT) {
		$locations = null;
		if (!isset($this->_location) && $this->id_admin) {
			// TODO: POSTGRES - will need to modify unix_timestamp function to work for postgres.
			$locations = Admin_Location::q('SELECT *, unix_timestamp(date) as ts FROM admin_location WHERE id_admin=? and date > ? and date is not null and lat is not null and lon is not null group by date ORDER BY date desc',
				[$this->id_admin, $maxDT->format('Y-m-d H:i:s')]);
		}
		return $locations;
	}


	// return the restaurant the admin could order from #3350
	public function restaurantOrderPlacement(){
		$permission_prefix = 'restaurant-order-placement-';
		$permissions = c::admin()->getAllPermissionsName();
		foreach( $permissions as $permission ){
			if( strpos( $permission->permission, $permission_prefix ) !== false ){
				$id_restaurant = str_replace( $permission_prefix, '', $permission->permission );
				$restaurant = Restaurant::o( $id_restaurant );
				if( $restaurant->id_restaurant ){
					return $restaurant;
				}
			}
		}
		return false;
	}

	public function deliveries() {
		if (!isset($this->_deliveries)) {
			$o = Order::q("
				select o.*, oa.type as status, oa.timestamp as status_time from `order` o
				left join order_action oa using (id_order)
				where
					id_admin=?
					and (oa.type='delivery-pickedup' or oa.type='delivery-accepted' or oa.type='delivery-delivered' or oa.type='delivery-rejected' or oa.type='delivery-transfered')
					and o.date >= (curdate() - interval 50 day)
				order by oa.timestamp asc
			", [$this->id_admin]);
			$orders = [];
			foreach ($o as $order) {
				if (!$orders[$order->id_order]) {
					$orders[$order->id_order] = $order;
					$orders[$order->id_order]->stati = [];
				}
				$orders[$order->id_order]->stati[] = [
					'status' => $order->status,
					'timestamp' => $order->status_time
				];
			}
			foreach ($orders as $k => $order) {
				$last = count($order->stati) - 1;
				$inactive = ['delivery-rejected', 'delivery-transfered', 'delivery-delivered'];

				if (in_array($order->stati[$last]['status'], $inactive)) {
					unset($orders[$k]);
					continue;
				}
			}
			$this->_deliveries = $orders;
		}
		return $this->_deliveries;
	}

	public function pex() {
		if (!isset($this->_pex)) {
			if ($this->id_admin) {
				$pex = Cockpit_Admin_Pexcard::getByAdmin($this->id_admin)->get(0);
				if( $pex ){
					$this->_pex = $pex->get( 0 );
				}
			} else {
				$this->_pex = new Cockpit_Admin_Pexcard;
			}
		}
		return $this->_pex;
	}

	public function totalReferralActivations($period = null) {
		$query = 'SELECT COUNT(*) AS total FROM referral WHERE id_admin_inviter = ? AND new_user = true ';
		if ($period) {
			$query .= ' AND date >= DATE_SUB( NOW(), INTERVAL ' . $period . ' DAY )';
		}
		$total = Crunchbutton_Referral::q( $query, [$this->id_admin])->get(0);
		return intval( $total->total );
	}

	public function exports( $params = [] ) {
		$out = parent::exports( $params );
		$out['shifts'] = [];
		$out['working'] = false;
		$out['working_today'] = false;
		$out['referral_admin_credit'] = floatval( $this->referral_admin_credit );
		$out['referral_customer_credit'] = floatval( $this->referral_customer_credit );
		$out['invite_code'] = $this->invite_code;
		$out['dob'] = $this->dob;

		$out['referral_total'] = $this->totalReferralActivations();
		$out['referral_total_last_week'] = $this->totalReferralActivations( 7 );

		$out['pexcard'] = [
			'card_serial' => $this->pex()->card_serial,
			'last_four' => $this->pex()->last_four,
			'active' => $this->pex()->card_serial && $this->pex()->card_serial ? true : false
		];

		$out['verified'] = $this->payment_type()->verified ? true : false;

		$author = $this->author();
		if( $author->id_admin ){
			$out['author'] = $author->name;
		} else {
			$out['author'] = null;
		}

		if ($this->location()) {
			$out['location'] = $this->location()->exports();
		}

		if( $params[ 'last-checkins' ] ){

			$next = Community_Shift::lastShiftsByAdmin($this->id_admin, 5);

			if ($next) {

				foreach ($next as $s) {

					$shift = $s->exports();

					$shift[ 'confirmed' ] = intval( $s->confirmed );

					$date = new DateTime($shift['date_start'], new DateTimeZone($this->timezone));
					$start = $date->getTimestamp();

					$today = new DateTime( 'now' , new DateTimeZone( $this->timezone ) );

					if( $date->format( 'Ymd' ) == $today->format( 'Ymd' ) ){
						$out['working_today'] = true;
					}

					if ($start <= time() ) {
						$now = new DateTime( 'now' , new DateTimeZone($this->timezone));
						$date = new DateTime($shift['date_end'], new DateTimeZone($this->timezone));
						$diff = $now->diff( $date );
						$shift['current'] = true;
						$out['working'] = true;
						$out['shift_ends'] = $diff->h;
						$out['shift_ends_formatted'] = $diff->h;
						if( $diff->i ){
							$out['shift_ends'] .= '' . str_replace(  '0.', '.', strval( number_format( $diff->i / 60, 2 ) ) );
							if( $diff->h ){
								$out['shift_ends_formatted'] .= ' hour' . ( ( $diff->h > 1 ) ? 's' : '' );
								$out['shift_ends_formatted'] .= ' and ';
							}
							 $out['shift_ends_formatted'] .= str_pad( $diff->i, '0', 2 ) . ' minute' . ( $diff->i > 1 ? 's' : '' ) ;
						}
					} else {
						$shift['current'] = false;
					}

					$out['last_checkins'][] = $shift;
				}
			}
		}

		if ($params['working'] !== false) {

			$next = Community_Shift::nextShiftsByAdmin($this->id_admin);

			if ($next) {

				foreach ($next as $s) {

					$shift = $s->exports();

					$shift[ 'confirmed' ] = intval( $s->confirmed );

					$date = new DateTime($shift['date_start'], new DateTimeZone($this->timezone));
					$start = $date->getTimestamp();

					$today = new DateTime( 'now' , new DateTimeZone( $this->timezone ) );

					if( $date->format( 'Ymd' ) == $today->format( 'Ymd' ) ){
						$out['working_today'] = true;
					}

					if ($start <= time() ) {
						$now = new DateTime( 'now' , new DateTimeZone($this->timezone));
						$date = new DateTime($shift['date_end'], new DateTimeZone($this->timezone));
						$diff = $now->diff( $date );
						$shift['current'] = true;
						$out['working'] = true;
						$out['shift_ends'] = $diff->h;
						$out['shift_ends_formatted'] = $diff->h;
						if( $diff->i ){
							$out['shift_ends'] .= '' . str_replace(  '0.', '.', strval( number_format( $diff->i / 60, 2 ) ) );
							if( $diff->h ){
								$out['shift_ends_formatted'] .= ' hour' . ( ( $diff->h > 1 ) ? 's' : '' );
								$out['shift_ends_formatted'] .= ' and ';
							}
							 $out['shift_ends_formatted'] .= str_pad( $diff->i, '0', 2 ) . ' minute' . ( $diff->i > 1 ? 's' : '' ) ;
						}
					} else {
						$shift['current'] = false;
					}

					$out['shifts'][] = $shift;
				}
			}
		}

		if( $this->date_terminated ){
			$date = $this->dateTerminated();
			$out['date_terminated_formatted'] = $date->format( 'm/d/Y' );
		}

		$out['status'] = $this->status();

		foreach ($this->deliveries() as $order) {
			$out['deliveries'][] = [
				'id_order' => $order->id_order,
				'status' => $order->stati[count($order->stati)-1]['status'],
				'update' => $order->stati[count($order->stati)-1]['timestamp']
			];
		}

		return $out;
	}

    public function score() {
        $qString = "SELECT * FROM `admin_score` WHERE id_admin= ? ";
        $s = Cockpit_Admin_Score::q($qString, [$this->id_admin,]);
        if (is_null($s) || $s->count()==0){
            $sc = new Cockpit_Admin_Score([
                'id_community' => $this->id_admin,
                'score' => Cockpit_Admin_Score::DEFAULT_SCORE
            ]);
            $sc->save();
            return $sc->score;
        } else{
            return $s->get(0)->score;
        }
    }

	public function __construct($id = null) {
		$this->_changeSetName = 'Cockpit_Admin';
		$this->changeOptions([
			'created' => true
		]);

		parent::__construct($id);
	}
}
