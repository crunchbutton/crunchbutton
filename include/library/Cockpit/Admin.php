<?php

class Cockpit_Admin extends Crunchbutton_Admin {
	
	public function stripeVerificationStatus() {
		if (!isset($this->_stripeVerificationStatus)) {
			$stripeAccount = $this->stripeAccount();
			
			$data = [
				'status' => $stripeAccount->legal_entity->verification->status,
				'fields' => $stripeAccount->verification->fields_needed,
				'due_by' => $stripeAccount->verification->due_by,
				'contacted' => trim($stripeAccount->verification->contacted) ? true : false
			];

			$this->_stripeVerificationStatus = $data;
		}
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
		if ($status->status == 'unverified') {
			return false;
		} else {
			return true;
		}
	}
	
	public function formatAddress($address = '') {
		if (!$address) {
			$address = $this->payment_type()->address;
		}
		
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address);

		$res = @json_decode(@file_get_contents($url));
		if (!$res) {
			return $address;
		}
		$res = $res->results[0];
		
		/*
		$parts = [];
		foreach ($res->address_components as $item) {
			$parts[$item->types[0]] = $item->short_name;
		}
		*/
		
		$f = explode(',',$res->formatted_address);
		array_pop($f);
		$formatted = array_shift($f)."\n".trim(implode(',',$f));

