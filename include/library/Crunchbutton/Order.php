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

class Crunchbutton_Order extends Crunchbutton_Order_Trackchange {

	const PAY_TYPE_CASH        = 'cash';
	const PAY_TYPE_CREDIT_CARD = 'card';
	const PAY_TYPE_CAMPUS_CASH = 'campus_cash';
	const PAY_TYPE_APPLE_PAY	 = 'applepay';
	const SHIPPING_DELIVERY    = 'delivery';
	const SHIPPING_TAKEOUT     = 'takeout';
	const TIP_PERCENT 				 = 'percent';
	const TIP_NUMBER				 	 = 'number';

	const PROCESS_TYPE_RESTAURANT = 'restaurant';
	const PROCESS_TYPE_WEB				= 'web';
	const PROCESS_TYPE_ADMIN			= 'admin';

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
	public function process($params, $processType = 'web')
	{

		$this->campus_cash = ( $params['pay_type'] == self::PAY_TYPE_CAMPUS_CASH );

		$this->pay_type  = ($params['pay_type'] == 'cash') ? 'cash' : 'card';
		$this->address   = $params['address'];
		$this->phone     = $params['phone'];
		$this->name      = $params['name'];
		$this->notes     = $params['notes'];
		$this->local_gid = $params['local_gid'];

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
							'campus_cash'		=> $this->campus_cash,
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

		if ($params['pay_type'] == self::PAY_TYPE_CREDIT_CARD) {
			$params['pay_type'] = self::PAY_TYPE_APPLE_PAY;
		}

		$this->id_restaurant = $params['restaurant'];

		if ( $params['processor'] && $params['pay_type'] == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && Crunchbutton_User_Payment_Type::processor() != $params['processor']) {
			$errors['processor'] = 'We recently upgraded our credit card processing security. Please press "Place Order" again to automagicly use our fancy new system.';
			$errors['set-processor'] = Crunchbutton_User_Payment_Type::processor();
		}

		if( $processType == static::PROCESS_TYPE_WEB ){
			// Check if the restaurant is active #2938
			if(!$this->restaurant()->active){
				$errors['inactive'] = 'This restaurant is not accepting orders.';
			}
		}

		if( $processType == static::PROCESS_TYPE_RESTAURANT ){
			// Check if the restaurant is active for restaurant order placement
			// https://github.com/crunchbutton/crunchbutton/issues/3350#issuecomment-48255149
			if(!$this->restaurant()->active_restaurant_order_placement){
				$errors['inactive'] = 'This restaurant is not accepting orders.';
			}
		}

		// Check if the restaurant delivery #2464
		if( $this->delivery_type == self::SHIPPING_DELIVERY ){
			if(!$this->restaurant()->delivery && $this->restaurant()->takeout){
				$this->delivery_type = self::SHIPPING_TAKEOUT;
			} else {
				// log when an order is not delivery nor takeout
				Crunchbutton_Log::error([
					'type'         => 'wrong delivery type',
					'order_params' => $params,
				]);
			}
		}

		if( $this->delivery_type == self::SHIPPING_TAKEOUT ){
			if(!$this->restaurant()->takeout && $this->restaurant()->delivery){
				$this->delivery_type = self::SHIPPING_DELIVERY;
			} else {
				// log when an order is not delivery nor takeout
				Crunchbutton_Log::error([
					'type'         => 'wrong delivery type',
					'order_params' => $params,
				]);
			}
		}

		$subtotal = 0;
		$subtotal_plus_delivery_service_markup = 0;

		$this->id_restaurant = $params['restaurant'];

		$delivery_service_markup = ( $this->restaurant()->delivery_service_markup ) ? $this->restaurant()->delivery_service_markup : 0;
		$this->delivery_service_markup = $delivery_service_markup;

		if ($processType == static::PROCESS_TYPE_RESTAURANT) {
			$subtotal = $params['subtotal'];
			$delivery_service_markup = $this->restaurant()->delivery_service_markup ? $this->restaurant()->delivery_service_markup : 0;
			$price_delivery_markup = number_format($subtotal * $delivery_service_markup / 100, 2);
			$subtotal_plus_delivery_service_markup = $subtotal + $price_delivery_markup;
			$this->type = 'restaurant';

		} else {

			foreach ($params['cart'] as $d) {
				$dish = new Order_Dish;
				$dish->id_dish = $d['id'];
				$price = number_format( $dish->dish()->price, 2);
				$price_delivery_markup = $price;
				if( $delivery_service_markup ){
					$price_delivery_markup = $price_delivery_markup + ( $price_delivery_markup * $delivery_service_markup / 100 );
					$price_delivery_markup = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $price_delivery_markup );
				}
				$subtotal += number_format( $price, 2 );
				$subtotal_plus_delivery_service_markup += number_format( $price_delivery_markup, 2 );
				if ($d['options']) {
					foreach ($d['options'] as $o) {
						$option = new Order_Dish_Option;
						$option->id_option = $o;
						$price = $option->option()->price;
						$price_delivery_markup = $price;
						if( $delivery_service_markup ){
							$price_delivery_markup = $price_delivery_markup + ( $price_delivery_markup * $delivery_service_markup / 100 );
							$price_delivery_markup = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $price_delivery_markup );
						}
						$subtotal_plus_delivery_service_markup += number_format( $price_delivery_markup, 2 );
						$subtotal += number_format( $price, 2 );
						$dish->_options[] = $option;
					}
				}
				$this->_dishes[] = $dish;
			}
			$this->type = 'web';
		}

		// to make sure the value will be 2 decimals
		$this->delivery_service_markup_value = number_format( $subtotal_plus_delivery_service_markup - $subtotal, 2 );

		$this->_card = $params['card'];

		// price and price_plus_delivery_markup #2236
		$this->price = Util::ceil( $subtotal, 2 );
		$this->price_plus_delivery_markup = Util::ceil( $subtotal_plus_delivery_service_markup, 2 );

		// delivery fee
		$this->delivery_fee = ($this->restaurant()->delivery_fee && $this->delivery_type == 'delivery') ? $this->restaurant()->delivery_fee : 0;

		// service fee for customer
		$this->service_fee = $this->restaurant()->fee_customer;
		$serviceFee = ($this->price + $this->delivery_fee) * Util::ceil(($this->service_fee/100),2);

		if( $this->campus_cash && $this->campusCashFee() ){
			$serviceFee += number_format(($this->price + $this->delivery_fee) * ($this->campusCashFee()/100),2);
		}

		$serviceFee = Util::ceil( $serviceFee, 2);
		$totalWithFees = $this->price + $this->delivery_fee + $serviceFee;
		$totalWithFees = Util::ceil( $totalWithFees, 2);

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
			// tip - percent
			/* 	- to calculate the tip it must use as reference the price with mark up
						because the marked up price was the one shown the the user
					- see talk between pererinha and david at hipchat 02/17/2014
						https://github.com/crunchbutton/crunchbutton/issues/2248#issuecomment-35381055 */
			$tip = ( $this->price_plus_delivery_markup * ( $this->tip / 100 ) );
			$tip = Util::ceil( $tip, 2 );
			$this->tip_type = static::TIP_PERCENT;
		}

		// tax
		/* 	- taxes should be calculated using the price without markup
				- if restaurant uses 3rd party delivery service remove the delivery_fee
				- see #2236 and #2248
				-> Removed the Util::ceil - see #2613
				*/
		if($this->restaurant()->delivery_service){
			$baseToCalcTax = $this->price;
		} else {
			$baseToCalcTax = $this->price + $this->delivery_fee;
		}

		$this->tax = $this->restaurant()->tax;
		$tax = $baseToCalcTax * ( $this->tax / 100 );
		$tax = number_format( round( $tax, 2 ), 2 );

		if($this->restaurant()->delivery_service){
			$this->final_price = Util::ceil( $totalWithFees  + $tax, 2 ); // price
			$this->final_price_plus_delivery_markup = Util::ceil( $this->final_price + $this->delivery_service_markup_value + $tip, 2 );
		} else {
			$this->final_price = Util::ceil( $totalWithFees + $tip + $tax, 2 ); // price
			$this->final_price_plus_delivery_markup = Util::ceil( $this->final_price + $this->delivery_service_markup_value, 2 );
		}

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

		// Block orders from this "customer" #6136
		if( c::user()->id_user ){
			if( Crunchbutton_Blocked::isUserBlocked( c::user()->id_user ) ){
				$errors['error'] = Crunchbutton_Blocked::getMessage();
			}
		}
		if( $this->phone ){
			if( Crunchbutton_Blocked::isPhoneNumberBlocked( $this->phone ) ){
				$errors['error'] = Crunchbutton_Blocked::getMessage();
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

		// check if the restaurant accepts campus cash payment method
		if( $this->campus_cash ){
			if( $this->restaurant()->campusCash() ){
				if( $this->campus_cash && !$params[ 'campusCash' ] ){
					$errors['campusCash'] = 'Please fill the field '.$this->restaurant()->campusCashName().'.';
				} else {
					if( $this->campus_cash ){
						$this->campusCash = $this->restaurant()->campusCashValidate( $params[ 'campusCash' ] );
					}

					if( $this->campus_cash && !$this->campusCash ){
						$errors['campusCashInvalid'] = 'Please enter a valid '.$this->restaurant()->campusCashName().'.';
					}
				}
			} else {
				$errors['payment_method'] = 'Please select a valid payment method.';
			}
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

		$this->giftCardInviter = false;

		// Find out if the user posted a gift card code at the notes field and get its value
		$this->giftcardValue = 0;
		if ( trim( $this->notes ) != '' ){
			$totalOrdersByPhone = self::totalOrdersByPhone( $this->phone );
			if( $totalOrdersByPhone < 1 ){
				$words = preg_replace( "/(\r\n|\r|\n)+/", ' ', $this->notes );
				$words = explode( ' ', $words );
				$words = array_unique( $words );
				foreach( $words as $word ){
					$giftCardAdded = false;
					// At first check if it is an user's invite code - rewards: two way gift cards #2561
					$reward = new Crunchbutton_Reward;
					$add_points = $reward->getRefered();
					$delivery_free_points = $reward->pointsToGetDeliveryFree();
					$inviter = $reward->validateInviteCode( $word );
					if( $totalOrdersByPhone <= 1 && $inviter ){
						// get the value of the discount
						if( $inviter[ 'id_admin' ] ){

							if( $add_points == $delivery_free_points ){
								$value = $this->restaurant()->delivery_fee;
								$admin_credit = $reward->adminRefersNewUserCreditAmount();
								$this->giftCardInviter = [ 'id_user' => $inviter[ 'id_user' ], 'id_admin' => $inviter[ 'id_admin' ], 'value' => $value, 'word' => $word, 'admin_credit' => $admin_credit ];
								if( $value ){
									$this->giftcardValue = $value;
									break;
								}
							} else {
								$value = $reward->getReferredDiscountAmount();
								$admin_credit = $reward->adminRefersNewUserCreditAmount();
								$this->giftCardInviter = [ 'id_user' => $inviter[ 'id_user' ], 'id_admin' => $inviter[ 'id_admin' ], 'value' => $value, 'word' => $word, 'admin_credit' => $admin_credit ];
								if( $value ){
									$this->giftcardValue = $value;
									break;
								}
							}
						} elseif( $inviter[ 'id_user' ] ){
							$referral = new Crunchbutton_Referral();
							$referral->id_admin_inviter = null;
							$referral->id_user_inviter = $inviter[ 'id_user'];
							$referral->id_user_invited = $this->id_user;
							$referral->admin_credit = null;
							$referral->invite_code = $word;
							$referral->new_user = 1;
							$referral->date = date('Y-m-d H:i:s');
							$referral->save();

							$settings = $reward->loadSettings();
							// if the amount of points is the same points that gives the customer a delivery free
							// it just add credit to customer
							if( $add_points == $delivery_free_points ){
							$credits_amount = $this->restaurant()->delivery_fee;
								if( $credits_amount ){
									$reward->saveRewardAsCredit( [ 	'id_user' => $this->id_user,
																									'value' => $credits_amount,
																									'id_order' => null,
																									'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_CASH,
																									'id_referral' => $referral->id_referral,
																									'note' => 'Cash Invited by: ' . $inviter[ 'id_user'] . ' code: ' . $word,
																								] );
								}
							} else {
								$credits_amount = $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT ];
								if( $credits_amount ){
									$reward->saveRewardAsCredit( [ 	'id_user' => $this->id_user,
																									'value' => $credits_amount,
																									'id_order' => null,
																									'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_CASH,
																									'id_referral' => $referral->id_referral,
																									'note' => 'Cash Invited by: ' . $inviter[ 'id_user'] . ' code: ' . $word,
																								] );
								}

								$credits_amount = $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_VALUE ];
								if( $credits_amount ){
									$reward->saveRewardAsCredit( [ 	'id_user' => $this->id_user,
																									'value' => $credits_amount,
																									'id_order' => null,
																									'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_POINT,
																									'id_referral' => $referral->id_referral,
																									'note' => 'Points Invited by: ' . $inviter[ 'id_user'] . ' code: ' . $word,
																								] );
								}
							}


							$credits_amount = $settings[ Crunchbutton_Reward::CONFIG_KEY_REFER_NEW_USER_AMOUNT ];
							if( $credits_amount ){
								$reward->saveRewardAsCredit( [ 	'id_user' => $inviter[ 'id_user'],
																								'value' => $credits_amount,
																								'id_order' => null,
																								'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_CASH,
																								'id_referral' => $referral->id_referral,
																								'note' => 'Cash Invited ID: ' . $this->id_user . ' code: ' . $word,
																							] );
							}

							$credits_amount = $settings[ Crunchbutton_Reward::CONFIG_KEY_REFER_NEW_USER_VALUE ];
							if( $credits_amount ){
								$reward->saveRewardAsCredit( [ 	'id_user' => $inviter[ 'id_user'],
																								'value' => $credits_amount,
																								'id_order' => null,
																								'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_POINT,
																								'id_referral' => $referral->id_referral,
																								'note' => 'Points Invited ID: ' . $this->id_user . ' code: ' . $word,
																							] );
							}

						}

					}
				}
			}
			// if the code doesn't belong to a user check if it belongs to a gift card
			if( !$this->giftCardInviter ) {

				$giftcards = Crunchbutton_Promo::validateNotesField( $this->notes, $this->id_restaurant, $this->phone );
				foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
					if( $giftcard->id_promo ){
						if( !$giftCardAdded ){
							$this->giftcardValue = $giftcard->value;
							$giftCardAdded = true;
							break;
						}
					}
				}
			}
			$_notes = $giftcards[ 'notes' ];
		}

		Log::debug([
			'issue' => '#1551',
			'method' => 'process',
			'$this->final_price' => $this->final_price,
			'giftcardValue'=> $this->giftcardValue,
			'$_notes' => $_notes,
			'$this->notes' => $this->notes
		]);

		$delivery_free = false;
		if( $params['use_delivery_points'] ){
			// Point redemption system improvements for customer-customer referrals #4248
			// https://github.com/crunchbutton/crunchbutton/issues/5092#issuecomment-90966989
			// of the same amount of the delivery order
			if ( $this->restaurant()->hasDeliveryService() && $this->delivery_type == 'delivery' && $this->delivery_fee && $this->pay_type == 'card' ) {
				$reward = new Crunchbutton_Reward;
				$reward = $reward->loadSettings();
				$user_points = Crunchbutton_Credit::points( $user->id_user );
				if( $user_points >= intval( $reward[ Crunchbutton_Reward::CONFIG_KEY_MAX_CAP_POINTS ] ) ){
					$delivery_free = true;
					// Add credit

					$credit_amount = $this->delivery_fee;

					if( $serviceFee ){
						$credit_amount += $serviceFee;
					}

					$credit = new Crunchbutton_Credit();
					$credit->id_user = $this->id_user;
					$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
					$credit->id_restaurant = $this->id_restaurant;
					$credit->id_promo = null;
					$credit->date = date('Y-m-d H:i:s');
					$credit->value = $credit_amount;
					$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
					$credit->paid_by = 'CRUNCHBUTTON';
					$credit->note = 'Reward: delivery free';
					$credit->save();
				}
			}
		}

		// process the payment
		$res = $this->verifyPayment();

		// failed to process the card
		if ($res !== true) {
			Log::debug( [
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
				'geomatched' 		=> $params['geomatched'],
				'cart' 					=> $params['cart'],
				'type' 					=> 'order-log'
			] );

			// Remove the delivery free credit
			if( $delivery_free ){

				$credits_amount = $this->delivery_fee;
				if( $serviceFee ){
					$credits_amount += $serviceFee;
				}

				$credit = new Crunchbutton_Credit();
				$credit->id_user = $this->id_user;
				$credit->type = Crunchbutton_Credit::TYPE_DEBIT;
				$credit->id_restaurant = $this->id_restaurant;
				$credit->id_promo = null;
				$credit->date = date('Y-m-d H:i:s');
				$credit->value = $credits_amount;
				$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
				$credit->paid_by = 'CRUNCHBUTTON';
				$credit->note = 'Reward: removed delivery free cash';
				$credit->save();
			}

			return $res['errors'];

		// successfully processed the card
		} else {
			$this->txn = $this->transaction();
		}

		// Remove the user points if they were used to get free delivery
		if( $delivery_free ){
			$free_delivery_points = intval( $reward[ Crunchbutton_Reward::CONFIG_KEY_MAX_CAP_POINTS ] );
			$credit = new Crunchbutton_Credit();
			$credit->id_user = $this->id_user;
			$credit->type = Crunchbutton_Credit::TYPE_DEBIT;
			$credit->id_restaurant = null;
			$credit->id_promo = null;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = $free_delivery_points;
			$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_POINT;
			$credit->paid_by = 'CRUNCHBUTTON';
			$credit->note = 'Reward: removed delivery free points';
			$this->reward_delivery_free = 1;
			$credit->save();
		}

		$user->location_lat = $params['lat'];
		$user->location_lon = $params['lon'];

		$this->location_lat = $params['lat'];
		$this->location_lon = $params['lon'];

		$user->name = $this->name;
		$user->email = $params['email'];
		$user->phone = $this->phone;

		$phone = Crunchbutton_Phone::byPhone( $this->phone );
		$user->id_phone = $phone->id_phone;

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

			// if the user gave us a new card, store it because thats the one we used
			if (!$payment_type || $this->_card['id'] || $this->campus_cash) {

				if( $this->campus_cash && $this->_campus_cash_sha1 ){

					$last_digits = substr( $this->campusCash, -3 );

					// create a new payment type
					$payment_type = new User_Payment_Type([
						'id_user' => $user->id_user,
						'active' => 1,
						'card' => $last_digits,
						'card_type' => Crunchbutton_User_Payment_Type::CARD_TYPE_CAMPUS_CASH,
						'stripe_id' => $this->_campus_cash_sha1,
						'stripe_customer' => $this->_customer,
						'card_exp_year' => null,
						'card_exp_month' => null,
						'date' => date('Y-m-d H:i:s')
					]);

					$payment_type->save();

					// Desactive others payments
					$payment_type->desactiveOlderPaymentsType();

				} else {
					// create a new payment type
					$payment_type = new User_Payment_Type([
						'id_user' => $user->id_user,
						'active' => 1,
						'card' => $this->_card['lastfour'] ? ('************'.$this->_card['lastfour']) : '',
						'card_type' => $this->_card['card_type'],
						'card_exp_year' => $this->_card['year'],
						'card_exp_month' => $this->_card['month'],
						'date' => date('Y-m-d H:i:s')
					]);

					switch (Crunchbutton_User_Payment_Type::processor()) {
						case 'stripe':
							$payment_type->stripe_id = $this->_paymentType;
							$user->stripe_id = $this->_customer;
							$user->save();
							break;
					}

					$payment_type->save();

					// Desactive others payments
					$payment_type->desactiveOlderPaymentsType();
				}
			}
			$this->id_user_payment_type = $payment_type->id_user_payment_type;
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

		if ($processType != static::PROCESS_TYPE_RESTAURANT) {
			c::auth()->session()->id_user = $user->id_user;
			c::auth()->session()->generateAndSaveToken();
		}

		$agent = Crunchbutton_Agent::getAgent();
		$this->id_agent = $agent->id_agent;

		if (c::auth()->session()->id_session != '') {
			$this->id_session = c::auth()->session()->id_session;
		}

		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->delivery_service = $this->restaurant()->hasDeliveryService();
		$this->id_community = $this->restaurant()->community()->id_community;

		$this->geomatched = ( intval( $params['geomatched'] ) ? 1 : 0 );

		$phone = Crunchbutton_Phone::byPhone( $this->phone );
		$this->id_phone = $phone->id_phone;

		// Get the informed eta before save the order
		if( $this->restaurant() ){
			$informed_eta = $this->restaurant()->smartETA();
		}

		$this->save();

		// register informed eta
		if( $informed_eta ){
			$eta = new Order_Eta([
				'id_order' => $this->id_order,
				'time' => $informed_eta,
				'distance' => null,
				'date' => date('Y-m-d h:i:s'),
				'method' => Crunchbutton_Order_Eta::METHOD_INFORMED_ETA
			]);
			$eta->save();
		}

		Log::debug( [ '$this->giftCardInviter' => $this->giftCardInviter, '$this->notes' => $this->notes ] );

		// If the payment succeds then redeem the gift card
		if ( trim( $this->notes ) != '' ){

			if( $this->giftCardInviter ){

				// remove the code from notes
				$__order = Order::o( $this->id_order );
				$__order->notes = str_replace( $this->giftCardInviter[ 'word' ], '', $__order->notes );
				$__order->save();

				$referral = new Crunchbutton_Referral();
				$referral->id_admin_inviter = $this->giftCardInviter[ 'id_admin'];
				$referral->id_user_inviter = $this->giftCardInviter[ 'id_user'];
				$referral->id_user_invited = $this->id_user;
				$referral->id_order = $this->id_order;
				$referral->admin_credit = $this->giftCardInviter[ 'admin_credit'];
				$referral->invite_code = $this->giftCardInviter[ 'word'];
				$referral->new_user = 1;
				$referral->date = date('Y-m-d H:i:s');
				$referral->save();

				$reward = new Crunchbutton_Reward;
				// the new user earns discount
				if( $this->giftCardInviter[ 'id_admin'] ){
					$notes = 'Inviter ID: ' . $this->giftCardInviter[ 'id_admin'] . ' code: ' . $this->giftCardInviter[ 'word'];
				}

				$reward->saveRewardAsCredit( [ 	'id_user' => $user->id_user,
																				'value' => $this->giftCardInviter[ 'value'],
																				'id_order' => $this->id_order,
																				'id_referral' => $referral->id_referral,
																				'credit_type' => Crunchbutton_Credit::CREDIT_TYPE_CASH,
																				'note' => $notes,
																			] );

				if( $this->giftCardInviter[ 'id_admin'] ){
					$credits_amount = $reward->adminRefersNewUserCreditAmount();
					Log::debug([ 'id_admin' => $this->giftCardInviter[ 'id_admin'], '$credits_amount' => $credits_amount ]);
				}
			} else {
				$giftcards = Crunchbutton_Promo::validateNotesField( $this->notes, $this->id_restaurant, $this->phone );
				$giftCardAdded = false;
				foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
					if( $giftcard->id_promo ){
						if( !$giftCardAdded ){
							$giftcard->addCredit( $user->id_user, $this->delivery_fee );
						}
						$giftCardAdded = true;
					}
				}
				$_order = Order::o( $this->id_order );
				$_order->notes = $giftcards[ 'notes' ];
				$_order->save();
				$this->notes = $_order->notes;
			}
		}


		$this->debitFromUserCredit( $user->id_user );

		if ( $params['make_default'] == 'true' ) {
			$preset = $user->preset($this->restaurant()->id_restaurant);
			if ($preset->id_preset) {
				$preset->delete();
			}
		}

		if ($this->_dishes) {
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
		}

		$this->que();

		$order = $this;

		if ( $params['make_default'] == 'true' ) {
			Preset::cloneFromOrder($order);
		}

		if ($processType != static::PROCESS_TYPE_RESTAURANT) {

			// Reward
			$reward = new Crunchbutton_Reward;
			// rewards: earn points when an order has been placed #2458
			$points = $reward->processOrder( $order->id_order );
			if( floatval( $points ) > 0 ){
				if( User_Auth::userHasAuth( $order->id_user ) ){
					$reward->saveReward( [ 'id_order' => $order->id_order, 'id_user' => $order->id_user, 'points' => $points, 'note' => 'points by order #' . $order->id_order ] );
				}
			}
			// rewards: 4x points when ordering 2 days in a row #3434
			$points = $reward->orderTwoDaysInARow( $order->id_user );
			if( floatval( $points ) > 0 ){
				if( User_Auth::userHasAuth( $order->id_user ) ){
					$reward->saveReward( [ 'id_order' => $order->id_order, 'id_user' => $order->id_user, 'points' => $points, 'note' => 'points by 2 days in a row' ] );
				}
			}
			if( !$points ){
				// rewards: 2x points when ordering in same week #3432
				$points = $reward->orderTwiceSameWeek( $order->id_user );
				if( floatval( $points ) > 0 ){
					if( User_Auth::userHasAuth( $order->id_user ) ){
						$reward->saveReward( [ 'id_order' => $order->id_order, 'id_user' => $order->id_user, 'points' => $points, 'note' => 'points by ordering twice same week' ] );
					}
				}
			}
		}

		// Referral disabled because of the new system:
		// rewards: two way gift cards #2561: https://github.com/crunchbutton/crunchbutton/issues/2561
		/**
		*******************************************************************************
		if( false && Crunchbutton_Referral::isReferralEnable() ){
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

					// Reward
					$reward = new Crunchbutton_Reward;
					$points = $reward->getRefered();
					if( floatval( $points ) > 0 ){
						$reward->saveReward( [ 'id_order' => $this->id_order, 'id_user' => $this->id_user, 'points' => $points, 'note' => 'points by getting referred O#' . $this->id_order . ' U#' . $_inviter->id_user ] );
					}

					$points = $reward->getReferNewUser();
					if( floatval( $points ) > 0 ){
						$reward->saveReward( [ 'id_order' => $this->id_order, 'id_user' => $_inviter->id_user, 'points' => $points, 'note' => 'points by reffering a new user O#' . $this->id_order . ' U#' . $this->id_user ] );
					}

					Log::debug([ 'inviter_code' => $inviter_code, 'totalOrdersByPhone' => $totalOrdersByPhone, 'type' => 'referral', 'pay_type' => $this->pay_type ]);

				}
				Crunchbutton_Referral::removeCookie();
			}
		}
		*************************************************************************
		*/

		$this->removeCouponCodesInTheNotes();

		return true;
	}

	public function removeCouponCodesInTheNotes(){
		// fix for #4256
		$_order = Crunchbutton_Order::o( $this->id_order );
		if ( trim( $_order->notes ) != '' ){
			$words = str_replace( "\n", ' ', $this->notes );
			$words = explode( ' ', $words );
			$words = array_unique( $words );
			$reward = new Crunchbutton_Reward;
			foreach( $words as $word ){
				$inviter = $reward->validateInviteCode( $word );
				if( $inviter ){
					$_order->notes = str_replace( $word, '', $_order->notes );
				}
			}
			$_order->save();
		}
	}

	public function campusCashName(){
		return ( $this->restaurant()->campusCashName() ? $this->restaurant()->campusCashName() : 'Student ID Number' );
	}

	public function campusCashReceiptInfo(){
		return $this->restaurant()->campusCashReceiptInfo();
	}

	public function campusCashLastDigits(){
		$paymentType = $this->paymentType();
		return $paymentType->card;
	}

	public function campusCashFee(){
		return $this->restaurant()->campusCashFee();
	}

	public function calcFinalPriceMinusUsersCredit(){
		$final_price = $this->final_price_plus_delivery_markup;
		if( $this->pay_type == 'card' ){
			$final_price = $final_price - $this->giftcardValue;
			if( $this->id_user ){
				$chargedByCredit = Crunchbutton_Credit::calcDebitFromUserCredit( $final_price, $this->id_user, $this->id_restaurant, $this->id_order, true );
				$final_price = $final_price - $chargedByCredit;
			}
			Log::debug([ 'issue' => '#1551', 'method' => 'calcFinalPriceMinusUsersCredit', 'final_price_plus_delivery_markup' => $this->final_price_plus_delivery_markup, 'final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, 'delivery_service_markup' => $this->delivery_service_markup ]);
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
		return number_format( $totalCredit, 2 );
	}

	public function charged(){

		if( $this->final_price_plus_delivery_markup && floatval( $this->final_price_plus_delivery_markup ) > 0 ){
			$final_price = $this->final_price_plus_delivery_markup;
		} else {
			$final_price = $this->final_price;
		}
		return number_format( abs( ( $final_price ) - ( $this->chargedByCredit() ) ), 2 );
	}

	public function debitFromUserCredit( $id_user ){
		if( $this->pay_type == 'card' ){
			$final_price = $this->final_price_plus_delivery_markup;
			Crunchbutton_Credit::debitFromUserCredit( $final_price, $id_user, $this->id_restaurant, $this->id_order );
		}
	}

	public static function uuid($uuid) {
		return self::q('select * from `order` where uuid=?', [$uuid]);
	}

	// return an order based on its local_gid - see #3086
	public static function gid( $gid ) {
		return self::q( 'SELECT * FROM `order` WHERE local_gid=?', [$gid]);
	}

	/**
	 * The restaurant to process the order
	 *
	 * @return Crunchbutton_Restaurant
	 */
	public function restaurant() {
		if( !$this->_restaurant ){
			$this->_restaurant = Restaurant::o($this->id_restaurant);
		}
		return $this->_restaurant;
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function accepted() {
		$nl = Notification_Log::q("select * from notification_log where id_order=? and status='accepted'", [$this->id_order]);
		return $nl->count() ? true : false;
	}

	public function fax_succeeds() {
		$nl = Notification_Log::q("select * from notification_log where id_order=? and type='phaxio' and status='success'", [$this->id_order]);
		return $nl->count() ? true : false;
	}

	public function transaction() {
		return $this->_txn;
	}

	public function actions(){
		return Crunchbutton_Order_Action::byOrder( $this->id_order );
	}

	public function date( $reset = false ) {
		if( $reset ){
			$this->_date = null;
		}
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}

	public function dateAtTz( $timezone ) {
		$date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		$date->setTimezone( new DateTimeZone( $timezone ) );
		return $date;
	}

	public function verifyPayment() {
		switch ($this->pay_type) {
			case 'cash':
				$status = true;
				break;

			case 'card':

				// campus cash
				if( $this->campus_cash ){

					$campus_money = new Crunchbutton_Stripe_Campus_Cash;
					$success = $campus_money->store( [
						'campus_cash' => $this->campusCash,
						'name' => $this->name,
						'email' => $this->email ] );

					if( $success && $success[ 'campus_cash_sha1' ] ){
						$this->_campus_cash_sha1 = $success[ 'campus_cash_sha1' ];
						$this->_customer = $success['customer'];
						$status = true;
					}

				} else {

					$user = c::user()->id_user ? c::user() : null;

					if ($user) {
						$paymentType = $user->payment_type();
					}

					// use a stored users card and the apporiate payment type

					if (!$this->_card['id'] && $paymentType->id_user_payment_type) {

						if (Crunchbutton_User_Payment_Type::processor() == 'stripe' && $paymentType->stripe_id) {
							$charge = new Charge_Stripe([
								'card_id' => $paymentType->stripe_id,
								'customer_id' => $user->stripe_id
							]);

						} else {
							// there is a mismatch with stripe and balanced
						}
					}

					// create the objects with no params
					if (!$charge) {
						switch (Crunchbutton_User_Payment_Type::processor()) {
							case 'stripe':
								$charge = new Charge_Stripe([
									'customer_id' => $user->stripe_id
								]);
								break;
						}
					}

					// If the amount is 0 it means that the user used his credit.
					$amount = $this->calcFinalPriceMinusUsersCredit();
					Log::debug([ 'issue' => '#1551', 'method' => 'verifyPayment', '$this->final_price' => $this->final_price,  'giftcardValue'=> $this->giftcardValue, 'amount' => $amount ]);


					// issue #3145
					if ($amount > .5) {
						$r = $charge->charge([
							'amount' => $amount,
							'card' => $this->_card,
							'name' => $this->name,
							'address' => $this->address,
							'email' => $user->email,
							'phone' => $this->phone,
							'user' => $user,
							'restaurant' => $this->restaurant()
						]);
						if ($r['status']) {
							$this->_txn = $r['txn'];
							$this->_user = $user;
							$this->_customer = $r['customer'];
							$this->_paymentType = $r['card'];
							$status = true;
						} else {
							$status = $r;
						}

					} elseif ($amount > 0) {
						// we just gave them 50c or something
						Log::debug([
							'issue' => '#3145',
							'method' => 'verifyPayment',
							'$this->final_price' => $this->final_price,
							'giftcardValue'=> $this->giftcardValue,
							'amount' => $amount
						]);
						$status = true;

					} else {
						$status = true;
					}
				}
				break;
		}
		return $status;
	}

	public static function recent() {
		return self::q('select * from `order` order by `date` DESC');
	}

	public static function deliveryOrders( $hours = 24, $all = false, $admin = null){
		if (c::admin()->getConfig('demo')->value == '1') {
			//$restaurant = Restaurant::q('select * from restaurant where name="devins driver test restaurant"');
			$query = '
				select o.* from `order` o
				left join restaurant r using(id_restaurant)
				where r.name like "%test restaurant%"
				and r.delivery_service=1
				limit 10
			';

		} else {

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->modify( '- ' . $hours . ' hours' );
			$interval = $now->format( 'Y-m-d H:i:s' );

			if( !$all ){
				if (!$admin) {
					$admin = c::admin();
				}
				$deliveryFor = $admin->allPlacesHeDeliveryFor();
				if( count( $deliveryFor ) == 0 ){
					$deliveryFor[] = 0;
				}
				$where = 'WHERE o.id_restaurant IN( ' . join( ',', $deliveryFor ) . ' )';
			} else {
				$where = 'WHERE 1=1 ';
			}

			$where .= ' AND o.delivery_service = true AND date > ? ';
			$query = 'SELECT DISTINCT( o.id_order ) id, o.* FROM `order` o ' . $where . ' ORDER BY o.id_order';
		}
		return Order::q($query, [$interval]);
	}

	public static function deliveryOrdersByCommunity($hours, $id_community){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- ' . $hours . ' hours' );
		$interval = $now->format( 'Y-m-d H:i:s' );

		$query = 'SELECT DISTINCT( o.id_order ) id, o.* FROM `order` o WHERE o.delivery_service=true and date > ? and id_community = ? ORDER BY o.id_order';
		return Order::q($query, [$interval, $id_community]);
	}

	/*
	Logic to make sure that admin doesn't see orders :

	1. Order action has been taken.
	2. Order is not in the priority list and the community has the logistics system activated and it's been more
	    than a minute
	3. Order is in the priority list and priority has expired.
	    IMPORTANT: This logic here does not screen by admin, and so the priority expiration must be the same for all
	     drivers.  Otherwise this code will break.
	4. Order is in the priority list and priority had not expired and admin is in the priority list and admin does not have low priority.

	IMPORTANT:
  	Note that if a new low priority type is added, this query may need to be rewritten.
	*/
	public static function deliveryOrdersForAdminOnly( $hours = 24, $admin = null){

		if (c::admin()->getConfig('demo')->value == '1') {
			//$restaurant = Restaurant::q('select * from restaurant where name="devins driver test restaurant"');
			//TODO: This code was left untouched from the original deliveryOrders code.  May not work as expected
			// in the demo environment.
			$query = '
				select o.* from `order` o
				left join restaurant r using(id_restaurant)
				where r.name like "%test restaurant%"
				and r.delivery_service=1
				limit 10
			';
			return Order::q($query);

		} else {

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$nowString = $now->format( 'Y-m-d H:i:s' );
			$now->modify( '- ' . $hours . ' hours' );
			$interval = $now->format( 'Y-m-d H:i:s' );
			$now->modify( '+ ' . $hours . ' hours' );
			// TODO: Hardwired constant here - not ideal
			$now->modify( '- 1 minutes' );
			$interval1Min = $now->format( 'Y-m-d H:i:s' );


			if (!$admin) {
				$admin = c::admin();
			}
			$deliveryFor = $admin->allPlacesHeDeliveryFor();
			if( count( $deliveryFor ) == 0 ){
				$deliveryFor[] = 0;
			}
			$where = 'o.id_restaurant IN( ' . join( ',', $deliveryFor ) . ' )';

			$query = 'SELECT DISTINCT(o.id_order) as id, o.* FROM `order` as o ' .
				'inner join community as c using (id_community) ' .
				'left outer join order_action as oa using (id_order) ' .
				'left outer join order_priority as op using (id_order) where (oa.id_order is not null or ' .
				'(op.id_order is null and ((c.delivery_logistics is null) or (o.date < ? and ' .
				'c.delivery_logistics is not null)))  or (op.id_order is not null and op.priority_expiration < ?) ' .
				'or (op.id_order is not null and op.priority_expiration >= ? and op.id_admin = ? '.
				'and op.priority_given != ?)) and o.delivery_service=true and o.delivery_type = "delivery" and o.date > ? '.
				'and ' . $where . ' ORDER BY o.id_order';
//			$op = Crunchbutton_Order_Priority::PRIORITY_LOW;
//			print "The query params: $nowString, $nowString, $admin->id_admin, $op, $interval\n";
			return Order::q($query, [$interval1Min, $nowString, $nowString, $admin->id_admin,
				Crunchbutton_Order_Priority::PRIORITY_LOW, $interval]);
		}

	}

	public static function outstandingOrders(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now = $now->format( 'Y-m-d' );

		$query = "SELECT id_order,
						TIMESTAMPDIFF(HOUR, o.date, NOW()) AS hours
						FROM `order` o
						WHERE o.delivery_type = 'delivery'
						AND o.delivery_service = true
						AND o.id_order NOT IN
						(SELECT id_order
						FROM order_action
						WHERE type = 'delivery-delivered')
						-- remove the commment below to get this the orders from today
						 AND o.date BETWEEN '{$now} 00:00:00' AND '{$now} 23:59:59'
						HAVING hours >= 2 ORDER BY id_order DESC ";
		return Order::q( $query );

	}

	public static function deliveryOrderTimes( $hours = 24, $all = false ){

		$id_admin = c::admin()->id_admin;
		if( !$all ){
			$admin = Admin::o( $id_admin );
			$deliveryFor = $admin->allPlacesHeDeliveryFor();
			if( count( $deliveryFor ) == 0 ){
				$deliveryFor[] = 0;
			}
			$where = 'WHERE o.id_restaurant IN( ' . join( ',', $deliveryFor ) . ' )';
		} else {
			$where = 'WHERE 1=1 ';
		}

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$now->modify( ' - ' . $hours . ' hour' );
		$interval = $now->format( 'Y-m-d H:i:s' );
		$where .= ' AND o.delivery_service = true ';
		$where .= ' AND date > ? ';

		$query = 'SELECT DISTINCT( o.id_order ) id, o.* FROM `order` o ' . $where . ' ORDER BY o.id_order';

		return Order::q( $query,[$interval]);
	}

	public function revenueByAdminPeriod( $id_admin, $date_start, $date_end ){
		//convert to La timezone
		$date_start = new DateTime( $date_start, new DateTimeZone( c::config()->timezone ) );
		$date_end = new DateTime( $date_end, new DateTimeZone( c::config()->timezone ) );

		//get orders at this period
		$query = 'SELECT DISTINCT( o.id_order ) id, oa.* FROM `order` o
								INNER JOIN order_action oa ON oa.id_order = o.id_order
								WHERE
									oa.id_admin = ?
									AND o.date >= ?
									AND o.date <= ?';
		return Crunchbutton_Order_Action::q( $query, [$id_admin, $date_start->format( 'Y-m-d H:i:s' ), $date_end->format( 'Y-m-d H:i:s' )]);
	}

	public static function deliveredByCBDrivers( $search ){

		$where = ' WHERE 1 = 1';
		$innerJoin = ' ';

		if( $search[ 'id_admin' ] ){
			$innerJoin .= ' INNER JOIN order_action oa ON oa.id_order = o.id_order AND oa.id_admin = ' . $search[ 'id_admin' ];
		}

		if( $search[ 'id_restaurant' ] ){
			$where .= ' AND o.id_restaurant = ' . $search[ 'id_restaurant' ];
		}

		if ( $search[ 'start' ] ) {
			$s = new DateTime( $search[ 'start' ] );
			$where .= ' AND DATE( `o.date`) >= \'' . $s->format( 'Y-m-d' ) . '\'';
		}

		if ( $search[ 'end' ] ) {
			$s = new DateTime( $search[ 'end' ] );
			$where .= ' AND DATE( `o.date` ) <= \'' . $s->format( 'Y-m-d' ) . '\'';
		}

		if( $search[ 'limit' ] ){
			$limit = 'LIMIT '. $search[ 'limit' ];
		} else {
			$limit = 'LIMIT 25';
		}

		$query = 'SELECT DISTINCT(o.id_order) id, o.* FROM `order` o
							INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.delivery_service = true
							' . $innerJoin . $where . '
							ORDER BY o.id_order DESC ' . $limit;

		return Order::q( $query );

	}

	public static function find($search = []) {
		$query = 'SELECT credit.total AS credit, promo.total AS gift_card, support.id_support, a.browser, a.os, o.* from `order` o left join restaurant using(id_restaurant)
								LEFT JOIN( SELECT id_order_reference, SUM( value ) as total FROM credit WHERE ( credit_type = "cash" OR credit_type != "point" ) AND id_order_reference IS NOT NULL AND id_promo IS NULL GROUP BY id_order_reference ) credit ON credit.id_order_reference = o.id_order
								LEFT JOIN( SELECT id_order_reference, SUM( value ) as total FROM promo WHERE id_order_reference IS NOT NULL GROUP BY id_order_reference ) promo ON promo.id_order_reference = o.id_order
								LEFT JOIN ( SELECT MAX( id_support ) AS id_support, id_order FROM support WHERE id_order IS NOT NULL GROUP BY id_order ) support ON support.id_order = o.id_order
								LEFT JOIN agent a ON a.id_agent = o.id_agent
								WHERE o.id_order IS NOT NULL';

		if ($search['env']) {
			$query .= ' and o.env="'.$search['env'].'" ';
		}
		if ($search['processor']) {
			$query .= ' and o.processor="'.$search['processor'].'" ';
		}
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(o.`date`)>="'.$s->format('Y-m-d').'" ';
		}
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(o.`date`)<="'.$s->format('Y-m-d').'" ';
		}

		$hasPermissionToAllRestaurants = c::admin()->permission()->check( [ 'global', 'orders-all' ] );

		if ($search['restaurant']) {
			if( $hasPermissionToAllRestaurants || c::admin()->permission()->check( [ "orders-list-restaurant-{$search['restaurant']}" ] ) ){
				$query .= ' and `o`.id_restaurant="'.$search['restaurant'].'" ';
			} else {
				exit;
			}
		} else {
			// If the user doesnt have permission to all restaurants show just the ones he could see
			if( !$hasPermissionToAllRestaurants ){
				$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirOrders();
				$restaurants[] = 0;
				$query .= ' and o.id_restaurant IN ( ' . join( $restaurants, ',' ) . ')';
			}
		}

		if ($search['community']) {
			$query .= ' and `restaurant`.community="'.$search['community'].'" ';
		}
		if ($search['order']) {
			$query .= ' and o.id_order="'.$search['order'].'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and o.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and o.address not like "%'.substr($word,1).'%" ';
					$qn .= ' and o.phone not like "%'.substr($word,1).'%" ';
					$qn .= ' and `restaurant`.name not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (o.name like "%'.$word.'%"
						or o.id_order = "'.$word.'"
						or o.address like "%'.$word.'%"
						or `restaurant`.name like "%'.$word.'%"
						or REPLACE( o.phone, "-", "" ) like "%'. ( str_replace( '-' , '',  $word ) ) .'%")
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

	public function dishes( $forceLoad = false ) {
		if( $forceLoad ){
			$this->_dishes = false;
			unset( $this->_dishes );
		}
		if ( !$this->_dishes ) {
			$this->_dishes = Order_Dish::q( 'SELECT * FROM order_dish WHERE id_order = ?', [$this->id_order]);
		}
		return $this->_dishes;
	}

	public function tip() {
		if( $this->tip_type == self::TIP_NUMBER ){
			return number_format( $this->tip, 2 );
		} else {
			/* 	- to calculate the tip it must use as reference the price with mark up
						because the marked up price was the one shown the the user
					- see talk between pererinha and david at hipchat 02/17/2014
						https://github.com/crunchbutton/crunchbutton/issues/2248#issuecomment-35381055 */
			if( $this->price_plus_delivery_markup && $this->price_plus_delivery_markup > 0 ){
				$tip = ( $this->price_plus_delivery_markup * ( $this->tip / 100 ) );
			} else {
				$tip = ( $this->price * ( $this->tip / 100 ) );
			}
			return number_format( $tip, 2 );
		}
	}

	public function tax() {
		/* 	- taxes should be calculated using the price without markup
				- if restaurant uses 3rd party delivery service remove the delivery_fee
				- see #2236 and #2248
				-> Removed the Util::ceil - see #2613
				*/
		if($this->delivery_service){
			$baseToCalcTax = $this->price;
		} else {
			$baseToCalcTax = $this->price + $this->delivery_fee;
		}
		return $tax = number_format( round( $baseToCalcTax * ( $this->tax / 100 ), 2 ), 2 );;
	}

	public function deliveryFee() {
		return number_format($this->delivery_fee,2);
	}

	public function serviceFee() {
		$fee = number_format(($this->price + $this->delivery_fee) * ($this->service_fee/100),2);
		if( $this->campus_cash && $this->campusCashFee() ){
			$fee += number_format(($this->price + $this->delivery_fee) * ($this->campusCashFee()/100),2);
		}
		return $fee;
	}

	public function cbFee() {
		return ($this->restaurant_fee_percent()) * ($this->price) / 100;
	}

	public function customer_fee(){
		return ($this->restaurant()->fee_customer) * ($this->price) / 100;
	}

	public function restaurant_fee_percent(){
		return ( !is_null( $this->fee_restaurant ) ) ? $this->fee_restaurant : $this->restaurant()->fee_restaurant;
	}

	public function fee(){
		if( $this->restaurant()->fee_on_subtotal ){
			return $this->cbFee();
		} else {
			return $this->restaurant_fee_percent() * ($this->final_price) / 100;
		}
	}

	public function notify(){
		$this->notifyRestaurants();
		if($this->restaurant()->delivery_service){
			$this->notifyDrivers();
		}
	}

	public function notifyRestaurants() {
		foreach ( $this->restaurant()->notifications() as $n ) {
			// admin type is depreciated. so lets not use it
			if ($n->type == Crunchbutton_Notification::TYPE_ADMIN) {
				continue;
			}
			Log::debug([ 'order' => $this->id_order, 'action' => 'sending notification', 'type' => $n->type, 'to' => $n->value, 'type' => 'notification']);
			$n->send( $this );
		}
	}

	// return a list of drivers that are currently working for the community to notify
	public function getDriversToNotify() {
		$drivers = Crunchbutton_Community_Shift::driversCouldDeliveryOrder($this->id_order);
		return $drivers;
	}


	public function notifyDrivers(){

		if( $this->ignoreDrivers ){
			return;
		}

		$order = $this;
		$needDrivers = false;
		$hasDriversWorking = false;

		// check if the restaurant is using our delivery system
		if($order->restaurant()->delivery_service){
			$needDrivers = true;
		}

		$drivers = $this->getDriversToNotify();
		if( $drivers ){
			foreach( $drivers as $driver ){
				foreach( $driver->activeNotifications() as $adminNotification ){
					$adminNotification->send( $order );
					$hasDriversWorking = true;
					$message = '#'.$order->id_order.' sending driver notification to ' . $driver->name . ' #' . $adminNotification->value;
					Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
				}
			}
		}

		Crunchbutton_Admin_Notification_Log::register( $this->id_order, ' Order::notifyDrivers' );

		if( $needDrivers && !$hasDriversWorking ){
			Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
		}
	}

	public function checkBeforeNotifications($drivers){

		if( $this->ignoreDrivers ){
			return null;
		}

		$retVal = ['needDrivers' => false, 'hasDriversWorking' => false];

		// check if the restaurant is using our delivery system
		if($this->restaurant()->delivery_service){
			$retVal['needDrivers'] = true;
		}

		if( $drivers ){
			foreach( $drivers as $driver ){
				if ($driver->activeNotifications()->count() > 0){
					$retVal['hasDriversWorking'] = true;
					break;
				}
			}
		}
		return $retVal;

	}

	public function registerAfterNotifications($id_admin, $seconds){

		Crunchbutton_Admin_Notification_Log::registerWithAdminForLogistics($this->id_order, $id_admin, $seconds, 0);

	}


	public function checkForNoRepsNotifications($needDrivers, $hasDriversWorking){

		if( $needDrivers && !$hasDriversWorking ){
			Crunchbutton_Admin_Notification::warningAboutNoRepsWorking($this);
		}
	}



	public function driver(){
		if( !$this->_driver && $this->id_order ){
			$this->_driver = Admin::q('SELECT a.* FROM order_action oa INNER JOIN admin a ON a.id_admin = oa.id_admin WHERE oa.id_order = ? AND type != "delivery-rejected" ORDER BY id_order_action DESC LIMIT 1', [$this->id_order]);
		}
		return $this->_driver;
	}

	public function resend_notify_drivers(){
		$order = $this;
		Crunchbutton_Admin_Notification_Log::cleanLog( $order->id_order );
		$order->notifyDrivers();
		return true;
	}

	public function resend_notify(){
		$order = $this;
		// Log::debug([ 'order' => $order->id_order, 'action' => 'restarting starting notification', 'type' => 'notification']);
		// Delete all the notification log in order to start a new one
		// Notification_Log::DeleteFromOrder( $order->id_order );
		// Log::debug([ 'order' => $order->id_order, 'action' => 'deleted previous notifications', 'type' => 'notification']);
		Crunchbutton_Admin_Notification_Log::cleanLog( $order->id_order );
		$order->ignoreDrivers = true;
		$order->notify();
		return true;
	}

	public function confirm() {

		Log::debug([ 'order' => $this->id_order, 'action' => 'confirm() - dial confirm call', '$this->confirmed' => $this->confirmed, '$this->restaurant()->confirmation' =>$this->restaurant()->confirmation, 'type' => 'notification']);

		if ($this->confirmed || !$this->restaurant()->confirmation) {
			return;
		}
		// the restaurant asked crunchbutton to call it, stop sending confirmations call - See #2848
		if( $this->asked_to_call ){
			Log::debug([ 'order' => $this->id_order, 'action' => 'asked_to_call() - dial confirm call', '$this->asked_to_call' => $this->asked_to_call, '$this->restaurant()->confirmation' =>$this->restaurant()->confirmation, 'type' => 'notification']);
			return;
		}

		$nl = Notification_Log::q("SELECT * FROM notification_log WHERE id_order=? AND type = 'confirm' AND ( status = 'created' OR status = 'queued' OR status ='success' )", [$this->id_order]);
		if( $nl->count() > 0 ){
			// Log
			Log::debug([ 'order' => $this->id_order, 'count' => $nl->count(), 'action' => 'confirmation call already in process', 'host' => c::config()->host_callback, 'type' => 'notification']);
			return;
		}

		$env = c::getEnv();

		$num = ($env == 'live' ? $this->restaurant()->phone : c::config()->twilio->testnumber);

		// Added new confirmation type: stealth. More 'Stealth confirmation call' #2848
		if( $this->restaurant()->confirmation_type == 'stealth' ){
			$confirmURL = 'http://'.c::config()->host_callback.'/api/order/'.$this->id_order.'/doconfirmstealth';
		} else {
			$confirmURL = 'http://'.c::config()->host_callback.'/api/order/'.$this->id_order.'/doconfirm';
		}

		// Log
		Log::debug([ 'order' => $this->id_order, 'num' => $num, 'confirmURL' => $confirmURL, 'action' => 'dial confirm call', 'count' => $nl->count(), 'num' => $num, 'host' => c::config()->host_callback, 'callback' => $callback, 'type' => 'notification']);
		$log = new Notification_Log;
		$log->type = 'confirm';
		$log->id_order = $this->id_order;
		$log->date = date('Y-m-d H:i:s');
		$log->status = 'created';
		$log->save();


		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingRestaurant,
			'+1'.$num,
			$confirmURL,
			[
				'StatusCallback' => 'http://'.c::config()->host_callback.'/api/notification/'.$log->id_notification_log.'/confirm'
			]
		);

		Log::debug([ 'order' => $this->id_order, 'action' => 'dial confirm call sent', 'confirmURL' => $confirmURL, 'count' => $nl->count(), 'num' => $num, 'host' => c::config()->host_callback, 'callback' => $callback, 'type' => 'notification']);

		$log->remote = $call->sid;
		$log->status = $call->status;
		$log->save();
	}

	public function receipt() {
		$message = Crunchbutton_Message_Sms::greeting( $this->user()->firstName() );
		$message .= $this->message('selfsms');
		Crunchbutton_Message_Sms::send([
			'to' => $this->phone,
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_ORDER
		]);
		$this->sendNativeAppLink();
	}

	public function que() {
		$q = Queue::create([
			'type' => Crunchbutton_Queue::TYPE_ORDER,
			'id_order' => $this->id_order
		]);
	}

	public function queConfirm() {

		$order = $this;

		if ($order->confirmed || !$order->restaurant()->confirmation) {
			return;
		}
		// Check if there are another confirm que, if it does it will not send two confirms. Just one is enough.
		$nl = Notification_Log::q("SELECT * FROM notification_log WHERE id_order=? AND type = 'confirm' AND ( status = 'created' OR status = 'queued' )", [$order->id_order]);
		if( $nl->count() > 0 ){
			return;
		}

		// Query to count the number of confirmations sent
		$nl = Notification_Log::q("SELECT * FROM notification_log WHERE id_order=? AND status='callback' AND `type`='confirm'", [$order->id_order]);

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

		$q = Queue::create([
			'type' => Crunchbutton_Queue::TYPE_ORDER_CONFIRM,
			'id_order' => $this->id_order,
			'seconds' => $confirmationTime / 1000
		]);

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

	public function warningStealthNotConfirmed(){

		$order = $this;

		$isConfirmed = Order::isConfirmed( $this->id_order );

		Log::debug( [ 'order' => $this->id_order, 'action' => 'warningStealthNotConfirmed', 'object' => $order->json(), 'type' => 'notification' ]);

		if ( $isConfirmed ) {
			Log::debug( [ 'order' => $this->id_order, 'action' => 'que warningStealthNotConfirmed ignored', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);
			return;
		}

		$date = $order->date();
		$date = $date->format( 'M jS Y' ) . ' - ' . $date->format( 'g:i:s A' );

		$env = c::getEnv();

		$message = "Please call {$order->restaurant()->name} in {$order->restaurant()->community()->name} ({$order->restaurant()->phone()}). They pressed 2 to say they didn't receive the fax for Order #{$order->id_order}";

		Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningStealthNotConfirmed sending sms', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);

		// keep this ugly true for tests only
		if( $env == 'live' ){

			Crunchbutton_Message_Sms::send([
				'to' => Crunchbutton_Support::getUsers(),
				'message' => $message,
				'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
			]);

		} else {
			Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningStealthNotConfirmed DEV dont send sms', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);
		}

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


		Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningOrderNotConfirmed sending sms', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		if( $env == 'live' ){

			Crunchbutton_Message_Sms::send([
				'to' => Crunchbutton_Support::getUsers(),
				'message' => $message,
				'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
			]);

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
			case 'sms-driver':
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

			// driver dumbphone tweaks #2673
			if( $type == 'sms-driver' ){
					$withOptions = '';
					$selectOptions = '';

					if ($options->count()) {

						foreach ($dish->options() as $option) {
							if ($option->option()->type == 'select') {
								continue;
							}

							if($option->option()->id_option_parent) {
								$optionGroup = Crunchbutton_Option::o($option->option()->id_option_parent);
								$selectOptions .= trim( $optionGroup->name ) . ': ';
								$selectOptions .= trim( $option->option()->name ) . ', ';
							} else {
								if( $withOptions == '' ){
									$withOptions .= 'With: ';
								}
								$withOptions .= trim( $option->option()->name ) . ', ';
							}
						}
						$withOptions = substr( $withOptions, 0, -2 );
						$selectOptions = substr( $selectOptions, 0, -2 );
					}
					$withoutDefaultOptions = '';
					if( $dish->id_order_dish && $dish->id_dish ){
						$optionsNotChoosen = $dish->optionsDefaultNotChoosen();
						$commas = ' ';
						if( $optionsNotChoosen->count() ){
							foreach( $optionsNotChoosen as $dish_option ){
								$withoutDefaultOptions .= $commas . 'No ' . trim( $dish_option->option()->name );
								$commas = ', ';
							}
						}
					}

					if ( $withOptions != '' || $withoutDefaultOptions != '' || $selectOptions != '' ) {
						$foodItem .= ': ';
					}

					if( $withOptions != '' ){
						$withOptions .= '. ';
					}

					if( $withoutDefaultOptions != '' ){
						$withoutDefaultOptions .= '. ';
					}

					if( $selectOptions != '' ){
						$selectOptions .= '. ';
					}

					$foodItem .= $withoutDefaultOptions;
					$foodItem .= $withOptions;
					$foodItem .= $selectOptions;
			} else {
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
			}

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
	public function message($type, $timezone = false) {

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
				// #3925
				if( !$this->restaurant()->formal_relationship ) {
					$msg .= "DO NOT call the restaurant. If you have any questions about your order, text or call us back directly!\n";
					$msg .= "\n";
				} else {
					// Removed the delivery estimate #3925
					if ( $this->delivery_type == 'delivery' && $this->restaurant()->delivery_estimated_time ) {
						$msg .= "Your order will arrive around ";
						$msg .= $this->restaurant()->calc_delivery_estimated_time();
						$msg .= "!\n\n";
					}
				}

				$msg .= "To contact Crunchbutton, text us back.\n\n";
				if ($this->pay_type == self::PAY_TYPE_CASH) {
					$msg .= "Remember to tip!\n\n";
				}
				break;

			case 'support':

				$date = $this->date();

				if( $timezone ){
					$date->setTimeZone( new DateTimeZone( $timezone ) );
				}

				$when = $date->format('M j, g:i a T');

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

			case 'sms-driver-priority':
					$spacer = '/';
					$msg = $this->user()->nameAbbr() . "\n" . strtoupper( $this->pay_type ) . $spacer . $this->restaurant()->name . $spacer . $this->driverInstructionsFoodStatus() . $spacer . $this->driverInstructionsPaymentStatus();
				break;

			case 'sms-driver':
				$spacer = ' / ';
				$msg = "Crunchbutton #".$this->id_order." \n\n";
				$msg .= $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food." \n\nphone: ".preg_replace('/[^\d.]/','',$this->phone).'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= " \naddress: ".$this->address;
				}
				if ($this->notes) {
					$msg .= " \nNOTES: ".$this->notes;
				}
				$msg .= " \n\nRestaurant: {$this->restaurant()->name} / {$this->restaurant()->phone}";
				$msg .= " \n\n";
				// Payment is card and user tipped
				if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && $this->tip ){
					// remove the tip amount from the notification SMS sent to drivers #5418
					// $msg .= 'TIP ' . $this->tip() . $spacer;
				} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && !$this->tip ){
					$msg .= 'TIP BY CASH' . $spacer;
				} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ){
					// Driver Text Bug-delete $ amt from cash text msg #3552
					// $msg .= 'TOTAL ' . $this->final_price . $spacer;
				}
				$msg .= $this->driverInstructionsFoodStatus() . $spacer . $this->driverInstructionsPaymentStatus();
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

				if( $this->type == 'restaurant' ){
					$spacer = ' / ';
					$msg = $this->name . $spacer . strtoupper( $this->pay_type ) . $spacer . preg_replace( '/[^\d.]/', '', $this->phone ) . $spacer;
					if( $this->delivery_type == Crunchbutton_Order::SHIPPING_DELIVERY ){
						$msg .= $this->address . $spacer;
					}
					$msg .= $this->restaurant()->name . $spacer ;
					if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ){
						$msg .= strtoupper( 'Charge Customer $' . $this->final_price_plus_delivery_markup );
						$msg .= $spacer;
						$msg .= strtoupper( 'Pay Restaurant $' . $this->final_price );
					} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ){
						$msg .= strtoupper( 'Customer Paid $' . $this->final_price_plus_delivery_markup );
						$msg .= $spacer;
						if( $this->tip ){
							$msg .= 'TIP ' . $this->tip();
						} else {
							$msg .= 'TIP BY CASH';
						}
					}
				} else {
					$spacer = ' / ';

					if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ){
						$pay_type = 'CARD';
						if( $this->campus_cash ){
							$pay_type = strtoupper( $this->campusCashName() );
						}
					} else {
						$pay_type = 'CASH';
					}

					$msg = $this->name . $spacer . $pay_type . $spacer . strtoupper( $this->delivery_type ) . $spacer . preg_replace( '/[^\d.]/', '', $this->phone ) . $spacer;

					if( $this->delivery_type == Crunchbutton_Order::SHIPPING_DELIVERY ){
						$msg .= $this->address . $spacer;
					}

					$msg .= $this->restaurant()->name ;

					// Payment is card and user tipped
					if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && $this->tip ){
						// Tip should not be in text to drivers #6351
						// $msg .= 'TIP ' . $this->tip();
					} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD && !$this->tip ){
						$msg .= $spacer . 'TIP BY CASH';
					} else if( $this->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ){
						$msg .= $spacer . 'TOTAL ' . $this->final_price_plus_delivery_markup;
					}

					$msg .= $spacer . $this->driverInstructionsFoodStatus() . $spacer . $this->driverInstructionsPaymentStatus();

					if( $this->campus_cash ){
						$msg .= $spacer . 'Check ID at delivery';
					}

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
		unset($out['delivery_service_markup']);
		unset($out['delivery_service_markup_value']);
		unset($out['txn']);

		$out['status'] = $status = $this->status()->last();

		$out['id'] = $this->uuid;

		if( $out[ 'price_plus_delivery_markup' ] && floatval( $out[ 'price_plus_delivery_markup' ] ) > 0 ){
			$out[ 'price' ] = $out[ 'price_plus_delivery_markup' ];
		}

		if( $out[ 'final_price_plus_delivery_markup' ] && floatval( $out[ 'final_price_plus_delivery_markup' ] ) > 0 ){
			$out[ 'final_price' ] = $out[ 'final_price_plus_delivery_markup' ];
		}

		unset( $out[ 'price_plus_delivery_markup' ] );
		unset( $out[ 'final_price_plus_delivery_markup' ] );

		if( isset( $out[ 'type' ] ) && $out[ 'type' ] == 'compressed' ){
			$out['_restaurant_name'] = $out['restaurant_name'];
			$out['_restaurant_permalink'] = $out['restaurant_permalink'];
			$timezone = new DateTimeZone( $out['timezone'] );
			unset( $out['type'] );
			unset( $out['uuid'] );
			unset( $out['restaurant_name'] );
			unset( $out['restaurant_permalink'] );
		} else {
			$date = $this->date();
			$out['date_formated'] = $date->format( 'g:i a, M dS, Y' );
			$out['_restaurant_name'] = $this->restaurant()->name;
			$out['_restaurant_permalink'] = $this->restaurant()->permalink;
			$out['_restaurant_phone'] = $this->restaurant()->phone;
			$out['_restaurant_lat'] = $this->restaurant()->loc_lat;
			$out['_restaurant_lon'] = $this->restaurant()->loc_long;
			$out['_restaurant_address'] = $this->restaurant()->address;
			$out['_restaurant_delivery_estimated_time'] = $this->restaurant()->delivery_estimated_time;
			$out['_restaurant_pickup_estimated_time'] = $this->restaurant()->pickup_estimated_time;
			$calc_delivery_estimated_time = $this->restaurant()->calc_delivery_estimated_time( $date->format( 'Y-m-d H:i:s' ), true );
			$out['_restaurant_delivery_estimated_time_formated'] = $calc_delivery_estimated_time->format( 'g:i a' );
			$calc_pickup_estimated_time = $this->restaurant()->calc_pickup_estimated_time( $date->format( 'Y-m-d H:i:s' ), true );
			$out['_restaurant_pickup_estimated_time_formated'] = $calc_pickup_estimated_time->format( 'g:i a' );
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

		$paymentType = $this->paymentType();
		if( $paymentType->id_user_payment_type ){
			$out['card_ending'] = substr( $paymentType->card, -4, 4 );
		} else {
			$out['card_ending'] = false;
		}

		if( $paymentType->card_type == Crunchbutton_User_Payment_Type::CARD_TYPE_CAMPUS_CASH ){
			$out['card_ending'] = false;
			$out['campus_cash'] = true;
			$out['campus_cash_name'] = $this->campusCashName();
			$out['campus_cash_receipt_info'] = $this->campusCashReceiptInfo();
		}

		$date = new DateTime($this->date);
		$date->setTimeZone($timezone);

		$out['_date_tz'] = $date->format('Y-m-d H:i:s');
		$out['_tz'] = $date->format('T');

		$out['summary'] = $this->orderMessage('summary');
		$out['user_has_auth'] = User_Auth::userHasAuth( $this->id_user );

		$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = ? AND c.type = ? AND credit_type = ? LIMIT 1', [$this->id_order, Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
		if( $out['user_has_auth'] ){
			$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = ? AND c.type = ? AND credit_type = ? LIMIT 1', [$this->id_order, Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
			if( $credit->id_credit ){
				$reward = new Crunchbutton_Reward;
				$points = $reward->processOrder( $this->id_order );
				$sharedTwitter = $reward->orderWasAlreadySharedTwitter( $this->id_order );
				$sharedFacebook = $reward->orderWasAlreadySharedFacebook( $this->id_order );
				$out['reward'] = array( 'points' => Crunchbutton_Credit::formatPoints( $points ), 'shared' => [ 'twitter' => $sharedTwitter, 'facebook' => $sharedFacebook ] );
			}
		} else {
			$reward = new Crunchbutton_Reward;
			$points = $reward->processOrder( $this->id_order );
			$out['reward'] = array( 'points' => Crunchbutton_Credit::formatPoints( $points ) );
		}

		return $out;
	}

	public function paymentType(){
		if( $this->id_user_payment_type ){
			return Crunchbutton_User_Payment_Type::o( $this->id_user_payment_type );
		}
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
					$creditRefounded->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
					$creditRefounded->note = 'Value ' . $credit->value . ' refunded from order: ' . $this->id_order . ' - ' . date('Y-m-d H:i:s');
					$creditRefounded->save();
				}
			}
		}
	}

	public function refundedAmount($ch) {
		if (!isset($this->_refundedAmount)) {
			$this->_refundedAmount = 0;
			foreach ($ch->refunds as $refund) {
				$this->_refundedAmount += $refund->amount;
			}
		}
		return $this->_refundedAmount;
	}

	public function tellDriverTheOrderWasCanceled(){
		$driver = $this->getDeliveryDriver();

		if( $driver->id_admin && $driver->phone ){

			$sendMessageToDriver = true;

			$status = $this->status();

			if( $status ){
				$last = $status->last();
				if( $last[ 'status' ] == 'delivered' ){
					$sendMessageToDriver = false;
				}
			}

			if( $sendMessageToDriver ){
				$message = "System notification: Sorry, " . $this->restaurant()->name . " order #" . $this->id_order . " from " . $this->name . " was just canceled. Please don't deliver it!";
				Crunchbutton_Support::createNewWarning(  [ 'body' => $message, 'phone' => $driver->phone, 'dont_open_ticket' => true ] );
				Crunchbutton_Message_Sms::send( [ 'to' => $driver->phone, 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_ORDER_CANCELED ] );
			}
		}

	}

	public function refund($amt = null, $note = null, $tell_driver = false) {

		if (!$this->refunded){

			if( $tell_driver ){
				$this->tellDriverTheOrderWasCanceled();
			}

			// Refund the gift
			$this->refundGiftFromOrder();

			if ( intval( $this->charged() ) > 0 ) {

				$paymentType = $this->paymentType();

				if ($this->pay_type == self::PAY_TYPE_CREDIT_CARD && $paymentType->card_type != Crunchbutton_User_Payment_Type::CARD_TYPE_CAMPUS_CASH) {

					switch ($this->processor) {
						case 'stripe':
						default:
							try {
								$params = $amt ? ['amount' => $amt * 100] : null;
								$ch = \Stripe\Charge::retrieve($this->txn);
								$re = $ch->refunds->create();

							} catch (Exception $e) {
								echo $e->getMessage();
								return (object)['status' => false, 'errors' => $e->getMessage()];
							}
							break;

						case 'balanced':
							try {
								// refund the debit
								// See #5191
								$amount = $this->charged();
								$amount = floatval( number_format( $amount, 2 ) );
								$amount = intval( $amount * 100 );
								$ch = Crunchbutton_Balanced_Debit::byId( $this->txn );

								Log::debug([
										'order' => $this->id_order,
										'action' => 'refund',
										'status' => 'trying to refund',
										'amount' => $amount
									]);

								$ch->refund( $amount );

							} catch (Exception $e) {

								// the amount is greater than the availble amount to debit
								if ($e->response->body->errors[0]->category_code == 'invalid-amount') {
									$amt = $this->refundedAmount($ch);

									Log::debug([
										'order' => $this->id_order,
										'action' => 'refund',
										'status' => 'refund amount too high',
										'refunded' => $amt
									]);

									$total = number_format($amt/100,2);
									if ($amt == $ch->amount && !$this->refunded) {
										// allow it to mark refunded
									} else {
										return (object)['status' => false, 'errors' => 'refund amount too high. refunded: '.$total.' of '.$this->final_price_plus_delivery_markup];
									}

								} else {
									var_dump($e);
									return (object)['status' => false, 'errors' => null];
								}
							}

							$res = false;
							try {
								// cancel the hold
								$hold = Crunchbutton_Balanced_CardHold::byOrder($this);
								Log::debug([
										'order' => $this->id_order,
										'action' => 'cancel hold',
										'status' => 'trying to cancel hold',
										'amount' => $amount
									]);
								if ($hold) {
									$res = $hold->void();
								}

							} catch (Exception $e) {
								// hold is already captured. no need to void
								if ($e->response->body->errors[0]->category_code == 'hold-already-captured') {

								} else {
									var_dump($e);
									return (object)['status' => false, 'errors' => null];
								}
							}
							if (!$res) {
								Log::debug([
									'order' => $this->id_order,
									'action' => 'refund',
									'status' => 'failed to void hold'
								]);
							}
							break;
					}
				}
			}

			$support = $this->getSupport();
			if ($support) {
				$support->addSystemMessage('Order refunded.');
			}
			$this->refunded = 1;
			$this->do_not_reimburse_driver = 1;
			$this->do_not_pay_driver = 1;
			$this->save();

			// saves an order transaction
			$transaction = new Crunchbutton_Order_Transaction;
			// needs to be changed when we start to do partial refund
			$transaction->amt = $this->charged();
			$transaction->type = Crunchbutton_Order_Transaction::TYPE_REFUNDED;
			$transaction->date = date( 'Y-m-d H:i:s' );;
			$transaction->note = $note;
			$transaction->id_order = $this->id_order;
			$transaction->id_user_payment_type = $this->id_user_payment_type;
			$transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
			$transaction->id_admin = c::user()->id_admin;
			$transaction->save();

			return (object)['status' => true];
		}
		return (object)['status' => false];
	}

	public function getSupport($create = false) {
		$support = Support::getSupportForOrder($this->id_order);
		if (!$support && $create) {
			$support = Crunchbutton_Support::createNewTicket([
				'id_order' => $this->id_order,
				'body' => 'Ticket created from admin panel.'
			]);
		}
		return $support;
	}

	public function refundedReason(){
		if( $this->refunded ){
			$transaction = Crunchbutton_Order_Transaction::getRefundedReason( $this->id_order );
			if( $transaction->id_order_transaction ){
				return $transaction;
			}
		}
		return false;
	}

	public function phone() {
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);

		return $phone;
	}

	// Gets the last order tipped by the user
	public static function lastTippedOrder( $id_user = null ) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;
		return self::q('select * from `order` where id_user=? and tip is not null and tip > 0 order by id_order desc limit 1 offset 0',[$id_user]);
	}

	public function lastTipByDelivery($id_user = null, $delivery ) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;
		if( $id_user ){
			$order = self::q('select * from `order` where id_user=? and delivery_type = ? and tip is not null order by id_order desc limit 1 offset 0', [$id_user, $delivery]);
			if( $order->tip ){
				return $order->tip;
			}
		}
		return null;
	}

	public static function lastDeliveredOrder($id_user = nul) {
		$id_user = ( $id_user ) ? $id_user : $this->id_user;
		if( $id_user ){
			$order = self::q("SELECT * FROM `order` WHERE id_user = ? AND delivery_type = 'delivery' ORDER BY id_order DESC LIMIT 1", [$id_user]);
			if( $order->id_order ){
				return Order::o( $order->id_order );
			}
		}
		return null;
	}

	public static function lastTip( $id_user = null ) {
			$last_order = self::lastTippedOrder( $id_user );
			if( $last_order->tip ){
				return $last_order->tip;
			}
			return null;
	}
	public static function lastTipType( $id_user = null ) {
			$last_order = self::lastTippedOrder( $id_user );
			if( $last_order->tip_type ){
				return strtolower( $last_order->tip_type );
			}
			return null;
	}

	public function agent() {
		return Agent::o($this->id_agent);
	}

	public function isNativeApp(){
		$agent = $this->agent();
		if( $agent->id_agent ){
			return $agent->isNativeApp();
		}
		return false;
	}

	public function isIPhone(){
		$agent = $this->agent();
		if( $agent->id_agent ){
			return $agent->isIPhone();
		}
		return false;
	}

	public function hasUserAlreadyOrderedUsingNativeApp(){
		return Crunchbutton_Agent::hasUserAlreadyOrderedUsingNativeApp( $this->phone );
	}

	public function wasLinkAlreadySent(){
		return Crunchbutton_Phone_Log::wasAppLinkAlreadySent( $this->phone );
	}

	// Issue #4262
	public function sendNativeAppLink(){
		if( $this->isIPhone() && !$this->hasUserAlreadyOrderedUsingNativeApp() && !$this->wasLinkAlreadySent() ){
			$message = "Enjoy your food, " . $this->name . ", and, next time, order faster with our app! \nhttp://_DOMAIN_/app";
			$num = $this->phone;
			Crunchbutton_Message_Sms::send( [
				'to' => $num,
				'message' => $message,
				'reason' => Crunchbutton_Message_Sms::REASON_APP_DOWNLOAD
			] );
		} else {
			Log::debug( [ 'action' => 'send native app link', 'phone' => $this->phone(), 'isPhone' => $this->isIPhone(), 'has used native app' => $this->hasUserAlreadyOrderedUsingNativeApp(), 'link already sent' =>$this->wasLinkAlreadySent(), 'type' => 'native app link' ] );
		}
	}

	public function community() {
		return Community::o($this->id_community);
	}

	public function hasGiftCard(){
		if( !$this->id_order ){
			 return 0;
		}
		$query = 'SELECT SUM( value ) as total FROM promo WHERE id_order_reference = ?';
		$row = Cana::db()->get( $query, [$this->id_order])->get(0);
		if( $row->total ){
			return $row->total;
		}
		return 0;
	}

	public function hasCredit(){
		$query = 'SELECT SUM( value ) as total FROM credit WHERE id_order_reference = ? AND ( credit_type = ? OR credit_type != ? ) AND id_promo IS NULL';
		$row = Cana::db()->get( $query ,[$this->id_order, Crunchbutton_Credit::CREDIT_TYPE_CASH, Crunchbutton_Credit::CREDIT_TYPE_POINT])->get(0);
		if( $row->total ){
			return $row->total;
		}
		return 0;
	}

	public function expectedByStealthFax() {
		$date = clone $this->date();
		$date->modify('+ 20 minute');
		return $date;
	}

	public function expectedBy() {

		// See #4306
		if( $this->restaurant()->delivery_service ){
			return $this->expectedByStealthFax();
		}

		$time = clone $this->date();
		$multipleOf = 15;
		$minutes = round( ( ( $time->format( 'i' ) + $this->restaurant()->delivery_estimated_time ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
		$minutes -= $time->format( 'i' );
		$time->modify( '+ ' . $minutes . ' minute' );
		return $time;
	}

	public static function totalOrdersByPhone( $phone ){
		$phone = Phone::clean( $phone );
		// referral phone test
		if( $phone == '_PHONE_' ){
			return 0;
		}
		$query = 'SELECT COUNT(*) AS total FROM `order` INNER JOIN phone using(id_phone) WHERE phone.phone = ?';
		$row = Cana::db()->get( $query, [$phone])->get(0);
		if( intval( $row->total ) ){
			return intval( $row->total );
		}
		return 0;
	}

	public static function totalOrdersByCustomer( $id_user ){
		$query = 'SELECT COUNT(*) AS total FROM `order` WHERE id_user = ?';
		$row = Cana::db()->get( $query, [$id_user])->get(0);
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

	public function status() {
		if (!$this->_statuss) {
			$this->_statuss = new Order_Status($this);
		}
		return $this->_statuss;
	}

	public function clearStatus(){
		$this->_statuss = null;
	}


	public function undoStatus() {
		$status = $this->status()->last();
		$status = 'delivery-'.$status['status'];

		switch ($status) {
			case Crunchbutton_Order_Action::DELIVERY_NEW:
			case Crunchbutton_Order_Action::DELIVERY_ACCEPTED:
				$newStatus = Crunchbutton_Order_Action::DELIVERY_REJECTED;
				break;
			case Crunchbutton_Order_Action::DELIVERY_PICKEDUP:
				$newStatus = Crunchbutton_Order_Action::DELIVERY_ACCEPTED;
				break;
			case Crunchbutton_Order_Action::DELIVERY_DELIVERED:
				$newStatus = Crunchbutton_Order_Action::DELIVERY_PICKEDUP;
				break;
			case Crunchbutton_Order_Action::DELIVERY_REJECTED:
				$newStatus = Crunchbutton_Order_Action::DELIVERY_NEW;
				break;
		}

		if (!$newStatus) {
			return false;
		}

		$this->setStatus($newStatus);

		return str_replace('delivery-', '', $newStatus);
	}


	public function setStatus($status, $notify = false, $admin = null, $note = null, $force = false, $rejectedTicket = true) {
		if (!$status) {
			return false;
		}

		if (!$admin) {
			$admin = c::user();
		}

		if( !$force ){
			if ($this->status()->driver() && $this->status()->driver()->id_admin != $admin->id_admin) {
				return false;
			}
		}

		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $admin->id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'note' => $note,
			'type' => $status
		]))->save();

		if ($notify) {
			// Notify customer about their driver
			$q = Queue::create([
				'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_YOUR_DRIVER,
				'id_order' => $this->id_order,
				'seconds' => 0
			]);
		}


		// Add/Remove pex card funds
		$q = Queue::create([
			'type' => Crunchbutton_Queue::TYPE_ORDER_PEXCARD_FUNDS,
			'id_order' => $this->id_order,
			'seconds' => 0
		]);

		if( $status == Crunchbutton_Order_Action::DELIVERY_REJECTED && $rejectedTicket ){
			Order_Action::ticketForRejectedOrder( $this->id_order );
		}


		// mark the order to be paid by commission structure
		if( $admin->openedCommunity() && !$this->isForcedToBeCommissioned( $admin->id_admin ) ){
			$this->markToBeCommissioned( $admin->id_admin );
		}

		if( $status == Crunchbutton_Order_Action::DELIVERY_CANCELED ){
			$this->tellDriverTheOrderWasCanceled();
		}

		return true;
	}

	public function markToBeCommissioned( $id_admin ){
		(new Order_Action([
			'id_order' => $this->id_order,
			'id_admin' => $id_admin,
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => Crunchbutton_Order_Action::FORCE_COMMISSION_PAYMENT
		]))->save();
	}

	public function isForcedToBeCommissioned( $id_admin = false ){
		return Crunchbutton_Order_Action::isForcedToBeCommissioned( $this->id_order, $id_admin );
	}

	public function pexcardFunds(){

		$order = Order::o( $this->id_order );

		$status = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE id_order = ? ORDER BY id_order_action DESC LIMIT 1', [ $order->id_order ] )->get( 0 );

		if( $status->id_order_action && $status->id_admin ){

			$driver = Admin::o( $status->id_admin );

			if( $driver->id_admin ){

				// Pexcard stuff - #3992
				$pexcard = $driver->pexcard();

				if( $pexcard->id_admin_pexcard ){

					$status = $status->type;

					switch ( $status ) {

						case Crunchbutton_Order_Action::DELIVERY_ACCEPTED:

								// Add $10 for the first accepted order - #3993
								$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $driver->id_admin );

								if( $shift->id_admin_shift_assign ){
									$pexcard->addShiftStartFunds( $shift->id_admin_shift_assign );
								}

								// https://github.com/crunchbutton/crunchbutton/issues/3992#issuecomment-70799809
								$loadCard = true;

								if( $order->pay_type == 'card' && $order->restaurant()->formal_relationship ){
									$loadCard = false;
								}

								if( $loadCard ){

									$pexcard->addFundsOrderAccepeted( $order->id_order );

									Log::debug([ 'actions' => 'pex card LOADED', 'id_order' => $order->id_order, 'type' => 'pexcard-load' ]);
								} else {
									Log::debug([ 'actions' => 'pex card NOT loaded', 'id_order' => $order->id_order, 'type' => 'pexcard-load' ]);

								}
							break;

						case Crunchbutton_Order_Action::DELIVERY_REJECTED:

							Log::debug([ 'actions' => 'pex card funds REMOVED', 'id_order' => $order->id_order, 'type' => 'pexcard-load' ]);

							$pexcard->removeFundsOrderRejected( $order->id_order );
							break;
					}
				}
			}
		}
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
						if ( $action->id_admin == $admin->id_admin ) {
							$act = 'rejected';
						}
						continue;
						break;
				}
			}
		}
		return $act;
	}

	// @legacy: should only be used on cbtn.io
	public function deliveryLastStatus(){
		$statuses = $this->deliveryStatus();
		if( $statuses[ 'delivered' ] ){
			return array( 'status' => 'delivered', 'name' => $statuses[ 'delivered' ]->name, 'id_admin' => $statuses[ 'delivered' ]->id_admin, 'order' => 3, 'date' => $statuses[ 'delivered_date' ], 'timezone' => $this->restaurant()->timezone  );
		}
		if( $statuses[ 'pickedup' ] ){
			return array( 'status' => 'pickedup', 'name' => $statuses[ 'pickedup' ]->name, 'id_admin' => $statuses[ 'pickedup' ]->id_admin,  'order' => 2, 'date' => $statuses[ 'pickedup_date' ], 'timezone' => $this->restaurant()->timezone );
		}
		if( $statuses[ 'accepted' ] ){
			return array( 'status' => 'accepted', 'name' => $statuses[ 'accepted' ]->name, 'id_admin' => $statuses[ 'accepted' ]->id_admin,  'order' => 1, 'date' => $statuses[ 'accepted_date' ], 'timezone' => $this->restaurant()->timezone );
		}
		return array ( 'status' => 'new', 'order' => 0 );
	}

	public function deliveryTimes(){
		$statuses = $this->deliveryStatus();
		if( $statuses[ 'delivered' ] ){
			return array( 'status' => 'delivered', 'name' => $statuses[ 'delivered' ]->name, 'id_admin' => $statuses[ 'delivered' ]->id_admin, 'order' => 3, 'date_pickedup' => $statuses[ 'pickedup_date' ], 'date_delivered' => $statuses[ 'delivered_date' ], 'timezone' => $this->restaurant()->timezone  );
		}
		if( $statuses[ 'pickedup' ] ){
			return array( 'status' => 'pickedup', 'name' => $statuses[ 'delivered' ]->name, 'id_admin' => $statuses[ 'delivered' ]->id_admin, 'order' => 2, 'date_pickedup' => $statuses[ 'pickedup_date' ], 'date_delivered' => $statuses[ 'delivered_date' ], 'timezone' => $this->restaurant()->timezone  );
		}
	}

	public function wasAcceptedByRep(){
		$query = "SELECT * FROM
								order_action ac
							WHERE
								ac.id_order = {$this->id_order}
							AND ( ac.type = '" . Crunchbutton_Order_Action::DELIVERY_PICKEDUP . "'
										OR ac.type = '" . Crunchbutton_Order_Action::DELIVERY_ACCEPTED . "'
										OR ac.type = '" . Crunchbutton_Order_Action::DELIVERY_REJECTED . "'
										OR ac.type = '" . Crunchbutton_Order_Action::DELIVERY_DELIVERED . "' )
							ORDER BY id_order_action DESC LIMIT 1";
		$action = Crunchbutton_Order_Action::q( $query );
		if( $action->count() > 0 ){
			$action = $action->get( 0 );
			if( $action->type == Crunchbutton_Order_Action::DELIVERY_REJECTED ){
				return false;
			}
			return true;
		}
		return false;
	}

	public function wasCanceled(){
		$lastStatus = $this->status()->last();
		if( $lastStatus && $lastStatus[ 'status' ] && $lastStatus[ 'status' ] == 'canceled' ){
			return true;
		}
		return false;
	}

	public function textCustomerAboutDriver(){

		$order = Crunchbutton_Order::o( $this->id_order );

		if( !$order->id_order ){
			return;
		}

		$this->_actions = false;

		$phone = $order->phone;
		$driver = $order->getDeliveryDriver();

		$firstName = Crunchbutton_Message_Sms::greeting( $order->user()->firstName() );

		if( $driver ){
			// Check if the order was rejected and change the message
			$action = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE type = ? AND id_order = ?', [Crunchbutton_Order_Action::DELIVERY_REJECTED, $this->id_order]);
			if( $action->count() > 0 ){
				$message = $firstName . "You've got a new driver! For order updates, text {$driver->nameAbbr()} at {$driver->phone}";
			} else {
				$message = $firstName . "Your driver today is {$driver->nameAbbr()}. For order updates, text {$driver->firstName()} at {$driver->phone}";
			}
			Crunchbutton_Message_Sms::send( [ 'to' => $phone, 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_DRIVER ] );
		}
	}

	public function hasGiftCardIssued(){
		// check if it has a gift card
		$promo = Crunchbutton_Promo::q('SELECT * FROM promo p WHERE p.id_order_reference = ?', [$this->id_order]);
		if( $promo->count() > 0 ){
			return true;
		}
		// check if it has credit
		$credit = Crunchbutton_Credit::q('SELECT * FROM credit c WHERE c.id_order_reference = ? AND ( c.credit_type = ? OR c.credit_type != ? )', [$this->id_order, Crunchbutton_Credit::CREDIT_TYPE_CASH, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
		if( $credit->count() > 0 ){
			return true;
		}
		return false;

	}

	public function getDeliveryDriver(){
		// for payment reasons the driver could be changed at payment time #3232
		$status = $this->status()->last();
		if( $status[ 'driver' ] && $status[ 'driver' ][ 'id_admin' ] ){
			return Admin::o( $status[ 'driver' ][ 'id_admin' ] );
		}
		return false;
	}

	public function deliveryExports() {
		return [
			'id_order' => $this->id_order,
			'uuid' => $this->uuid,
			'delivery-status' => [
				'delivered' => $this->deliveryStatus('delivery-delivered') ? $this->deliveryStatus('delivery-delivered') : false,
				'pickedup' => $this->deliveryStatus('delivery-pickedup') ? $this->deliveryStatus('delivery-pickedup') : false,
				'accepted' => $this->deliveryStatus('delivery-accepted') ? $this->deliveryStatus('delivery-accepted') : false,
			],
			'self-reply' => $this->deliveryReply(c::admin())
		];
	}

	public function driverInstructionsPaymentStatus(){
		// Clarify Cash/Credit Orders #4481
		if( $this->restaurant()->formal_relationship ){
			if( $this->pay_type == 'cash' ){
				$driver = c::user();
				if( $driver->id_admin && $driver->hasPexCard() ){
					return 'Pay restaurant with your own cash, not PEX';
				} else {
					return 'Pay the restaurant with own cash';
				}
			} else {
				return 'Do not pay the restaurant';
			}
		} else {
			$driver = c::user();
			if( $this->pay_type == 'cash' ){
				if( $driver->id_admin && $driver->hasPexCard() ){
					return 'Pay restaurant with your own cash, not PEX';
				} else {
					return 'Pay the restaurant with own cash';
				}
			} else {
				if( $driver->id_admin && $driver->hasPexCard() ){
					return 'Pay the restaurant with PEX card';
				} else {
					return 'Pay the restaurant';
				}
			}
		}
	}

	public function driverInstructionsFoodStatus(){
		// https://github.com/crunchbutton/crunchbutton/issues/2463#issue-28386594
		// #emergency #6809
		if( ( $this->restaurant()->id_restaurant != 313 && $this->restaurant()->id_restaurant != 316 )
			&& $this->restaurant()->formal_relationship || $this->restaurant()->order_notifications_sent ){
			return 'Food already prepared';
		} else {
			return 'Place the order yourself';
		}
	}

	// decodes 9 digit order #s
	public static function getByNinjaId($id) {
		$v = $id[0];
		$len = substr($id, -1);
		$id = substr($id, 1, -1);
		$pad = 5;

		$first = substr($id, 0, 2);
		$rest = substr(strrev(substr($id, 2)),$pad-$len);
		$id = $rest.$first;

		return $id;
	}

	// generates 9 digit order #s
	public function ninjaId($version = 1) {
		$id = $this->id;
		if ($this->id >= 10000000) {
			// @todo: ERROR!
		}
		$first = substr($id,-2);
		$rest = substr($id,0,-2);
		$pad = 5; // works until 10 million orders

		$ret =
			$version
			.$first
			.str_pad(strrev($rest),$pad,'0')
			.strlen($rest);

		return $ret;
	}

	// aliases
	public function subtotal(){
		return $this->price;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}

	public function deliveryStatus($type = null) {
		if (!$this->_actions) {
			$this->_actions = Order_Action::q('select * from order_action where id_order=? order by timestamp', [$this->id_order]);
			$this->_deliveryStatus = ['accepted' => false, 'delivered' => false, 'pickedup' => false];
			$acpt = [];

			foreach ($this->_actions as $action) {
				switch ($action->type) {
					case 'delivery-delivered':
						$this->_deliveryStatus['delivered'] = Admin::o($action->id_admin);
						$this->_deliveryStatus['delivered_date'] = $action->date()->format( 'g:i A' );
						break;

					case 'delivery-pickedup':
						$this->_deliveryStatus['pickedup'] = Admin::o($action->id_admin);
						$this->_deliveryStatus['pickedup_date'] = $action->date()->format( 'g:i A' );
						break;

					case 'delivery-accepted':
						$acpt[$action->id_admin] = true;
						$this->_deliveryStatus['accepted_date'] = $action->date()->format( 'g:i A' );
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


	public function eta($refresh = false) {
		if (!isset($this->_eta) || $refresh) {
			$eta = Order_Eta::q('select * from order_eta where id_order=? order by date desc limit 1', [$this->id_order])->get(0);
			if (!$eta->id_order_eta || $refresh) {
				$eta = Order_Eta::create($this);
			}
			$this->_eta = $eta;
		}
		return $this->_eta;
	}

	public function ordersExports() {
		$out = $this->exports();
		$out[ 'delivery_service' ] = intval( $out[ 'delivery_service' ] );
		$out['user'] = $this->user()->id_user ? $this->user()->exports() : null;
		$out['restaurant'] = $this->restaurant()->id_restaurant ? $this->restaurant()->exports() : null;
		$out['_community_name'] = $this->restaurant()->community()->name;
		$out['_community_permalink'] = $this->restaurant()->community()->permalink;
		$out['_driver_name'] = $this->status()->last()['driver']['name'];
		$out['_driver_id'] = $this->status()->last()['driver']['id_admin'];

		return $out;
	}

	public function checkIfOrderWasRefunded( $force = false ){
		if( $this->refunded && !$force ){
			return true;
		}
		if( $this->txn ){
			$env = ( $this->env == 'live' ) ? 'live' : 'dev';
			$api_key = c::config()->balanced->{$env}->secret;
			Balanced\Settings::$api_key = $api_key;
			$url = '/debits/' . $this->txn . '/refunds';
			$refund = json_decode( json_encode( ( object ) Balanced\Refund::get( $url ) ) );
			if( $refund && $refund->status && $refund->status == 'succeeded' ){
				$this->refunded = 1;
				$this->do_not_reimburse_driver = 1;
				$this->do_not_pay_driver = 1;
				$this->save();
				return true;
			}
			else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function ticketsForNotGeomatchedOrders(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 5 min' );
		$orders = Order::q( 'SELECT * FROM `order` WHERE date > ? AND ( geomatched IS NULL OR geomatched = 0 )', [ $now->format( 'Y-m-d H:i:s' ) ] );
		$pattern = "%s just did Place Order Anyway! Order details: Order %d in the %s community to this address %s. Please double check that this address is close enough to be delivered (if it's just slightly out of range it may be fine), and cancel the order if necessary. Thanks!";
		foreach( $orders as $order ){
			if( !$order->orderHasGeomatchedTicket() ){
					$message = sprintf( $pattern, $order->name, $order->id_order, $order->community()->name, $order->address );
					echo $message . "\n";
					Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message ] );
					$action = new Crunchbutton_Order_Action;
					$action->id_order = $order->id_order;
					$action->timestamp = date( 'Y-m-d H:i:s' );
					$action->type = Crunchbutton_Order_Action::TICKET_NOT_GEOMATCHED;
					$action->save();
			}
		}
	}

	public static function ticketForCampusCashOrder(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 5 min' );
		$orders = Order::q( 'SELECT * FROM `order` WHERE date > ? AND campus_cash = 1', [ $now->format( 'Y-m-d H:i:s' ) ] );
		$pattern = "Charge this customer now on Verifone in front room and mark as Already Charged from this Support ticket! - More info: %s just placed an %s (Campus Cash) Order! Order details: Order %s in the %s community to this address %s";
		foreach( $orders as $order ){
			if( !$order->orderHasCampusCashTicket() ){
					$campus_cash_name = $order->campusCashName();
					$message = sprintf( $pattern, $order->name, $campus_cash_name, $order->id_order, $order->community()->name, $order->address );
					echo $message . "\n";
					Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message, 'bubble' => true ] );
					$action = new Crunchbutton_Order_Action;
					$action->id_order = $order->id_order;
					$action->timestamp = date( 'Y-m-d H:i:s' );
					$action->type = Crunchbutton_Order_Action::TICKET_CAMPUS_CASH;
					$action->save();
			}
		}
		self::ticketToReminderToChargeCampusCashOrder();
	}

	public static function ticketToReminderToChargeCampusCashOrder(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 15 min' );
		$orders = Order::q( 'SELECT * FROM `order` WHERE date <= ? AND campus_cash = 1 AND id_order NOT IN ( SELECT id_order FROM order_transaction WHERE type = ? )', [ $now->format( 'Y-m-d H:i:s' ), Crunchbutton_Order_Transaction::TYPE_CAMPUS_CASH_CHARGED ] );
		$pattern = "Remind to charge this customer now on Verifone in front room and mark as Already Charged from this Support ticket!";
		foreach( $orders as $order ){
			if( !$order->orderHasCampusCashTicketReminder() && !$order->campus_cash_charged() ){
					$campus_cash_name = $order->campusCashName();
					$message = sprintf( $pattern, $order->name, $campus_cash_name, $order->id_order, $order->community()->name, $order->address );
					echo $message . "\n";
					Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message, 'bubble' => true ] );
					$action = new Crunchbutton_Order_Action;
					$action->id_order = $order->id_order;
					$action->timestamp = date( 'Y-m-d H:i:s' );
					$action->type = Crunchbutton_Order_Action::TICKET_CAMPUS_CASH_REMINDER;
					$action->save();
			}
		}
	}

	public function orderHasGeomatchedTicket(){
		$action = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE id_order = ? AND type = ? ORDER BY id_order_action DESC LIMIT 1', [ $this->id_order, Crunchbutton_Order_Action::TICKET_NOT_GEOMATCHED ] );
		if( $action->id_order_action ){
			return true;
		}
		return false;
	}

	public function orderHasCampusCashTicket(){
		$action = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE id_order = ? AND type = ? ORDER BY id_order_action DESC LIMIT 1', [ $this->id_order, Crunchbutton_Order_Action::TICKET_CAMPUS_CASH ] );
		if( $action->id_order_action ){
			return true;
		}
		return false;
	}

	public function orderHasCampusCashTicketReminder(){
		$action = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE id_order = ? AND type = ? ORDER BY id_order_action DESC LIMIT 1', [ $this->id_order, Crunchbutton_Order_Action::TICKET_CAMPUS_CASH_REMINDER ] );
		if( $action->id_order_action ){
			return true;
		}
		return false;
	}

	public function notifyDriverAboutCustomerChanges( $changes ){

		if( count( $changes ) ){

			$message = 'The order #' . $this->id_order . ' was updated:';
			if( $changes[ 'name' ] ){
				$message .= "\nName was changed to: " . $this->name;
			}
			if( $changes[ 'phone' ] ){
				$message .= "\nPhone was changed to: " . $this->phone;
			}
			if( $changes[ 'address' ] ){
				$message .= "\nAddress was changed to: " . $this->address;
			}

			$status = $this->status()->last();

			if( $status && $status[ 'driver' ] ){
				$driver = Admin::o( $status[ 'driver' ] );

				$notifications = $driver->getNotifications();

				$message = $driver->firstName() .': ' . $message;

				foreach( $notifications as $notification ){

					if( $notification->active ){
						switch ( $notification->type ) {
							case Crunchbutton_Admin_Notification::TYPE_SMS:
							case Crunchbutton_Admin_Notification::TYPE_PHONE:
							case Crunchbutton_Admin_Notification::TYPE_DUMB_SMS:
								$notification->sendSms( $this, $message );
								break;

							case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS:
								$notification->sendPushIos( $this, $message );
								break;

							case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID:
								// $notification->sendPushAndroid( $this, $message );
								break;
						}
					}
				}
			}

		}
	}

	public function save($new = false) {

		$new = $this->id_order ? false : true;

		$phone = Phone::byPhone( $this->phone );
		$this->id_phone = $phone->id_phone;

		parent::save();

		Event::emit([
			'room' => [
				'order.'.$this->id_order,
				'orders',
				'restaurant.'.$this->id_restaurant.'.orders',
				'user.'.$this->id_user.'.orders'
			]
		], $new ? 'create' : 'update', $this->ordersExports());
	}
}
