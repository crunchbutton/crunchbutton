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

		$this->_number    = $params['card']['number'];
		$this->_exp_month = $params['card']['month'];
		$this->_exp_year  = $params['card']['year'];

		$subtotal = 0;

		foreach ($params['cart'] as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d['id'];
			$subtotal += $dish->dish()->price;
			if ($d['options']) {
				foreach ($d['options'] as $o) {
					$option = new Order_Dish_Option;
					$option->id_option = $o;
					$subtotal += $option->option()->price;
//                    $subtotal += $option->option()->optionPrice($d['options']);
					$dish->_options[] = $option;
				}
			}

			$this->_dishes[] = $dish;
		}

		$this->id_restaurant = $params['restaurant'];

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

			$DeLorean = new TimeMachine($this->restaurant()->timezone);
			$debug    = [
				'type'       => 'closed',
				'now'        => $DeLorean->now(),
				'cart'       => $params['cart'],
				'params'     => $params,
				'restaurant' => $this->restaurant()->exports(['categories' => true, 'notifications' => true]),
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
			return $errors;
		}

		$user = c::user()->id_user ? c::user() : new User;

		if (!c::user()->id_user) {
			$user->active = 1;
		}

		// Save the user just to add to him the gift cards
		$user->save();
		$this->id_user = $user->id_user;

		if ( trim( $this->notes ) != '' ){
			$giftcards = Crunchbutton_Promo::validateNotesField( $this->notes, $this->id_restaurant );
			foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
				if( $giftcard->id_promo ){
					$giftcard->addCredit( $user->id_user );
				}
			}
			$this->notes = $giftcards[ 'notes' ];	
		}

		$res = $this->verifyPayment();

		if ( $res !== true ) {
			return $res['errors'];
		} else {
			$this->txn = $this->transaction();
		}

		if ($this->_customer->id) {
			switch (c::config()->processor) {
				case 'stripe':
				default:
					$user->stripe_id = $this->_customer->id;
					break;

				case 'balanced':
					$user->balanced_id = $this->_customer->id;
					break;
			}
		}

		$user->location_lat = $params['lat'];
		$user->location_lon = $params['lon'];

		$user->name = $this->name;
		$user->phone = $this->phone;

		if ($this->delivery_type == 'delivery') {
			$user->address = $this->address;
		}

		if ($this->pay_type == 'card' && $params['card']['number']) {
			$user->card = str_repeat('*',strlen($params['card']['number'])-4).substr($params['card']['number'],-4);
			$user->card_exp_year = $params['card']['year'];
			$user->card_exp_month = $params['card']['month'];
		}

		$user->pay_type = $this->pay_type;
		$user->delivery_type = $this->delivery_type;
		$user->tip = $this->tip;

		$this->env = c::env();
		$this->processor = c::config()->processor;

		$user->save();

		$user = new User($user->id_user);
		$this->_user = $user;

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
			switch (c::config()->processor) {
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

		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->id_community = $this->restaurant()->community()->id_community;
		$this->save();

		$this->que();

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

			foreach ($dish->options() as $option) {
				$option->id_order_dish = $dish->id_order_dish;
				$option->save();
			}
		}

		if ($params['make_default']) {
			Preset::cloneFromOrder($this);
		}

		Crunchbutton_Hipchat_Notification::OrderPlaced($this);

		return true;
	}

	public function calcFinalPriceMinusUsersCredit(){
		if( $this->pay_type == 'card' ){
			if( c::user()->id_user ){
				$chargedByCredit = Crunchbutton_Credit::calcDebitFromUserCredit( $this->final_price, $this->id_user, $this->id_restaurant, $this->id_order, true );
				$final = $this->final_price - $chargedByCredit;
				if( $final < 0 ){
					$final = 0;
				}
				return Util::ceil($final, 2);
			} 
		}
		return $this->final_price;
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
				switch (c::config()->processor) {
					case 'stripe':
					default:
						$charge = new Charge_Stripe([
							'stripe_id' => $user->stripe_id
						]);
						break;

					case 'balanced':
						$charge = new Charge_Balanced([
							'balanced_id' => $user->balanced_id
						]);
						break;
				}

				$amount = $this->calcFinalPriceMinusUsersCredit();
				// If the amount is 0 it means that the user used his credit.

				if( $amount > 0 ){
						$r = $charge->charge([
						'amount' => $amount,
						'number' => $this->_number,
						'exp_month' => $this->_exp_month,
						'exp_year' => $this->_exp_year,
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

		if ($search['restaurant']) {
			$query .= ' and `order`.id_restaurant="'.$search['restaurant'].'" ';
		}

		if ($search['community']) {
			$query .= ' and `order`.id_community="'.$search['community'].'" ';
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

	public function notify() {
		$order = $this;
		foreach ($order->restaurant()->notifications() as $n) {
			/* @var $n Crunchbutton_Notification */
			Log::debug([ 'order' => $order->id_order, 'action' => 'starting notification', 'notification_type' => $n->type, 'type' => 'notification']);
			$n->send($order);
		}
	}

	public function resend_notify(){
		$order = $this;
		Log::debug([ 'order' => $order->id_order, 'action' => 'restarting starting notification', 'notification_type' => $n->type, 'type' => 'notification']);
		$order->confirmed = 0;
		$order->save();
		// Delete all the notification log in order to start a new one
		Notification_Log::DeleteFromOrder( $order->id_order );
		Cana::timeout(function() use($order) {
			$order->notify();
		});
	}

	public function confirm() {

		if ($this->confirmed || !$this->restaurant()->confirmation) {
			return;
		}

		$nl = Notification_Log::q('SELECT * FROM notification_log WHERE id_order="'.$this->id_order.'" AND type = "confirm" AND ( status = "created" OR status = "queued" OR status ="success" ) ');
		if( $nl->count() > 0 ){
			// Log
			Log::debug([ 'order' => $this->id_order, 'count' => $nl->count(), 'action' => 'confirmation call already in process', 'host' => c::config()->host_callback, 'type' => 'notification']);
			return;
		} 

		$env = c::env() == 'live' ? 'live' : 'dev';
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
		$env = c::env() == 'live' ? 'live' : 'dev';
		//$num = ($env == 'live' ? $this->phone : c::config()->twilio->testnumber);
		$num = $this->phone;

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = str_split($this->message('selfsms'),160);

		$type = 'twilio';

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

	public function que() {
		$order = $this;
		Cana::timeout(function() use($order) {
			/* @var $order Crunchbutton_Order */
			$order->notify();
		});

		// if (!$this->restaurant()->confirmation) {
		c::timeout(function() use($order) {
			$order->receipt();
		}, 30 * 1000); // 30 seconds
		// }

		// Start the timer to check if the order was confirmed. #1049
		if ($this->restaurant()->confirmation) {
			$timer = c::config()->twilio->warningOrderNotConfirmedTime;
			// Log
			Log::debug( [ 'order' => $order->id_order, 'action' => 'que warningOrderNotConfirmed started', 'time' => $timer, 'type' => 'notification' ]);
			c::timeout(function() use($order) {
				$order->warningOrderNotConfirmed();
			}, $timer );
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
		Log::debug( [ 'order' => $this->id_order, 'action' => 'confirm', 'hasFaxNotification' => $order->restaurant()->hasFaxNotification(), 'confirmationTime' => $confirmationTime, 'confirmation number' => $nl->count(), 'confirmed' => $this->confirmed, 'type' => 'notification' ] );

		c::timeout(function() use($order) {
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

		$order = $this;

		$isConfirmed = Order::isConfirmed( $this->id_order );

		Log::debug( [ 'order' => $this->id_order, 'action' => 'warningOrderNotConfirmed', 'object' => $order->json(), 'type' => 'notification' ]);

		if ( $isConfirmed || !$this->restaurant()->confirmation ) {
			Log::debug( [ 'order' => $this->id_order, 'action' => 'que warningOrderNotConfirmed ignored', 'confirmed' => $isConfirmed, 'type' => 'notification' ]);
			return;
		}

		$date = $order->date();
		$date = $date->format( 'M jS Y' ) . ' - ' . $date->format( 'g:i:s A' );

		$env = c::env() == 'live' ? 'live' : 'dev';
		
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

		switch ($type) {
			case 'sms':
			case 'web':
				$with = 'w/';
				$space = ',';
				break;

			case 'phone':
				$with = '. ';
				$space = '.';
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
			
			// Facebook does not share the options
			if( $type == 'facebook' ){
				$foodItem .= '. ';
				$food .= $foodItem;
				continue;
			}

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
				$msg .= "Contact ".$this->restaurant()->shortName().": ".$this->restaurant()->phone().".\n";
				$msg .= "To contact Crunchbutton, text us back.\n\n";
				if ($this->pay_type == self::PAY_TYPE_CASH) {
					$msg .= "Remember to tip!\n\n";
				}
				break;

			case 'sms':
				$msg = "Crunchbutton #".$this->id_order."\n\n";
				$msg .= $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food."\n\nphone: ".preg_replace('/[^\d.]/','',$this->phone).'.';
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

		$out['_restaurant_name'] = $this->restaurant()->name;
		$out['_restaurant_permalink'] = $this->restaurant()->permalink;
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

		$date = new DateTime($this->date);
		$date->setTimeZone($timezone);
		
		$out['_date_tz'] = $date->format('Y-m-d H:i:s');
		$out['_tz'] = $date->format('T');

		return $out;
	}

	public function refundGiftFromOrder(){
		if( $this->chargedByCredit() ){
			$credits = Crunchbutton_Credit::creditByOrder( $this->id_order );
			if( $credits->count() > 0 ){
				foreach( $credits as $credit ){
					$creditRefounded = new Crunchbutton_Credit();
					$creditRefounded->id_user = $credit->id_user;
					$creditRefounded->type = Crunchbutton_Credit::TYPE_CREDIT;
					$creditRefounded->id_restaurant = $this->id_restaurant;
					$creditRefounded->date = date('Y-m-d H:i:s');
					$creditRefounded->value = $credit->value;
					$creditRefounded->id_order_reference = $this->id_order_reference;
					$creditRefounded->id_restaurant_paid_by = $this->id_restaurant_paid_by;
					$creditRefounded->paid_by = $this->paid_by;
					$creditRefounded->note = 'Value ' . $credit->value . ' refunded from order: ' . $this->id_order . ' - ' . date('Y-m-d H:i:s');
					$creditRefounded->save();
				}
			}
		}
	}

	public function refund() {
		if( $this->refunded < 1 ){
			// Refund the gift
			$this->refundGiftFromOrder();

			if( $this->charged() > 0 ){
				$env = c::env() == 'live' ? 'live' : 'dev';
				switch ($this->processor) {
					case 'stripe':
					default:
						Stripe::setApiKey(c::config()->stripe->{$env}->secret);
						$ch = Stripe_Charge::retrieve($this->txn);
						try {
							$ch->refund();
						} catch (Exception $e) {
							return false;
						}
					break;

					case 'balanced':
						$ch = Crunchbutton_Balanced_Debit::byId($this->txn);
						try {
							$ch->refund();
						} catch (Exception $e) {
							return false;
						}
						break;
				}
			} 
			$this->refunded = 1;
			$this->save();
			return true;
		}
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
		$order = self::q('select * from `order` where id_user="'.$id_user.'" and delivery_type = "' . $delivery . '" and tip is not null order by id_order desc limit 0,1');
		if( $order->tip ){
			return $order->tip;
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

	public function community() {
		return Community::o($this->id_community);
	}

	public function hasGiftCard(){
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

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}
