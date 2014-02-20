<?php
/**
 * Order placed
 *
 * (also known as listorders)
 *
 * @package  Crunchbutton.Order
 * @category model
 *
 * @property notes The comments the user set for the order
 */
class Crunchbutton_Order extends Cana_Table {

	const PAY_TYPE_CASH        = 'cash';
	const PAY_TYPE_CREDIT_CARD = 'card';
	const SHIPPING_DELIVERY    = 'delivery';
	const SHIPPING_TAKEOUT     = 'takeout';
	const TIP_PERCENT 				 = 'percent';
	const TIP_NUMBER				 	 = 'number';

	/**
	 * Process an order
	 *
	 *
	 * @param array $params
	 *
	 * @return string|Ambigous <>|boolean
	 *
	 * @todo Add more security here
	 * @todo It looks like if there are orders not set as delivery nor takeout, we need to log them.
	 */
	public function process($params)
	{
		$this->pay_type = ($params['pay_type'] == 'cash') ? 'cash' : 'card';
		$this->address  = $params['address'];
		$this->phone    = $params['phone'];
		$this->name     = $params['name'];
		$this->notes    = $params['notes'];

		// Log the order - process started
		Log::debug([
							'action' 				=> 'process started',
							'address' 			=> $params['address'],
							'phone' 				=> $params['phone'],
							'pay_type' 			=> $params['pay_type'],
							'tip' 					=> $params['tip'],
							'autotip'				=> $params['autotip'],
							'autotip_value'	=> $params['autotip_value'],
							'name' 					=> $params['name'],
							'user_id' 			=> c::user()->id_user,
							'delivery_type' => $params['delivery_type'],
							'restaurant' 		=> $params['restaurant'],
							'notes' 				=> $params['notes'],
							'cart' 					=> $params['cart'],
							'type' 					=> 'order-log'
						]);

		// set delivery as default,
		$this->delivery_type = self::SHIPPING_DELIVERY;
		if ($params['delivery_type'] == self::SHIPPING_TAKEOUT)  {
			$this->delivery_type = self::SHIPPING_TAKEOUT;
		} elseif ($params['delivery_type'] != self::SHIPPING_DELIVERY ) {
			// log when an order is not delivery nor takeout
			Crunchbutton_Log::error([
				'type'         => 'wrong delivery type',
				'order_params' => $params,
			]);
		}

		$subtotal = 0;

		$this->id_restaurant = $params['restaurant'];

		$delivery_service_markup = ( $this->restaurant()->delivery_service_markup ) ? $this->restaurant()->delivery_service_markup : 0;
		$this->delivery_service_markup = $delivery_service_markup;
		$delivery_service_markup_value = 0;
		foreach ($params['cart'] as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d['id'];
			$price = $dish->dish()->price;
			if( $delivery_service_markup > 0 && $price > 0 ){
				$price = $price + ( $price * $delivery_service_markup / 100 );
				$price = number_format( $price, 2 );
				$delivery_service_markup_value += number_format( $dish->dish()->price * $delivery_service_markup / 100, 2 );
			}
			$subtotal += $price;
			if ($d['options']) {
				foreach ($d['options'] as $o) {
					$option = new Order_Dish_Option;
					$option->id_option = $o;
					$price = $option->option()->price;
					if( $delivery_service_markup > 0 && $price > 0 ){
						$price = $price + ( $price * $delivery_service_markup / 100 );
						$price = number_format( $price, 2 );
						$delivery_service_markup_value += number_format( $option->option()->price * $delivery_service_markup / 100, 2 );
					}
					$subtotal += $price;
//                    $subtotal += $option->option()->optionPrice($d['options']);
					$dish->_options[] = $option;
				}
			}
			$this->_dishes[] = $dish;
		}
		
		// to make sure the value will be 2 decimals
		$this->delivery_service_markup_value = number_format( $delivery_service_markup_value, 2 );

		$this->_card = $params['card'];

		// price
		$this->price = Util::ceil($subtotal, 2);

		// delivery fee
		$this->delivery_fee = ($this->restaurant()->delivery_fee && $this->delivery_type == 'delivery') ? $this->restaurant()->delivery_fee : 0;

		// service fee for customer
		$this->service_fee = $this->restaurant()->fee_customer;
		$serviceFee = ($this->price + $this->delivery_fee) * Util::ceil(($this->service_fee/100),2);
		$serviceFee = Util::ceil($serviceFee, 2);
		$totalWithFees = $this->price + $this->delivery_fee + $serviceFee;
		$totalWithFees = Util::ceil($totalWithFees, 2);

		// Start to store the fee_restaurant because it could change and we need to know the
		// exacly value at the moment the user ordered his food
		$this->fee_restaurant = $this->restaurant()->fee_restaurant;

		// tip
		$this->tip = $params['tip'];

		if(!strcmp($this->tip, 'autotip')) {
			$this->tip = floatval($params['autotip_value']);
			$tip = $this->tip;
			$tip = Util::ceil($tip, 2);
			$this->tip_type = static::TIP_NUMBER;
		}
		else {
			$tip = ($this->price * ($this->tip/100));
			$tip = Util::ceil($tip, 2);
			$this->tip_type = static::TIP_PERCENT;
		}

		// tax
		$this->tax = $this->restaurant()->tax;
		$tax = $totalWithFees * ($this->tax/100);
		$tax = Util::ceil($tax, 2);

		$this->final_price = Util::ceil($totalWithFees + $tip + $tax, 2); // price

		$this->order = json_encode($params['cart']);

		if (!c::user()->id_user) {
			if (!$params['name']) {
				$errors['name'] = 'Please enter a name.';
			}
			if (!$params['phone']) {
				$errors['phone'] = 'Please enter a phone #.';
			}
			if (!$params['address'] && $this->delivery_type == 'delivery') {
				$errors['address'] = 'Please enter an address.';
			}
		} else {
			if (!$params['address']) {
				$this->address = c::user()->address;
			}
			if (!$params['phone']) {
				$this->phone = c::user()->phone;
			}
			if (!$params['name']) {
				$this->name = c::user()->name;
			}
		}

		if (!$this->restaurant()->open()) {
			$errors['closed'] = 'This restaurant is closed.';

			$time = new DateTime('now', new DateTimeZone($this->restaurant()->timezone));
			$debug    = [
				'type'       => 'closed',
				'time'        => $time->format('Y-m-d H:i:s'),
				'timezone'   => $this->restaurant()->timezone,
				'cart'       => $params['cart'],
				'params'     => $params,
				'restaurant' => $this->restaurant()->exports(),
			];
			Crunchbutton_Log::error($debug);

			if (Cana::env() != 'live') {
				$errors['debug']  = $debug;
			}
		}

		if (!$this->restaurant()->meetDeliveryMin($this) && $this->delivery_type == 'delivery') {
			$errors['minimum'] = 'Please meet the delivery minimum of '.$this->restaurant()->delivery_min.'.';
		}

		if ($errors) {
		// Log the order - validation error
			Log::debug([
				'action' 				=> 'validation error',
				'address' 			=> $params['address'],
				'phone' 				=> $params['phone'],
				'pay_type' 			=> $params['pay_type'],
				'tip' 					=> $params['tip'],
				'autotip'				=> $params['autotip'],
				'autotip_value'	=> $params['autotip_value'],
				'name' 					=> $params['name'],
				'user_id' 			=> c::user()->id_user,
				'delivery_type' => $params['delivery_type'],
				'restaurant' 		=> $params['restaurant'],
				'notes' 				=> $params['notes'],
				'errors' 				=> $params['errors'],
				'cart' 					=> $params['cart'],
				'type' 					=> 'order-log'
			]);
			return $errors;
		}

		$user = c::user()->id_user ? c::user() : new User;

		if (!c::user()->id_user) {
			$user->active = 1;
		}

		// Save the user just to add to him the gift cards
		$user->saving_from = $user->saving_from.'Order->process 1 - ';
		
		$user->save();

		// Reload the user from db #1737
		$user = User::o($user->id_user);

		$this->id_user = $user->id_user;

		// Find out if the user posted a gift card code at the notes field and get its value
		$this->giftcardValue = 0;
		if ( trim( $this->notes ) != '' ){
			$giftCardAdded = false;
			$giftcards = Crunchbutton_Promo::validateNotesField( $this->notes, $this->id_restaurant );
			foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
				if( $giftcard->id_promo ){
					if( !$giftCardAdded ){
						$this->giftcardValue += $giftcard->value;
						$giftCardAdded = true;
					}
				}
			}
			$_notes = $giftcards[ 'notes' ];	
		}