		return $formatted;
	}
	
	public function addressParts($address = '') {
		if (!$address) {
			$pt = $this->payment_type();
			$address = $pt->address;
		}
		$parts = explode("\n", trim($address));
		$parts[1] = explode(',', trim($parts[1]));
		$parts[1][1] = explode(' ', trim($parts[1][1]));

		return [
			'address' =>$parts[0],
			'city' => $parts[1][0],
			'state' => $parts[1][1][0],
			'zip' => $parts[1][1][1]
		];
	}
	
	public function autoStripeVerify($force = false) {
		$stripeAccount = $this->stripeAccount();
		$status = $this->stripeVerificationStatus();
		$paymentType = $this->payment_type();
		$name = explode(' ', $paymentType->legal_name_payment);

		$formattedAddress = $this->formatAddress($paymentType->address);
		if ($formattedAddress != $paymentType->address) {
			$paymentType->address = $formattedAddress;
			$paymentType->save();
		}

		$address = $this->addressParts($formattedAddress);

		// make sure we can verify it
		if (trim($status['status']) == 'unverified' && !$status['contacted'] && ($force || $status['due_by'])) {
			$saving = 0;

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
							$stripeAccount->legal_entity->last_name = array_pop($name);
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
			if ($paymentType->legal_name_payment && $paymentType->address && $paymentType->stripe_account_id && $paymentType->stripe_id && $this->ssn() && $this->dob) {
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
			$this->_pex = Cockpit_Admin_Pexcard::getByAdmin($this->id_admin)->get(0);
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

		if ($params['working'] !== false) {

			$next = Community_Shift::nextShiftsByAdmin($this->id_admin);

			if ($next) {

				foreach ($next as $shift) {

					$shift = $shift->exports();

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

		return $out;
	}
	
	public function tempConvertBalancedToStripe() {
		if (!$this->id_user || c::env() != 'live' || c::admin()->id_admin != 1) {
			//return false;
		}

		$p = Crunchbutton_Admin_Payment_Type::q('
			select p.* from admin_payment_type p
			where id_admin=?
			and balanced_bank is not null
			and stripe_account_id is null
			order by p.id_admin_payment_type desc
		',[$this->id_admin]);
		
		// nothing left to import
		if ($p->count() < 1 && $this->stripe_id) {
			return true;
		}

		$idStripe = $paymentType->stripe_id ? $paymentType->stripe_id : $this->stripe_id;
		
		echo "\nWorking on admin #".$this->id_admin."\n";

		if (!$idStripe) {

			// create a stripe managed account
			try {
				$name = explode(' ',$paymentType->legal_name_payment);
				$address = explode("\n", $paymentType->address);
				$address[1] = explode(',', $address[1]);
				$address[1][1] = explode(' ', $address[1][1]);

				$ip = c::db()->get('select session.* from session where id_admin=? and ip is not null order by session.date_activity desc limit 1', [$paymentType->id_admin])->get(0)->ip;

				$dob = explode('-',$this->dob);
				$ssn = substr($this->ssn(), -4);

				$stripeAccount = \Stripe\Account::create([
					'managed' => true,
					'country' => 'US',
					'email' => $paymentType->summary_email ? $paymentType->summary_email : $this->email,
					'tos_acceptance' => [
						'date' => time(),
						'ip' => $ip ? $ip : '76.171.15.26'
					],
					'legal_entity' => [
						'type' => 'individual',
						'first_name' => array_shift($name),
						'last_name' => implode(' ',$name),
						'dob' => [ // @note: this viloates stripes docs but this is the correct way
							'day' => $dob[2], 
							'month' => $dob[1], 
							'year' => $dob[0]
						], 
						'ssn_last_4' => $ssn,
						'address' => [
							'line1' => $address[0], 
							'city' => $address[1][0],
							'state' => $address[1][1][0],
							'postal_code' => $address[1][1][1],
							'country' => 'US'
						]
					]
				]);
				
				$created = true;

			} catch (Exception $e) {
				echo 'ERROR: '.$e->getMessage()."\n";
				return false;
			}
		} else {
			try {
				$stripeAccount = \Stripe\Account::retrieve($idStripe);
			} catch (Exception $e) {
				echo 'ERROR: '.$e->getMessage()."\n";
				return false;
			}
		}

		$idStripe = $stripeAccount->id;

		if ($idStripe) {
			echo 'Stripe account '.$idStripe."\n";
			
			if ($created) {
				$this->stripe_id = $idStripe;
				$this->save();
			}

		} else {
			echo "ERROR: no stripe account\n";
		}

		// get their bank info
		foreach ($p as $paymentType) {
			echo "Balanced bank account ".$paymentType->balanced_bank."\n";

			$handle = fopen('/Users/arzynik/Downloads/2015-05-19-1431998893-***REMOVED***.csv', 'r');
			if (substr($paymentType->balanced_id,0,2) != 'BA') {
				$bkey = 4;
				$skey = 5;
			}

			while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
				if ($data[$bkey] == $paymentType->balanced_bank) {
					$stripeBankToken = $data[$skey];
					echo "Found token in csv ".$stripeBankToken."\n";
					break;
				}
			}

			
			if (!$stripeBankToken) {
				try {
					$bank = Crunchbutton_Balanced_BankAccount::byId($paymentType->balanced_bank);
				} catch (Exception $e) {
					print_r($e);
					echo "ERROR: Failed to get balanced id\n";
					continue;
				}

				$stripeBankToken = $bank->meta->{'stripe_customer.funding_instrument.id'};
			}

			if (!$stripeBankToken) {
				echo "ERROR: No bank meta.\n";
				continue;
			}

			if (strpos($stripeBankToken, 'btok_') === 0) {
				echo "Stripe bank token is ".$stripeBankToken."\n";
			} else {
				echo "WARNING: Not sure what to do with this: ".$stripeBankToken."\n";
				continue;
			}


			if ($idStripe && $stripeBankToken) {
				// do something with the token

				$stripeAccount->bank_account = $stripeBankToken;
				try {
					$stripeAccount->save();

					foreach ($stripeAccount->bank_accounts->all()->data as $stripeBankAccount) {			
						break;
					}

					echo "Stripe bank account is ".$stripeBankAccount->id."\n";

					$paymentType->stripe_id = $idStripe;
					$paymentType->stripe_account_id = $stripeBankAccount->id;
					$paymentType->save();
				} catch (Exception $e) {
					echo 'ERROR: '.$e->getMessage()."\n";
					return false;
				}


			}

		}

		
	}

}