		Log::debug([ 'issue' => '#1551', 'method' => 'process', '$this->final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, '$_notes' => $_notes, '$this->notes' => $this->notes ]);

		$res = $this->verifyPayment();

		if ( $res !== true ) {
		// Log the order - credit card error
		Log::debug([
							'action' 				=> 'credit card error',
							'address' 			=> $params['address'],
							'phone' 				=> $params['phone'],
							'pay_type' 			=> $params['pay_type'],
							'tip' 					=> $params['tip'],
							'autotip'				=> $params['autotip'],
							'autotip_value'	=> $params['autotip_value'],
							'name' 					=> $params['name'],
							'user_id' 			=> c::user()->id_user,
							'delivery_type' => $params['delivery_type'],
							'restaurant' 		=> $params['restaurant'],
							'notes' 				=> $params['notes'],
							'errors' 				=> $res['errors'],
							'cart' 					=> $params['cart'],
							'type' 					=> 'order-log'
						]);
			return $res['errors'];
		} else {
			$this->txn = $this->transaction();
		}

		$user->location_lat = $params['lat'];
		$user->location_lon = $params['lon'];

		$user->name = $this->name;
		$user->phone = $this->phone;

		if ($this->delivery_type == 'delivery') {
			$user->address = $this->address;
		}

		$user->pay_type = $this->pay_type;
		$user->delivery_type = $this->delivery_type;
		$user->tip = $this->tip;

		$this->env = c::getEnv(false);
		$this->processor = Crunchbutton_User_Payment_Type::processor();

		$user->saving_from = $user->saving_from.'Order->process 2 - ';
		$user->save();

		$user = new User( $user->id_user );
		$this->_user = $user;

		// If the pay_type is card 
		if ($this->pay_type == 'card' ) {
			// Verify if the user already has a payment type
			$payment_type = $user->payment_type();
			if( !$payment_type ){
				// Copy the last user's payment
				$payment_type = Crunchbutton_User_Payment_Type::copyPaymentFromUserTable( $user->id_user );	
			}
			$saveThisPayment = false;
			// The user hasnt any payment type, so lets create one
			if( $payment_type ){
				// Compare this payment with the last one
				if( $params['card']['id'] && $params['card']['year'] && $params['card']['lastfour'] && $params['card']['month'] && ( 
						$user_payment_type->card != '************'.$params['card']['lastfour'] ||
						$user_payment_type->card_exp_year != $params['card']['year'] || 
						$user_payment_type->card_exp_month != $params['card']['month'] ) ){
					$saveThisPayment = true;
				}
			} else {
				$saveThisPayment = true;
			}
			if( $saveThisPayment ){
				$user_payment_type = new Crunchbutton_User_Payment_Type();
				$user_payment_type->id_user = $user->id_user;
				$user_payment_type->active = 1;
				if ($this->_customer->id) {
					switch (Crunchbutton_User_Payment_Type::processor()) {
						case 'stripe':
						default:
							$user_payment_type->stripe_id = $this->_customer->id;
							break;
						case 'balanced':
							$user_payment_type->balanced_id = $this->_customer->id;
							break;
					}
				}
				$user_payment_type->card = '************'.$params['card']['lastfour'];
				$user_payment_type->card_type = $params['card']['card_type'];
				$user_payment_type->card_exp_year = $params['card']['year'];
				$user_payment_type->card_exp_month = $params['card']['month'];
				$user_payment_type->date = date('Y-m-d H:i:s');
				$user_payment_type->save();
				// Desactive others payments
				$user_payment_type->desactiveOlderPaymentsType( $user->id_user, $user_payment_type->id_user_payment_type );
			}
		}

		// If the user typed a password it will create a new user auth
		if( $params['password'] != '' ){
			$params_auth = array();
			$params_auth[ 'email' ] = $params['phone'];
			$params_auth[ 'password' ] = $params[ 'password' ];
			$emailExists = User_Auth::checkEmailExists( $params_auth[ 'email' ] );
			if( !$emailExists ){
				$user_auth = new User_Auth();
				$user_auth->id_user = $user->id_user;
				$user_auth->type = 'local';
				$user_auth->auth = User_Auth::passwordEncrypt( $params_auth[ 'password' ] );
				$user_auth->email = $params_auth[ 'email' ];
				$user_auth->active = 1;
				$user_auth->save();
			}
		}

		// This line will create a phone user auth just if the user already has an email auth
		User_Auth::createPhoneAuth( $user->id_user, $user->phone );
		User_Auth::createPhoneAuthFromFacebook( $user->id_user, $user->phone );

		if ($this->_customer->id) {
			switch (Crunchbutton_User_Payment_Type::processor()) {
				case 'balanced':
					$this->_customer->email_address = 'uuid-'.$user->uuid.'@_DOMAIN_';
					try {
						$this->_customer->save();
					} catch (Exception $e) {

					}
					break;
			}
		}

		c::auth()->session()->id_user = $user->id_user;
		c::auth()->session()->generateAndSaveToken();

		$agent = Crunchbutton_Agent::getAgent();
		$this->id_agent = $agent->id_agent;

		if( c::auth()->session()->id_session != '' ){
			$this->id_session = c::auth()->session()->id_session;	
		}

		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->delivery_service = $this->restaurant()->hasDeliveryService();
		$this->id_community = $this->restaurant()->community()->id_community;
		$this->save();

		// If the payment succeds then redeem the gift card
		if ( trim( $this->notes ) != '' ){
			$giftcards = Crunchbutton_Promo::validateNotesField( $this->notes, $this->id_restaurant );
			$giftCardAdded = false;
			foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
				if( $giftcard->id_promo ){
					if( !$giftCardAdded ){
						$giftcard->addCredit( $user->id_user );
					}
					$giftCardAdded = true;
				}
			}
			$this->notes = $giftcards[ 'notes' ];	
		}

		Log::debug([ 'issue' => '#1551', 'method' => 'process', '$this->final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, '$this->notes' => $this->notes ]);

		$this->debitFromUserCredit( $user->id_user );

		if ($params['make_default']) {
			$preset = $user->preset($this->restaurant()->id_restaurant);
			if ($preset->id_preset) {
				$preset->delete();
			}
		}

		foreach ($this->_dishes as $dish) {
			$dish->id_order = $this->id_order;
			$dish->save();
			$_Dish = Dish::o( $dish->id_dish );
			foreach ($dish->options() as $option) {
				# Issue 1437 - https://github.com/crunchbutton/crunchbutton/issues/1437#issuecomment-20561023
				# 1 - When an option is removed, it should NEVER appear in the order or on the fax.
				if( $_Dish->dish_has_option( $option->id_option ) ){
					$option->id_order_dish = $dish->id_order_dish;
					$option->save();	
				}
			}
		}

		$this->que();
		
		$order = $this;

		if ($params['make_default']) {
			// Cana::timeout(function() use($order) {
				Preset::cloneFromOrder($order);
			// });
		}
		
		Cana::timeout(function() use($order) {
			Crunchbutton_Hipchat_Notification::OrderPlaced($order);
		});

		Cana::timeout(function() use($order) {
			$rules = new Crunchbutton_Order_Rules();
			$rules->run( $order );
		});


		if( Crunchbutton_Referral::isReferralEnable() ){
			// If the user was invited we'll give credit to the inviter user 
			$inviter_code = Crunchbutton_Referral::checkCookie();
			if( $inviter_code ){
				// If the code is valid it will return the inviter user
				$_inviter = Crunchbutton_Referral::validCode( $inviter_code );
				if( $_inviter ){
					$totalOrdersByPhone = $this->totalOrdersByPhone( $this->phone );
					$referral = new Crunchbutton_Referral();
					$referral->id_user_inviter = $_inviter->id_user;
					$referral->id_user_invited = $this->id_user;
					$referral->id_order = $this->id_order;
					$referral->invite_code = $inviter_code;
					if( $totalOrdersByPhone <= 1 ){
						$referral->new_user = 1;
					} else {
						$referral->new_user = 0;
					}
					$referral->date = date('Y-m-d H:i:s');
					$referral->save();
					// See #1660
					if( $this->pay_type == 'card' ){
						// Finally give credit to inviter
						$referral->addCreditToInviter();
					}

					Log::debug([ 'inviter_code' => $inviter_code, 'totalOrdersByPhone' => $totalOrdersByPhone, 'type' => 'referral', 'pay_type' => $this->pay_type ]);

				}
				Crunchbutton_Referral::removeCookie();
			}
		}
		return true;
	}

	public function calcFinalPriceMinusUsersCredit(){
		$final_price = $this->final_price;
		if( $this->pay_type == 'card' ){
			$final_price = $final_price - $this->giftcardValue;
			if( $this->id_user ){
				$chargedByCredit = Crunchbutton_Credit::calcDebitFromUserCredit( $final_price, $this->id_user, $this->id_restaurant, $this->id_order, true );
				$final_price = $final_price - $chargedByCredit;
			}
			Log::debug([ 'issue' => '#1551', 'method' => 'calcFinalPriceMinusUsersCredit', '$this->final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, 'final_price' => $final_price, ]);
			if( $final_price < 0 ){ $final_price = 0; }
			return Util::ceil( $final_price, 2 );
		}
		return $final_price;
	}

	public function chargedByCredit(){
		$totalCredit = 0;
		if( $this->pay_type == 'card' ){
			$credits = Crunchbutton_Credit::creditByOrder( $this->id_order );
			if( $credits->count() > 0 ){
				foreach( $credits as $credit ){
					$totalCredit = $totalCredit + $credit->value;
				}
			}
		}
		return $totalCredit;
	}

	public function charged(){
		return number_format( abs( ( $this->final_price ) - ( $this->chargedByCredit() ) ), 2 );
	}

	public function debitFromUserCredit( $id_user ){
		if( $this->pay_type == 'card' ){
			Crunchbutton_Credit::debitFromUserCredit( $this->final_price, $id_user, $this->id_restaurant, $this->id_order );
		}
	}

	public static function uuid($uuid) {
		return self::q('select * from `order` where uuid="'.$uuid.'"');
	}

	/**
	 * The restaurant to process the order
	 *
	 * @return Crunchbutton_Restaurant
	 */
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function accepted() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="accepted"');
		return $nl->count() ? true : false;
	}

	public function fax_succeeds() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and type="phaxio" and status="success"');
		return $nl->count() ? true : false;
	}

	public function transaction() {
		return $this->_txn;
	}

	public function actions(){
		return Crunchbutton_Order_Action::byOrder( $this->id_order );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}

	public function verifyPayment() {
		switch ($this->pay_type) {
			case 'cash':
				$status = true;
				break;

			case 'card':
				$user = c::user()->id_user ? c::user() : null;
				switch (Crunchbutton_User_Payment_Type::processor()) {
					case 'stripe':
					default:
						if( $user && $user->payment_type() ){
							$stripe_id = $user->payment_type()->stripe_id;
							// if we dont have the stripe_id at the user_payment_type table, try to use the one from user table
							// it will be copy to the user_payment_type in a few miliseconds
							if( !$stripe_id ){
								$stripe_id = $user->stripe_id;
							}
						}
						$charge = new Charge_Stripe([
							'stripe_id' => $stripe_id
						]);
						break;

					case 'balanced':
						if( $user && $user->payment_type() ){
							$balanced_id = $user->payment_type()->balanced_id;
						}
							// if we dont have the balanced_id at the user_payment_type table, try to use the one from user table
							// it will be copy to the user_payment_type in a few miliseconds
							if( !$balanced_id ){
								$balanced_id = $user->balanced_id;
							}
						$charge = new Charge_Balanced([
							'balanced_id' => $balanced_id
						]);
						break;
				}

				$amount = $this->calcFinalPriceMinusUsersCredit();
				Log::debug([ 'issue' => '#1551', 'method' => 'verifyPayment', '$this->final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, 'amount' => $amount ]);
				// If the amount is 0 it means that the user used his credit.

				if( $amount > 0 ){
						$r = $charge->charge( [
						'amount' => $amount,
						'card' => $this->_card,
						'name' => $this->name,
						'address' => $this->address,
						'phone' => $this->phone,
						'user' => $user,
						'restaurant' => $this->restaurant()
					]);
					if ($r['status']) {
						$this->_txn = $r['txn'];
						$this->_user = $user;
						$this->_customer = $r['customer'];
						$status = true;
					} else {
						$status = $r;
					}	
				} else {
					$status = true;
				}

				break;
		}
		return $status;
	}

	public static function recent() {
		return self::q('select * from `order` order by `date` DESC');
	}

	public static function deliveredByCBDrivers( $search ){

		$where = ' WHERE 1 = 1';

		if( $search[ 'id_admin' ] ){
			$where .= ' AND oa.id_admin = ' . $search[ 'id_admin' ];
		}

		if( $search[ 'id_restaurant' ] ){
			$where .= ' AND o.id_restaurant = ' . $search[ 'id_restaurant' ];
		}

		if ( $search[ 'start' ] ) {
			$s = new DateTime( $search[ 'start' ] );
			$where .= ' AND DATE( `o.date`) >= "' . $s->format( 'Y-m-d' ) . '"';
		}

		if ( $search[ 'end' ] ) {
			$s = new DateTime( $search[ 'end' ] );
			$where .= ' AND DATE( `o.date` ) <= "' . $s->format( 'Y-m-d' ) . '"';
		}

		if( $search[ 'limit' ] ){
			$limit = 'LIMIT '. $search[ 'limit' ];
		} else {
			$limit = 'LIMIT 25'; 
		}

		$query = 'SELECT DISTINCT(o.id_order) id, o.* FROM `order` o
							INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.delivery_service = 1
							' . $where . '
							ORDER BY o.id_order DESC ' . $limit;
		return Order::q( $query );

	}

	public static function find($search = []) {
		$query = '
			select `order`.* from `order`
			left join restaurant using(id_restaurant)
			where id_order is not null
		';
		if ($search['env']) {
			$query .= ' and env="'.$search['env'].'" ';
		}
		if ($search['processor']) {
			$query .= ' and processor="'.$search['processor'].'" ';
		}
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}

		$hasPermissionToAllRestaurants = c::admin()->permission()->check( [ 'global', 'orders-all' ] );	

		if ($search['restaurant']) {
			if( $hasPermissionToAllRestaurants || c::admin()->permission()->check( [ "orders-list-restaurant-{$search['restaurant']}" ] ) ){
				$query .= ' and `order`.id_restaurant="'.$search['restaurant'].'" ';
			} else {
				exit;
			}
		} else {
			// If the user doesnt have permission to all restaurants show just the ones he could see
			if( !$hasPermissionToAllRestaurants ){
				$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirOrders();
				$restaurants[] = 0;
				$query .= ' and `order`.id_restaurant IN ( ' . join( $restaurants, ',' ) . ')';
			}
		}

		if ($search['community']) {
			$query .= ' and `restaurant`.community="'.$search['community'].'" ';
		}
		if ($search['order']) {
			$query .= ' and `order`.id_order="'.$search['order'].'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `order`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `order`.address not like "%'.substr($word,1).'%" ';
					$qn .= ' and `order`.phone not like "%'.substr($word,1).'%" ';
					$qn .= ' and `restaurant`.name not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`order`.name like "%'.$word.'%"
						or `order`.address like "%'.$word.'%"
						or `restaurant`.name like "%'.$word.'%"
						or `order`.phone like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= '
			order by `date` DESC
		';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$orders = self::q($query);
		return $orders;
	}

	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Order_Dish::q('select * from order_dish where id_order="'.$this->id_order.'"');
		}
		return $this->_dishes;
	}

	public function tip() {
		if( $this->tip_type == self::TIP_NUMBER ){
			return number_format( $this->tip, 2 );
		} else {
			return number_format($this->price * ($this->tip/100),2);	
		}
	}

	public function tax() {
		return number_format(($this->price + $this->deliveryFee() + $this->serviceFee()) * ($this->tax/100),2);
	}

	public function deliveryFee() {
		return number_format($this->delivery_fee,2);
	}

	public function serviceFee() {
		return number_format(($this->price + $this->delivery_fee) * ($this->service_fee/100),2);
	}

	public function cbFee() {
		return ($this->restaurant()->fee_restaurant) * ($this->price) / 100;
	}

	public function customer_fee(){
		return ($this->restaurant()->fee_customer) * ($this->price) / 100;
	}

	public function fee(){
		if( $this->restaurant()->fee_on_subtotal ){
			return $this->cbFee();
		} else {
			return ($this->restaurant()->fee_restaurant) * ($this->final_price) / 100;
		}
	}

	public function notify() {

		$order = $this;
		$needDrivers = false;
		$hasDriversWorking = false;

		$driversToNotify = [];

		foreach ( $order->restaurant()->notifications() as $n ) {
			// Admin notification type means it needs a driver
			if( $n->type == Crunchbutton_Notification::TYPE_ADMIN ){
				$needDrivers = true;
				$admin = $n->admin();
				// Store the drivers
				$driversToNotify[ $admin->id_admin ] = $admin;
			} else {
				Log::debug([ 'order' => $order->id_order, 'action' => 'sending notification', 'type' => $n->type, 'to' => $n->value, 'type' => 'notification']);
				$n->send( $order );
			}
		}

		// check if the restaurant is using our delivery system
		if( intval( $order->restaurant()->delivery_service ) == 1 ){		
			// get the restaurant community and its drivers
			$community = $order->restaurant()->community;
			if( $community ){
				$group = Crunchbutton_Group::getDeliveryGroupByCommunity( Crunchbutton_Group::driverGroupOfCommunity( $community ) );
				if( $group->id_group ){
					$drivers = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );	
					foreach( $drivers as $driver ){
						$driversToNotify[ $driver->id_admin ] = $driver;
					}
				}
			}
		}

		// Send notification to drivers
		if( count( $driversToNotify ) > 0 ){
			foreach( $driversToNotify as $driver ){
				if( $driver->isWorking() ){
					foreach( $driver->activeNotifications() as $adminNotification ){
						$hasDriversWorking = true;
						$adminNotification->send( $order );
						Log::debug([ 'order' => $order->id_order, 'action' => 'sending notification', 'type' => 'admin', 'type' => 'notification']);
					}	
				}
			}
			Crunchbutton_Admin_Notification_Log::register( $this->id_order );	
		}

		if( $needDrivers && !$hasDriversWorking ){
			Log::debug([ 'order' => $order->id_order, 'action' => 'there is no drivers to get the order', 'type' => 'notification']);
			Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
		}
	}

	public function driver(){
		return Admin::q( "SELECT DISTINCT(a.id_admin) id, a.* FROM order_action o INNER JOIN admin a ON a.id_admin = o.id_admin WHERE o.id_order = {$this->id_order}" );
	}

	public function resend_notify(){
		$order = $this;
		// Log::debug([ 'order' => $order->id_order, 'action' => 'restarting starting notification', 'type' => 'notification']);
		// Delete all the notification log in order to start a new one
		// Notification_Log::DeleteFromOrder( $order->id_order );
		// Log::debug([ 'order' => $order->id_order, 'action' => 'deleted previous notifications', 'type' => 'notification']);
		$order->notify();
	}

	public function confirm() {

		Log::debug([ 'order' => $this->id_order, 'action' => 'confirm() - dial confirm call', '$this->confirmed' => $this->confirmed, '$this->restaurant()->confirmation' =>$this->restaurant()->confirmation, 'type' => 'notification']);

		if ($this->confirmed || !$this->restaurant()->confirmation) {
			return;
		}

		$nl = Notification_Log::q('SELECT * FROM notification_log WHERE id_order="'.$this->id_order.'" AND type = "confirm" AND ( status = "created" OR status = "queued" OR status ="success" ) ');
		if( $nl->count() > 0 ){
			// Log
			Log::debug([ 'order' => $this->id_order, 'count' => $nl->count(), 'action' => 'confirmation call already in process', 'host' => c::config()->host_callback, 'type' => 'notification']);
			return;
		} 

		$env = c::getEnv();
		$num = ($env == 'live' ? $this->restaurant()->phone : c::config()->twilio->testnumber);

		$log = new Notification_Log;
		$log->type = 'confirm';
		$log->id_order = $this->id_order;
		$log->date = date('Y-m-d H:i:s');
		$log->status = 'created';
		$log->save();

		$callback = 'http://'.c::config()->host_callback.'/api/notification/'.$log->id_notification_log.'/confirm';

		// Log
		Log::debug([ 'order' => $this->id_order, 'action' => 'dial confirm call', 'count' => $nl->count(), 'num' => $num, 'host' => c::config()->host_callback, 'callback' => $callback, 'type' => 'notification']);

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingRestaurant,
			'+1'.$num,
			'http://'.c::config()->host_callback.'/api/order/'.$this->id_order.'/doconfirm',
			[
				'StatusCallback' => $callback
			]
		);

		$log->remote = $call->sid;
		$log->status = $call->status;
		$log->save();
	}

	public function receipt() {
		$env = c::getEnv();

		$num = ($env == 'live' ? $this->phone : c::config()->twilio->testnumber);


		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = str_split($this->message('selfsms'),160);

		$type = 'twilio';

		Log::debug( [ 'order' => $this->id_order, 'action' => 'receipt', 'num' => $num, 'message' => $message, 'type' => 'notification' ]);

		foreach ($message as $msg) {
			switch ($type) {
				case 'googlevoice':
					$gv = new GoogleVoice('cbvoice@arzynik.com', base64_decode('eXVtbWllc3Q='));
					$gv->sms($num, $msg);
					break;

				case 'twilio':
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextCustomer,
						'+1'.$num,
						$msg
					);
					break;

				case 'textbelt':
				default:
					$cmd = 'curl http://crunchr.co:9090/text \\'
						.'-d number='.$num.' \\'
						.'-d "message='.$msg.'"';
					exec($cmd, $return);
					print_r($return);
					break;
			}
		}
	}

	public function que( $sendReceipt = true ) {

		$order = $this;

		Cana::timeout(function() use($order) {
			/* @var $order Crunchbutton_Order */
			$order->notify();
		});

		if( $sendReceipt ){
			c::timeout(function() use($order) {
				$order->receipt();
			}, 30 * 1000); // 30 seconds
		} else {
			Log::debug( [ 'order' => $order->id_order, 'action' => 'receipt already sent', 'type' => 'notification' ]);
		}

		// Start the timer to check if the order was confirmed. #1049
		if ($this->restaurant()->confirmation) {
			$timer = c::config()->twilio->warningOrderNotConfirmedTime;

			// Log
			Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningOrderNotConfirmed started', 'time' => $timer, 'type' => 'notification' ]);
			/* Removed for while by @pererinha asked by @DavidKlumpp at 11/07/2013
			c::timeout(function() use($order, $timer) {
				$order->warningOrderNotConfirmed();
			}, $timer );
			*/
		}
	}

	// After 5 minutes the fax was sent we have to send this confirmation to make sure that the fax as delivered. If the order was already confirmed this confirmation will be ignored.
	public function queConfirmFaxWasReceived(){
		
		// Issue #1239
		return false;

		$order = $this;

		$isConfirmed = Order::isConfirmed( $order->id_order );

		if ( $isConfirmed || !$order->restaurant()->confirmation) {
			return;
		}

		$confirmTimeFaxReceived = c::config()->twilio->confirmTimeFaxReceived;

		// Log
		Log::debug( [ 'order' => $this->id_order, 'action' => 'confirmFaxWasReceived', 'confirmationTime' => $confirmTimeFaxReceived,  'confirmed' => $isConfirmed, 'type' => 'notification' ] );

		c::timeout(function() use($order) {
			$order->confirm();
		}, $confirmTimeFaxReceived );
	}

	public function queConfirm() {
		
		$order = $this;

		if ($order->confirmed || !$order->restaurant()->confirmation) {
			return;
		}
		// Check if there are another confirm que, if it does it will not send two confirms. Just one is enough.
		$nl = Notification_Log::q('SELECT * FROM notification_log WHERE id_order="'.$order->id_order.'" AND type = "confirm" AND ( status = "created" OR status = "queued" ) ');
		if( $nl->count() > 0 ){
			return;
		}

		// Query to count the number of confirmations sent
		$nl = Notification_Log::q('SELECT * FROM notification_log WHERE id_order="'.$order->id_order.'" AND status="callback" AND `type`="confirm"');

		if( $nl->count() > 0 ){ // if it is the 2nd, 3rd, 4th... call the confirmation time should be 2 min even to hasFaxNotification - #974
			$confirmationTime = c::config()->twilio->confirmTimeCallback;
			
		} else { // if it is the first confirmation call

			if( $order->restaurant()->hasFaxNotification() ){ // If restaurant has fax notification
				$confirmationTime = c::config()->twilio->confirmFaxTime;
			} else {
				$confirmationTime = c::config()->twilio->confirmTime;			
			}
		}			

		// Log
		Log::debug( [ 'order' => $this->id_order, 'action' => 'queConfirm - confirm', 'hasFaxNotification' => $order->restaurant()->hasFaxNotification(), 'confirmationTime' => $confirmationTime, 'confirmation number' => $nl->count(), 'confirmed' => $this->confirmed, 'type' => 'notification' ] );

		// $order = $this;

		Cana::timeout(function() use($order) {
			/* @var $order Crunchbutton_Order */
			$order->confirm();
		}, $confirmationTime );
	
	}

	// At the method warningOrderNotConfirmed() i've tried to use $this->confirmed
	// but it always returns an empty string, so I had to create this method.
	public function isConfirmed( $id_order ){
		$order = Order::o( $id_order );
		if( $order->id_order ){
			if( $order->confirmed ){
				return true;
			}
		}
		return false;
	}

	public function warningOrderNotConfirmed(){
		return;
		$order = $this;

		$isConfirmed = Order::isConfirmed( $this->id_order );

		Log::debug( [ 'order' => $this->id_order, 'action' => 'warningOrderNotConfirmed', 'object' => $order->json(), 'type' => 'notification' ]);

		if ( $isConfirmed || !$this->restaurant()->confirmation ) {
			Log::debug( [ 'order' => $this->id_order, 'action' => 'que warningOrderNotConfirmed ignored', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);
			return;
		}

		$date = $order->date();
		$date = $date->format( 'M jS Y' ) . ' - ' . $date->format( 'g:i:s A' );

		$env = c::getEnv();
		
		$message = 'O# ' . $order->id_order . ' for ' . $order->restaurant()->name . ' (' . $date . ') not confirmed.';
		$message .= "\n";
		$message .= 'R# ' . $order->restaurant()->phone();
		$message .= "\n";
		$message .= 'C# ' . $order->user()->name . ' : ' . $order->phone();
		$message .= "\n";
		$message .= 'E# ' . $env;

		$message = str_split( $message,160 );

		Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningOrderNotConfirmed sending sms', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		
		if( $env == 'live' ){
			foreach ( c::config()->text as $supportName => $supportPhone ) {
				foreach ( $message as $msg ) {
					Log::debug( [ 'order' => $order->id_order, 'action' => 'warningOrderNotConfirmed', 'message' => $message, 'supportName' => $supportName, 'supportPhone' => $supportPhone,  'type' => 'notification' ]);
					try {
						$twilio->account->sms_messages->create(
							c::config()->twilio->{$env}->outgoingTextCustomer,
							'+1'.$supportPhone,
							$msg
						);
					} catch (Exception $e) {}
				}
			}
		} else {
			Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningOrderNotConfirmed DEV dont send sms', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);
		}
	}


	public function orderMessage($type) {

		// @TODO: need to combine all this stuff into one streamlined thing
	
		if ($type == 'summary' || $type == 'facebook') {
			// does not show duplicate items, configuration, or item count. returns human readable
			$dishes = [];
			foreach ($this->dishes() as $dish) {
				$dishes[$dish->id_dish] = $dish;
			}
			$dishes = array_values($dishes);
			$food = '';
			$c = count($dishes);
			foreach ($dishes as $x => $dish) {
				$food .= ($x != 0 ? ', ' : '') . ($c > 1 && $x == $c-1 ? '& ' : '') . $dish->dish()->name . ($x == $c-1 ? '.' : '');
			}
			return $food;
		}

		
		// everything else
		switch ($type) {
			case 'sms':
			case 'web':
			case 'support':
				$with = 'w/';
				$space = ',';
				$group = false;
				$showCount = false;
				break;

			case 'phone':
				$with = '. ';
				$space = '.';
				$group = false;
				$showCount = true;
				break;

			case 'summary':
				$with = '';
				$space = '';
				$group = true;
				$showCount = false;
				break;

		}

		if ($type == 'phone') {
			$pFind = ['/fries/i','/BBQ/i'];
			$pReplace = ['frys','barbecue'];
		} else {
			$pFind = $pReplace = [];
		}

		$i = 1;
		$d = new DateTime('01-01-2000');

		foreach ($this->dishes() as $dish) {

			if ($type == 'phone') {
				$prefix = $d->format('jS').' item. ';
				$d->modify('+1 day');
			}

			$foodItem = "\n- ".$prefix.preg_replace($pFind, $pReplace, $dish->dish()->name);
			$options = $dish->options();

			if (gettype($options) == 'array') {
				$options = i::o($options);
			}

			if ($options->count()) {
				$foodItem .= ' '.$with.' ';

				foreach ($dish->options() as $option) {
					if ($option->option()->type == 'select') {
						continue;
					}
					$foodItem .= preg_replace($pFind, $pReplace, $option->option()->name).$space.' ';
				}
				$foodItem = substr($foodItem, 0, -2);
			} 

			$withoutDefaultOptions = '';

			if( $dish->id_order_dish && $dish->id_dish ){
				$optionsNotChoosen = $dish->optionsDefaultNotChoosen();
				$commas = ' ';
				if( $optionsNotChoosen->count() ){
					foreach( $optionsNotChoosen as $dish_option ){
						$withoutDefaultOptions .= $commas . 'No ' . $dish_option->option()->name;
						$commas = $space . ' ';
					}
				}
			}

			if ( $options->count() && $withoutDefaultOptions != '' ) {
				$foodItem .= $space;
			}

			$withoutDefaultOptions .= '.';
			$foodItem .= $withoutDefaultOptions;

			if ($type == 'phone') {
				$foodItem .= ']]></Say><Pause length="2" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[';
			}
			$food .= $foodItem;
		}

		return $food;
	}

	public function streetName() {
		$name = explode("\n",$this->address);
		$name = preg_replace('/^[0-9]+ (.*)$/i','\\1',$name[0]);
		$spaceName = '';

		for ($x=0; $x<strlen($name); $x++) {
			$letter = strtolower($name{$x});
			switch ($letter) {
				case ' ':
				case '.':
				case ',':
				case "\n":
					$addPause = true;
					break;
				case 'c':
					$letter = 'see.';
				default:
					if ($addPause) {
						$spaceName .= '<Pause length="1" />';
					}
					$spaceName .= '<Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$letter.']]></Say><Pause length="1" />';
					$addPause = false;
					break;
			}

		}
		return $spaceName;
	}

	public function phoneticStreet($st) {
		$pFind = ['/(st\.)|( st($|\n))/i','/(ct\.)|( ct($|\n))/i','/(ave\.)|( ave($|\n))/i'];
		$pReplace = [' street. ',' court. ',' avenue. '];
		$st = preg_replace($pFind,$pReplace,$st);
		return $st;
	}

	/**
	 * Generates the message to be send in the notification
	 *
	 * @param string $type What kind of message will be send,
	 *
	 * @return string
	 */
	public function message($type) {

		$food = $this->orderMessage($type);

		/**
		 * Not used anymore but could in the future, so, I'm leaving it here
		 * @var string
		 */
		$supportPhone = Cana::config()->phone->support;

		switch ($type) {
			case 'selfsms':
				$msg  = "Crunchbutton.com #".$this->id_order."\n\n";
				$msg .= "Order confirmed!\n\n";
				// #2416
				if( !$this->delivery_service ){
					$msg .= "Restaurant Phone: ".$this->restaurant()->phone().".\n";	
				}
				$msg .= "To contact Crunchbutton, text us back.\n\n";
				if ($this->pay_type == self::PAY_TYPE_CASH) {
					$msg .= "Remember to tip!\n\n";
				}
				break;

			case 'support':
				
				$date = new DateTime($this->date);
				$timezone = new DateTimeZone($this->restaurant()->timezone);
				$date->setTimeZone($timezone);
				$when = $date->format('M j, g:i a');

				$confirmed = $this->confirmed? 'yes' : 'no';
				$refunded = $this->refunded? 'yes':'no';

				$msg = "
					$this->delivery_type / $this->pay_type, $when
					<br>name: $this->name
					<br>phone: ".Crunchbutton_Util::format_phone($this->phone)."
					<br>confirmed: $confirmed
					<br>refunded: $refunded
					<br><br>food: $food
				";
				if ($this->delivery_type == 'delivery') {
					$msg .= "<br>address: ".$this->address;
				}
				if ($this->notes) {
					$msg .= "<br>notes: ".$this->notes;
				}
				if ($this->pay_type == 'card' && $this->tip) {
					$msg .= "<br>tip: $".$this->tip();
				}
				break;

			case 'sms':
				$msg = "Crunchbutton #".$this->id_order." \n\n";
				$msg .= $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food." \n\nphone: ".preg_replace('/[^\d.]/','',$this->phone).'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= " \naddress: ".$this->address;
				}
				if ($this->notes) {
					$msg .= " \nNOTES: ".$this->notes;
				}
				if ($this->pay_type == 'card' && $this->tip) {
					$msg .= " \nTIP: $".$this->tip();
				}
				break;

			case 'phone':
				$spacedPhone = preg_replace('/[^\d.]/','',$this->phone);
				for ($x=0; $x<strlen($spacedPhone); $x++) {
					$spacedPhones .= $spacedPhone{$x}.'. ';
				}
				$msg =
						'Customer Phone number. '.$spacedPhones.'.'
						.'</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Customer Name. '.$this->name.'.]]></Say><Pause length="1" /><Say>';

				if ($this->delivery_type == 'delivery') {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Address '.$this->phoneticStreet($this->address).'.]]>';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">This order is for pickup. ';
				}

				$msg .= '</Say><Pause length="2" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$food.'.]]>';

				if ($this->notes) {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Customer Notes. '.$this->notes.'.]]>';
				}

				$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">Order total: '.$this->phoeneticNumber($this->final_price);

				if ($this->pay_type == 'card' && $this->tip ) {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">A tip of '.$this->phoeneticNumber($this->tip()).' has been charged to the customer\'s credit card.';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">The customer will be paying the tip . by cash.';
				}

				if ( $this->pay_type == 'card') {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">The customer has already paid for this order by credit card.';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">The customer will pay for this order with cash.';
				}

				break;
			case 'sms-admin':
				$spacer = ' / ';
				$payment = 
				$msg = $this->name . $spacer . strtoupper( $this->pay_type ) . $spacer . strtoupper( $this->delivery_type ) . $spacer . preg_replace( '/[^\d.]/', '', $this->phone ) . $spacer;

				if( $this->delivery_type == Crunchbutton_Order::SHIPPING_DELIVERY ){
					$msg .= $this->address . $spacer;
				}

				$msg .= $this->restaurant()->name . $spacer ;

				// Payment is card and user tipped
				if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && $this->tip ){
					$msg .= 'TIP ' . $this->tip();
				} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && !$this->tip ){
					$msg .= 'TIP BY CASH';
				} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ){
					$msg .= 'TOTAL ' . $this->final_price;
				}

				break;
			
		}

		return $msg;
	}

	public function phoeneticNumber($num) {
		$num = explode('.',$num);
		return $num[0].' dollar'.($num[0] == 1 ? '' : 's').' and '.$num[1].' cent'.($num[1] == '1' ? '' : 's');
	}

	public function exports() {

		$out = $this->properties();

		unset($out['id_user']);
		unset($out['id']);
		unset($out['id_order']);

		$out['id'] = $this->uuid;
		
		if( isset( $out[ 'type' ] ) && $out[ 'type' ] == 'compressed' ){
			$out['_restaurant_name'] = $out['restaurant_name'];
			$out['_restaurant_permalink'] = $out['restaurant_permalink'];
			$timezone = new DateTimeZone( $out['timezone'] );
			unset( $out['type'] );
			unset( $out['uuid'] );
			unset( $out['restaurant_name'] );
			unset( $out['restaurant_permalink'] );
		} else {
			$date = new DateTime( $this->date, new DateTimeZone( $this->restaurant()->timezone ) );
			$out['date_formated'] = $date->format( 'g:i a, M dS, Y' );
			$out['_restaurant_name'] = $this->restaurant()->name;
			$out['_restaurant_permalink'] = $this->restaurant()->permalink;
			$out['_restaurant_phone'] = $this->restaurant()->phone;
			$out['_restaurant_lat'] = $this->restaurant()->loc_lat;
			$out['_restaurant_lon'] = $this->restaurant()->loc_long;
			$out['_restaurant_address'] = $this->restaurant()->address;
			$out['_restaurant_delivery_estimated_time'] = $this->restaurant()->delivery_estimated_time;
			$out['_restaurant_pickup_estimated_time'] = $this->restaurant()->pickup_estimated_time;
			$out['_restaurant_delivery_estimated_time_formated'] = $this->restaurant()->calc_delivery_estimated_time( $this->date );
			$out['_restaurant_pickup_estimated_time_formated'] = $this->restaurant()->calc_pickup_estimated_time( $this->date );
			$out['user'] = $this->user()->uuid;
			$out['_message'] = nl2br($this->orderMessage('web'));
			$out['charged'] = $this->charged();
			$credit = $this->chargedByCredit();
			if( $credit > 0 ){
				$out['credit'] = $credit;
			} else {
				$out['credit'] = 0;
			}
			$timezone = new DateTimeZone($this->restaurant()->timezone);
		}

		$date = new DateTime($this->date);
		$date->setTimeZone($timezone);
		
		$out['_date_tz'] = $date->format('Y-m-d H:i:s');
		$out['_tz'] = $date->format('T');
		
		$out['summary'] = $this->orderMessage('summary');

		return $out;
	}

	public function refundGiftFromOrder(){
		if( $this->chargedByCredit() ){
			$credits = Crunchbutton_Credit::creditByOrder( $this->id_order );
			if( $credits->count() > 0 ){
				foreach( $credits as $credit ){

					// We want just the debits
					if( $credit->type != Crunchbutton_Credit::TYPE_DEBIT ){
						continue;
					}

					// Creates a new credit to the user
					$creditRefounded = new Crunchbutton_Credit();
					$creditRefounded->id_user = $credit->id_user;
					$creditRefounded->type = Crunchbutton_Credit::TYPE_CREDIT;
					// $creditRefounded->id_restaurant = $this->id_restaurant;
					$creditRefounded->date = date('Y-m-d H:i:s');
					$creditRefounded->value = $credit->value;
					$creditRefounded->id_order_reference = $this->id_order;
					$creditRefounded->id_restaurant_paid_by = $this->id_restaurant_paid_by;
					$creditRefounded->paid_by = $this->paid_by;
					$creditRefounded->note = 'Value ' . $credit->value . ' refunded from order: ' . $this->id_order . ' - ' . date('Y-m-d H:i:s');
					$creditRefounded->save();
				}
			}
		}
	}

	public function refund() {

		if (!$this->refunded){

			// Refund the gift
			$this->refundGiftFromOrder();

			if ( intval( $this->charged() ) > 0 ) {

				if ($this->pay_type == self::PAY_TYPE_CREDIT_CARD) {

					switch ($this->processor) {
						case 'stripe':
						default:
							$env = c::getEnv();
							Stripe::setApiKey(c::config()->stripe->{$env}->secret);
							$ch = Stripe_Charge::retrieve($this->txn);
							try {
								$ch->refund();
							} catch (Exception $e) {
								return false;
							}
						break;

						case 'balanced':
							try {
								$ch = Crunchbutton_Balanced_Debit::byId($this->txn);
								$ch->refund();
							} catch (Exception $e) {
								print_r($e);
								return false;
							}
							break;
					}
				}
			}

			$support = $this->getSupport();
			if ($support) {
				$support->addNote('Order refunded.', 'system', 'internal');
			}

			$this->refunded = 1;
			$this->save();
			return true;
		}
	}

	public function getSupport() {
		$support = Support::getSupportForOrder($this->id_order);
		return $support;
	}

	public function phone() {
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);

		return $phone;
	}

	// Gets the last order tipped by the user
	public function lastTippedOrder( $id_user = null ) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;		
		return self::q('select * from `order` where id_user="'.$id_user.'" and tip is not null and tip > 0 order by id_order desc limit 0,1');
	}

	public function lastTipByDelivery($id_user = null, $delivery ) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;
		if( $id_user ){
			$order = self::q('select * from `order` where id_user="'.$id_user.'" and delivery_type = "' . $delivery . '" and tip is not null order by id_order desc limit 0,1');
			if( $order->tip ){
				return $order->tip;
			}	
		}
		return null;
	}

	public function lastDeliveredOrder($id_user = nul) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;
		if( $id_user ){
			$order = self::q('SELECT * FROM `order` WHERE id_user = ' . $id_user . ' AND delivery_type = "delivery" ORDER BY id_order DESC LIMIT 1');
			if( $order->id_order ){
				return Order::o( $order->id_order );
			}
		}
		return null;
	}

	public function lastTip( $id_user = null ) {
			$last_order = self::lastTippedOrder( $id_user );
			if( $last_order->tip ){
				return $last_order->tip;
			}
			return null;
	}
	public function lastTipType( $id_user = null ) {
			$last_order = self::lastTippedOrder( $id_user );
			if( $last_order->tip_type ){
				return strtolower( $last_order->tip_type );
			}
			return null;
	}

	public function agent() {
		return Agent::o($this->id_agent);
	}

	public function community() {
		return Community::o($this->id_community);
	}

	public function hasGiftCard(){
		if( !$this->id_order ){
			 return 0;
		}
		$query = 'SELECT SUM( value ) as total FROM promo WHERE id_order_reference = ' . $this->id_order;
		$row = Cana::db()->get( $query )->get(0);
		if( $row->total ){
			return $row->total;
		}
		return 0;
	}

	public function hasCredit(){
		$query = 'SELECT SUM( value ) as total FROM credit WHERE id_order_reference = ' . $this->id_order . '  AND id_promo IS NULL';
		$row = Cana::db()->get( $query )->get(0);
		if( $row->total ){
			return $row->total;
		}
		return 0;
	}
	
	public function expectedBy() {
		$date = clone $this->date();
		if ($this->restaurant()->pickup_estimated_time && $this->delivery_type == 'takeout') {
			$date->modify('+ '.$this->restaurant()->pickup_estimated_time. ' minute');
		} else {
			if( $this->restaurant()->delivery_estimated_time ){
				$date->modify('+ '.$this->restaurant()->delivery_estimated_time. ' minute');	
			}
		}
		return $date;
	}

	public function totalOrdersByPhone( $phone ){
		$query = "SELECT COUNT(*) AS total FROM `order` WHERE phone = '{$phone}'";
		$row = Cana::db()->get( $query )->get(0);
		if( $row->total ){
			return $row->total;
		}
		return 0;
	}

	public function restaurantsUserHasPermissionToSeeTheirOrders(){
		$restaurants_ids = [];
		$_permissions = new Crunchbutton_Admin_Permission();
		$all = $_permissions->all();
		// Get all restaurants permissions
		$restaurant_permissions = $all[ 'order' ][ 'permissions' ];
		$permissions = c::admin()->getAllPermissionsName();
		$restaurants_id = array();
		foreach ( $permissions as $permission ) {
			$permission = $permission->permission;
			$info = $_permissions->getPermissionInfo( $permission );
			$name = $info[ 'permission' ];
			foreach( $restaurant_permissions as $restaurant_permission_name => $meta ){
				if( $restaurant_permission_name == $name ){
					if( strstr( $name, 'ID' ) ){
						$regex = str_replace( 'ID' , '((.)*)', $name );
						$regex = '/' . $regex . '/';
						preg_match( $regex, $permission, $matches );
						if( count( $matches ) > 0 ){
							$restaurants_ids[] = $matches[ 1 ];
						}
					}
				}
			}
		}
		return array_unique( $restaurants_ids );
	}
	
	/*
		get the delivery status of the order based on reps or restaurants actions agaisnt it
		@todo: add restaurant actions
	*/
	public function deliveryStatus($type = null) {
		if (!$this->_actions) {
			$this->_actions = Order_Action::q('select * from order_action where id_order="'.$this->id_order.'" order by timestamp');
			$this->_deliveryStatus = ['accepted' => false, 'delivered' => false, 'pickedup' => false];
			$acpt = [];

			foreach ($this->_actions as $action) {
				switch ($action->type) {
					case 'delivery-delivered':
						$this->_deliveryStatus['delivered'] = Admin::o($action->id_admin);
						break;
						
					case 'delivery-pickedup':
						$this->_deliveryStatus['pickedup'] = Admin::o($action->id_admin);
						break;

					case 'delivery-accepted':
						$acpt[$action->id_admin] = true;
						break;
						
					case 'delivery-rejected':
						$acpt[$action->id_admin] = false;
						break;
				}
			}

			foreach ($acpt as $admin => $status) {
				if ($status) {
					$this->_deliveryStatus['accepted'] = Admin::o($admin);
				}
			}
		}
		return $type === null ? $this->_deliveryStatus : $this->_deliveryStatus[$type];
	}
	
	public function deliveryAccept($admin) {
		if ($this->deliveryStatus('accepted')) {
			return false;
		}
		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $admin->id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => 'delivery-accepted'
		]))->save();
		$this->_actions = null;
		return true;
	}

	public function deliveryReject($admin) {
		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $admin->id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => 'delivery-rejected'
		]))->save();
		$this->_actions = null;
		return true;
	}

	public function deliveryPickedup($admin) {
		if (!$this->deliveryStatus('accepted') || $this->deliveryStatus('accepted')->id_admin != $admin->id_admin) {
			return false;
		}
		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $admin->id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => 'delivery-pickedup'
		]))->save();
		$this->_actions = null;
		return true;
	}
	
	public function deliveryDelivered($admin) {
		if (!$this->deliveryStatus('accepted') || $this->deliveryStatus('accepted')->id_admin != $admin->id_admin) {
			return false;
		}
		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $admin->id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => 'delivery-delivered'
		]))->save();
		$this->_actions = null;
		return true;
	}

	public function deliveryReply($admin) {
		$act = false;
		foreach ($this->_actions as $action) {
			if ($action->id_admin && $admin->id_admin) {
				switch ($action->type) {
					case 'delivery-delivered':
						$act = 'delivered';
						continue;
						break;
						
					case 'delivery-pickedup':
						$act = 'pickedup';
						continue;
						break;

					case 'delivery-accepted':
						$act = 'accepted';
						continue;
						break;
						
					case 'delivery-rejected':
						$act = 'rejected';
						continue;
						break;
				}
			}
		}
		return $act;
	}
	
	public function wasAcceptedByRep(){
		$query = "SELECT * FROM 
								order_action ac 
							WHERE 
								ac.id_order = {$this->id_order} 
							AND ( ac.type = '" . Crunchbutton_Order_Action::DELIVERY_PICKEDUP . "' 
										OR ac.type = '" . Crunchbutton_Order_Action::DELIVERY_ACCEPTED . "' 
										OR ac.type = '" . Crunchbutton_Order_Action::DELIVERY_DELIVERED . "' )";
		$action = Crunchbutton_Order_Action::q( $query );
		if( $action->count() > 0 ){
			return true;
		}
		return false;
	}

	public function hasGiftCardIssued(){
		// check if it has a gift card
		$promo = Crunchbutton_Promo::q( "SELECT * FROM promo p WHERE p.id_order_reference = {$this->id_order}" );
		if( $promo->count() > 0 ){
			return true;
		}
		// check if it has credit
		$credit = Crunchbutton_Credit::q( "SELECT * FROM credit c WHERE c.id_order_reference = {$this->id_order}" );
		if( $credit->count() > 0 ){
			return true;
		}
		return false;

	}


	public function getDeliveryDriver(){
		$action = Crunchbutton_Order_Action::q( "SELECT * FROM order_action WHERE id_order = {$this->id_order} AND ( type = '" . Crunchbutton_Order_Action::DELIVERY_PICKEDUP . "' OR type = '" . Crunchbutton_Order_Action::DELIVERY_ACCEPTED . "' OR type = '" . Crunchbutton_Order_Action::DELIVERY_DELIVERED . "') LIMIT 1" );
		if( $action->id_admin ){
			return $action->admin();	
		}
		
	}

	public function deliveryExports() {
		return [
			'id_order' => $this->id_order,
			'uuid' => $this->uuid,
			'delivery-status' => [
				'delivered' => $this->deliveryStatus('delivered') ? $this->deliveryStatus('delivered')->publicExports() : false,
				'pickedup' => $this->deliveryStatus('pickedup') ? $this->deliveryStatus('pickedup')->publicExports() : false,
				'accepted' => $this->deliveryStatus('accepted') ? $this->deliveryStatus('accepted')->publicExports() : false
			],
			'self-reply' => $this->deliveryReply(c::admin())
		];
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}
